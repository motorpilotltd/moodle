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
 * Library of interface functions and constants for module arupapplication.
 *
 * @package    mod_arupapplication
 * @copyright  2014 Epic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function arupapplication_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_BACKUP_MOODLE2: return true;

        default: return null;
    }
}

/**
 * Saves a new instance of the arupapplication into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $arupapplication An object from the form in mod_form.php
 * @param mod_arupapplication_mod_form $mform
 * @return int The id of the newly inserted arupapplication record
 */
function arupapplication_add_instance(stdClass $arupapplication, mod_arupapplication_mod_form $mform = null) {
    global $DB;

    $arupapplication->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('arupapplication', $arupapplication);
}

/**
 * Updates an instance of the arupapplication in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $arupapplication An object from the form in mod_form.php
 * @param mod_arupapplication_mod_form $mform
 * @return boolean Success/Fail
 */
function arupapplication_update_instance(stdClass $arupapplication, mod_arupapplication_mod_form $mform = null) {
    global $DB;

    $arupapplication->timemodified = time();
    $arupapplication->id = $arupapplication->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('arupapplication', $arupapplication);
}

/**
 * Removes an instance of the arupapplication from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function arupapplication_delete_instance($id) {
    global $DB;

    if (! $arupapplication = $DB->get_record('arupapplication', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('arupapplication', $id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

    // delete files associated with this assignment
    $fs = get_file_storage();
    if (! $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission') ) {
        $result = false;
    }

    // delete_records will throw an exception if it fails - so no need for error checking here

    $DB->delete_records('arupstatementquestions', array('applicationid'=>$arupapplication->id));
    $DB->delete_records('arupdeclarations', array('applicationid'=>$arupapplication->id));
    $DB->delete_records('arupapplication_tracking', array('applicationid'=>$arupapplication->id));
    $DB->delete_records('arupstatementanswers', array('applicationid'=>$arupapplication->id));
    $DB->delete_records('arupdeclarationanswers', array('applicationid'=>$arupapplication->id));
    $DB->delete_records('arupsubmissions', array('applicationid'=>$arupapplication->id));

    $DB->delete_records('arupapplication', array('id' => $arupapplication->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function arupapplication_user_outline($course, $user, $mod, $arupapplication) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $arupapplication the module instance record
 * @return void, is supposed to echp directly
 */
function arupapplication_user_complete($course, $user, $mod, $arupapplication) {
    // Get application form details
    $applicationdetail = $DB->get_record('arupapplication', array('id'=>$cm->instance), '*', MUST_EXIST);
    $params = array('userid'=>$userid, 'applicationid'=>$applicationdetail->id);

    $record = $DB->get_record('arupapplication_tracking', array('id'=>$cm->instance));

    if ($record) {
        switch($record->completed) {
            case 1:
                echo get_string('progress:started', 'arupapplication');
                break;
            case 2:
            case 3:
            case 4:
            case 5:
                echo get_string('progress:inprogress', 'arupapplication');
                break;
            case 6:
                echo get_string('progress:applicationsubmitted', 'arupapplication');
                break;
            case 7:
                echo get_string('progress:complete', 'arupapplication');
                break;
            default:
                echo get_string('progress:notstarted', 'arupapplication');
                break;
        }
    } else {
        echo get_string('progress:notstarted', 'arupapplication');
    }
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in arupapplication activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function arupapplication_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link arupapplication_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function arupapplication_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see arupapplication_get_recent_mod_activity()}

 * @return void
 */
function arupapplication_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function arupapplication_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function arupapplication_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function arupapplication_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for arupapplication file areas
 *
 * @package mod_arupapplication
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function arupapplication_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the arupapplication file areas
 *
 * @package mod_arupapplication
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the arupapplication's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function arupapplication_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $USER;

    require_login();

    if ($filearea !== 'submission') {
        send_file_not_found();
    }

    $itemid = (int) array_shift($args);

    $submissiondetails = $DB->get_record('arupsubmissions', array('id'=>$itemid), '*', MUST_EXIST);

    $returnfile = 0;
    if ($submissiondetails->userid == $USER->id) {
        $returnfile = 1;
    } elseif (strtolower($submissiondetails->sponsor_email) == strtolower($USER->email)) {
        $returnfile = 1;
    } elseif (has_capability('mod/arupapplication:edititems', $context)) {
        $returnfile = 1;
    }
    if ($returnfile == 0) {
        send_file_not_found();
    }

    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_arupapplication/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!

}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding arupapplication nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the arupapplication module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function arupapplication_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the arupapplication settings
 *
 * This function is called when the context for the page is a arupapplication module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $arupapplicationnode {@link navigation_node}
 */
