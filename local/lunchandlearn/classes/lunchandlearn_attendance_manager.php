<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles attendance and sign-ups for lunchandlearns
 *
 * @author paulstanyer
 */
class lunchandlearn_attendance_manager extends lal_base {

    const TABLE_USER = 'user';

    protected $lunchandlearn;
    protected $attendees = array();

    // Contain y/n.
    protected $availableinperson;
    protected $capacity;
    protected $overbookinperson;

    // Contain y/n.
    protected $availableonline;
    protected $onlinecapacity; // 0 -> no limit.
    protected $overbookonline;

    public function __construct(lunchandlearn $lal, $attendancedata = null) {
        $this->lunchandlearn = $lal;
        if (empty($attendancedata)) {
            $this->set_defaults();
            return;
        }
        $this->availableinperson = $attendancedata->availableinperson;
        $this->capacity = $attendancedata->capacity;
        $this->overbookinperson = $attendancedata->overbookinperson;

        $this->availableonline = $attendancedata->availableonline;
        $this->onlinecapacity = $attendancedata->onlinecapacity;
        $this->overbookonline = $attendancedata->overbookonline;

        $this->set_capacity($attendancedata->capacity);
    }

    protected function set_defaults() {

    }

    public function get_capacity() {
        return $this->capacity;
    }

    public function is_fully_subscribed() {
        return $this->get_capacity() > 0
               && ($this->get_attendee_count() >= $this->get_capacity());
    }

    public function set_capacity($capacity) {
        $this->capacity = $capacity;
    }

    public function get_usersignups() {
        global $DB;
        return $DB->get_records('local_lunchlearn_attendees', array('lunchandlearnid' => $this->lunchandlearn->id));
    }

    public function is_user_signedup($userid, $checkcancelled=true) {
        if (empty($this->attendees)) {
            $this->load_attendees();
        }

        if (empty($this->attendees[$userid])) {
            return false;
        }
        if ($checkcancelled && $this->attendees[$userid]->has_cancelled()) {
            return false;
        }
        return true;
    }

    public function get_by_user($userid, $checkcancelled=true) {
        if (false === $this->is_user_signedup($userid, $checkcancelled)) {
            throw new Exception('There is no attendance record for this user');
        }
        return $this->attendees[$userid];
    }

    public function get_mailto() {
        $mailto = array();
        if ($this->lunchandlearn->scheduler->is_cancelled()) {
            $filter = lunchandlearn_attendance::STATUS_ATTENDING + lunchandlearn_attendance::STATUS_ATTENDED + lunchandlearn_attendance::STATUS_CANCELLED;
        } else {
            $filter = lunchandlearn_attendance::STATUS_ATTENDING + lunchandlearn_attendance::STATUS_ATTENDED;
        }

        foreach ($this->get_attendees($filter) as $attendee) {
            $mailto[] = rawurlencode($attendee->get_user()->email.';');
        }
        if (!empty($mailto)) {
            return 'mailto:' . implode('', $mailto) . '?subject=' . rawurlencode(get_string('emailattendeessubject', 'local_lunchandlearn', $this->lunchandlearn->name));
        } else {
            return false;
        }
    }

    public function cancel_signup($user, $notes='') {
        if (is_object($user)) {
            $user = $user->id;
        }
        if (true === $this->is_user_signedup($user)) {
            $this->attendees[$user]->cancel($notes);
        }
    }

    public function did_attend($userid=0) {
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        if (empty($userid)) {
            return false;
        }
        if (empty($this->attendees)) {
            $this->load_attendees();
        }
        if (empty($this->attendees[$userid])) {
            return false;
        }
        return $this->attendees[$userid]->has_attended();
    }

    public function attended($userid) {
        if ($this->is_user_signedup($userid)) {
            $this->attendees[$userid]->attended();
        }
    }

    public function signup($user, $data) {
        if (false === $this->is_user_signedup($user->id)) {
            // Potentially delete any cancelled records to avoid duplications.
            if (!empty($this->attendees[$user->id]) && $this->attendees[$user->id]->has_cancelled()) {
                $this->attendees[$user->id]->delete();
                unset($this->attendees[$user->id]);
            }
            $attendance = new stdClass();
            $attendance->lunchandlearnid = $this->lunchandlearn->id;
            $attendance->userid = $user->id;
            $attendance->idnumber = $user->idnumber;
            if (isset($data->requirements)) {
                $attendance->requirements = $data->requirements;
            }
            $attendance->inperson = !empty($data->inperson);

            if (($attendance->inperson == true && !$this->has_inperson_capacity())
                    || ($attendance->inperson == false && !$this->has_online_capacity())) {
                throw new Exception('Attempt to add attendee failed due to capacity limit reached');
            }

            $this->attendees[$user->id] = new lunchandlearn_attendance($attendance);
            $this->attendees[$user->id]->save();
        }
    }

    public function load_attendees() {
        global $DB;
        $usernamefields = get_all_user_name_fields(true, 'u');
        $sql = "
        SELECT
            a.*,
            {$usernamefields}, u.email, u.idnumber
        FROM {" . lunchandlearn_attendance::TABLE . "} a
        JOIN {" . self::TABLE_USER. "} u
            ON a.userid = u.id
        WHERE
            a.lunchandlearnid = :sessionid
        ORDER BY
            a.status DESC, u.lastname ASC, u.firstname ASC";

        $results = $DB->get_records_sql($sql, array('sessionid' => $this->lunchandlearn->id));
        foreach ($results as $result) {
            $this->attendees[$result->userid] = new lunchandlearn_attendance($result);
        }
    }

