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
}