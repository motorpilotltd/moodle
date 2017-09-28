<?php

/**
 * Version details
 *
 * @package		block_wa_learning_path_nav
 * @author		Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright	2016 Webanywhere (http://www.webanywhere.co.uk)
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version = 2016062901;              // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires = 2014050800;             // Requires this Moodle version.
$plugin->component = 'block_wa_learning_path_nav';    // Full name of the plugin (used for diagnostics).
$plugin->release = 'v0.5.2';                  // Human readable version name.
$plugin->dependencies = array(
    'local_wa_learning_path' => ANY_VERSION,   // Module must be present (any version).
);
$plugin->cron = 1;
