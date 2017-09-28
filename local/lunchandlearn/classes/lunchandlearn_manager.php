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

class lunchandlearn_manager {
    const TABLE_EVENT = 'event';

    public static function get_base_sql($extracols=array()) {
        $columns = array_merge(array(
            'l.*',
            'e.name',
            'e.description',
            'e.timestart'
        ), $extracols);

        return "
            SELECT ".implode(', ', $columns)."
            FROM
                {".  lunchandlearn::TABLE."} l
            JOIN
                {".self::TABLE_EVENT."} e
                ON e.id = l.eventid
        ";
    }

    public static function add_where(array $where) {
        if (count($where) > 0) {
            return "WHERE (" . implode(') AND (', $where) . ')';
        }
        return '';
    }

    public static function get_user_sessions($user=0, DateTime $from = null, $limitfrom= 0, $limitto=0) {
        global $DB, $USER;
        if (empty($user)) {
            $user = $USER;
        }
        $sql = self::get_base_sql()
        . "
        JOIN
            {" . lunchandlearn_attendance::TABLE . "} a
            ON a.lunchandlearnid = l.id
";
        $where = array();
        $where[] = 'l.cancelled = 0';
        $where[] = 'a.userid = '.$user->id;
        if (!empty($from)) {
            $where[] = "e.timestart > " . $from->getTimestamp();
        }
        $sql .= self::add_where($where);
        $sql .= ' ORDER BY e.timestart';

        return $DB->get_records_sql($sql, null, $limitfrom, $limitto);
    }

    public static function get_markable_categories() {
        global $USER, $DB;

        if (has_any_capability(array('local/lunchandlearn:mark', 'local/lunchandlearn:global'), context_system::instance())) {
            return $DB->get_records('course_categories');
        }

        $sql = <<<EOS
SELECT
    instanceid as categoryid
FROM
    {context} cx
JOIN
    {role_assignments} ra
    ON ra.contextid = cx.id
JOIN
    {role_capabilities} rc
    ON rc.roleid = ra.roleid
    AND capability = ?
WHERE
    ra.userid = ?
    AND contextlevel = ?
EOS;

        return $DB->get_records_sql($sql, array(
                    'local/lunchandlearn:mark',
                    $USER->id,
                    CONTEXT_COURSECAT));
    }

    public static function get_marking_list($status, $orderby = 'e.timestart DESC') {
        if (has_capability('local/lunchandlearn:global', context_system::instance())) {
            $coursecats = array(); // Empty array will show all.
        } else {
            // Find categories that a user hold a capability in.
            $coursecats = array_keys(self::get_markable_categories());
        }

        if ('closed' === $status) {
            $from = null;
            $to = new DateTime();
            $showcancelled = false;
        } else if ($status === 'open') {
            $from = new DateTime();
            $to = null;
            $showcancelled = false;
        } else {
            $from = null;
            $to = null;
            $showcancelled = true;
        }

        return self::get_sessions($from, 0, $coursecats, $to, $showcancelled, $orderby);
    }

    public static function search_sessions($searchtext = '', $region = 0, $page = 1, $perpage = 10)
    {
        global $DB;

        $sql = self::get_base_sql();

        $from = new DateTime("now");

        $where[] = "e.name LIKE :searchtext1 OR l.summary LIKE :searchtext2";
        $where[] = "e.timestart >= " . $from->getTimestamp();
        $where[] = 'l.cancelled = 0';

        if ($region < 0) {
            $where[] = "l.regionid = ''"; // Global events
        }
        elseif (false === empty($region)) {
            $where[] = "l.regionid = $region";
        }

        $sql .= self::add_where($where);

        $limitfrom = $page - 1;
        if ($limitfrom < 0) {
            $limitfrom = 0;
        }
        $limitto = $limitfrom + $perpage;
        if ($limitto < $limitfrom) {
            $limitto = $limitfrom + 10; // sensible default
        }
        $searchtext = "%$searchtext%";
        $allresults = $DB->get_recordset_sql($sql, array('searchtext1' => $searchtext, 'searchtext2' => $searchtext));

        $sessions = array();
        $counter = 0;

        foreach ($allresults as $result) {
            if ($counter >= $limitfrom && $counter < $limitto) {
                //$lal = new lunchandlearn();
                //$lal->bind($result);
                $sessions[] = $result;
            }
            $counter++;
        }

        return array('total' => $counter, 'sessions' => $sessions);
    }