function arupapplication_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $navref) {

    global $PAGE;

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;


    if (!$course) {
        return;
    }

   // Link to download all submissions
   if (has_capability('mod/arupapplication:printapplication', $context)) {
       $link = new moodle_url('/mod/arupapplication/view.php', array('id' => $cm->id,'action'=>'downloadall'));
       $node = $navref->add(get_string('action:all', 'arupapplication'), $link, navigation_node::TYPE_SETTING);

       $link = new moodle_url('/mod/arupapplication/view.php', array('id' => $cm->id,'action'=>'downloadcompleted'));
       $node = $navref->add(get_string('action:complete', 'arupapplication'), $link, navigation_node::TYPE_SETTING);
   }

}

/**
 * Obtains the automatic completion state for this application based on the condition
 * in arup application settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function arupapplication_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get application form details
    $applicationdetail = $DB->get_record('arupapplication', array('id'=>$cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if ($applicationdetail->completionsubmit) {
        $params = array('userid'=>$userid, 'applicationid'=>$applicationdetail->id, 'completed'=>7);
        return $DB->record_exists('arupapplication_tracking', $params);
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * save the changes of a given record.
 *
 * @global object
 * @param object $item
 * @return boolean
 */
function arupapplication_update_record($record, $thistable) {
    global $DB;
    return $DB->update_record($thistable, $record);
}

/**
 * renumbers all items of the given applicationid
 *
 * @global object
 * @param int $applicationid
 * @return void
 */
function arupapplication_renumber_records($recordid, $thistable) {
    global $DB;

    $records = $DB->get_records($thistable, array('applicationid'=>$recordid), 'sortorder');
    $pos = 1;
    if ($records) {
        foreach ($records as $record) {
            $DB->set_field($thistable, 'sortorder', $pos, array('id'=>$record->id));
            $pos++;
        }
    }
}

