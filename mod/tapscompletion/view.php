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
$t = optional_param('t',  0, PARAM_INT);  // TAPS Completion Activity ID.

$tapscompletion = new \mod_tapscompletion\tapscompletion();

if ($id) {
    if (!$tapscompletion->set_cm('id', $id)) {
        print_error('invalidcoursemodule');
    }
    if (!$tapscompletion->set_course($tapscompletion->cm->course)) {
        print_error('coursemisconf');
    }
    if (!$tapscompletion->set_tapscompletion($tapscompletion->cm->instance)) {
        print_error('invalidcoursemodule');
    }
} else {
    if (!$tapscompletion->set_tapscompletion($t)) {
        print_error('invalidcoursemodule');
    }
    if (!$tapscompletion->set_course($tapscompletion->tapscompletion->course)) {
        print_error('coursemisconf');
    }
    if (!$tapscompletion->set_cm('instance', $tapscompletion->tapscompletion->id, $tapscompletion->course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($tapscompletion->course, false, $tapscompletion->cm);

require_capability('mod/tapscompletion:updatecompletion', context_module::instance($tapscompletion->cm->id));

$PAGE->set_url('/mod/tapscompletion/view.php', array('id' => $tapscompletion->cm->id));

$title = $tapscompletion->course->shortname . ': ' . format_string($tapscompletion->tapscompletion->name);
$PAGE->set_title($title);
$PAGE->set_heading($tapscompletion->course->fullname);

$PAGE->requires->js_call_amd('mod_tapscompletion/enhance', 'initialise');
$PAGE->requires->js('/report/completion/textrotate.js');
$PAGE->requires->js_function_call('textrotate_init', null, true);

$output = $PAGE->get_renderer('mod_tapscompletion');

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('tapscompletion', 'tapscompletion'));

if (!$tapscompletion->check_installation()) {
    echo $output->alert(html_writer::tag('p', get_string('installationissue', 'tapscompletion')), 'alert-danger', false);
    echo $output->back_to_module($tapscompletion->course->id);
    echo $OUTPUT->footer();
    exit;
}

if (isset($_POST['submit'])) {
    require_sesskey();

    $other = array(
        'courseid' => $tapscompletion->tapscompletion->tapscourse,
    );
    $event = \mod_tapscompletion\event\statuses_updated::create(array(
        'objectid' => $tapscompletion->tapscompletion->id,
        'context' => context_module::instance($tapscompletion->cm->id),
        'other' => $other,
    ));
    $event->trigger();

    $staffids = optional_param_array('staffid', array(), PARAM_ALPHANUMEXT);

    $taps = new \local_taps\taps();
    $tapsenrols = $DB->get_records('tapsenrol', array('course' => $tapscompletion->course->id));
    // Already checked installation is OK so this is fine.
    $tapsenrol = new tapsenrol(reset($tapsenrols)->id, 'instance');
    $classes = [];

    require_once($CFG->libdir.'/completionlib.php');
    $completion = new completion_info($tapscompletion->course);
    $cancomplete = $completion->is_enabled($tapscompletion->cm) == COMPLETION_TRACKING_AUTOMATIC && $tapscompletion->tapscompletion->completionattended;

    foreach ($staffids as $staffid => $value) {
        $user = $DB->get_record('user', array('idnumber' => $staffid));
        list($enrolmentid, $classid) = explode('_', $value);
        $enrolment = $taps->get_enrolment_by_id($enrolmentid);
        if (!isset($classes[$classid])) {
            $classes[$classid] = $tapscompletion->taps->get_class_by_id($classid);
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
            $a->errormessage = get_string('completionfailed:invalidenrolment', 'tapscompletion');
            echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
            continue;
        }

        if (empty($classes[$classid])) {
            $a->errormessage = get_string('completionfailed:invalidclass', 'tapscompletion');
            echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
            continue;
        }

        if ($cancomplete) {
            $cmcompletion = $completion->get_data($tapscompletion->cm, false, $user->id);
             if ($cmcompletion->completionstate != COMPLETION_INCOMPLETE) {
                 $a->errormessage = 'Activity already marked as completed.';
                 echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
                 continue;
             }
        }

        switch ($status) {
            case 'No Show':
                $cancelresult = $tapsenrol->cancel_enrolment($enrolment->enrolmentid, 'No Show');
                if (!$cancelresult->success) {
                    $a->errormessage = $cancelresult->status;
                    echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
                    continue 2;
                }
                $tapsenrol->cancel_workflow($enrolment, 'TUTOR: No Show', null);
                break;
            case 'Full Attendance':
                if ($tapscompletion->tapscompletion->completiontimetype == \mod_tapscompletion\tapscompletion::$completiontimetypes['classendtime']) {
                    $completiontime = !empty($class->classendtime) ? $class->classendtime : time();
                } else {
                    // Everyone completes now.
                    $completiontime = time();
                }
                $result = $taps->set_status($enrolmentid, 'Full Attendance', $completiontime);
                $a->errormessage = $result->status;
                if (!$result->success) {
                    echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
                    continue 2;
                } else {
                    if ($cancomplete) {
                        // Mark as complete.
                        $record = $DB->get_record('tapscompletion_completion', array('tapscompletionid' => $tapscompletion->tapscompletion->id, 'userid' => $user->id));
                        if (!$record) {
                            $record = new stdClass();
                            $record->tapscompletionid = $tapscompletion->tapscompletion->id;
                            $record->userid = $user->id;
                            $record->completed = $enrolmentid;
                            $record->timemodified = time();
                            $DB->insert_record('tapscompletion_completion', $record);
                        } else if (!$record->completed) {
                            $record->completed = $enrolmentid;
                            $record->timemodified = time();
                            $DB->update_record('tapscompletion_completion', $record);
                        }
                        $completion->update_state($tapscompletion->cm, COMPLETION_COMPLETE, $user->id);
                    }
                }
                break;
            default:
                $a->errormessage = get_string('completionfailed:invalidstatus', 'tapscompletion');
                echo html_writer::tag('p', get_string('completionfailed', 'tapscompletion', $a));
                continue 2;
        }
        echo html_writer::tag('p', get_string('completionsucceeded', 'tapscompletion', $a));
    }
} else {
    tapscompletion_view($tapscompletion->tapscompletion, $tapscompletion->course, $tapscompletion->cm, context_module::instance($tapscompletion->cm->id));

    $modinfo = get_fast_modinfo($tapscompletion->course);
    $completion = new completion_info($tapscompletion->course);
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
    $tapscompletion->get_classes_and_users($classid);

    if (!empty($tapscompletion->classes)) {
        $classfilterform = new \mod_tapscompletion\class_filter_form(null, array('id' => $tapscompletion->cm->id, 'classes' => $tapscompletion->classes));
        $classfilterform->set_data(array('classid' => $tapscompletion->classid));
        echo $classfilterform->display();
    }

    echo $output->user_table(
            $tapscompletion->course,
            $completion,
            $criteria,
            $progress,
            $modinfo,
            $tapscompletion->users,
            $tapscompletion->cm->id);
}
echo $output->back_to_module($tapscompletion->course->id);

echo $OUTPUT->footer();
