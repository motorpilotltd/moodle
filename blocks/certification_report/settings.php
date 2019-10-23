<?php

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'block_certification_report/headerconfig',
        get_string('headerconfig', 'block_certification_report'),
        get_string('descconfig', 'block_certification_report')
    ));

    $settings->add(new admin_setting_configtext(
        'block_certification_report/amberthreshold',
        get_string('labelamberthreshold', 'block_certification_report'),
        get_string('descamberthreshold', 'block_certification_report'),
        get_string('defaultamberthreshold', 'block_certification_report')
    ));

    $settings->add(new admin_setting_configtext(
        'block_certification_report/greenthreshold',
        get_string('labelgreenthreshold', 'block_certification_report'),
        get_string('descgreenthreshold', 'block_certification_report'),
        get_string('defaultgreenthreshold', 'block_certification_report')
    ));

    $categoryoptions = array(0 => get_string('top', 'block_certification_report')) + \local_custom_certification\certification::get_categories();
    $settings->add(
        new admin_setting_configselect(
            'block_certification_report/root_category',
            get_string('choose_root_category', 'block_certification_report'),
            get_string('choose_root_category_desc', 'block_certification_report'),
            0,
            $categoryoptions
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'block_certification_report/ticker_active',
            get_string('activate_ticker_report', 'block_certification_report'),
            get_string('activate_ticker_report_desc', 'block_certification_report'),
            0
        )
    );
}