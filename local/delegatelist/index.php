<?php
require('../../config.php');
require('lib.php');

$contextid = required_param('contextid', PARAM_INT);

$PAGE->set_url('/local/delegatelist/index.php', array('contextid' => $contextid));

$PAGE->requires->js_call_amd('local_delegatelist/enhance', 'initialise');

$context = context::instance_by_id($contextid, MUST_EXIST);
if ($context->contextlevel != CONTEXT_COURSE) {
    print_error('invalidcontext');
}
$course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);

require_login($course);

$PAGE->set_title(get_site()->shortname . ': ' . $course->fullname);

$renderer = $PAGE->get_renderer('local_delegatelist');

try {
    $delegatelist = new delegate_list($course, $context, optional_param('classid', 0, PARAM_INT));
} catch (moodle_exception $e) {
    echo $OUTPUT->header();
    echo $renderer->alert($e->getMessage(), 'alert-danger', false);
    echo $OUTPUT->footer();
    exit;
}

// render page
echo $OUTPUT->header();
echo html_writer::start_div('row-fluid clearfix');
echo $OUTPUT->heading(get_string('delegatelist', 'local_delegatelist'), 2, 'main mdl-left pull-left');
echo $renderer->navigation_buttons($delegatelist);
echo html_writer::end_div();
echo $renderer->summary($delegatelist);
echo html_writer::start_div('delegate-list-container');
$renderer->delegate_list($delegatelist);
echo html_writer::end_div();
$url = new moodle_url('/course/view.php', array('id' => $course->id));
$link = html_writer::link($url, get_string('backtomodule', 'local_delegatelist'));
echo html_writer::tag('p', $link, array('class' => 'delegate-list-back'));
echo $OUTPUT->footer();