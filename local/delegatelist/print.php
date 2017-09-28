<?php
require('../../config.php');
require('lib.php');

$contextid = required_param('contextid', PARAM_INT);
$classid = required_param('classid', PARAM_INT);

$PAGE->set_url('/local/delegatelist/print.php', array('contextid' => $contextid, 'classid' => $classid));

$PAGE->requires->js_call_amd('local_delegatelist/enhance', 'initialise');

$context = context::instance_by_id($contextid, MUST_EXIST);
if ($context->contextlevel != CONTEXT_COURSE) {
    print_error('invalidcontext');
}
$course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);

require_login($course);

$PAGE->set_title(get_site()->shortname . ': ' . $course->fullname);
$PAGE->set_pagelayout('embedded');

$renderer = $PAGE->get_renderer('local_delegatelist');

try {
    $delegatelist = new delegate_list($course, $context, $classid, 'print');
} catch (moodle_exception $e) {
    echo $OUTPUT->header();
    echo $renderer->close_window();
    echo $renderer->alert($e->getMessage(), 'alert-danger', false);
    echo $OUTPUT->footer();
    exit;
}

// render page
echo $OUTPUT->header();
echo $renderer->close_window();
echo $renderer->summary($delegatelist);
echo html_writer::start_div('delegate-list-container');
$renderer->delegate_list($delegatelist);
echo html_writer::end_div();
echo $OUTPUT->footer();