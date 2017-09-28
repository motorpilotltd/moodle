<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library file for mod_arupadvert.
 *
 * @package     mod_arupadvert
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add arupadvert instance.
 *
 * @param stdClass $data
 * @param moodleform $mform
 * @return int new arupadvert instance id
 */
function arupadvert_add_instance($data, $mform) {
    global $DB;

    $data->altword = trim($data->altword);
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    if (!isset($data->showheadings)) {
        $data->showheadings = 0;
    }

    $data->id = $DB->insert_record('arupadvert', $data);

    // Further processing (for advert block image).
    $cmid = $data->coursemodule;
    $draftitemid = $data->advertblockimage;

    // We need to use context now, so we need to make sure all needed info is already in Db.
    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));
    $context = context_module::instance($cmid);

    file_save_draft_area_files($draftitemid, $context->id, 'mod_arupadvert', 'originalblockimage', 0);
    $fs = get_file_storage();
    $newimages = $fs->get_area_files($context->id, 'mod_arupadvert', 'originalblockimage', false, 'itemid, filepath, filename', false);
    $newimage = array_pop($newimages);
    if (!is_null($newimage) && $newimage->get_imageinfo()) {
        arupadvert_process_blockimage($context->id, $newimage);
    } else {
        $newimage = null;
    }

    if (is_null($newimage)) {
        // Tidy up after deletion.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_arupadvert');
    }

    // Further processing (for datatype).
    if ($data->id) {
        $datatype = \mod_arupadvert\arupadvertdatatype::factory($data->datatype);
        if ($datatype) {
            $datatype->edit_instance($data, $mform);
        }
    }

    return $data->id;
}

/**
 * Update arupadvert instance.
 *
 * @param stdClass $data
 * @param moodleform $mform
 * @return bool true
 */
function arupadvert_update_instance($data, $mform) {
    global $DB;

    $data->altword = trim($data->altword);
    $data->timemodified = time();
    $data->id = $data->instance;
    if (!isset($data->showheadings)) {
        $data->showheadings = 0;
    }

    $result = $DB->update_record('arupadvert', $data);

    // Further processing (for advert block image).
    $cmid = $data->coursemodule;
    $draftitemid = $data->advertblockimage;

    $context = context_module::instance($cmid);

    file_save_draft_area_files($draftitemid, $context->id, 'mod_arupadvert', 'originalblockimage', 0);
    $fs = get_file_storage();
    $newimages = $fs->get_area_files($context->id, 'mod_arupadvert', 'originalblockimage', false, 'itemid, filepath, filename', false);
    $newimage = array_pop($newimages);
    if (!is_null($newimage) && $newimage->get_imageinfo()) {
        arupadvert_process_blockimage($context->id, $newimage);
    } else {
        $newimage = null;
    }

    if (is_null($newimage)) {
        // Tidy up after deletion.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_arupadvert');
    }

    // Further processing (for datatype).
    if ($result) {
        $datatypes = arupadvert_load_datatypes();
        foreach ($datatypes as $datatype) {
            if ($datatype->type == $data->datatype) {
                $datatype->edit_instance($data, $mform);
            } else {
                // Tidy up any now unused.
                $datatype->delete_instance($data->id);
            }
        }
    }

    return $result;
}

/**
 * Delete arupadvert instance.
 *
 * @param int $id
 * @return bool true
 */