    public static function get_sessions(
            DateTime $from = null, $region = 0, $coursecat = 0, DateTime $to = null,
            $showcancelled = false, $orderby = 'e.timestart DESC', $limitfrom=0, $limitto=0) {
        global $DB;

        $sql = self::get_base_sql();

        $where = array();
        if (!empty($from)) {
            $where[] = "e.timestart >= " . $from->getTimestamp();
        }
        if (!empty($region)) {
            $where[] = "l.regionid IN (0," . $region . ")";
        }
        if (!empty($coursecat)) {
            if (is_array($coursecat)) {
                $where[]  = "l.categoryid IN (" . implode(',', $coursecat). ')';
            } else {
                $where[]  = "l.categoryid = " . $coursecat;
            }
        }
        if (!empty($to)) {
            $where[] = "e.timestart <= " . $to->getTimestamp();
        }
        if (false === $showcancelled) {
            $where[] = 'l.cancelled = 0';
        }
        $sql .= self::add_where($where);

        // Order by.
        if (!empty($orderby)) {
            $sql .= " ORDER BY $orderby";
        }

        return $DB->get_records_sql($sql, null, $limitfrom, $limitto);
    }

    public static function get_session_attendees($sessionid) {
        $lunchandlearn = new lunchandlearn($sessionid);
        return $lunchandlearn->attendeemanager->get_attendees();
    }

    public static function take_attendance($sessionid, $users, $extra = null, $locked = false) {
        global $DB;
        $lal = new lunchandlearn($sessionid);
        if (!$locked) {
            $resetsql = "
                    UPDATE {" . lunchandlearn_attendance::TABLE . "}
                        SET status=" . lunchandlearn_attendance::STATUS_ATTENDING . "
                    WHERE lunchandlearnid = ".$sessionid."
                    AND   status=" . lunchandlearn_attendance::STATUS_ATTENDED;
            $DB->execute($resetsql);
            $lal->attendeemanager->load_attendees();
        }

        foreach ($lal->attendeemanager->get_attendees() as $user) {
            if (!empty($users[$user->userid]) && !$lal->attendeemanager->did_attend($user->userid)) {
                $lal->attendeemanager->attended($user->userid);
                self::add_cpd($lal, $user, $extra);
            }
        }

        // Lock.
        $lal->lock();
    }

    /**
     * Parse a mail string stored in a setting name and return
     */
    public static function parse_mail_string($settingname, lunchandlearn $lal, $userid=2) {
        global $DB, $CFG;

        $user = $DB->get_record(lunchandlearn_attendance_manager::TABLE_USER, array('id' => $userid));

        $html = preg_replace_callback('/[{]{2}([^}]+)[}]{2}/', function ($match) use ($lal, $user) {
            if (empty($match[1])) {
                return;
            }
            $bits = explode('.', $match[1]);
            try {
                if (count($bits) === 2) {
                    return lunchandlearn_manager::replace_object_string(array(
                        'lunchandlearn' => $lal,
                        'scheduler' => $lal->scheduler,
                        'attendees' => $lal->attendeemanager,
                        'user' => $user,
                        'attendee' => $lal->attendeemanager->get_by_user($user->id, false)
                    ), $bits);
                } else {
                    // Define some convenience methods.
                    switch ($bits[0]) {
                        case 'fullname':
                            return fullname($user);
                        case 'sessionname':
                            return $lal->get_name();
                        case 'date':
                            return userdate($lal->scheduler->get_date());
                        case 'cancelurl':
                            return new moodle_url('/local/lunchandlearn/signup.php', array('action' => 'cancel', 'id' => $lal->get_id()));
                        case 'cancellationnote':
                            return $lal->attendeemanager->get_by_user($user->id, false)->notes;
                    }
                }
            } catch (Exception $ex) {
                error_log($ex->getMessage());
                return '[['.$match[1].']]'; // Don't let exceptions stop the email or cancellation.
            }
        }, $CFG->$settingname);

        $plain = strip_tags(str_replace(array("<br />", "<br>", "<br/>"), "\n", str_replace('</p>', "\n\n", $html)));

        return array($plain, $html);
    }

    public static function replace_object_string(array $objects, $bits) {
        // Sometimes we can pass params using | syntax, e.g. object.method|param1|param2.
        if (!isset($objects[$bits[0]])) {
            $objs = implode(',', array_keys($objects));
            throw new Exception("Unable to parse replacement string, missing object named '{$bits[0]}'. Objects available [$objs]");
        }
        $object = $objects[$bits[0]];
        $method = $bits[1];
        $params = explode('|', $bits[1]);
        if (count($params) > 1) {
            $method = array_shift($params);
        }
        if (property_exists($object, $method)) {
            return $object->$method;
        }
        return call_user_func_array(array($object, $method), $params);
    }

