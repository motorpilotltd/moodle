<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/kaltura/locallib.php');

require_login(SITEID, false);

$id = required_param('id', PARAM_ALPHANUMEXT);
$return = optional_param('return', '', PARAM_URL);

$PAGE->requires->js('/local/kalturaview/js/video.js', false);

$PAGE->set_url(new moodle_url('/local/kalturaview/index.php'));

$PAGE->set_pagelayout('base');

$renderer = $PAGE->get_renderer('local_kalturaview');

$client = arup_local_kaltura_get_kaltura_client(get_config('local_kalturaview', 'privileges'), get_config('local_kalturaview', 'sessionexpires'));

$video = $client->media->get($id);

echo $OUTPUT->header();

echo $renderer->video_player($video, $client->getKs());

echo $OUTPUT->footer();