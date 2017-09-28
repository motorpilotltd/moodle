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

require_once(dirname(__FILE__) . '/../../config.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.

$arupenrol = $DB->get_record('arupenrol', array('id' => $id), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('arupenrol', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Check login and get context.
require_login($course, false, $cm);
require_sesskey();

$PAGE->set_url('/mod/arupenrol/process.php', array('id' => $cm->id));
$redirecturl = new moodle_url('/course/view.php', array('id' => $course->id));

if (!isset($SESSION->arupenrol)) {
    $SESSION->arupenrol = new stdClass();
}

if ($arupenrol->action == 2) {
    // Check key.
    $keyok = false;
    switch ($arupenrol->usegroupkeys) {
        case 1 :
            $keyvalue = required_param('keyvalue', PARAM_RAW);
            $groups = $DB->get_records('groups', array('courseid' => $course->id), 'id', 'id, enrolmentkey');
            foreach ($groups as $group) {
                if (empty($group->enrolmentkey)) {
                    continue;
                }
                if ($keyvalue === $group->enrolmentkey) {
                    $keyok = $group->id;
                    break;
                }
            }
            break;
        case 0 :
        default :
            $keyvalue = required_param('keyvalue', PARAM_INT);
            if ($arupenrol->keytransform) {
                $keyok = $keyvalue == $arupenrol->keyvalue + $USER->idnumber;
            } else {
                $keyok = $keyvalue == $arupenrol->keyvalue;
            }
            break;
    }


    if (!$keyok) {
        $SESSION->arupenrol->alert = new stdClass();
        $SESSION->arupenrol->alert->message = get_string('process:badkey', 'arupenrol');
        $SESSION->arupenrol->alert->type = 'alert-danger';
        redirect($redirecturl);
        exit;
    }
}

if ($arupenrol->enroluser) {
    $enrolinstances = enrol_get_instances($course->id, true);
    $selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
    $selfenrolinstance = array_shift($selfenrolinstances);
    $enrolself = enrol_get_plugin('self');

    if (!$selfenrolinstance || !$enrolself) {
        $SESSION->arupenrol->alert = new stdClass();
        $SESSION->arupenrol->alert->message = get_string('process:couldnotenrol', 'arupenrol', core_text::strtolower(get_string('course')));
        $SESSION->arupenrol->alert->type = 'alert-danger';
        redirect($redirecturl);
        exit;
    }

    $isenrolled = $DB->get_record('user_enrolments', array('enrolid' => $selfenrolinstance->id, 'userid' => $USER->id));

    if (!$isenrolled) {
        $timestart = time();
        if ($selfenrolinstance->enrolperiod) {
            $timeend = $timestart + $selfenrolinstance->enrolperiod;
        } else {
            $timeend = 0;
        }
        $enrolself->enrol_user($selfenrolinstance, $USER->id, $selfenrolinstance->roleid, $timestart, $timeend);

        role_assign(
            $selfenrolinstance->roleid,
            $USER->id,
            context_course::instance($course->id)
        );
    }
}

// Will not group if, at this stage, the user is not enrolled.
if ($arupenrol->action == 2 && $arupenrol->usegroupkeys && $keyok) {
    require_once("$CFG->dirroot/group/lib.php");
    groups_add_member($keyok, $USER->id);
}

$complete = $DB->get_record('arupenrol_completion', array('userid' => $USER->id, 'arupenrolid' => $arupenrol->id));
if (!$complete) {
    $complete = new stdClass();
    $complete->arupenrolid = $arupenrol->id;
    $complete->userid = $USER->id;
    $complete->completed = 1;
    $DB->insert_record('arupenrol_completion', $complete);
} else {
    $complete->completed = 1;
    $DB->update_record('arupenrol_completion', $complete);
}

$completion = new completion_info($course);
$completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);

if (!empty($arupenrol->successmessage)) {
    $SESSION->arupenrol->alert = new stdClass();
    $SESSION->arupenrol->alert->message = $arupenrol->successmessage;
    $SESSION->arupenrol->alert->type = 'alert-success';
}
redirect($redirecturl);
exit;