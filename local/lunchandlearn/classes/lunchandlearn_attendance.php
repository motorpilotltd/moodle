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
 * Handles an attendance, by a user, on a lunch and learn
 *
 * @author paulstanyer
 */
class lunchandlearn_attendance extends lal_base {

    // These should be powers of 2 so that we can bitmask filter them.
    const STATUS_ATTENDING = 1;
    const STATUS_ATTENDED = 2;
    const STATUS_CANCELLED = 4;

    const TABLE = 'local_lunchlearn_attendees';

    protected $id;
    protected $lunchandlearnid;
    protected $userid;
    protected $idnumber;
    protected $status;
    protected $notes;
    protected $requirements;
    protected $inperson;
    protected $usersignedup; /* which user did the sign-up (could be admin for instance) */
    protected $timesignup;
    protected $userupdated;
    protected $timeupdated;

    private $user;
    private $signupuser;

    public function __construct($data) {
        global $USER;
        $this->id = isset($data->id) ? $data->id : 0;
        $this->lunchandlearnid = $data->lunchandlearnid;
        $this->userid = $data->userid;
        $this->idnumber = $data->idnumber;
        $this->status = isset($data->status) ? $data->status : self::STATUS_ATTENDING;
        $this->notes = isset($data->notes) ? $data->notes : '';
        $this->requirements = isset($data->requirements) ? $data->requirements : '';
        $this->inperson = isset($data->inperson) ? $data->inperson : 1;
        $this->usersignedup = isset($data->usersignedup) ? $data->usersignedup : $USER->id;
        $this->timesignup = isset($data->timesignup) ? $data->timesignup : time();
        $this->userupdated = isset($data->userupdated) ? $data->userupdated : 0;
        $this->timeupdated = isset($data->timeupdated) ? $data->timeupdated : 0;
    }

    public function get_icon() {
        $name = ($this->inperson) ? lunchandlearn::ICON_INPERSON : lunchandlearn::ICON_ONLINE;
        return "<i class=\"fa fa-fw fa-$name\"></i>";
    }

    public function get_user() {
        global $DB;
        if (empty($this->user)) {
            $this->user = $DB->get_record('user', array('id' => $this->userid));
        }
        return $this->user;
    }

    public function get_signup_userdate() {
        return userdate($this->timesignup);
    }

    public function get_signup_datetime() {
        return DateTime::createFromFormat('U', $this->timesignup);
    }

    public function did_self_enrol() {
        if ($this->usersignedup == 0) {
            return true; // Allow for zero values from old system (unknown).
        }
        return $this->userid == $this->usersignedup;
    }

    public function get_signup_userfull() {
        global $DB;
        if (empty($this->signupuser)) {
            $this->signupuser = $DB->get_record('user', array('id' => $this->usersignedup));
        }
        return fullname($this->signupuser);
    }

    public function delete() {
        global $DB;
        $DB->delete_records(self::TABLE, array('id' => $this->id));
    }

    public function save() {
        global $DB;

        $attendance = get_object_vars($this);

        if (empty($this->id)) {
            $this->id = $DB->insert_record(self::TABLE, $attendance);
        } else {
            $DB->update_record(self::TABLE, $attendance);
        }
    }

    public function cancel($notes = '') {
        $this->status = self::STATUS_CANCELLED;
        if (!empty($notes)) {
            $this->notes .= $notes;
        }
        $this->save();
    }

    public function attended() {
        $this->status = self::STATUS_ATTENDED;
        $this->save();
    }

    public function has_attended() {
        return $this->status == self::STATUS_ATTENDED;
    }

    public function has_cancelled() {
        return $this->status == self::STATUS_CANCELLED;
    }
}