function arupadvert_delete_instance($id) {
    global $DB;

    $datatypes = arupadvert_available_datatypes();
    foreach (array_keys($datatypes) as $datatype) {
        $class = \mod_arupadvert\arupadvertdatatype::factory($datatype);
        if ($class) {
            $class->delete_instance($id);
        }
    }

    if (!$arupadvert = $DB->get_record('arupadvert', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('arupadvert', array('id' => $arupadvert->id));

    // Main activity deletion clears up file area.

    return true;
}

/**
 * Given a coursemodule object, this function returns the extra
 * information needed to print this activity in various places.
 *
 * Hides view link if on actual course and not editing.
 *
 * @param cm_info $cm
 */
function arupadvert_cm_info_dynamic(cm_info $cm) {
    global $CFG, $COURSE, $PAGE;

    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        if (!$PAGE->user_is_editing() || !can_update_moduleinfo($cm)) {
            $cm->set_no_view_link();
        }
    }
}

/**
 * Overwrites the content in the course-module object with the advert data.
 *
 * @param cm_info $cm
 */
function arupadvert_cm_info_view(cm_info $cm) {
    global $DB, $PAGE;

    $renderer = $PAGE->get_renderer('mod_arupadvert');

    $arupadvert = $DB->get_record('arupadvert', array('id' => $cm->instance));

    if ($arupadvert) {
        $class = \mod_arupadvert\arupadvertdatatype::factory($arupadvert->datatype, $arupadvert);
    }

    if (!empty($class)) {
        if (!$class->name) { // Datatype doesn't need to set name.
            $class->name = $arupadvert->name;
        }
        $class->get_advert_block();
        $output = $renderer->cm_info_view($arupadvert, $class);
    } else {
        $output = html_writer::tag('p', get_string('nooutput', 'arupadvert'));
    }

    $cm->set_content($output);
}

/**
 * List of features supported by arupadvert.
 * 
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function arupadvert_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_CONTROLS_GRADE_VISIBILITY:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_NO_VIEW_LINK:
            return false; // Determined in arupadvert_cm_info_dynamic().
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
            return false;
        case FEATURE_COMMENT:
            return false;
        case FEATURE_RATE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        default:
            return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * 
 * @param stdClass $data the data submitted from the reset course.
 * @return array status array
 */
function arupadvert_reset_userdata($data) {
    return array();
}

/**
 * Returns array of available data types for select form element.
 *
 * @return array
 */
function arupadvert_available_datatypes() {
    $installed = array_keys(get_plugin_list('arupadvertdatatype'));

    $options = array('' => get_string('choosedots'));

    foreach ($installed as $datatype) {
        $options[$datatype] = get_string('pluginname', 'arupadvertdatatype_' . $datatype);
    }

    return $options;
}

/**
 * Load all data types.
 *
 * @return array
 */
function arupadvert_load_datatypes() {
    $return = array();

    $types = array_keys(get_plugin_list('arupadvertdatatype'));

    foreach ($types as $type) {
        $class = \mod_arupadvert\arupadvertdatatype::factory($type);
        if ($class) {
            $return[$type] = $class;
        }
    }

    return $return;
}

/**
 * Process the uploaded image for display in the associated arupadvert block.
 *
 * @param int $contextid
 * @param stored_file $newimage
 * @return void
 */
function arupadvert_process_blockimage($contextid, $newimage) {
    global $CFG;
    require_once($CFG->libdir.'/gdlib.php');

    $fs = get_file_storage();
    // Clear existing files.
    $fs->delete_area_files($contextid, 'mod_arupadvert', 'blockimage');

    $tempimage = $newimage->copy_content_to_temp();

    if (!is_file($tempimage)) {
        // Can't process, just save.
        arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
        return;
    }

    $imageinfo = getimagesize($tempimage);

    if (empty($imageinfo)) {
        // Can't process, just save.
        arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
        return;
    }

    $desiredaspectratio = 9 / 16; // Widescreen.

    $image = new stdClass();
    $image->width = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->aspectratio = $image->height / $image->width;
    $image->type = $imageinfo[2];

    $image->newwidth = 800;
    $image->heightstart = 0;
    $image->cropheight = $image->height;
    if ($image->aspectratio > $desiredaspectratio) { // Will need cropping.
        $image->newheight = $image->newwidth * $desiredaspectratio;
        $image->cropheight = round($image->width * $desiredaspectratio);
        $image->heightstart = round(($image->height - $image->cropheight) / 2);
    } else {
        $image->newheight = $image->newwidth * $image->aspectratio;
    }

    switch ($image->type) {
        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $im = imagecreatefromgif($tempimage);
            } else {
                arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                return;
            }
            break;
        case IMAGETYPE_JPEG:
            if (function_exists('imagecreatefromjpeg')) {
                $im = imagecreatefromjpeg($tempimage);
            } else {
                arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                return;
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $im = imagecreatefrompng($tempimage);
            } else {
                arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                return;
            }
            break;
        default:
            // Won't process, just save.
            arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
            return;
    }

    if (function_exists('imagejpeg')) {
        $imagefnc = 'imagejpeg';
        $imageext = '.jpg';
        $filters = null; // Not used.
        $quality = 60;
    } else {
        // Can't process, just save.
        arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
        return;
    }

    $im1 = imagecreatetruecolor($image->newwidth, $image->newheight);

    imagecopybicubic($im1, $im, 0, 0, 0, $image->heightstart, $image->newwidth, $image->newheight, $image->width, $image->cropheight);

    ob_start();
    if (!$imagefnc($im1, null, $quality, $filters)) {
        ob_end_clean();
        arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
        return;
    }
    $data = ob_get_clean();
    imagedestroy($im1);

    $filerecord = array(
        'filename' => 'info_image'.$imageext,
        'contextid' => $contextid,
        'component' => 'mod_arupadvert',
        'filearea' => 'blockimage',
        'itemid' => 0,
        'filepath' => '/');
    $fs->create_file_from_string($filerecord, $data);

    @unlink($tempimage);
}

/**
 * Use originally uploaded image for display in the associated arupadvert block.
 *
 * @param file_storage $fs
 * @param stored_file $newimage
 * @param string|bool $tempimage
 * @param int $contextid
 * @return void
 */
function arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid) {
    $filerecord = array('contextid' => $contextid, 'component' => 'mod_arupadvert', 'filearea' => 'blockimage', 'itemid' => 0, 'filepath' => '/');
    $fs->create_file_from_storedfile($filerecord, $newimage);
    if (is_file($tempimage)) {
        @unlink($tempimage);
    }
}

/**
 * Serves the arupadvert files.
 *
 * @package mod_arupadvert
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function arupadvert_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea !== 'blockimage') {
        return false;
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'mod_arupadvert', 'blockimage', 0, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}