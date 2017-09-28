<?php

global $USER, $OUTPUT, $CFG, $PAGE;
echo \html_writer::start_div('learning_path_global_list');
echo $OUTPUT->heading($this->get_string('header_learning_path_global_list'));

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');

// Add region filter
echo \html_writer::start_tag('form',
        array('method' => 'get', 'action' => $PAGE->url, 'class' => 'mform pull-right', 'id' => 'learning_path_global_list_filtration'));
echo \html_writer::input_hidden_params($PAGE->url);

echo \html_writer::label($this->get_string('region_filter') , 'learning_path_global_list_region_id');
echo \html_writer::select($this->regions, 'region', $this->region, false,
        array('class' => '', 'id' => 'learning_path_global_list_region_id', 'onchange' => "$('#learning_path_global_list_filtration').submit()"));

echo \html_writer::end_tag('form');

echo html_writer::div('', 'clearfix');

echo html_writer::link($this->modeurl, $this->modetext, array('class' => 'pull-right'));
echo html_writer::div('', 'clearfix');
echo " <br />";
echo html_writer::div($this->get_string('list_instructional'), 'clearfix');
echo " <br />";

require_once("$CFG->dirroot/local/wa_learning_path/view/learning_path/{$this->template}.php");

echo \html_writer::end_div();
