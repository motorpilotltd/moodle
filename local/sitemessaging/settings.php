<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $PAGE;

require_once ($CFG->dirroot.'/local/sitemessaging/lib.php');

if ($hassiteconfig) {
    $ADMIN->add('root', new admin_category('local_sitemessaging', get_string('pluginname', 'local_sitemessaging')));

    $settings = new admin_settingpage('local_sitemessaging_settings', get_string('configuration', 'local_sitemessaging'));
    $ADMIN->add('local_sitemessaging', $settings);
    
    $settings->add(
        new local_sitemessaging_admin_setting_configcheckbox(
            'local_sitemessaging/active',
            get_string('active', 'local_sitemessaging'),
            get_string('active_desc', 'local_sitemessaging'),
            0,
            1,
            0
        )
    );

    $choices = array(
        'info' => 'Info',
        'warning' => 'Warning',
        'danger' => 'Error/Danger',
        'success' => 'Success'
    );
    $settings->add(
        new admin_setting_configselect(
            'local_sitemessaging/type',
            get_string('type', 'local_sitemessaging'),
            get_string('type_desc', 'local_sitemessaging'),
            'info',
            $choices
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_sitemessaging/title',
            get_string('title', 'local_sitemessaging'),
            get_string('title_desc', 'local_sitemessaging'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_sitemessaging/body',
            get_string('body', 'local_sitemessaging'),
            get_string('body_desc', 'local_sitemessaging'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_sitemessaging/url',
            get_string('url', 'local_sitemessaging'),
            get_string('url_desc', 'local_sitemessaging'),
            '',
            PARAM_URL
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_sitemessaging/url_text',
            get_string('url_text', 'local_sitemessaging'),
            get_string('url_text_desc', 'local_sitemessaging'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_heading(
            'local_sitemessaging/countdown_heading',
            get_string('countdown_heading', 'local_sitemessaging'),
            get_string('countdown_heading_desc', 'local_sitemessaging')
        )
    );
    
    $settings->add(
        new local_sitemessaging_admin_setting_configcheckbox(
            'local_sitemessaging/countdown_active',
            get_string('countdown_active', 'local_sitemessaging'),
            get_string('countdown_active_desc', 'local_sitemessaging'),
            0,
            1,
            0
        )
    );

    $settings->add(
        new local_sitemessaging_admin_setting_configdatetime(
            'local_sitemessaging/countdown_until',
            get_string('countdown_until', 'local_sitemessaging'),
            get_string('countdown_until_desc', 'local_sitemessaging', date_default_timezone_get()),
            NULL
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_sitemessaging/countdown_ended',
            get_string('countdown_ended', 'local_sitemessaging'),
            get_string('countdown_ended_desc', 'local_sitemessaging'),
            get_string('countdown_ended_default', 'local_sitemessaging'),
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_sitemessaging/countdown_pre',
            get_string('countdown_pre', 'local_sitemessaging'),
            get_string('countdown_pre', 'local_sitemessaging'),
            '',
            PARAM_TEXT
        )
    );
    
    $settings->add(
        new local_sitemessaging_admin_setting_configcheckbox(
            'local_sitemessaging/countdown_stop_login',
            get_string('countdown_stop_login', 'local_sitemessaging'),
            get_string('countdown_stop_login_desc', 'local_sitemessaging', get_string('countdown_active', 'local_sitemessaging')),
            0,
            1,
            0
        )
    );

    $choices = array();
    for ($i = 10; $i <= 60; $i += 10) {
        $choices[$i] = $i;
    }
    $settings->add(
        new admin_setting_configselect(
            'local_sitemessaging/countdown_stop_login_time',
            get_string('countdown_stop_login_time', 'local_sitemessaging'),
            get_string('countdown_stop_login_time_desc', 'local_sitemessaging'),
            30,
            $choices
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_sitemessaging/countdown_stop_login_message',
            get_string('countdown_stop_login_message', 'local_sitemessaging'),
            '',
            get_string('error:login_stopped', 'local_sitemessaging'),
            PARAM_TEXT
        )
    );
}

