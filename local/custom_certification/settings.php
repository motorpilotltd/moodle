<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('root', new admin_category('local_custom_certification', get_string('pluginname', 'local_custom_certification')));

    $adminpage = new admin_externalpage('local_custom_certification_admin', get_string('adminpage', 'local_custom_certification'), "$CFG->wwwroot/local/custom_certification/index.php", 'local/custom_certification:view');
    $ADMIN->add('local_custom_certification', $adminpage);

    $settings = new admin_settingpage('local_custom_certification_settings', get_string('settings', 'local_custom_certification'));
    $ADMIN->add('local_custom_certification', $settings);

    $name = 'local_custom_certification/send_message_rate';
    $title = get_string('setting:send_message_rate', 'local_custom_certification');
    $description = get_string('setting:send_message_rate_desc', 'local_custom_certification');
    $settings->add(new admin_setting_configtext($name, $title, $description, 100, PARAM_INT));
}
