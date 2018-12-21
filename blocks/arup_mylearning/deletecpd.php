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
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

if ($tab == 'rbreport') {
    $redirecturl = new moodle_url('/local/reportbuilder/report.php', array('id' => $instance));
    $context = context_system::instance();
} else {
    $redirecturl = new moodle_url('/my/index.php', array('tab' => $tab));
    $context = context_block::instance($instance);
}

if (!isset($SESSION->block_arup_mylearning)) {
    $SESSION->block_arup_mylearning = new stdClass ();
}

require_login();

require_capability('block/arup_mylearning:deletecpd', $context);

$PAGE->requires->js_call_amd('block_arup_mylearning/enhance', 'initialise');
$PAGE->set_context($context);
$PAGE->set_url('/blocks/arup_mylearning.php');
$PAGE->navbar->add(get_string('myhome'), $redirecturl);
$PAGE->navbar->add(get_string('deletecpd', 'block_arup_mylearning'));

$PAGE->set_title(get_string('deletecpd', 'block_arup_mylearning'));
$PAGE->set_heading(get_string('deletecpd', 'block_arup_mylearning'));

$confirmurl = new moodle_url('/blocks/arup_mylearning/deletecpd.php', array('tab' => $tab, 'instance' => $instance, 'id' => $id, 'confirm' => 1));

$cpd = \local_learningrecordstore\lrsentry::fetch(['id' => $id, 'staffid' => $USER->idnumber]);

if (!$cpd) {
    $SESSION->block_arup_mylearning->alert = new stdClass();
    $SESSION->block_arup_mylearning->alert->message = get_string('alert:cannot:delete', 'block_arup_mylearning');
    $SESSION->block_arup_mylearning->alert->type = 'alert-danger';
    redirect($redirecturl);
    exit;
} else if ($confirm) {
    $SESSION->block_arup_mylearning->alert = new stdClass();

    if ($cpd->delete()) {
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

echo html_writer::tag('p', get_string('confirmdeletecpd', 'block_arup_mylearning', $cpd->providername));
echo html_writer::tag('div', $continue . $cancel, array('class' => 'buttons'));

echo $OUTPUT->footer();
