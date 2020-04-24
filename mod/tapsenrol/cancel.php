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
require_once($CFG->dirroot.'/mod/tapsenrol/forms/cancel_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$t = optional_param('t',  0, PARAM_INT);  // TAPS Enrolment Activity ID.
$enrolmentid = optional_param('enrolmentid', 0, PARAM_INT); // TAPS Enrolment ID.

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

// Check login and get context.
require_login($tapsenrol->course, false, $tapsenrol->cm);

$heading = get_string('cancelenrolment', 'tapsenrol') . get_string('separator', 'tapsenrol') . $tapsenrol->course->fullname;

$PAGE->set_url('/mod/tapsenrol/cancel.php', array('id' => $tapsenrol->cm->id, 'enrolmentid' => $enrolmentid));
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$output = $PAGE->get_renderer('mod_tapsenrol');

$enrolment = $tapsenrol->taps->get_enrolment_by_id($enrolmentid);

$redirecturl = new moodle_url('/course/view.php', array('id' => $tapsenrol->course->id));

if (!$enrolment && has_capability('moodle/block:edit', $tapsenrol->context->cm)) {
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
} else if (!$enrolment) {
    redirect($redirecturl, get_string('error:invalidenrolment', 'tapsenrol'));
    exit;
}

$formaction = new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $id, 't' => $t, 'enrolmentid' => $enrolment->enrolmentid));
$mform = new mod_tapsenrol_cancel_form($formaction, array('iw' => $tapsenrol->iw));

if ($mform->is_cancelled()) {
    $SESSION->tapsenrol->alert = new stdClass();
    $SESSION->tapsenrol->alert->message = get_string('cancel:alert:cancelled', 'tapsenrol');
    $SESSION->tapsenrol->alert->type = 'alert-warning';
    redirect($redirecturl);
    exit;
} else if ($fromform = $mform->get_data()) {
    $other = array(
        'enrolmentid' => $enrolment->enrolmentid,
        'staffid' => $enrolment->staffid,
        'classid' => $enrolment->classid,
    );
    $event = \mod_tapsenrol\event\enrolment_cancelled::create(array(
        'objectid' => $tapsenrol->tapsenrol->id,
        'context' => $tapsenrol->context->cm,
        'other' => $other,
    ));
    $event->trigger();
    $SESSION->tapsenrol->alert = new stdClass();
    $cancelresult = $tapsenrol->cancel_enrolment($enrolment->enrolmentid);

    if ($cancelresult->success) {
        if ($tapsenrol->tapsenrol->internalworkflowid) {
            // Variable $enrolment will have original booking status which is required.
            $tapsenrol->cancel_workflow($enrolment, $fromform->comments);
        }

        $SESSION->tapsenrol->alert->message = $cancelresult->message;
        $SESSION->tapsenrol->alert->type = 'alert-success';
    } else {
        $SESSION->tapsenrol->alert->message = $cancelresult->message;
        $SESSION->tapsenrol->alert->type = 'alert-danger';
    }
    redirect($redirecturl);
    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading($heading, '2');

if (!isset($enrolment->trainingcenter)) {
    // Needed for renderer.
    $class = $tapsenrol->taps->get_class_by_id($enrolment->classid);
    $enrolment->price = $class ? $class->price : null;
    $enrolment->currencycode = $class ? $class->currencycode : null;
    $enrolment->trainingcenter = $class ? $class->trainingcenter : null;
}
echo $output->cancel_enrolment($tapsenrol, $enrolment);

$mform->display();

echo $output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();
