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
 * Serves wa_learning_path_nav files.
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
function block_wa_learning_path_nav_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;
    require_once($CFG->dirroot . '/blocks/wa_learning_path_nav/lib/image.class.php');

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if (!isset($args[2])) {
        return false;
    }

    $data = explode('_', $args[2]);
    if (!isset($data[1])) {
        return false;
    }
    
    $entryid = (int) $data[1];
    if (!isset($args[2])) {
        return false;
    }

    $img = \wa_learning_path_nav\lib\wa_image::get_image($entryid);

    $filename = \wa_learning_path_nav\lib\wa_image::get_image_file_name($img->id);
    
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, \wa_learning_path_nav\lib\wa_image::COMPONENT_NAME, \wa_learning_path_nav\lib\wa_image::FILE_AREA,
            $img->id, \wa_learning_path_nav\lib\wa_image::FILE_PATH, $filename);
    if (!$file) {
        send_file_not_found();
    }
    
    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload, $options);
    return false;
}
