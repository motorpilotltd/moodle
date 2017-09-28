<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');
require_once($CFG->dirroot . '/local/learningpath/forms/add_form.php');

require_login(SITEID, false);

require_capability('local/learningpath:add', context_system::instance());

$PAGE->set_url(new moodle_url('/local/learningpath/add.php'));
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/learningpath/js/select2.min.js'));
$PAGE->requires->css(new moodle_url('/local/learningpath/css/select2.css'));
$PAGE->requires->js_init_code(js_writer::function_call("jQuery('.select2').select2", array(array('width'=>'75%'))), true);

$title = get_string('title:add', 'local_learningpath');

$PAGE->set_title($title);

if (!isset($SESSION->local_learningpath)) {
    $SESSION->local_learningpath = new stdClass();
}

$output = $PAGE->get_renderer('local_learningpath');

$mform = new local_learningpath_add_form();

if ($mform->is_cancelled()) {
    // Message and redirect to index
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('add:cancelled', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-warning';
    redirect(new moodle_url('/local/learningpath/index.php'));
} else if ($data = $mform->get_data()) {
    // Add it
    $data->visible = 0;
    $learningpathid = $DB->insert_record('local_learningpath', $data);
    // Add axes
    $axes = array(
        'xaxis' => 'x',
        'yaxis' => 'y',
    );
    foreach ($axes as $field => $axis) {
        $axisrecord = new stdClass();
        $axisrecord->learningpathid = $learningpathid;
        $axisrecord->axis = $axis;
        $axisrecord->visible = 1;
        $sortorder = 1;
        $axisdata = trim(str_ireplace(array("\r\n", "\r"), "\n", $data->{$field}));
        foreach (explode("\n", $axisdata) as $name) {
            $name = trim($name);
            if (!empty($name)) {
                $axisrecord->name = $name;
                $axisrecord->sortorder = $sortorder++;
                $DB->insert_record('local_learningpath_axes', $axisrecord);
            }
        }
    }
    // Message and redirect
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('add:added', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-success';
    redirect(new moodle_url('/local/learningpath/edit.php', array('id' => $learningpathid)));
}

echo $OUTPUT->header();

if (isset($SESSION->local_learningpath->alert)) {
    echo $output->alert($SESSION->local_learningpath->alert->message, $SESSION->local_learningpath->alert->type);
    unset($SESSION->local_learningpath->alert);
}

echo $OUTPUT->heading($title);

$mform->display();

echo $OUTPUT->footer();