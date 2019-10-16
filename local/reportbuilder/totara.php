<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jonathan Newman <jonathan.newman@catalyst.net.nz>
 * @author Aaron Barnes <aaron.barnes@totaralms.com>
 * @package totara
 * @subpackage local_reportbuilder
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

define('PUBLIC_KEY_PATH', $CFG->dirroot . '/totara_public.pem');
define('TOTARA_SHOWFEATURE', 1);
define('TOTARA_HIDEFEATURE', 2);
define('TOTARA_DISABLEFEATURE', 3);

define('COHORT_ALERT_NONE', 0);
define('COHORT_ALERT_AFFECTED', 1);
define('COHORT_ALERT_ALL', 2);

define('COHORT_COL_STATUS_ACTIVE', 0);
define('COHORT_COL_STATUS_DRAFT_UNCHANGED', 10);
define('COHORT_COL_STATUS_DRAFT_CHANGED', 20);
define('COHORT_COL_STATUS_OBSOLETE', 30);

define('COHORT_BROKEN_RULE_NONE', 0);
define('COHORT_BROKEN_RULE_NOT_NOTIFIED', 1);
define('COHORT_BROKEN_RULE_NOTIFIED', 2);

define('COHORT_MEMBER_SELECTOR_MAX_ROWS', 1000);

define('COHORT_OPERATOR_TYPE_COHORT', 25);
define('COHORT_OPERATOR_TYPE_RULESET', 50);

define('COHORT_ASSN_ITEMTYPE_CATEGORY', 40);
define('COHORT_ASSN_ITEMTYPE_COURSE', 50);
define('COHORT_ASSN_ITEMTYPE_PROGRAM', 45);
define('COHORT_ASSN_ITEMTYPE_CERTIF', 55);
define('COHORT_ASSN_ITEMTYPE_MENU', 65);
define('COHORT_ASSN_ITEMTYPE_FEATURED_LINKS', 66);

// This should be extended when adding other tabs.
define ('COHORT_ASSN_VALUE_VISIBLE', 10);
define ('COHORT_ASSN_VALUE_ENROLLED', 30);
define ('COHORT_ASSN_VALUE_PERMITTED', 50);

// Visibility constants.
define('COHORT_VISIBLE_ENROLLED', 0);
define('COHORT_VISIBLE_AUDIENCE', 1);
define('COHORT_VISIBLE_ALL', 2);
define('COHORT_VISIBLE_NOUSERS', 3);

require_once("$CFG->dirroot/local/reportbuilder/lib.php");

/**
 * This function loads the program settings that are available for the user
 *
 * @param navigation_node $navinode The navigation_node to add the settings to
 * @param context $context
 * @param bool $forceopen If set to true the course node will be forced open
 * @return navigation_node|false
 */
