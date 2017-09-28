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

// Called by AJAX only, displays nothing.
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id   = optional_param('id', null, PARAM_INT);
$courseid = optional_param('course', null, PARAM_INT);

$debug = array();

$course = $DB->get_record('course', array('id' => $courseid), '*');
$cm     = get_coursemodule_from_instance('aruphonestybox', $id, $courseid);

$params = array(
    'context' => context_module::instance($cm->id),
    'courseid' => $course->id,
    'objectid' => $cm->instance,
    'relateduserid' => $USER->id,
    'other' => array(
        'automatic' => false,
    )
);
$logevent = \mod_aruphonestybox\event\cpd_request_sent::create($params);
$logevent->trigger();

$result = aruphonestybox_sendtotaps($id, $USER, $debug);
$return = aruphonestybox_process_result($result, $debug);

if ($return->success == true) {
    // Remove any current records.
    $status = $DB->delete_records('aruphonestybox_users', array(
            'aruphonestyboxid' => $id,
            'userid' => $USER->id
    ));
    $debug[] = 'deleted ahbu record, status: ' . $status;
    $ahbu = new stdClass;
    $ahbu->aruphonestyboxid = $id;
    $ahbu->userid = $USER->id;
    $ahbu->completion = 1;
    $ahbu->taps = 1;
    $DB->insert_record('aruphonestybox_users', $ahbu);

    $debug[] = 'Added record to ahbu';
}

$completion = new completion_info($course);

if ($completion->is_enabled($cm)) {
    $completion->update_state($cm, COMPLETION_COMPLETE);
    $debug[] = 'Updated the completion state';
}

echo json_encode($return);
