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
require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$t = optional_param('t',  0, PARAM_INT);  // TAPS Enrolment Activity ID.

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

require_login($tapsenrol->course, false, $tapsenrol->cm);

require_capability('mod/tapsenrol:updatecompletion', context_module::instance($tapsenrol->cm->id));

$PAGE->set_url('/mod/tapsenrol/updatecompletion.php', array('id' => $tapsenrol->cm->id));

$title = $tapsenrol->course->shortname . ': ' . format_string($tapsenrol->tapsenrol->name);
$PAGE->set_title($title);
$PAGE->set_heading($tapsenrol->course->fullname);

$output = $PAGE->get_renderer('mod_tapsenrol');

$PAGE->requires->js_call_amd('mod_tapsenrol/enhance', 'initialise');

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('updatecompletionheader', 'tapsenrol'));

if (isset($_POST['submit'])) {
    require_sesskey();

    $other = array(
        'courseid' => $tapsenrol->tapsenrol->tapscourse,
    );
    $event = \mod_tapsenrol\event\statuses_updated::create(array(
        'objectid' => $tapsenrol->tapsenrol->id,
        'context' => context_module::instance($tapsenrol->cm->id),
        'other' => $other,
    ));
    $event->trigger();

    $staffids = optional_param_array('staffid', array(), PARAM_ALPHANUMEXT);

    $taps = new \local_taps\taps();
    $classes = [];

    require_once($CFG->libdir.'/completionlib.php');
    $completion = new completion_info($tapsenrol->course);
    $cancomplete = $completion->is_enabled($tapsenrol->cm) == COMPLETION_TRACKING_AUTOMATIC && $tapsenrol->tapsenrol->completionattended;

    foreach ($staffids as $staffid => $value) {
        $user = $DB->get_record('user', array('idnumber' => $staffid));
        list($enrolmentid, $classid) = explode('_', $value);
        $enrolment = $taps->get_enrolment_by_id($enrolmentid);
        if (!isset($classes[$classid])) {
            $classes[$classid] = $taps->get_class_by_id($classid);
        }
        $status = optional_param($value, 'Full Attendance', PARAM_RAW);

        $a = new stdClass();
        $a->user = fullname($user);
        $a->staffid = $staffid;
        $a->classname = !empty($classes[$classid]->classname) ? $classes[$classid]->classname : '-';
        $a->enrolmentid = $enrolmentid;
        $a->status = $status;
        $a->errormessage = '';

        if (empty($enrolment)) {
            $a->errormessage = get_string('completionfailed:invalidenrolment', 'tapsenrol');
            echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
            continue;
        }

        if (empty($classes[$classid])) {
            $a->errormessage = get_string('completionfailed:invalidclass', 'tapsenrol');
            echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
            continue;
        }

        if ($cancomplete) {
            $cmcompletion = $completion->get_data($tapsenrol->cm, false, $user->id);
             if ($cmcompletion->completionstate != COMPLETION_INCOMPLETE) {
                 $a->errormessage = 'Activity already marked as completed.';
                 echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
                 continue;
             }
        }

        switch ($status) {
            case 'No Show':
                $cancelresult = $tapsenrol->cancel_enrolment($enrolment->enrolmentid, 'No Show');
                if (!$cancelresult->success) {
                    $a->errormessage = $cancelresult->status;
                    echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
                    continue 2;
                }
                $tapsenrol->cancel_workflow($enrolment, 'TUTOR: No Show', null);
                break;
            case 'Full Attendance':
                if ($tapsenrol->tapsenrol->completiontimetype == tapsenrol::$completiontimetypes['classendtime']) {
                    $completiontime = !empty($classes[$classid]->classendtime) ? $classes[$classid]->classendtime : time();
                } else {
                    // Everyone completes now.
                    $completiontime = time();
                }
                $result = $taps->set_status($enrolmentid, 'Full Attendance', $completiontime);
                $a->errormessage = $result->status;
                if (!$result->success) {
                    echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
                    continue 2;
                } else {
                    if ($cancomplete) {
                        // Mark as complete.
                        $record = $DB->get_record('tapsenrol_completion', array('tapsenrolid' => $tapsenrol->tapsenrol->id, 'userid' => $user->id));
                        if (!$record) {
                            $record = new stdClass();
                            $record->tapsenrolid = $tapsenrol->tapsenrol->id;
                            $record->userid = $user->id;
                            $record->completed = $enrolmentid;
                            $record->timemodified = time();
                            $DB->insert_record('tapsenrol_completion', $record);
                        } else if (!$record->completed) {
                            $record->completed = $enrolmentid;
                            $record->timemodified = time();
                            $DB->update_record('tapsenrol_completion', $record);
                        }
                        $completion->update_state($tapsenrol->cm, COMPLETION_COMPLETE, $user->id);
                    }
                }
                break;
            default:
                $a->errormessage = get_string('completionfailed:invalidstatus', 'tapsenrol');
                echo html_writer::tag('p', get_string('completionfailed', 'tapsenrol', $a));
                continue 2;
        }
        echo html_writer::tag('p', get_string('completionsucceeded', 'tapsenrol', $a));
    }
} else {
    \mod_tapsenrol\event\course_module_viewed::initfromobjects($tapsenrol->tapsenrol, $tapsenrol->course, $tapsenrol->cm, context_module::instance($tapsenrol->cm->id));

    $modinfo = get_fast_modinfo($tapsenrol->course);
    $completion = new completion_info($tapsenrol->course);
    $progress = $completion->get_progress_all();

    $criteria = array();
    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $criterion) {
        $criteria[] = $criterion;
    }
    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion) {
        $criteria[] = $criterion;
    }
    foreach ($completion->get_criteria() as $criterion) {
        if (!in_array($criterion->criteriatype, array(
                COMPLETION_CRITERIA_TYPE_COURSE, COMPLETION_CRITERIA_TYPE_ACTIVITY))) {
            $criteria[] = $criterion;
        }
    }

    $classid = optional_param('classid', 0, PARAM_INT);
    $tapsenrol->get_classes_and_users($classid);

    if (!empty($tapsenrol->classes)) {
        $classfilterform = new \mod_tapsenrol\class_filter_form(null, array('id' => $tapsenrol->cm->id, 'classes' => $tapsenrol->classes));
        $classfilterform->set_data(array('classid' => $tapsenrol->classid));
        echo $classfilterform->display();
    }

    echo $output->user_table(
            $tapsenrol->course,
            $completion,
            $criteria,
            $progress,
            $modinfo,
            $tapsenrol->users,
            $tapsenrol->cm->id);
}
echo $output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();