function totara_load_program_settings($navinode, $context, $forceopen = false) {
    global $CFG;

    $program = new program($context->instanceid);
    $exceptions = $program->get_exception_count();
    $exceptioncount = $exceptions ? $exceptions : 0;

    $adminnode = $navinode->add(get_string('programadministration', 'totara_program'), null, navigation_node::TYPE_COURSE, null, 'progadmin');
    if ($forceopen) {
        $adminnode->force_open();
    }
    // Standard tabs.
    if (has_capability('totara/program:viewprogram', $context)) {
        $url = new moodle_url('/totara/program/edit.php', array('id' => $program->id, 'action' => 'view'));
        $adminnode->add(get_string('overview', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
                    'progoverview', new pix_icon('i/settings', get_string('overview', 'totara_program')));
    }
    if (has_capability('totara/program:configuredetails', $context)) {
        $url = new moodle_url('/totara/program/edit.php', array('id' => $program->id, 'action' => 'edit'));
        $adminnode->add(get_string('details', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
                    'progdetails', new pix_icon('i/settings', get_string('details', 'totara_program')));
    }
    if (has_capability('totara/program:configurecontent', $context)) {
        $url = new moodle_url('/totara/program/edit_content.php', array('id' => $program->id));
        $adminnode->add(get_string('content', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
                    'progcontent', new pix_icon('i/settings', get_string('content', 'totara_program')));
    }
    if (has_capability('totara/program:configureassignments', $context)) {
        $url = new moodle_url('/totara/program/edit_assignments.php', array('id' => $program->id));
        $adminnode->add(get_string('assignments', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
                    'progassignments', new pix_icon('i/settings', get_string('assignments', 'totara_program')));
    }
    if (has_capability('totara/program:configuremessages', $context)) {
        $url = new moodle_url('/totara/program/edit_messages.php', array('id' => $program->id));
        $adminnode->add(get_string('messages', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
                    'progmessages', new pix_icon('i/settings', get_string('messages', 'totara_program')));
    }
    if (($exceptioncount > 0) && has_capability('totara/program:handleexceptions', $context)) {
        $url = new moodle_url('/totara/program/exceptions.php', array('id' => $program->id, 'page' => 0));
        $adminnode->add(get_string('exceptions', 'totara_program', $exceptioncount), $url, navigation_node::TYPE_SETTING, null,
                    'progexceptions', new pix_icon('i/settings', get_string('exceptionsreport', 'totara_program')));
    }
    if ($program->certifid && has_capability('totara/certification:configurecertification', $context)) {
        $url = new moodle_url('/totara/certification/edit_certification.php', array('id' => $program->id));
        $adminnode->add(get_string('certification', 'local_custom_certification'), $url, navigation_node::TYPE_SETTING, null,
                    'certification', new pix_icon('i/settings', get_string('certification', 'local_custom_certification')));
    }
    if (!empty($CFG->enableprogramcompletioneditor) && has_capability('totara/program:editcompletion', $context)) {
        // Certification/Program completion editor. Added Feb 2016 to 2.5.36, 2.6.29, 2.7.12, 2.9.4.
        $url = new moodle_url('/totara/program/completion.php', array('id' => $program->id));
        $adminnode->add(get_string('completion', 'totara_program'), $url, navigation_node::TYPE_SETTING, null,
            'certificationcompletion', new pix_icon('i/settings', get_string('completion', 'totara_program')));
    }
    // Roles and permissions.
    $usersnode = $adminnode->add(get_string('users'), null, navigation_node::TYPE_CONTAINER, null, 'users');
    // Override roles.
    if (has_capability('moodle/role:review', $context)) {
        $url = new moodle_url('/admin/roles/permissions.php', array('contextid' => $context->id));
        $permissionsnode = $usersnode->add(get_string('permissions', 'role'), $url, navigation_node::TYPE_SETTING, null, 'override');
    } else {
        $url = null;
        $permissionsnode = $usersnode->add(get_string('permissions', 'role'), $url, navigation_node::TYPE_CONTAINER, null, 'override');
        $trytrim = true;
    }

    // Add assign or override roles if allowed.
    if (is_siteadmin()) {
        if (has_capability('moodle/role:assign', $context)) {
            $url = new moodle_url('/admin/roles/assign.php', array('contextid' => $context->id));
            $permissionsnode->add(get_string('assignedroles', 'role'), $url, navigation_node::TYPE_SETTING, null,
                    'roles', new pix_icon('t/assignroles', get_string('assignedroles', 'role')));
        }
    }
    // Check role permissions.
    if (has_any_capability(array('moodle/role:assign', 'moodle/role:safeoverride', 'moodle/role:override', 'moodle/role:assign'), $context)) {
        $url = new moodle_url('/admin/roles/check.php', array('contextid' => $context->id));
        $permissionsnode->add(get_string('checkpermissions', 'role'), $url, navigation_node::TYPE_SETTING, null,
                    'permissions', new pix_icon('i/checkpermissions', get_string('checkpermissions', 'role')));
    }
    // Just in case nothing was actually added.
    if (isset($trytrim)) {
        $permissionsnode->trim_if_empty();
    }

    $usersnode->trim_if_empty();
    $adminnode->trim_if_empty();
}










/**
 *  Calls module renderer to return markup for displaying a progress bar for a user's course progress
 *
 * @param int $userid User id
 * @param int $courseid Course id
 * @param int $status COMPLETION_STATUS_ constant
 * @return string
 */
function totara_display_course_progress_bar($userid, $courseid, $status) {
    global $PAGE;

    /** @var local_reportbuilder_renderer $renderer */
    $renderer = $PAGE->get_renderer('local_reportbuilder');
    $content = $renderer->course_progress_bar($userid, $courseid, $status);
    return $content;
}

function get_my_reports_list() {
    $reportbuilder_permittedreports = reportbuilder::get_user_permitted_reports();

    foreach ($reportbuilder_permittedreports as $key => $reportrecord) {
        if ($reportrecord->embedded) {
            try {
                new reportbuilder($reportrecord->id);
            } catch (moodle_exception $e) {
                if ($e->errorcode == "nopermission") {
                    // The report creation failed, almost certainly due to a failed is_capable check in an embedded report.
                    // In this case, we just skip it.
                    unset($reportbuilder_permittedreports[$key]);
                } else {
                    throw ($e);
                }
            }
        }
    }

    return $reportbuilder_permittedreports;
}


/**
* Returns markup for displaying saved scheduled reports
*
* Optionally without the options column and add/delete form
* Optionally with an additional sql WHERE clause
* @access public
* @param boolean $showoptions SHow icons to edit and delete scheduled reports.
* @param boolean $showaddform Show a simple form to allow reports to be scheduled.
* @param array $sqlclause In the form array($where, $params)
*/
function totara_print_scheduled_reports($showoptions=true, $showaddform=true, $sqlclause=array()) {
    global $CFG, $PAGE;

    require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
    require_once($CFG->dirroot . '/calendar/lib.php');
    require_once($CFG->dirroot . '/local/reportbuilder/scheduled_forms.php');

    $scheduledreports = get_my_scheduled_reports_list();

    // If we want the form generate the content so it can be used into the templated.
    if ($showaddform) {
        $mform = new scheduled_reports_add_form($CFG->wwwroot . '/local/reportbuilder/scheduled.php', array());
        $addform = $mform->render();
    } else {
        $addform = '';
    }

    $renderer = $PAGE->get_renderer('local_reportbuilder');
    echo $renderer->scheduled_reports($scheduledreports, $showoptions, $addform);
}


/**
 * Build a list of scheduled reports for display in a table.
 *
 * @param array $sqlclause In the form array($where, $params)
 * @return array
 * @throws coding_exception
 */
function get_my_scheduled_reports_list($sqlclause=array()) {
    global $DB, $REPORT_BUILDER_EXPORT_FILESYSTEM_OPTIONS, $USER;

    $myreports = reportbuilder::get_user_permitted_reports();

    $sql = "SELECT rbs.*, rb.fullname
            FROM {report_builder_schedule} rbs
            JOIN {report_builder} rb
            ON rbs.reportid=rb.id
            WHERE rbs.userid=?";

    $parameters = array($USER->id);

    if (!empty($sqlclause)) {
        list($conditions, $params) = $sqlclause;
        $parameters = array_merge($parameters, $params);
        $sql .= " AND " . $conditions;
    }
    //note from M2.0 these functions return an empty array, not false
    $scheduledreports = $DB->get_records_sql($sql, $parameters);
    //pre-process before sending to renderer
    foreach ($scheduledreports as $sched) {
        if (!isset($myreports[$sched->reportid])) {
            // Cannot access this report.
            unset($scheduledreports[$sched->id]);
            continue;
        }
        //data column
        if ($sched->savedsearchid != 0){
            $sched->data = $DB->get_field('report_builder_saved', 'name', array('id' => $sched->savedsearchid));
        }
        else {
            $sched->data = get_string('alldata', 'local_reportbuilder');
        }
        // Format column.
        $format = \local_reportbuilder\tabexport_writer::normalise_format($sched->format);
        $allformats = \local_reportbuilder\tabexport_writer::get_export_classes();
        if (isset($allformats[$format])) {
            $classname = $allformats[$format];
            $sched->format = $classname::get_export_option_name();
        } else {
            $sched->format = get_string('error');
        }
        // Export column.
        $key = array_search($sched->exporttofilesystem, $REPORT_BUILDER_EXPORT_FILESYSTEM_OPTIONS);
        $sched->exporttofilesystem = get_string($key, 'local_reportbuilder');
        //schedule column
        if (isset($sched->frequency) && isset($sched->schedule)){
            $schedule = new \local_reportbuilder\scheduler($sched, array('nextevent' => 'nextreport'));
            $formatted = $schedule->get_formatted();
            if ($next = $schedule->get_scheduled_time()) {
                if ($next < time()) {
                    // As soon as possible.
                    $next = time();
                }
                $formatted .= '<br />' . userdate($next);
            }
        } else {
            $formatted = get_string('schedulenotset', 'local_reportbuilder');
        }
        $sched->schedule = $formatted;
    }

    return $scheduledreports;
}







/**
 * Purge the MUC of ignored embedded reports and sources.
 *
 * @return void
 */
function totara_rb_purge_ignored_reports() {
    // Embedded reports.
    $cache = cache::make('local_reportbuilder', 'rb_ignored_embedded');
    $cache->purge();
    // Report sources.
    $cache = cache::make('local_reportbuilder', 'rb_ignored_sources');
    $cache->purge();
}



/**
 * Get course/program icon for displaying in course/program page.
 *
 * @param $instanceid
 * @return string icon URL.
 */
function totara_get_icon($instanceid, $icontype) {
    global $DB, $OUTPUT, $PAGE;

    $component = 'local_reportbuilder';
    $urlicon = '';

    if ($icontype == TOTARA_ICON_TYPE_COURSE) {
        $icon = $DB->get_field('course', 'icon', array('id' => $instanceid));
    } else {
        $icon = $DB->get_field('prog', 'icon', array('id' => $instanceid));
    }

    if ($customicon = $DB->get_record('files', array('pathnamehash' => $icon))) {
        $fs = get_file_storage();
        $context = context_system::instance();
        if ($file = $fs->get_file($context->id, $component, $icontype, $customicon->itemid, '/', $customicon->filename)) {
            $urlicon = moodle_url::make_pluginfile_url($file->get_contextid(), $component,
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $customicon->filename, true);
        }
    }

    if (empty($urlicon)) {
        $iconpath = $icontype . 'icons/';
        $imagelocation = $PAGE->theme->resolve_image_location($iconpath . $icon, $component);
        if (empty($icon) || empty($imagelocation)) {
            $icon = 'default';
        }
        $urlicon = $OUTPUT->pix_url('/' . $iconpath . $icon, $component);
    }

    return $urlicon->out();
}

/**
 * Determine if the current request is an ajax request
 *
 * @param array $server A $_SERVER array
 * @return boolean
 */
function is_ajax_request($server) {
    return (isset($server['HTTP_X_REQUESTED_WITH']) && strtolower($server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

/**
 * Totara specific initialisation
 * Currently needed only for AJAX scripts
 * Caution: Think before change to avoid conflict with other $CFG->moodlepageclass affecting code (for example installation scripts)
 */
function totara_setup() {
    global $CFG;
    if (is_ajax_request($_SERVER)) {
        $CFG->moodlepageclassfile = $CFG->dirroot.'/totara/core/pagelib.php';
        $CFG->moodlepageclass = 'totara_page';
    }
}

/**
 * SQL concat ready option of totara_get_all_user_name_fields function
 * This function return null-safe field names for concatentation into one field using $DB->sql_concat_join()
 *
 * @param string $tableprefix table query prefix to use in front of each field.
 * @param string $prefix prefix added to the name fields e.g. authorfirstname.
 * @param bool $onlyused true to only return the fields used by fullname() (and sorted as they appear)
 * @return array|string All name fields.
 */
function totara_get_all_user_name_fields_join($tableprefix = null, $prefix = null) {
    $fields = get_all_user_name_fields(false, $tableprefix, $prefix);
    foreach($fields as $key => $field) {
        $fields[$key] = "COALESCE($tableprefix.$field,'')";
    }
    return $fields;
}
/**
 * Is the clone db configured?
 *
 * @return bool
 */
function totara_is_clone_db_configured() {
    global $CFG;
    return !empty($CFG->clone_dbname);
}

/**
 * Returns instance of read only database clone.
 *
 * @param bool $reconnect force reopening of new connection
 * @return moodle_database|null
 */
function totara_get_clone_db($reconnect = false) {
    global $CFG;

    /** @var moodle_database $db */
    static $db = null;

    if ($reconnect) {
        if ($db) {
            $db->dispose();
        }
        $db = null;
    } else if (isset($db)) {
        if ($db === false) {
            // Previous init failed.
            return null;
        }
        return $db;
    }

    if (empty($CFG->clone_dbname)) {
        // Not configured, this is fine.
        $db = false;
        return null;
    }

    if (!$db = moodle_database::get_driver_instance($CFG->dbtype, $CFG->dblibrary, false)) {
        debugging('Cannot find driver for the cloned database', DEBUG_DEVELOPER);
        $db = false;
        return null;
    }

    try {
        // NOTE: dbname is always required and the prefix must be exactly the same.
        $dbhost = isset($CFG->clone_dbhost) ? $CFG->clone_dbhost : $CFG->dbhost;
        $dbuser = isset($CFG->clone_dbuser) ? $CFG->clone_dbuser : $CFG->dbuser;
        $dbpass = isset($CFG->clone_dbpass) ? $CFG->clone_dbpass : $CFG->dbpass;
        $dboptions = isset($CFG->clone_dboptions) ? $CFG->clone_dboptions : $CFG->dboptions;

        $db->connect($dbhost, $dbuser, $dbpass, $CFG->clone_dbname, $CFG->prefix, $dboptions);
    } catch (Exception $e) {
        debugging('Cannot connect to the cloned database', DEBUG_DEVELOPER);
        $db = false;
        return null;
    }

    return $db;
}

/**
 * print out the table of visible reports
 */
function totara_print_report_manager() {
    global $CFG, $USER, $PAGE;
    require_once($CFG->dirroot.'/local/reportbuilder/lib.php');

    $context = context_system::instance();
    $canedit = has_capability('local/reportbuilder:managereports',$context);

    $reportbuilder_permittedreports = get_my_reports_list();

    if (count($reportbuilder_permittedreports) > 0) {
        $renderer = $PAGE->get_renderer('local_reportbuilder');
        $returnstr = $renderer->report_list($reportbuilder_permittedreports, $canedit);
    } else {
        $returnstr = get_string('nouserreports', 'local_reportbuilder');
    }
    return $returnstr;
}