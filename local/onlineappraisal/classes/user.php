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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

class user extends \core_user {
    public static function get_dummy_appraisal_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'appraisaluser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }

    public static function loginas_check() {
        global $USER;
        // Restrict access for users logged in at course level.
        if (\core\session\manager::is_loggedinas() && $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
            if ($USER->loginascontext->contextlevel == CONTEXT_COURSE) {
                $a = new \stdClass();
                $a->coursename = get_course($USER->loginascontext->instanceid)->fullname;
                $a->courseurl = (new \moodle_url('/course/view.php', array('id' => $USER->loginascontext->instanceid)))->out(false);
                $a->loggedinas = fullname($USER);
                throw new \moodle_exception('error:loggedinas', 'local_onlineappraisal', '', $a);
            }
        }
    }
}