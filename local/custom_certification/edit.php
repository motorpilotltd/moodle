<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_login();

$systemcontext = context_system::instance();
require_capability('local/custom_certification:view', $systemcontext);

global $TEXTAREA_OPTIONS, $CFG, $PAGE, $SESSION;
$TEXTAREA_OPTIONS = [
    'subdirs' => 0,
    'maxfiles' => -1,
    'maxbytes' => get_max_upload_file_size(),
    'trusttext' => false,
    'context' => $systemcontext,
    'collapsed' => true
];

$overviewfilesoptions = [
    'maxfiles' => 50,
    'maxbytes' => 999999,
    'subdirs' => 0,
    'accepted_types' => '*'
];

$PAGE->requires->css(new moodle_url('/local/custom_certification/styles/custom_certification.css'));
$PAGE->requires->jquery();

$action = required_param('action', PARAM_RAW);
$certifid = required_param('id', PARAM_INT);
$assignmentid = optional_param('assignmentid', null, PARAM_INT);


$certif = new \local_custom_certification\certification($certifid, $action == 'assignments' ? true : false);

if (empty($certif->id) || $certif->deleted == 1) {
    return redirect(new moodle_url('/local/custom_certification/index.php'));
}
$currenttab = $action;
$fileinclude = 'tabs/' . $action . '.php';

$actualurl = new moodle_url('/local/custom_certification/edit.php', ['action' => $action, 'id' => $certifid]);

$PAGE->set_url($actualurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string($currenttab, 'local_custom_certification'));

require($fileinclude);

$PAGE->navbar->add(get_string('heading', 'local_custom_certification'));

echo $OUTPUT->header();


echo html_writer::tag('h2', $certif->fullname, ['class' => 'certif-name']);



require('tabs.php');

if(\local_custom_certification\notification::exists()){
    echo \local_custom_certification\notification::get();
}
if($action == 'content'){
    echo \html_writer::div(get_string('saveaftersavebutton', 'local_custom_certification'), 'alert alert-warning');
}

$detailsform->display();
echo $OUTPUT->footer();


