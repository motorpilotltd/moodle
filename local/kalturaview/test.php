<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/kaltura/locallib.php');

$id = required_param('id', PARAM_ALPHANUMEXT);

$client = arup_local_kaltura_get_kaltura_client(get_config('local_kalturaview', 'privileges') );

$video = $client->media->get($id);

$url = local_kaltura_build_kaf_uri($video->downloadUrl);
