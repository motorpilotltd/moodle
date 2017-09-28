<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(SITEID, false);

$PAGE->set_url(new moodle_url('/local/kalturaview/index.php'));

$PAGE->set_pagelayout('base');

$renderer = $PAGE->get_renderer('local_kalturaview');

echo $OUTPUT->header();

echo $renderer->view_video();

echo $OUTPUT->footer();


