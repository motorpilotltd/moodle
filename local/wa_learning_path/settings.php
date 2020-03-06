<?php

/*
 * wa_learning_path module
 *
 * @package     local_wa_learning_path
 * Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
defined('MOODLE_INTERNAL') || die;

// Content of this file has been provided by client:
// http://issues.webanywhere.co.uk/issues/47226
    
$isactivityeditor = array(
    'local/wa_learning_path:editactivity',
    'local/wa_learning_path:addactivity',
    'local/wa_learning_path:deleteactivity'
);
$iscontenteditor = array(
    'local/wa_learning_path:addlearningpath',
    'local/wa_learning_path:amendlearningcontent',
    'local/wa_learning_path:deletelearningpath',
    'local/wa_learning_path:editlearningmatrix',
    'local/wa_learning_path:editmatrixgrid',
    'local/wa_learning_path:publishlearningpath'
);

$component = 'local_wa_learning_path';
$ADMIN->add('root', new admin_category('admin_menu_plugin_navigation', get_string('menu_plugin_navigation', $component)));

$contenteditorurl = new moodle_url('/local/wa_learning_path/index.php', array('c' => 'admin', 'a' => 'index'));
$ADMIN->add(
        'admin_menu_plugin_navigation',
        new admin_externalpage('wa_lp_learning_path_management', get_string('menu_plugin_learning_path_management', $component), $contenteditorurl, $iscontenteditor)
        );



$activityeditorurl = new moodle_url('/local/wa_learning_path/index.php', array('c' => 'activity', 'a' => 'index'));
$ADMIN->add(
        'admin_menu_plugin_navigation',
        new admin_externalpage('wa_lp_activity_management',get_string('menu_plugin_activity_management', $component), $activityeditorurl, $isactivityeditor)
        );

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_wa_learning_path_settings', get_string('settings', 'local_wa_learning_path'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(
        new admin_setting_configcheckbox(
            'local_wa_learning_path/activate_cpd_upload',
            get_string('activate_cpd_upload', 'local_wa_learning_path'),
            get_string('activate_cpd_upload_desc', 'local_wa_learning_path'),
            0, 1, 0
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_subscribe',
            get_string('icon_subscribe', 'local_wa_learning_path'),
            get_string('icon_subscribe_desc', 'local_wa_learning_path'),
            'fa fa-star'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_unsubscribe',
            get_string('icon_unsubscribe', 'local_wa_learning_path'),
            get_string('icon_unsubscribe_desc', 'local_wa_learning_path'),
            'fa fa-star-o'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_unsubscribe',
            get_string('icon_unsubscribe', 'local_wa_learning_path'),
            get_string('icon_unsubscribe_desc', 'local_wa_learning_path'),
            'fa fa-star-o'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_edit',
            get_string('icon_edit', 'local_wa_learning_path'),
            get_string('icon_edit_desc', 'local_wa_learning_path'),
            'fa fa-pencil'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_print',
            get_string('icon_print', 'local_wa_learning_path'),
            get_string('icon_print_desc', 'local_wa_learning_path'),
            'fa fa-print'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_export',
            get_string('icon_export', 'local_wa_learning_path'),
            get_string('icon_export_desc', 'local_wa_learning_path'),
            'fa-external-link'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ui_objectives',
            get_string('icon_ui_objectives', 'local_wa_learning_path'),
            get_string('icon_ui_objectives_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-info-circle'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ui_complete',
            get_string('icon_ui_complete', 'local_wa_learning_path'),
            get_string('icon_ui_complete_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-check-square-o'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ui_modules_and_objectives',
            get_string('icon_ui_modules_and_objectives', 'local_wa_learning_path'),
            get_string('icon_ui_modules_and_objectives_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-tasks'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ui_in_progress',
            get_string('icon_ui_in_progress', 'local_wa_learning_path'),
            get_string('icon_ui_in_progress_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-spinner'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ai_no_objectives',
            get_string('icon_ai_no_objectives', 'local_wa_learning_path'),
            get_string('icon_ai_no_objectives_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-plus'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ai_objectives',
            get_string('icon_ai_objectives', 'local_wa_learning_path'),
            get_string('icon_ai_objectives_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-plus-circle'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_ai_modules_and_objectives',
            get_string('icon_ai_modules_and_objectives', 'local_wa_learning_path'),
            get_string('icon_ai_modules_and_objectives_desc', 'local_wa_learning_path'),
            'fa fa-2x fa-plus-square'
        )
    );
    
    $settings->add(
        new admin_setting_configtext(
            'local_wa_learning_path/icon_modal_navigation_icon',
            get_string('icon_modal_navigation_icon', 'local_wa_learning_path'),
            get_string('icon_modal_navigation_icon_desc', 'local_wa_learning_path'),
            'fa fa-chevron-right'
        )
    );
}