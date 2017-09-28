<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');
require_once($CFG->dirroot . '/local/learningpath/forms/edit_cell_form.php');

require_login(SITEID, false);

$learningpathid = required_param('id', PARAM_INT);
$idx = required_param('x', PARAM_INT);
$idy = required_param('y', PARAM_INT);

// Load learning path and check capability against category
$learningpath = $DB->get_record('local_learningpath', array('id' => $learningpathid), '*', MUST_EXIST);
$x = $DB->get_record('local_learningpath_axes', array('id' => $idx, 'learningpathid' => $learningpath->id, 'axis' => 'x'), '*', MUST_EXIST);
$y = $DB->get_record('local_learningpath_axes', array('id' => $idy, 'learningpathid' => $learningpath->id, 'axis' => 'y'), '*', MUST_EXIST);

$cell = $DB->get_record('local_learningpath_cells', array('x' => $x->id, 'y' => $y->id));
$courses = array('essential' => array(), 'recommended' => array(), 'elective' => array());
if ($cell) {
    foreach ($courses as $index => &$array) {
        $array = $DB->get_records_menu('local_learningpath_courses', array('cellid' => $cell->id, 'coursetype' => $index), '', 'id, courseid');
    }
}
$context = context_coursecat::instance($learningpath->categoryid, IGNORE_MISSING);
require_capability('local/learningpath:edit', $context ? $context : context_system::instance());

$PAGE->set_url(new moodle_url('/local/learningpath/edit_cell.php', array('id' => $learningpath->id, 'x' => $x->id, 'y' => $y->id)));
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/learningpath/js/select2.min.js'));
$PAGE->requires->css(new moodle_url('/local/learningpath/css/select2.css'));
$PAGE->requires->js_init_code(js_writer::function_call("jQuery('.select2').select2", array(array('width'=>'75%'))), true);

$a = new stdClass();
$a->x = $x->name;
$a->y = $y->name;
$title = get_string('title:edit_cell', 'local_learningpath', $a);

$PAGE->set_title($title);

if (!isset($SESSION->local_learningpath)) {
    $SESSION->local_learningpath = new stdClass();
}

$output = $PAGE->get_renderer('local_learningpath');

$mform = new local_learningpath_edit_cell_form();
$setdata = array(
    'id' => $learningpath->id,
    'x' => $x->id,
    'y' => $y->id,
);
if ($cell) {
    $setdata['description'] = $cell->description;
    $setdata = array_merge($setdata, $courses);
}
$mform->set_data($setdata);

if ($mform->is_cancelled()) {
    // Message and redirect to edit page
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('edit_cell:cancelled', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-warning';
    redirect(new moodle_url('/local/learningpath/edit.php', array('id' => $learningpath->id)));
} else if ($data = $mform->get_data()) {
    // Update/add cell data
    if (!$cell) {
        $cell = new stdClass();
        $cell->x = $x->id;
        $cell->y = $y->id;
        $cell->description = $data->description;
        $cell->id = $DB->insert_record('local_learningpath_cells', $cell);
    } elseif ($cell->description != $data->description) {
        $cell->description = $data->description;
        $DB->update_record('local_learningpath_cells', $cell);
    }
    // Update/add course mapping
    foreach ($courses as $coursetype => $coursearray) {
        if (!isset($data->{$coursetype})) {
            $data->{$coursetype} = array();
        }
        $adds = array_diff($data->{$coursetype}, $coursearray);
        $deletes = array_diff($coursearray, $data->{$coursetype});
        // Add new ones
        foreach ($adds as $add) {
            $record = new stdClass();
            $record->cellid = $cell->id;
            $record->coursetype = $coursetype;
            $record->courseid = $add;
            $DB->insert_record('local_learningpath_courses', $record);
        }
        // Delete removed ones
        if (!empty($deletes)) {
            list($in, $params) = $DB->get_in_or_equal($deletes, SQL_PARAMS_NAMED, 'cid');
            $deleteselect = "cellid = :cellid AND coursetype = :coursetype AND courseid {$in}";
            $params['cellid'] = $cell->id;
            $params['coursetype'] = $coursetype;
            $DB->delete_records_select('local_learningpath_courses', $deleteselect, $params);
        }
    }
    // Message and redirect
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('edit_cell:saved', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-success';
    redirect(new moodle_url('/local/learningpath/edit.php', array('id' => $learningpath->id)));
}

echo $OUTPUT->header();

if (isset($SESSION->local_learningpath->alert)) {
    echo $output->alert($SESSION->local_learningpath->alert->message, $SESSION->local_learningpath->alert->type);
    unset($SESSION->local_learningpath->alert);
}

echo $OUTPUT->heading($title);

$mform->display();

echo $OUTPUT->footer();