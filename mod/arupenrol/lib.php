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
 * Library file for mod_arupenrol.
 *
 * @package     mod_arupenrol
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Add arupenrol instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return int new arupenrol instance id
 */
function arupenrol_add_instance($data, $mform) {
    global $DB;

    $modcontext = context_module::instance($data->coursemodule);
    $data->outro = file_save_draft_area_files(
        $data->outroeditor['itemid'],
        $modcontext->id,
        'mod_'.$data->modulename,
        'outro',
        0,
        array('subdirs' => true),
        $data->outroeditor['text']
    );
    $data->outroformat = $data->outroeditor['format'];

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $data->id = $DB->insert_record('arupenrol', $data);

    return $data->id;
}

/**
 * Update arupenrol instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return bool result
 */
function arupenrol_update_instance($data, $mform) {
    global $DB;

    if ($data->action != 2) {
        unset($data->keytransform);
        unset($data->enroluser);
    }
    if ($data->action == 1) {
        unset($data->enroluser);
    }

    $modcontext = context_module::instance($data->coursemodule);
    $data->outro = file_save_draft_area_files(
        $data->outroeditor['itemid'],
        $modcontext->id,
        'mod_'.$data->modulename,
        'outro',
        0,
        array('subdirs' => true),
        $data->outroeditor['text']
    );
    $data->outroformat = $data->outroeditor['format'];

    $data->timemodified = time();
    $data->id = $data->instance;

    $result = $DB->update_record('arupenrol', $data);

    return $result;
}

/**
 * Delete arupenrol instance.
 *
 * @param int $id
 * @return bool result
 */
function arupenrol_delete_instance($id) {
    global $DB;

    $arupenrol = $DB->get_record('arupenrol', array('id' => $id));

    if (!$arupenrol) {
        return false;
    }

    $DB->delete_records('arupenrol', array('id' => $arupenrol->id));
    $DB->delete_records('arupenrol_completion', array('arupenrolid' => $arupenrol->id));

    return true;
}

/**
 * Carries out dynamic actions on activity content for course page.
 * Hides activity link unless editing (and can add instance).
 *
 * @param cm_info $cm
 */
function arupenrol_cm_info_dynamic(cm_info $cm) {
    global $CFG, $COURSE, $PAGE;

    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        if (!$PAGE->user_is_editing() || !can_update_moduleinfo($cm)) {
            $cm->set_no_view_link();
        }
        if (!$PAGE->requires->is_head_done()
                && $cm->__get('uservisible') === false) {
            // Hide core availability info message.
            $PAGE->requires->css('/mod/arupenrol/css/availability-info.css');
            return;
        }
    }
}

/**
 * Carries out dview actions on activity content for course page.
 * Output depends on chosen functionality settings.
 *
 * @param cm_info $cm
 */
