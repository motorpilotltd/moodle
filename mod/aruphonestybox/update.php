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
$state  = optional_param('status', null, PARAM_BOOL);
$courseid = optional_param('course', null, PARAM_INT);

if (!isset($state)) {
    exit;
}

$course = $DB->get_record('course', array('id' => $courseid), '*');
$cm     = get_coursemodule_from_instance('aruphonestybox', $id, $course->id);

// Remove any current records.
$DB->delete_records('aruphonestybox_users', array(
        'aruphonestyboxid' => $id,
        'userid' => $USER->id
    ));

if ($state == 1) {
    $ahbu = new stdClass;
    $ahbu->aruphonestyboxid = $id;
    $ahbu->userid = $USER->id;
    $ahbu->completion = 1;
    $ahbu->taps = 0;
    $DB->insert_record('aruphonestybox_users', $ahbu);
}

$completion = new completion_info($course);

if ($completion->is_enabled($cm)) {
    $completion->update_state($cm, $state === 1 ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE);
}

exit;