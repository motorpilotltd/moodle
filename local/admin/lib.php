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

class local_admin_user extends \core_user {
    public static function get_dummy_local_admin_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'localadminuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}

function local_admin_extend_navigation_course($nav, $course, $context) {
    global $DB, $PAGE, $USER;

    if (is_role_switched($course->id)) {
        if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
            // Build role-return link instead of logout link.
            $url = new moodle_url('/course/switchrole.php', array(
                'id' => $course->id,
                'sesskey' => sesskey(),
                'switchrole' => 0,
                'returnurl' => $PAGE->url->out_as_local_url(false)
            ));
            $icon = 'a/logout';
            $stringid = 'switchrolereturn';
        }
    } else {
        // Build switch role link.
        $roles = get_switchable_roles($context);
        if (is_array($roles) && (count($roles) > 0)) {
            $url = new moodle_url('/course/switchrole.php', array(
                'id' => $course->id,
                'switchrole' => -1,
                'returnurl' => $PAGE->url->out_as_local_url(false)
            ));
            $icon = 'i/switchrole';
            $stringid = 'switchroleto';
        }
    }

    if (!empty($url)) {
        $node = $nav->add(get_string($stringid), $url, navigation_node::TYPE_SETTING, null, 'switchroles', new pix_icon($icon, ''));
        $node->preceedwithhr = true;
    }
}