    public static function admin_bulk_cancel_meeting_request(lunchandlearn $lal, $user) {
        self::cancel_meeting_request($lal, $user, 'lunchandlearnadminbulkcancelemailsubject', 'lunchandlearnadminbulkcancelemail');
    }

    public static function admin_cancel_meeting_request(lunchandlearn $lal, $user) {
        self::cancel_meeting_request($lal, $user, 'lunchandlearnadmincancelemailsubject', 'lunchandlearnadmincancelemail');
    }

    public static function cancel_meeting_request(lunchandlearn $lal, $user, $langsubject='lunchandlearncancelemailsubject', $langbody='lunchandlearncancelemail') {
        global $CFG;
        require_once($CFG->dirroot . '/local/invites/requester.php');

        list($plain, $html) = self::parse_mail_string($langbody, $lal, $user->id);

        $invite = new invite('', get_string($langsubject, 'local_lunchandlearn', $lal->get_name()), $html, $plain);
        $invite->set_id($lal->get_id().'-'.$user->id);
        $invite->set_url($lal->get_cal_url('full'));
        $invite->set_as_cancelled();
        $invite->add_organizer(new organizer(get_admin()));
        $invite->add_recipient(new invitee($user));
        $date = new DateTime();
        $date->setTimestamp($lal->scheduler->get_date());
        $date->setTimezone(new DateTimeZone('UTC')); // Send the vcal in UTC.
        $invite->set_date(
                $date,
                DateInterval::createFromDateString((int)$lal->scheduler->get_duration(). ' mins'));

        new vcal_requester($invite, 'CANCEL');
    }

    public static function send_meeting_request(lunchandlearn $lal, $user) {
        global $CFG;
        require_once($CFG->dirroot . '/local/invites/requester.php');
        $location = $lal->scheduler->get_office();
        $room = $lal->scheduler->get_room();
        if (!empty($room)) {
            $location .= " - $room";
        }

        list($plain, $html) = self::parse_mail_string('lunchandlearnsignupemail', $lal, $user->id);

        $invite = new invite($location, get_string('lunchandlearnsignupemailsubject', 'local_lunchandlearn', $lal->get_name()), $html, $plain);
        $invite->set_id($lal->get_id().'-'.$user->id);
        $invite->set_url($lal->get_cal_url('full'));
        $invite->add_organizer(new organizer(get_admin()));
        $invite->add_recipient(new invitee($user));
        $date = new DateTime();
        $date->setTimestamp($lal->scheduler->get_date());
        $date->setTimezone(new DateTimeZone('UTC')); // Send the vcal in UTC.
        $invite->set_date(
                $date,
                DateInterval::createFromDateString($lal->scheduler->get_duration(). ' mins'));
        new vcal_requester($invite);
    }

    public static function add_cpd(lunchandlearn $session, $user, $extra=null) {
        $taps = new \local_taps\taps();

        $desc =!empty($extra->p_learning_desc['text']) ? $extra->p_learning_desc['text'] : $session->get_summary();

        $cpdparams = array(
                'p_organization_name' => null,
                'p_location' => $session->scheduler->get_office(),
                'p_learning_method' => isset($extra->p_learning_method) ? $extra->p_learning_method : 'LUNCH_AND_LEARN',
                'p_subject_catetory' => 'PD',
                'p_course_start_date' => $session->scheduler->get_date(),
                'p_learning_desc' => $desc,
                'p_health_and_safety_category' => null
        );

        $result = $taps->add_cpd_record(
            $user->idnumber,
            $session->get_name(),
            $session->get_supplier(),
            $session->scheduler->get_date(),
            $session->scheduler->get_duration(),
            'MIN',
            $cpdparams
        );

        if ($result === false) {
            // Inputs.
            debugging('INPUTS: '. print_r(array(
                'user id: ' => $user->idnumber,
                'class name: ' => $session->get_name(),
                'provider: ' => $session->get_supplier(),
                'date: ' => strtoupper(date('d-M-Y')),
                'duration' => $session->scheduler->get_duration()
            ), true)
            . print_r($cpdparams, true), DEBUG_DEVELOPER);
            // Outputs.
            debugging('RESULT: '.print_r($result, true), DEBUG_DEVELOPER);
            throw new Exception('CPD add failed for user '.$user->idnumber);
        }
    }
}