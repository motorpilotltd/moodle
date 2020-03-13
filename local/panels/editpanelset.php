<?php

require_once(__DIR__ . '/../../config.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/panels/editpanelset'));
$PAGE->set_pagelayout('base');

$PAGE->set_title(get_string('editpanelset', 'local_panels'));
$PAGE->set_heading(get_string('editpanelset', 'local_panels'));

require_capability('local/panels:manage', context_system::instance());

echo $OUTPUT->header();

echo \local_panels\panelset::renderforediting();

echo $OUTPUT->footer();