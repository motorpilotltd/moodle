<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_externalpage('local_custom_certification', get_string('pluginname', 'local_custom_certification'), "$CFG->wwwroot/local/custom_certification/index.php", 'local/custom_certification:view');

    $ADMIN->add('root', $settings);
}

