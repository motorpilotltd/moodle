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

$cpdid = optional_param('cpdid', 0, PARAM_INT);
$action = 'add';
if ($cpdid) {
    $action = 'edit';
}

$redirecturl = new moodle_url('/my/index.php', array('tab' => $tab));

if (!isset($SESSION->block_arup_mylearning)) {
    $SESSION->block_arup_mylearning = new stdClass ();
}

if (!get_config('local_taps', 'version')) {
    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:cannot:'.$action, 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
    redirect($redirecturl);
    exit;
}

require_login();

$context = context_block::instance($instance);

$taps = new \local_taps\taps();

$cpd = new stdClass();
if ($action == 'edit') {
    require_capability('block/arup_mylearning:editcpd', $context);
    $cpd = $DB->get_record('local_taps_enrolment', array('cpdid' => $cpdid));
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
    }

    // Mapping for specific form select options that are not returned as keys.
    $cpd->classcategory = array_search($cpd->classcategory, $taps->get_classcategory());
    $cpd->classtype = array_search($cpd->classtype, $taps->get_classtypes('cpd'));
    $learningdesc = $cpd->learningdesc . ' ' . $cpd->learningdesccont1 . ' ' . $cpd->learningdesccont2;
    $cpd->learningdesc = ['text' => $learningdesc , 'format' => FORMAT_HTML];
} else {
    require_capability('block/arup_mylearning:addcpd', $context);
}

$PAGE->requires->js_call_amd('block_arup_mylearning/enhance', 'initialise');
$PAGE->set_context($context);
$PAGE->set_url('/blocks/arup_mylearning.php');
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
    $data->staffid = $USER->idnumber; // Set staffid (Only used to add CPD).

    switch ($action) {
        case 'add' :
            $result = $taps->add_cpd_record(
                $data->staffid,
                $data->classname,
                $data->provider,
                $data->classcompletiondate,
                $data->duration,
                $data->durationunitscode,
                array(
                    'p_location' => $data->location,
                    'p_learning_method' => $data->classtype,
                    'p_subject_catetory' => $data->classcategory,
                    'p_course_cost' => $data->classcost,
                    'p_course_cost_currency' => $data->classcostcurrency,
                    'p_course_start_date' => $data->classstartdate,
                    'p_certificate_number' => $data->certificateno,
                    'p_certificate_expiry_date' => $data->expirydate,
                    'p_learning_desc' => $data->learningdesc['text'],
                    'p_learning_desc_cont_1' => '',
                    'p_learning_desc_cont_2' => '',
                    'p_health_and_safety_category' => $data->healthandsafetycategory
                )
            );
            break;
        case 'edit' :
            $result = $taps->edit_cpd_record(
                $data->cpdid,
                $data->classname,
                $data->provider,
                $data->classcompletiondate,
                $data->duration,
                $data->durationunitscode,
                array(
                    'p_location' => $data->location,
                    'p_learning_method' => $data->classtype,
                    'p_subject_catetory' => $data->classcategory,
                    'p_course_cost' => $data->classcost,
                    'p_course_cost_currency' => $data->classcostcurrency,
                    'p_course_start_date' => $data->classstartdate,
                    'p_certificate_number' => $data->certificateno,
                    'p_certificate_expiry_date' => $data->expirydate,
                    'p_learning_desc' => $data->learningdesc['text'],
                    'p_learning_desc_cont_1' => '',
                    'p_learning_desc_cont_2' => '',
                    'p_health_and_safety_category' => $data->healthandsafetycategory
                )
            );
            break;
    }

    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->type = 'alert-danger';

    if ($result === false) {
        $a = get_string('alert:error:failedtoconnect', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:error:'.$action, 'block_arup_mylearning', $a);
    } else if ($result['cpdid'] < 0) {
        if (get_string_manager()->string_exists($result['errormessage'], 'local_taps')) {
            $a = get_string($result['errormessage'], 'local_taps');
        } else {
            $a = $result['errormessage'];
        }
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:error:'.$action, 'block_arup_mylearning', $a);
        $SESSION->block_arup_mylearning->alert->type = '';
    } else {
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:success:'.$action, 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-success';
    }
    redirect($redirecturl);
    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string($action.'cpd', 'block_arup_mylearning'), 2);

echo html_writer::tag('p', get_string($action.'cpd_help', 'block_arup_mylearning'));

$form->set_data($cpd); // Variable $cpd will be empty if adding.
echo $form->display();

echo $OUTPUT->footer();
