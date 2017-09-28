<?php

require_once("../../config.php");

$PAGE->set_url(new moodle_url('/local/sitemessaging/nologin.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$site = get_site();

$strloginstopped = get_string('error:login_stopped_title', 'local_sitemessaging');

$PAGE->navbar->add($strloginstopped, $PAGE->url);

$PAGE->set_title("$site->fullname : $strloginstopped");
$PAGE->set_heading($site->fullname);

echo $OUTPUT->header();

echo $OUTPUT->heading($strloginstopped, 2);

$message = get_config('local_sitemessaging', 'countdown_stop_login_message');
if (empty($message)) {
    $message = get_string('error:login_stopped', 'local_sitemessaging');
}
echo html_writer::tag('div', $message, array('class' => 'alert fade in alert-danger'));

echo $OUTPUT->footer();