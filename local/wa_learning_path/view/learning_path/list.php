<?php

if(empty($this->list)) {
    $this->display_error($this->get_string('no_results'), 'info');
    return;
}
// Prepare table of learning paths list.
$this->table = new \html_table();
$this->table->id = 'global_learning_paths_list';
$this->table->head = array();
$this->table->colclasses = array();

$lpurl = new \moodle_url($this->url, array('a' => 'view'));

foreach ($this->list as $learningpath) {
    $content = array();
    $lpurl->param('id', (int) $learningpath->id);
    $lpurl->param('a', ($learningpath->subscribed) ? 'matrix' : 'view');
    $content[] = \html_writer::link($lpurl, $learningpath->title);
    $content[] = \html_writer::div($learningpath->summary);
    $this->table->data[] = array(
        implode('', $content),
        implode(', ', $learningpath->regions_names),
        ($learningpath->subscribed) ? $this->get_string('subscribed') : ''
    );
}

echo \html_writer::start_tag('div', array('class' => 'no-overflow'));
echo \html_writer::table($this->table);
echo \html_writer::end_tag('div');

