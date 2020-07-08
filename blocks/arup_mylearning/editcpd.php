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
require_once($CFG->dirroot.'/blocks/arup_mylearning/editcpd_form.php');

$tab = required_param('tab', PARAM_ALPHA);
$instance = required_param('instance', PARAM_INT);

$id = optional_param('id', 0, PARAM_INT);
$action = 'add';
if ($id) {
    $action = 'edit';
}

if ($tab == 'rbreport') {
    $redirecturl = new moodle_url('/local/reportbuilder/report.php', array('id' => $instance));
    $context = context_system::instance();
} else {
    $context = context_block::instance($instance);
    $redirecturl = new moodle_url('/my/index.php', array('tab' => $tab));
}

if (!isset($SESSION->block_arup_mylearning)) {
    $SESSION->block_arup_mylearning = new stdClass ();
}

require_login();


$PAGE->set_url('/blocks/arup_mylearning.php');

$taps = new \mod_tapsenrol\taps();

$cpd = new stdClass();
if ($action == 'edit') {
    require_capability('block/arup_mylearning:editcpd', $context);
    $cpd = \local_learningrecordstore\lrsentry::fetch(['id' => $id]);
    if (!$cpd) {
        // No CPD to edit.
        $SESSION->block_arup_mylearning->alert = new stdClass();
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:edit:nocpd', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
        redirect($redirecturl);
        exit;
    } else if ($cpd->staffid != $USER->idnumber) {
        // Can only edit own CPD.
        $SESSION->block_arup_mylearning->alert = new stdClass();
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:edit:notown', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
        redirect($redirecturl);
        exit;
    } else if ($cpd->locked) {
        // Can only edit unlocked CPD.
        $SESSION->block_arup_mylearning->alert = new stdClass();
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:edit:noteditable', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
        redirect($redirecturl);
        exit;
    }
} else {
    require_capability('block/arup_mylearning:addcpd', $context);
    $cpd = new \local_learningrecordstore\lrsentry();
}

$PAGE->requires->js_call_amd('block_arup_mylearning/enhance', 'initialise');
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('myhome'), $redirecturl);
$PAGE->navbar->add(get_string($action.'cpd', 'block_arup_mylearning'));

$PAGE->set_title(get_string($action.'cpd', 'block_arup_mylearning'));
$PAGE->set_heading(get_string($action.'cpd', 'block_arup_mylearning'));

$actionurl = new moodle_url('/blocks/arup_mylearning/editcpd.php', array('tab' => $tab, 'instance' => $instance));
$form = new block_arup_mylearning_editcpd_form(
        $actionurl,
        array('action' => $action, 'taps' => $taps), 'post', '', array('class' => 'editcpd_form')
        );

if ($form->is_cancelled()) {
    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:cancelled:'.$action, 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = '';
    redirect($redirecturl);
    exit;
} else if ($data = $form->get_data()) {
    // Set empty data to null for TAPS.
    foreach ($data as $name => $indata) {
        if (!$indata) {
            $data->$name = null;
        }
    }

    \local_learningrecordstore\lrsentry::set_properties($cpd, $data);
    $cpd->staffid = $USER->idnumber; // Set staffid (Only used to add CPD).
    $cpd->description = $data->description['text'];
    $cpd->timemodified = time();
    $data->durationunitscode = 'H'; // Set durationunits to Hour(s) as default
    $data->duration = $taps->combine_duration_hours($data->duration); // converting hh:mm to hours
    switch ($action) {
        case 'add' :
            $cpd->insert();
            break;
        case 'edit' :
            $cpd->update();
            break;
    }

    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:success:'.$action, 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = 'alert-success';

    redirect($redirecturl);
    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string($action.'cpd', 'block_arup_mylearning'), 2);

echo html_writer::tag('p', get_string($action.'cpd_help', 'block_arup_mylearning'));

// Mapping for specific form select options that are not returned as keys.
$formdata = clone($cpd);
$formdata->classcategory = array_search($cpd->classcategory, $taps->get_classcategory());
$formdata->description = ['text' => $cpd->description , 'format' => FORMAT_HTML];

$form->set_data($formdata); // Variable $cpd will be empty if adding.
echo $form->display();

echo $OUTPUT->footer();
