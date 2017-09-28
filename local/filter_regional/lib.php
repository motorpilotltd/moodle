<?php

defined('MOODLE_INTERNAL') || die();

// This is a temporary hack to ensure filter_regional JS is loaded on every page.
global $PAGE, $CFG;
require_once($CFG->dirroot.'/filter/regional/filter.php');
$filterregional = new filter_regional(null, array());
$filterregional->setup($PAGE, null);