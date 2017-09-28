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

// Check login and get context.
require_login($tapsenrol->course, false, $tapsenrol->cm);

require_capability('mod/tapsenrol:manageenrolments', $PAGE->context);

$url = new moodle_url('/mod/tapsenrol/manage_enrolments.php', array('id' => $tapsenrol->cm->id));

$PAGE->set_url($url);

$heading = get_string('manageenrolments:heading', 'tapsenrol', $tapsenrol->course->fullname);

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/tapsenrol/js/tapsenrol.js', false);

$output = $PAGE->get_renderer('mod_tapsenrol');

$html = '';
$displayform = false;

$html .= $OUTPUT->header();

$html .= $OUTPUT->heading($heading, '2');

if (!empty($SESSION->tapsenrol->alert->message)) {
    $html .= $output->alert($SESSION->tapsenrol->alert->message, $SESSION->tapsenrol->alert->type);
    unset($SESSION->tapsenrol->alert);
}

if (!$tapsenrol->tapsenrol->internalworkflowid) {
    $html .= $output->alert(get_string('manageenrolments:cannot', 'tapsenrol'), 'alert-danger', false);
} else {
    $classtypes = array('waitlist', 'future', 'past', 'cancel', 'move', /*'update',*/ 'delete');
    // Option to update enrolments (next step, at class level).
    $now = time();
    $wheres = array(
        'waitlist' => 'courseid = :courseid',
        'future' => 'courseid = :courseid AND (enrolmentenddate = 0 OR enrolmentenddate > :now) AND (classstarttime > :now2 OR classstarttime = 0)',
        'past' => 'courseid = :courseid AND (enrolmentenddate = 0 OR enrolmentenddate > :now) AND classstarttime <= :now2 AND classstarttime != 0',
        'cancel' => 'courseid = :courseid',
        'move' => 'courseid = :courseid AND (classstarttime > :now2 OR classstarttime = 0)',
        'update' => 'courseid = :courseid',
        'delete' => 'courseid = :courseid',
    );
    $params = array(
        'courseid' => $tapsenrol->tapsenrol->tapscourse,
        'now' => $now,
        'now2' => $now,
    );
    $customdata = array();
    $customdata['id'] = $id;
    $customdata['classid'] = optional_param('classid', 0, PARAM_INT);
    $customdata['classes'] = new stdClass();
    foreach ($classtypes as $classtype) {
        $customdata['classes']->{$classtype} = $DB->get_records_select_menu('local_taps_class', $wheres[$classtype], $params, '', 'classid, classname');
    }

    require_once($CFG->dirroot.'/mod/tapsenrol/forms/manage_enrolments_form.php');
    $mform = new mod_tapsenrol_manage_enrolments_form(null, $customdata);

    // No cancellation to handle on this form.

    $fromform = $mform->get_data();

    if ($fromform) {
        // Redirect to new form.
        $getdata = array();
        $getdata['id'] = $fromform->id;
        $getdata['type'] = '';
        $getdata['classid'] = 0;
        foreach ($classtypes as $classtype) {
            $button = "submit-{$classtype}";
            if (isset($fromform->{$button})) {
                $getdata['type'] = $classtype;
                $classid = "{$classtype}classid";
                $getdata['classid'] = isset($fromform->{$classid}) ? $fromform->{$classid} : 0;
                break;
            }
        }
        redirect(new moodle_url('/mod/tapsenrol/manage_enrolments_process.php', $getdata));
        exit;
    } else {
        $html .= html_writer::tag('p', get_string('manageenrolments:help', 'tapsenrol'));
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('tapscompletion')) {
            $tapscompletion = $DB->get_record('tapscompletion', array('course' => $tapsenrol->course->id));
            if ($tapscompletion) {
                $tapscompletionurl = new moodle_url('/mod/tapscompletion/view.php', array('t' => $tapscompletion->id));
                $tapscompletionlink = html_writer::link($tapscompletionurl, get_string('manageenrolments:markattendance', 'tapsenrol'));
                $html .= html_writer::tag('p', $tapscompletionlink);
            }
        }
        $displayform = true;
    }
}

echo $html;

if ($displayform) {
    $mform->display();
}

echo $output->back_to_module($tapsenrol->course->id);

echo $output->back_to_coursemanager($tapsenrol->get_tapscourse()->id);

echo $OUTPUT->footer();