function arupenrol_cm_info_view(cm_info $cm) {
    global $COURSE, $DB, $PAGE, $SESSION, $USER;

    $output = '';
    $renderer = $PAGE->get_renderer('mod_arupenrol');

    if (!$cm->uservisible) {
        $a = '';
        if (!empty($cm->availableinfo)) {
            $availableinfo = $renderer->format_available_info($cm->availableinfo);
            $a = empty($availableinfo) ? '' : get_string('uservisible:availableinfo', 'arupenrol', $availableinfo);
        }
        $output .= $renderer->alert(get_string('uservisible:not', 'arupenrol', $a), 'alert-warning', false);
        $cm->set_content($output);
        return;
    }

    $arupenrol = $DB->get_record('arupenrol', array('id' => $cm->instance));
    $completion = new completion_info($COURSE);
    if (!$arupenrol || !$completion->is_enabled()) {
        return;
    }

    $complete = $DB->get_record('course_modules_completion', array('coursemoduleid' => $cm->id, 'userid' => $USER->id, 'completionstate' => 1));
    $showname = ($arupenrol->shownamebefore && !$complete) || ($arupenrol->shownameafter && $complete);
    $showdescription = !empty($arupenrol->intro) && (
        ($arupenrol->showdescriptionbefore && !$complete) ||
        ($arupenrol->showdescriptionafter && $complete)
    );
    $showenrol = in_array($arupenrol->action, array(2, 3)) && !$complete;
    $showoutro = $complete && !empty($arupenrol->outro);
    $showunenrol = in_array($arupenrol->action, array(2, 3)) && $complete && $arupenrol->enroluser && $arupenrol->unenroluser;

    $hideit = false;
    if (!isset($SESSION->arupenrol->alert)) {
        $hideit =
            !$showname &&
            !$showdescription &&
            !$showenrol &&
            !$showunenrol &&
            !$showoutro;
    }

    if ($hideit) {
        // Hide it.
        $cm->set_extra_classes('arupenrol-hidden');
        return;
    }

    $output .= $renderer->intro($arupenrol, $cm, $showname, $showdescription);

    if (isset($SESSION->arupenrol->alert)) {
        $output .= $renderer->alert($SESSION->arupenrol->alert->message, $SESSION->arupenrol->alert->type);
        unset($SESSION->arupenrol->alert);
    }

    $actionmethod = "action_{$arupenrol->action}";
    if (!$complete && method_exists($renderer, $actionmethod)) {
        $output .= $renderer->{$actionmethod}($arupenrol);
    }

    if ($showoutro) {
        $output .= $renderer->outro($arupenrol, $cm);
    }

    if ($showunenrol) {
        $output .= $renderer->unenrol_button($arupenrol, arupenrol_unenrol_link($arupenrol));
    }

    $cm->set_content($output);
}

function arupenrol_unenrol_link($arupenrol) {
    global $COURSE, $DB, $USER;

    $enrolinstances = enrol_get_instances($COURSE->id, true);
    $selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
    $selfenrolinstance = array_shift($selfenrolinstances);
    $enrolself = enrol_get_plugin('self');

    if (!$selfenrolinstance || !$enrolself) {
        return false;
    }

    if (!$DB->get_record('user_enrolments', array('enrolid' => $selfenrolinstance->id, 'userid' => $USER->id))) {
        return false;
    }

    return new moodle_url("/mod/arupenrol/unenrol.php", array('id' => $arupenrol->id, 'enrolid' => $selfenrolinstance->id));
}

/**
 * Get completion state for user.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param int|stdClass $userid
 * @param int $type
 * @return bool result
 */
function arupenrol_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $result = $type;

    $arupenrol = $DB->get_record('arupenrol', array('id' => $cm->instance), '*', MUST_EXIST);

    switch ($arupenrol->action) {
        case 1:
            // Completed if actively enrolled.
            $context = context_course::instance($course->id);
            $completed = is_enrolled($context, $userid);
            $result = completion_info::aggregate_completion_states($type, $result, $completed);
            break;
        case 2:
        case 3:
            $completed = $DB->get_record('arupenrol_completion', array('arupenrolid' => $arupenrol->id, 'userid' => $userid, 'completed' => 1));
            $result = completion_info::aggregate_completion_states($type, $result, $completed);
            break;
    }

    return $result;
}

/**
 * Informs of supported features in arupenrol.
 *
 * @param string $feature
 * @return int|bool|null
 */
function arupenrol_supports($feature) {
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
            return true;
        case FEATURE_NO_VIEW_LINK:
            return false; // See arupenrol_cm_info_dynamic().
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
            return false;
        case FEATURE_COMMENT:
            return false;
        case FEATURE_RATE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
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
function arupenrol_reset_userdata($data) {
    return array();
}

/**
 * Serves the arupenrol outro attachments.
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
function arupenrol_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea !== 'outro') {
        return false;
    }

    // All users may access it.
    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    $fs = get_file_storage();
    if (!$file = $fs->get_file($context->id, 'mod_arupenrol', 'outro', 0, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, null, 0, false, $options);
}