/**
 * Obtains the status for this application
 * in arup application settings.
 *
 * @param object $cm Course-module
 * @param int $userid User ID
*/
function arupapplication_application_status($cm, $userid) {
    global $CFG, $DB;

    // Get application form details
    $applicationdetail = $DB->get_record('arupapplication', array('id'=>$cm->instance), '*', MUST_EXIST);
    $params = array('userid'=>$userid, 'applicationid'=>$applicationdetail->id);

    $record = $DB->get_record('arupapplication_tracking', array('userid'=>$userid, 'applicationid'=>$applicationdetail->id));
    $arr_returnvar = array();
    if ($record) {
        $arr_returnvar['gopage'] = $record->completed;
        switch($record->completed) {
            case '0':
                $arr_returnvar['progressbar'] = '5';
                $arr_returnvar['status'] = get_string('progress:verbose:started', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 1:
                $arr_returnvar['progressbar'] = '15';
                $arr_returnvar['status'] = get_string('progress:verbose:inprogress', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 2:
                $arr_returnvar['progressbar'] = '30';
                $arr_returnvar['status'] = get_string('progress:verbose:inprogress', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 3:
                $arr_returnvar['progressbar'] = '45';
                $arr_returnvar['status'] = get_string('progress:verbose:inprogress', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 4:
                $arr_returnvar['progressbar'] = '60';
                $arr_returnvar['status'] = get_string('progress:verbose:inprogress', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 5:
                $arr_returnvar['progressbar'] = '75';
                $arr_returnvar['status'] = get_string('progress:verbose:inprogress', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:continueapplication', 'arupapplication');
                break;
            case 6:
                $arr_returnvar['progressbar'] = '95';
                $arr_returnvar['status'] = get_string('progress:verbose:applicationsubmitted', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:viewapplication', 'arupapplication');
                break;
            case 7:
                $arr_returnvar['progressbar'] = '100';
                $arr_returnvar['status'] = get_string('progress:verbose:complete', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:viewapplication', 'arupapplication');
                break;
            default:
                $arr_returnvar['progressbar'] = '0';
                $arr_returnvar['status'] = get_string('progress:verbose:notstarted', 'arupapplication');
                $arr_returnvar['button'] = get_string('button:startapplication', 'arupapplication');
                break;
        }
    } else {
        $arr_returnvar['gopage'] = '0';
        $arr_returnvar['progressbar'] = '0';
        $arr_returnvar['status'] = get_string('progress:verbose:notstarted', 'arupapplication');
        $arr_returnvar['button'] = get_string('button:startapplication', 'arupapplication');
    }
    return $arr_returnvar;
}

function arupapplication_application_updatestatus($course, $cm, $userid, $status) {
    global $DB;


}

/**
 * Checks if submissions are in progress for this instance of arupapplication.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $applicationid int
 * @return boolean true if submissions are in progress for this arupapplication instance
 */
function arupapplication_submissionsinprogress($applicationid) {
    global $DB;

    /** @example */
    if ($DB->record_exists('arupsubmissions', array('applicationid' => $applicationid))) {
        return true;
    } else {
        return false;
    }
}

function arupapplication_cm_info_view(cm_info $cm) {
    global $USER, $DB;
    require_once('locallib.php');
    $output = '';

    $arupapplication = $DB->get_record('arupapplication', array('id' => $cm->instance), '*', MUST_EXIST);
    $applicationstatus = arupapplication_application_status($cm, $USER->id);

    $output .= format_module_intro('arupapplication', $arupapplication, $cm->id);

    $progressextraclass = 'progress-bar-primary';
    if ($applicationstatus['progressbar'] == 100) {
        $progressextraclass = 'progress-bar-success';
    }

    $percentage = $applicationstatus['progressbar'].'%';
    $style = 'width:'.$percentage.';';
    if ($applicationstatus['progressbar'] == 0) {
        $style .= 'color:#262626;padding-left:5px;';
    }
    $baroptions = array(
        'class' => "progress-bar {$progressextraclass}",
        'style' => $style,
        'role' => 'progressbar',
        'aria-valuemin' => 0,
        'aria-valuemax' => 100,
        'aria-valuenow' => $applicationstatus['progressbar'],
    );
    $bar = html_writer::tag('div', $percentage, $baroptions);
    $srspan = html_writer::span($percentage, 'sr-only');

    $output .= html_writer::tag('div', $bar.$srspan, array('class' => "progress {$progressextraclass}"));

    $output .= $applicationstatus['status'];

    if ($applicationstatus['gopage'] != 0) {
        if (arupapplication_referencesponsorfeedback($arupapplication->id, $USER->id, 'referee')) {
            $output .= get_string('progress:verbose:receivedtechnicalreference', 'arupapplication');
        } else {
            $output .= get_string('progress:verbose:awaitingtechnicalreference', 'arupapplication');
        }
    }
    if ($arupapplication->sponsorstatementreq && $applicationstatus['gopage'] >= 6) {
        if (arupapplication_referencesponsorfeedback($arupapplication->id, $USER->id, 'sponsor')) {
            $output .= get_string('progress:verbose:receivedsponsorstatement', 'arupapplication');
        } else {
            $output .= get_string('progress:verbose:awaitingsponsorstatement', 'arupapplication');
        }
    }
    switch ($applicationstatus['progressbar']) {
        case 75 :
        case 95 :
        case 100 :
            $class = 'btn btn-default';
            break;
        default :
            $class = 'btn btn-primary';
            break;
    }
    $url = new moodle_url('/mod/arupapplication/complete.php', array('id' => $cm->id, 'gopage' => $applicationstatus['gopage']));
    $link = html_writer::link($url, $applicationstatus['button'], array('class' => $class));
    $output .= html_writer::div($link, 'text-right');
    $cm->set_content($output);
}

/**
 * Trigger the course_module_viewed event.
 *
 * @param  stdClass $arupapplication arupapplication object
 * @param  stdClass $course course object
 * @param  stdClass $cm course module object
 * @param  stdClass $context context object
 * @since Moodle 3.0
 */
function arupapplication_view($arupapplication, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $arupapplication->id
    );

    $event = \mod_arupapplication\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('arupapplication', $arupapplication);
    $event->trigger();
}