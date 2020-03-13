<?php

/**
 * Serves plugin files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function datasource_image_pluginfile($course,
        $cm,
        context $context,
        $filearea,
        $args,
        $forcedownload,
        array $options = array()) {
    require_login($course, true, $cm);

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = '/';

    if (!$file = $fs->get_file($context->id, 'datasource_image', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
