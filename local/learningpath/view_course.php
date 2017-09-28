<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(SITEID, false);

$id = required_param('id', PARAM_INT);

$url = new moodle_url('/course/view.php', array('id' => $id));
$event = \local_learningpath\event\learningpath_course_viewed::create(array(
    'objectid' => $id,
));
$event->trigger();

redirect($url);