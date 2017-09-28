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

$tab = required_param('tab', PARAM_ALPHA);
$instance = required_param('instance', PARAM_INT);
$cpdid = required_param('cpdid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$redirecturl = new moodle_url('/my/index.php', array('tab' => $tab));

if (!isset($SESSION->block_arup_mylearning)) {
    $SESSION->block_arup_mylearning = new stdClass ();
}

if (!get_config('local_taps', 'version')) {
    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:cannot:delete', 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
    redirect($redirecturl);
    exit;
}

require_login();

$context = context_block::instance($instance);

require_capability('block/arup_mylearning:deletecpd', $context);

$PAGE->requires->js_call_amd('block_arup_mylearning/enhance', 'initialise');
$PAGE->set_context($context);
$PAGE->set_url('/blocks/arup_mylearning.php');
$PAGE->navbar->add(get_string('myhome'), $redirecturl);
$PAGE->navbar->add(get_string('deletecpd', 'block_arup_mylearning'));

$PAGE->set_title(get_string('deletecpd', 'block_arup_mylearning'));
$PAGE->set_heading(get_string('deletecpd', 'block_arup_mylearning'));

$confirmurl = new moodle_url('/blocks/arup_mylearning/deletecpd.php', array('tab' => $tab, 'instance' => $instance, 'cpdid' => $cpdid, 'confirm' => 1));

$cpd = $DB->get_record('local_taps_enrolment', array('cpdid' => $cpdid, 'staffid' => $USER->idnumber));

if (!$cpd) {
    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:cannot:delete', 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
    redirect($redirecturl);
    exit;
} else if ($confirm) {
    $taps = new \local_taps\taps();

    $SESSION->block_arup_mylearning->alert = new stdClass();

    if ($taps->delete_cpd_record($cpdid)) {
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:success:delete', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-success';
    } else {
        $SESSION->block_arup_mylearning->alert->message = get_string('alert:error:delete', 'block_arup_mylearning');
        $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
    }

    redirect($redirecturl);
    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('deletecpd', 'block_arup_mylearning'), 2);

$continue = html_writer::link($confirmurl, get_string('deletecpd:save', 'block_arup_mylearning'), array('class' => 'btn btn-primary'));
$cancel = html_writer::link($redirecturl, get_string('cancel'), array('class' => 'btn btn-default m-l-10'));

echo html_writer::tag('p', get_string('confirmdeletecpd', 'block_arup_mylearning', $cpd->classname));
echo html_writer::tag('div', $continue . $cancel, array('class' => 'buttons'));

echo $OUTPUT->footer();
