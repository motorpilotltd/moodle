<?php

/**
 * Plugin version information
 *
 * This file defines the current version of plugin being used.
 *
 * @package     local_wa_learning_path
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @license
 *
 */
defined('MOODLE_INTERNAL') || die;

// The version number of the plugin. The format is partially date based with
// the form YYYYMMDDXX where XX is an incremental counter for the given
// year (YYYY), month (MM) and date (DD) of the plugin version's release.
// Every new plugin version must have this number increased in this file,
// which is detected by Moodle core and the upgrade process is triggered.
$plugin->version = 2016081203;


// Specifies the minimum version number of Moodle core that this plugin requires.
// It is not possible to install it to earlier Moodle version. Moodle core's
// version number is defined in the file version.php located in Moodle root
// directory, in the $version variable.
$plugin->requires = 2015111603; // Moodle 3.0.3+ release and upwards
// Allows to throttle the plugin's cron function calls. If set to 0, or not set,
// the cron function is disabled. The set value represents a minimal required
// gap in seconds between two calls of the plugin's cron function. Note that
// the cron function is not supported in all plugin types.
// This value is stored in the database. After changing this value,
// the version number must be incremented.
$plugin->cron = 1;

// The full frankenstyle compoment name in the form of plugintype_pluginname.
// It is used during the installation and upgrade process for diagnostics
// and validation purposes to make sure the plugin.
$plugin->component = 'local_wa_learning_path';

// Declares the maturity level of this plugin version, that is how stable it is.
// This affects the available update notifications feature in Moodle.
// Administrators can configure their site so that they are not notified about
// an available update unless it has certain maturity level declared.
$plugin->maturity = MATURITY_BETA;

// Human readable version name that should help to identify each release of
// the plugin. It can be anything you like although it is recommended to choose
// a pattern and stick with it. Usually it is a simple version like 2.1 but
// some plugin authors use more sophisticated schemes or follow the upstream
// release name if the plugin represents a wrapper for another program.
$plugin->release = 'v0.10.2';

// Allows to declare explicit dependency on other plugin(s) for this plugin to work.
// Moodle core checks these declared dependencies and will not allow the plugin
// installation and/or upgrade until all dependencies are satisfied.
$plugin->dependencies = array(

);
