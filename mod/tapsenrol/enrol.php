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
require_once($CFG->dirroot.'/mod/tapsenrol/forms/enrol_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$t = optional_param('t',  0, PARAM_INT);  // TAPS Enrolment Activity ID.
$classid = optional_param('classid', 0, PARAM_INT); // TAPS Class ID.

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

//SET context/url in case we get redirected by require_login().
$PAGE->set_context(context_module::instance($tapsenrol->cm->id));
$PAGE->set_url('/mod/tapsenrol/enrol.php', array('id' => $tapsenrol->cm->id, 'classid' => $classid));

// Check login and get context.
require_login($tapsenrol->course, false, $tapsenrol->cm);

$heading = get_string('reviewenrolment', 'tapsenrol') . get_string('separator', 'tapsenrol') . $tapsenrol->course->fullname;

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$output = $PAGE->get_renderer('mod_tapsenrol');

if (!$tapsenrol->check_installation()) {
    // TODO : Update to avoid the duplicated code here and at foot of script.
    echo $OUTPUT->header();

    echo $OUTPUT->heading($heading, '2');

    echo $output->alert(html_writer::tag('p', get_string('installationissue', 'tapsenrol')), 'alert-danger', false);

    echo $output->back_to_module($tapsenrol->course->id);

    echo $OUTPUT->footer();
}

$class = $tapsenrol->taps->get_class_by_id($classid);
$redirecturl = new moodle_url('/course/view.php', array('id' => $tapsenrol->course->id));

if (!$class && has_capability('moodle/block:edit', $tapsenrol->context->cm)) {
    $edit = optional_param('edit', -1, PARAM_BOOL);
    if ($edit == 1 && confirm_sesskey()) {
        $USER->editing = 1;
        redirect($PAGE->url);
    } else if ($edit == 0 && confirm_sesskey()) {
        $USER->editing = 0;
        redirect($PAGE->url);
    }

    // Enter 'admin' mode.
    echo $OUTPUT->header();

    echo $OUTPUT->heading($heading, '2');

    echo html_writer::tag('p', get_string('admin:blockediting', 'tapsenrol'));

    echo html_writer::tag('p', $OUTPUT->edit_button($PAGE->url));

    echo $output->back_to_module($tapsenrol->course->id);

    echo $OUTPUT->footer();

    exit;
} else if (!$class) {
    redirect($redirecturl, get_string('error:invalidclass', 'tapsenrol'));
    exit;
}

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/tapsenrol/js/tapsenrol.js', false);

$enrolmentkey = '';
$enrolinstances = enrol_get_instances($tapsenrol->course->id, true);
$selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
$selfenrolinstance = array_shift($selfenrolinstances);
if ($selfenrolinstance && $selfenrolinstance->password) {
    $enrolmentkey = $selfenrolinstance->password;
}

$formaction = new moodle_url('/mod/tapsenrol/enrol.php', array('id' => $id, 't' => $t, 'classid' => $classid));
if ($tapsenrol->iw) {
    $enrolmentclosed = $class->classstarttime && $tapsenrol->iw->closeenrolment
            && $class->classstarttime < (time() + $tapsenrol->iw->closeenrolment)
            && $tapsenrol->taps->is_classtype($class->classtype, 'classroom');
    if ($enrolmentclosed) {
        $SESSION->tapsenrol->alert = new stdClass();
        $hours = $tapsenrol->iw->closeenrolment / (60 * 60);
        $SESSION->tapsenrol->alert->message = get_string('enrol:alert:enrolmentclosed', 'tapsenrol', $hours);
        $SESSION->tapsenrol->alert->type = 'alert-warning';
        redirect($redirecturl);
        exit;
    }

    $tapsenrol->iw->declarations = $DB->get_records('tapsenrol_iw_declaration', array('internalworkflowid' => $tapsenrol->iw->id), 'declarationid ASC');
/* Temporarily disable setting latest sponsor.
    // Doesn't matter if archived or not here.
    $sql = <<<EOS
SELECT
    iwt.id, iwt.sponsoremail
FROM
    {tapsenrol_iw_tracking} iwt
JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = iwt.enrolmentid
WHERE
    lte.staffid = :staffid
ORDER BY
    iwt.timecreated DESC
EOS;
    $params = array('staffid' => $USER->idnumber);
    $records = $DB->get_records_sql($sql, $params, 0, 1);
    $tapsenrol->iw->latestsponsor = array_pop($records);
*/
}
$customdata = array(
    'enrolmentkey' => $enrolmentkey,
    'internalworkflow' => $tapsenrol->iw,
);
$mform = new mod_tapsenrol_enrol_form($formaction, $customdata);

if ($mform->is_cancelled()) {
    $SESSION->tapsenrol->alert = new stdClass();
    $SESSION->tapsenrol->alert->message = get_string('enrol:alert:cancelled', 'tapsenrol');
    $SESSION->tapsenrol->alert->type = 'alert-warning';
    redirect($redirecturl);
    exit;
}

// Do we need the form?
$passthru = !$tapsenrol->iw
            || ($tapsenrol->iw->approvalrequired == 0
                && !$DB->get_records('tapsenrol_iw_declaration', array('internalworkflowid' => $tapsenrol->iw->id)));
if ((!$enrolmentkey && $passthru) || $fromform = $mform->get_data()) {
    // Here we need to find and reset any linked certifications.
    $completioncache = \cache::make('core', 'completion');
    $alreadyattended = $tapsenrol->already_attended($USER);
    $resetcourses = [];
    foreach ($alreadyattended->completions as $completion) {
        $resetcourses = \local_custom_certification\completion::open_window($completion);
        foreach ($resetcourses as $resetcourseid) {
            $completioncache->delete("{$USER->id}_{$resetcourseid}");
        }
    }
    // If not already done via a linked certification, simply reset the course.
    if (!in_array($tapsenrol->course->id, $resetcourses)) {
        \local_custom_certification\completion::reset_course_for_user($tapsenrol->course->id, $USER->id);
        $completioncache->delete("{$USER->id}_{$tapsenrol->course->id}");
    }

    $SESSION->tapsenrol->alert = new stdClass();

    if ($passthru) {
        // Auto approve if no workflow.
        $enrolresult = $tapsenrol->enrol_employee($classid, $USER->idnumber, !$tapsenrol->iw);
        if ($enrolresult->success && $tapsenrol->iw) {
            // Need form with some empty, but not null, fields for tracking.
            $fromform = new stdClass();
            foreach (array('sponsoremail', 'sponsorfirstname', 'sponsorlastname', 'comments') as $empty) {
                $fromform->{$empty} = '';
            }
            $message = $tapsenrol->trigger_workflow_no_approval($enrolresult->enrolment->enrolmentid, $fromform);
            if (!empty($message)) {
                $enrolresult->message = $message;
            }
        }
    } else {
        $enrolresult = $tapsenrol->enrol_employee($classid, $USER->idnumber);
        if ($enrolresult->success) {
            // New enrolment created.
            if ($tapsenrol->iw && !$tapsenrol->iw->approvalrequired) {
                // Need alternative message.
                $message = $tapsenrol->trigger_workflow_no_approval($enrolresult->enrolment->enrolmentid, $fromform);
                if (!empty($message)) {
                    $enrolresult->message = $message;
                }
            } else if ($tapsenrol->iw) {
                $tapsenrol->trigger_workflow($enrolresult->enrolment->enrolmentid, $fromform);
            }
        }
    }
    if ($enrolresult->success) {
        $other = array(
            'staffid' => $USER->idnumber,
            'classid' => $classid,
            'enrolmentid' => $enrolresult->enrolment->enrolmentid,
        );
        $event = \mod_tapsenrol\event\enrolment_created::create(array(
            'objectid' => $tapsenrol->tapsenrol->id,
            'context' => $tapsenrol->context->cm,
            'other' => $other,
        ));
        $event->trigger();

        $SESSION->tapsenrol->alert->message = $enrolresult->message;
        $SESSION->tapsenrol->alert->type = 'alert-success';
    } else {
        $SESSION->tapsenrol->alert->message = $enrolresult->message;
        $SESSION->tapsenrol->alert->type = 'alert-danger';
    }
    redirect($redirecturl);
    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading($heading, '2');

if ($tapsenrol->iw) {
    echo get_string('reviewenrolment:pre:iw', 'tapsenrol');
} else {
    echo get_string('reviewenrolment:pre', 'tapsenrol');
}

echo $output->review_enrolment($tapsenrol, $class);

$mform->display();

echo $output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();
