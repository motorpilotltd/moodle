<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020030301;
$plugin->requires  = 2015111600;
$plugin->component = 'filter_wa_regional';

$plugin->dependencies = array(
    'local_regions' => 2015111600
);