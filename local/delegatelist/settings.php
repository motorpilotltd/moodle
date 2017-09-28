<?php
/**
 * Settings File
 *
 * @package    local
 * @subpackage delegatelist
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_delegatelist_settings', get_string('settings', 'local_delegatelist'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(
        new admin_setting_configtext(
            'local_delegatelist/classesinlast',
            get_string('setting:classesinlast', 'local_delegatelist'),
            get_string('setting:classesinlast_desc', 'local_delegatelist'),
            365,
            PARAM_INT,
            10
        )
    );
}