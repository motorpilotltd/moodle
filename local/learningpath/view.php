<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');

require_login(SITEID, false);

$learningpathid = required_param('id', PARAM_INT);
$learningpath = $DB->get_record('local_learningpath', array('id' => $learningpathid), '*', MUST_EXIST);

$PAGE->requires->jquery();
$PAGE->requires->js('/local/learningpath/js/learningpath.js', false);

$PAGE->set_url(new moodle_url('/local/learningpath/view.php', array('id' => $learningpath->id)));
$PAGE->set_pagelayout('standard');

$PAGE->set_title($learningpath->name);

$output = $PAGE->get_renderer('local_learningpath');

$PAGE->blocks->show_only_fake_blocks();

echo $OUTPUT->header();

if (isset($SESSION->local_learningpath->alert)) {
    echo $output->alert($SESSION->local_learningpath->alert->message, $SESSION->local_learningpath->alert->type);
    unset($SESSION->local_learningpath->alert);
}

echo $OUTPUT->heading($learningpath->name);

echo html_writer::div(format_text($learningpath->description));

// Axes table
$table = new html_table();
$headings = array('');
$alignment = array('left');
$xaxisdetails = $DB->get_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'x'), 'sortorder ASC');
foreach ($xaxisdetails as $xaxis) {
    $heading = html_writer::start_span('rotated-text');
    $heading .= html_writer::start_span('rotated-text__inner');
    $heading .= $xaxis->name;
    $heading .= html_writer::end_span();
    $heading .= html_writer::end_span();
    $headings[] = $heading;
    $alignment[] = null;
}

$table->head = $headings;
$table->align = $alignment;
$table->width = 'auto';
$table->attributes['class'] = 'generaltable lp-table lp-view-table';

$yaxisdetails = $DB->get_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'y'), 'sortorder ASC');
foreach ($yaxisdetails as $yaxis) {
    $cells = array();
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = $yaxis->name;
    $cells[] = clone($cell);

    $cell->header = false;
    foreach ($xaxisdetails as $xaxis) {
        $viewcellurl = new moodle_url('/local/learningpath/view_cell.php', array('id' => $learningpathid, 'x' => $xaxis->id, 'y' => $yaxis->id));
        $countparams = array('x' => $xaxis->id, 'y' => $yaxis->id);
        if ($DB->get_record_select('local_learningpath_cells', "x = :x AND y = :y AND description NOT LIKE ''", $countparams)) {
            $cell->attributes['class'] = 'lp-has-courses';
            $cell->text = html_writer::link($viewcellurl, '');
        } else {
            $cell->attributes['class'] = '';
            $cell->text = '';
        }
        $cells[] = clone($cell);
    }

    $table->data[] = new html_table_row($cells);
}

echo html_writer::table($table);

echo $OUTPUT->footer();

$event = \local_learningpath\event\learningpath_viewed::create(array(
    'objectid' => $learningpath->id,
    'other' => array(
        'name' => $learningpath->name,
    ),
));
$event->trigger();