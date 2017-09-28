<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('classes/certification.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_login();

$systemcontext = context_system::instance();
require_capability('local/custom_certification:view', $systemcontext);

global $TEXTAREA_OPTIONS, $CFG;
$TEXTAREA_OPTIONS = [
    'subdirs' => 0,
    'maxfiles' => -1,
    'maxbytes' => get_max_upload_file_size(),
    'trusttext' => false,
    'context' => $systemcontext,
    'collapsed' => true
];

$overviewfilesoptions = [
    'maxfiles' => $CFG->courseoverviewfileslimit,
    'maxbytes' => $CFG->maxbytes,
    'subdirs' => 0,
    'accepted_types' => '*'
];

$PAGE->requires->css(new moodle_url('/local/custom_certification/styles/custom_certification.css'));

$action = 'add';

$certif = local_custom_certification\certification::create();

$currenttab = 'details';
$actualurl = new moodle_url('/local/custom_certification/add.php');

$PAGE->set_url($actualurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);

$PAGE->navbar->add(get_string('heading', 'local_custom_certification'));

require('tabs/details.php');

echo $OUTPUT->header();

require('tabs.php');
$detailsform->display();

echo $OUTPUT->footer();

