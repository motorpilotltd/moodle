<?php
require('../../config.php');
require('lib.php');

$contextid = required_param('contextid', PARAM_INT);
$classid = required_param('classid', PARAM_INT);
$download = required_param('download', PARAM_ALPHA);

$PAGE->set_url('/local/delegatelist/download.php', array('contextid' => $contextid, 'classid' => $classid));

$context = context::instance_by_id($contextid, MUST_EXIST);
if ($context->contextlevel != CONTEXT_COURSE) {
    print_error('invalidcontext');
}
$course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);

require_login($course);

$renderer = $PAGE->get_renderer('local_delegatelist');

try {
    $delegatelist = new delegate_list($course, $context, $classid, 'download');
    $activeclass = $delegatelist->get_active_class();
    $dl = $delegatelist->get_list_table();
    if (empty($dl)) {
        throw new moodle_exception('error:noclasses', 'local_delegatelist');
    }

    $filename = clean_param($activeclass->classname, PARAM_FILE) . '_' . date('Ymd');
    $dl->is_downloading($download, $filename);
    $dl->out($dl->custom_page_size(), false);
    exit;
} catch (moodle_exception $e) {
    echo $OUTPUT->header();
    echo $renderer->alert($e->getMessage(), 'alert-danger', false);
    echo $OUTPUT->footer();
    exit;
}