<?php

require_once(dirname(__FILE__) . '/../../config.php');

$courseid = optional_param('courseid', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid]);

if (empty($course)) {
    redirect(new moodle_url('/'));
}

$contextcourse = context_course::instance($course->id);
if ($cms = get_coursemodules_in_course('arupadvert', $course->id)) {
    foreach ($cms as $cm) { 
        $context = context_module::instance($cm->id);
        
        // Serve advert image if user was not logged in
        if(!isloggedin()) {
            $fs = get_file_storage();
            if (!$file = $fs->get_file($context->id, 'mod_arupadvert', 'blockimage', 0, '/', 'info_image.jpg') or $file->is_directory()) {
                redirect(new moodle_url('/'));
            }
            // Finally send the file.
            send_stored_file($file, null, 0, false, []);
            exit;
        } else {        
            $url = moodle_url::make_file_url('/pluginfile.php', '/'. $context->id .'/mod_arupadvert/blockimage/info_image.jpg');
            redirect($url);
        }

        break;
    }
}

redirect(new moodle_url('/'));