    /**
     * Places booked with capcity if selected
     */
    public function get_inperson_attendance_string() {
        if (false == $this->availableinperson) {
            return '';
        }
        $count = 0;
        foreach ($this->get_attendees() as $attendee) {
            if ($attendee->inperson == true) {
                $count++;
            }
        }

        if (!empty($this->capacity)) {
            $count .= ' out of '.$this->capacity;
            $count .= ' - ' . get_string('overbooking'.(empty($this->overbookinperson) ? 'not' : '').'permitted', 'local_lunchandlearn');
        }

        return $count;
    }

    public function get_attendance_count_by_location($location) {
        $count = 0;
        foreach ($this->get_attendees() as $attendee) {
            if ($attendee->inperson == ($location == 'inperson')) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Places booked with capacity if selected
     */
    public function get_online_attendance_string() {
        if (false == $this->availableonline) {
            return '';
        }

        $count = $this->get_attendance_count_by_location('online');

        if (!empty($this->onlinecapacity)) {
            $count .= ' out of ' . $this->onlinecapacity;
            $count .= ' - ' . get_string('overbooking'.(empty($this->overbookonline) ? 'not' : '').'permitted', 'local_lunchandlearn');
        }

        return $count;
    }

    public function has_inperson_capacity() {
        if (false == $this->availableinperson) {
            return false;
        }
        if ($this->overbookinperson) {
            return true;
        }
        if (empty($this->capacity)) {
            return true; // 0 capacity special case, means no limit.
        }
        return $this->get_attendance_count_by_location('inperson') < $this->capacity;
    }

    public function get_remaining_inperson($limit = 0) {
        $remain = $this->capacity - $this->get_attendance_count_by_location('inperson');
        if (!empty($limit)) {
            if (empty($this->capacity)) {
                return $limit.'+';
            }
            return $remain > $limit ? $limit.'+' : $remain;
        }
        return $remain;
    }

    public function has_online_capacity() {
        if (false == $this->availableonline) {
            return false;
        }
        if ($this->overbookonline) {
            return true;
        }
        if (empty($this->onlinecapacity)) {
            return true; // 0 capacity special case, means no limit.
        }
        return $this->get_attendance_count_by_location('online') < $this->onlinecapacity;
    }

    public function get_remaining_online($limit=0) {
        $remain = $this->onlinecapacity - $this->get_attendance_count_by_location('online');
        if (!empty($limit)) {
            if (empty($this->onlinecapacity)) {
                return $limit.'+';
            }
            return $remain > $limit ? $limit.'+' : $remain;
        }
        return $remain;
    }

    public function get_attendees($filter = 3 /* STATUS_ATTENDING + STATUS_ATTENDED */) {
        if (empty($this->attendees)) {
            $this->load_attendees();
        }
        $attendees = array();
        foreach ($this->attendees as $userid => $attendee) {
            // This is a bitmask.
            if ($filter & $attendee->status) {
                $attendees[$userid] = $attendee;
            }
        }
        return $attendees;
    }

    public function get_attendee_count($filter = 3 /* STATUS_ATTENDING + STATUS_ATTENDED */) {
        return count($this->get_attendees($filter));
    }

    public function get_attendance() {
        return $this->get_attendee_count() . ' '
                . get_string('outof', 'local_lunchandlearn') . ' '
                . $this->get_capacity();
    }

    public function get_potential_attendees($filter = '', $limit = 0) {
        global $DB;

        $usertable = self::TABLE_USER;
        $attendeetable = lunchandlearn_attendance::TABLE;
        $canx = lunchandlearn_attendance::STATUS_CANCELLED;

        $fullname = $DB->sql_concat('firstname', "' '", 'lastname');

        $sql = <<<EOS
SELECT
    u.id,
    lastname,
    firstname,
    idnumber
FROM {{$usertable}} u
LEFT JOIN {{$attendeetable}} a
    ON u.id = a.userid
    AND a.lunchandlearnid = :lalid
WHERE
    (a.id IS NULL OR a.status = $canx)
    AND (
        lastname LIKE '%$filter%'
        OR firstname LIKE '%$filter%'
        OR idnumber LIKE '%$filter%'
        OR $fullname LIKE '%$filter%'
     )
ORDER BY
    u.lastname
EOS;
        $userlist = array();
        foreach ($DB->get_records_sql($sql, array('lalid' => $this->lunchandlearn->id), 0, $limit) as $user) {
            $userlist[$user->id] = "$user->firstname $user->lastname ($user->idnumber)";
        }
        return $userlist;
    }

    public function has_capacity($includeoverbooking=true) {
        // If we consider overbooking as capacity.
        if ($includeoverbooking&&($this->availableinperson&&$this->overbookinperson)
                ||($this->availableonline&&$this->overbookonline)) {
            return true;
        }
        return $this->has_inperson_capacity()||$this->has_online_capacity();
    }
}
