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

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

try {
    require_login();
    require_sesskey();

    $userid = required_param('userid', PARAM_INT);

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/blocks/certification_report/ajax/user_info.php');

    $castidnumber = $DB->sql_cast_char2int('u.idnumber');
    $sql = "SELECT * FROM {user}  u
        JOIN SQLHUB.ARUP_ALL_STAFF_V h ON h.EMPLOYEE_NUMBER = {$castidnumber}
        WHERE u.id = :userid
        ";
    $user = $DB->get_record_sql($sql, ['userid' => $userid]);
    $cohortssql = "SELECT c.id, c.name
                     FROM {cohort} c
                     JOIN {cohort_members} cm ON c.id = cm.cohortid
                     JOIN {user} u ON cm.userid = u.id
                    WHERE u.id = :userid";
    // Re-index for mustache.
    $user->cohorts = array_values($DB->get_records_sql_menu($cohortssql, ['userid' => $userid]));
    $user->hascohorts = !empty($user->cohorts);
    $user->userpic = $OUTPUT->user_picture($user, array('size' => 100, 'link' => true));
    echo $OUTPUT->render_from_template('block_certification_report/user_info', $user);
} catch (Exception $e) {
    echo $e->getMessage();
}
exit;