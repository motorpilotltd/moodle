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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');
require_once($CFG->dirroot . '/local/lunchandlearn/bulk_attendance_upload_form.php');
require_once($CFG->dirroot . '/local/lunchandlearn/bulk_attendance_process_form.php');

admin_externalpage_setup('lunchandlearnlist');

$session = new lunchandlearn(required_param('id', PARAM_INT));

if (false === $session->markable()) {
    print_error('error:notmarkable', 'local_lunchandlearn');
}

$returnurl = new moodle_url('/local/lunchandlearn/attendees.php', array(
    'id' => $session->get_id(),
    'action' => 'list'
));

$errors = [];
$staffids = array_map(function($val) {
        return str_pad($val, 6, '0', STR_PAD_LEFT);
    },
    optional_param_array('staffids', [], PARAM_INT)
);
$users = [
    'found' => [],
    'notfound' => [],
    'attended' => [],
];
$renderform = null;

$uploadform = new bulk_attendance_upload_form(null, true);
$uploadform->set_data(['id' => $session->get_id()]);

if ($uploadform->is_cancelled()) {
    redirect($returnurl);
}

if ($uploadform->is_submitted()) {
    if ($uploaddata = $uploadform->get_data()) {
        $csv = $uploadform->get_file_content('csvfile');
        foreach (explode("\n", $csv) as $row) {
            $firstcol = str_getcsv($row)[0];
            if (is_numeric($firstcol)) {
                // We've got an idnumber from the first column.
                $staffids[] = str_pad($firstcol, 6, '0', STR_PAD_LEFT);
            }
        }
        if (!empty($staffids)) {
            $renderform = 'processform';
        } else {
            $uploadform->empty_csv();
            $renderform = 'uploadform';
        }
    } else {
        $renderform = 'uploadform';
    }
}

if (!empty($staffids)) {
    list($insql, $inparams) = $DB->get_in_or_equal($staffids);
    $users['found'] = $DB->get_records_select('user', "idnumber {$insql}", $inparams, 'lastname ASC, firstname ASC', 'idnumber as staffid, *');
}
foreach ($staffids as $staffid) {
    if (!array_key_exists($staffid, $users['found'])) {
        $users['notfound'][] = $staffid;
    }
}

$processform = new bulk_attendance_process_form(null, ['session' => $session, 'staffids' => $staffids, 'users' => $users]);

$processform->set_data([
    'id' => $session->get_id(),
    'p_learning_desc' => ['text' => $session->get_summary(), 'format' => FORMAT_HTML]
]);

if ($processform->is_cancelled()) {
    $renderform = 'uploadform';
}

if ($processform->is_submitted()) {
    // Process data.
    if ($processdata = $processform->get_data()) {
        $renderform = 'results';
        // Override any capacity settings.
        $session->attendeemanager->set_capacity(0);
        $session->attendeemanager->set_onlinecapacity(0);
        $signupdata = new stdClass;
        $signupdata->inperson = $session->attendeemanager->availableinperson ? true : false;
        foreach ($users['found'] as $user) {
            // Add as attending.
            try {
                $session->attendeemanager->signup($user, $signupdata);
                if (!$session->attendeemanager->did_attend($user->id)) {
                    $session->attendeemanager->attended($user->id);
                    lunchandlearn_manager::add_cpd($session, $user, $processdata);
                }
            } catch (Exception $ex) {
                $errors[$staffid] = $ex->getMessage();
                break;
            }
            $users['attended'][$user->id] = $user;
        }
        $session->lock();
    }
}

if (empty($renderform)) {
    redirect($returnurl);
}

$renderer = $PAGE->get_renderer('local_lunchandlearn');

print $OUTPUT->header();
print $OUTPUT->heading(get_string('bulkattendanceupload:title', 'local_lunchandlearn'));

if (in_array($renderform, ['uploadform', 'processform'])) {
    ${$renderform}->display();
} else {
    // Success/Errors to display.
    print $renderer->bulk_attendance_summary($users, $errors);
    print $renderer->wrapped_link($returnurl, 'returntosession');
}

print $OUTPUT->footer();