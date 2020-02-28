<?php

/**
 * Generate a Plugin menu.
 * @global type $CFG
 * @global type $PAGE
 * @global type $ADMIN
 * @param global_navigation $nav
 * @return type
 */

/**
 * Serves wa_learning_path files.
 *
 * @param stdClass $course course object
 * @param cm_info $cm course module object
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function local_wa_learning_path_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
        array $options = array()) {
    global $CFG;
    require_once($CFG->dirroot . '/local/wa_learning_path/lib/lib.php');
    \wa_learning_path\lib\load_model('learningpath');

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = '/';
    $itemid = (int) reset($args);
    $file = $fs->get_file($context->id, \wa_learning_path\model\learningpath::FILE_COMPONENT, $filearea, $itemid,
            $filepath, $filename);
    if (!$file or $file->is_directory()) {
        send_file_not_found();
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload, $options);
    return false;
}

function local_wa_learning_path_extend_navigation(global_navigation $navigation) {
    // Menu has been moved in to "Site Administration" block.
    /*
    global $CFG;

    require_once("$CFG->dirroot/local/wa_learning_path/lib/lib.php");

    $isactivityeditor = \wa_learning_path\lib\is_activity_editor();
    $iscontenteditor = \wa_learning_path\lib\is_contenteditor();

    if (!$isactivityeditor && !$iscontenteditor) {
        return false;
    }

    $component = 'local_wa_learning_path';

    $menucontainer = $navigation->add(get_string('menu_plugin_navigation', $component), null,
            navigation_node::TYPE_CONTAINER, 'local_wa_learning_path_menu_container',
            'wa_lp_menu_container');

    if ($iscontenteditor) {
        $urlindex = new moodle_url('/local/wa_learning_path/index.php', array('c' => 'admin', 'a' => 'index'));
        $menucontainer->add(get_string('menu_plugin_learning_path_management', $component), $urlindex,
                navigation_node::TYPE_CUSTOM, 'wa_lp_learning_path_management',
                'wa_lp_learning_path_management');
    }

    if ($isactivityeditor) {
        $urlindex = new moodle_url('/local/wa_learning_path/index.php', array('c' => 'activity', 'a' => 'index'));
        $menucontainer->add(get_string('menu_plugin_activity_management', $component), $urlindex,
                navigation_node::TYPE_CUSTOM, 'wa_lp_activity_management', 'wa_lp_activity_management');
    }
    */
}
