<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');

require_login(SITEID, false);

$learningpathid = required_param('id', PARAM_INT);
$xaxis = required_param('x', PARAM_INT);
$yaxis = required_param('y', PARAM_INT);
$learningpath = $DB->get_record('local_learningpath', array('id' => $learningpathid), '*', MUST_EXIST);
$cell = $DB->get_record('local_learningpath_cells', array('x' => $xaxis, 'y' => $yaxis), '*', MUST_EXIST);
$x = $DB->get_record('local_learningpath_axes', array('id' => $xaxis, 'learningpathid' => $learningpath->id, 'axis' => 'x'), '*', MUST_EXIST);
$y = $DB->get_record('local_learningpath_axes', array('id' => $yaxis, 'learningpathid' => $learningpath->id, 'axis' => 'y'), '*', MUST_EXIST);

$PAGE->requires->jquery();
$PAGE->requires->js('/local/learningpath/js/learningpath.js', false);

$PAGE->set_url(new moodle_url('/local/learningpath/view_cell.php', array('id' => $learningpath->id, 'x' => $xaxis, 'y' => $yaxis)));
$PAGE->set_pagelayout('standard');

$a = new stdClass();
$a->name = $learningpath->name;
$a->x = $x->name;
$a->y = $y->name;
$title = get_string('title:view_cell', 'local_learningpath', $a);

$PAGE->set_title($title);

$output = $PAGE->get_renderer('local_learningpath');

$PAGE->blocks->show_only_fake_blocks();

$filters = array();
$region = '';
$showfilters = get_config('local_regions', 'version') && get_config('local_learningpath', 'regions_filter');
if ($showfilters) {
    $filterblock = new local_learningpath_filter_block();
    // Set a fake instance id so we can hide the block.
    $filterblock->contents->blockinstanceid = -1; // Real instances won't be neagtive.
    $PAGE->blocks->add_fake_block($filterblock->contents, 'left');
    $filters = $filterblock->get_filters();
}

$mappedcourses = array();
$mappedcourses['essential'] = learningpath_get_courses($cell->id, 'essential', $filters);
$mappedcourses['recommended'] = learningpath_get_courses($cell->id, 'recommended', $filters);
$mappedcourses['elective'] = learningpath_get_courses($cell->id, 'elective', $filters);

echo $OUTPUT->header();

if (isset($SESSION->local_learningpath->alert)) {
    echo $output->alert($SESSION->local_learningpath->alert->message, $SESSION->local_learningpath->alert->type);
    unset($SESSION->local_learningpath->alert);
}

echo $OUTPUT->heading($title);

echo html_writer::div(format_text($cell->description), 'lp-cell-description');

foreach ($mappedcourses as $coursetype => $courses) {
    echo $output->courses($coursetype, $courses);
}

echo $output->back_to_learningpath($learningpath->id);

echo $OUTPUT->footer();

$event = \local_learningpath\event\learningpath_cell_viewed::create(array(
    'objectid' => $cell->id,
    'other' => array(
        'name' => $title,
    ),
));
$event->trigger();