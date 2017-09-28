<?php
// cutdown version, just display table, for swapping out html

require('../../config.php');
require('lib.php');

$contextid = required_param('contextid', PARAM_INT);
$context = context::instance_by_id($contextid, MUST_EXIST);
$PAGE->set_context($context);
if ($context->contextlevel != CONTEXT_COURSE) {
    print_error('invalidcontext');
}
$course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);
$delegatelist = new delegate_list($course, $context, optional_param('classid', 0, PARAM_INT), optional_param('function', 'display', PARAM_ALPHA));
$renderer = $PAGE->get_renderer('local_delegatelist');

echo $renderer->delegate_list($delegatelist);