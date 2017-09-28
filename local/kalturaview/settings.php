<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage( 'local_kalturaview', get_string('settingstitle', 'local_kalturaview'));

    $ADMIN->add( 'localplugins', $settings );

    $settings->add( new admin_setting_configtext(
        'local_kalturaview/privileges',
        get_string('privileges', 'local_kalturaview'),
        get_string('privilegeshelp', 'local_kalturaview'),
        '',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configtext(
        'local_kalturaview/categoryids',
        get_string('categoryids', 'local_kalturaview'),
        get_string('categoryidshelp', 'local_kalturaview'),
        '9635471',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configtext(
        'local_kalturaview/resultsperpage',
        get_string('resultsperpage', 'local_kalturaview'),
        get_string('resultsperpagehelp', 'local_kalturaview'),
        '500',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configtext(
        'local_kalturaview/uiconfid',
        get_string('uiconfid', 'local_kalturaview'),
        get_string('uiconfidhelp', 'local_kalturaview'),
        '37497541',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configtext(
        'local_kalturaview/sessionexpires',
        get_string('sessionexpires', 'local_kalturaview'),
        get_string('sessionexpireshelp', 'local_kalturaview'),
        7200,
        PARAM_INT
    ));
}
