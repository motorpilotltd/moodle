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
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Saves a new instance of the arupmyproxy activity into the database
 *
 * @param stdClass $arupmyproxy Submitted data from the form in mod_form.php
 * @param mod_arupmyproxy_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted arupmyproxy record
 */
function arupmyproxy_add_instance(stdClass $arupmyproxy, mod_arupmyproxy_mod_form $mform = null) {
    global $DB;

    $arupmyproxy->timemodified = $arupmyproxy->timecreated = time();

    $arupmyproxy->id = $DB->insert_record('arupmyproxy', $arupmyproxy);

    // Add capability assignment.
    $coursecontext = context_course::instance($arupmyproxy->course);
    assign_capability('moodle/course:view', CAP_ALLOW, $arupmyproxy->roleid, $coursecontext->id);
    $coursecontext->mark_dirty();

    return $arupmyproxy->id;
}

/**
 * Updates an instance of the arupmyproxy activity in the database
 *
 * @param stdClass $arupmyproxy An object from the form in mod_form.php
 * @param mod_arupmyproxy_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Failure
 */
function arupmyproxy_update_instance(stdClass $arupmyproxy, mod_arupmyproxy_mod_form $mform = null) {
    global $DB;

    $oldarupmyproxy = $DB->get_record('arupmyproxy', array('id' => $arupmyproxy->instance));

    $arupmyproxy->timemodified = time();
    $arupmyproxy->id = $arupmyproxy->instance;

    $result = $DB->update_record('arupmyproxy', $arupmyproxy);

    // Sort capability assignment.
    $coursecontext = context_course::instance($arupmyproxy->course);
    if ($oldarupmyproxy && $oldarupmyproxy->roleid != $arupmyproxy->roleid) {
        unassign_capability('moodle/course:view', $arupmyproxy->roleid, $coursecontext->id);
    }
    assign_capability('moodle/course:view', CAP_ALLOW, $arupmyproxy->roleid, $coursecontext->id);
    $coursecontext->mark_dirty();

    return $result;
}

/**
 * Removes an instance of the arupmyproxy activity from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function arupmyproxy_delete_instance($id) {
    global $DB;

    $arupmyproxy = $DB->get_record('arupmyproxy', array('id' => $id));

    if (!$arupmyproxy) {
        return false;
    }

    // Delete proxies.
    $DB->delete_records('arupmyproxy_proxies', array('arupmyproxyid' => $arupmyproxy->id));
    // Delete activity.
    $DB->delete_records('arupmyproxy', array('id' => $arupmyproxy->id));

    $coursecontext = context_course::instance($arupmyproxy->course);

    // Remove role assignment from users.
    role_unassign_all(
        array(
            'roleid' => $arupmyproxy->roleid,
            'contextid' => $coursecontext->id,
            'component' => 'mod_arupmyproxy',
            'itemid' => $arupmyproxy->id
        ),
        false,
        false
    );

    // Unassign specially added capability.
    $stillused = $DB->count_records_select(
        'arupmyproxy',
        'course = :courseid AND roleid = :roleid',
        array('courseid' => $arupmyproxy->course, 'roleid' => $arupmyproxy->roleid)
    );
    if ($stillused == 0) {
        // No other arupmyproxy activities using this cap.
        unassign_capability('moodle/course:view', $arupmyproxy->roleid, $coursecontext->id);
        $coursecontext->mark_dirty();
    }

    return true;
}

/**
 * Carries out dynamic actions on activity content for course page.
 * Hides activity link unless editing (and can add instance)
 *
 * @param cm_info $cm
 */
function arupmyproxy_cm_info_dynamic(cm_info $cm) {
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
 * Carries out view actions on activity content for course page.
 *
 * @param cm_info $cm
 */
function arupmyproxy_cm_info_view(cm_info $cm) {
    global $CFG, $PAGE, $SESSION, $USER;

    require_once($CFG->dirroot.'/mod/arupmyproxy/classes/arupmyproxy.php');

    $arupmyproxyclass = new arupmyproxy($cm->instance);

    $renderer = $PAGE->get_renderer('mod_arupmyproxy');

    // Check if already proxying.
    if (\core\session\manager::is_loggedinas()) {
        $realuser = \core\session\manager::get_realuser();
        $a = new stdClass();
        $a->realfullname = fullname($realuser);
        $a->fullname = fullname($USER);
        $alert = html_writer::start_tag('p');
        $alert .= get_string('currentlyloggedinas', 'arupmyproxy', $a);
        $logouturl = new moodle_url('/mod/arupmyproxy/logout.php', array('id' => $cm->id));
        $alert .= html_writer::empty_tag('br');
        $alert .= html_writer::link($logouturl, get_string('logout', 'arupmyproxy'), array('class' => 'btn btn-danger'));
        $alert .= html_writer::end_tag('p');
        $cm->set_content($renderer->alert($alert, 'alert-warning', false));
        return;
    }

    $prewrapper = '';
    $wrapped = '';

    $wrappedalert = isset($SESSION->arupmyproxy[$cm->id]->errors->pending) || isset($SESSION->arupmyproxy[$cm->id]->errors->request);

    if (isset($SESSION->arupmyproxy[$cm->id]->alert)) {
        $prewrapper .= $renderer->alert($SESSION->arupmyproxy[$cm->id]->alert->message, $SESSION->arupmyproxy[$cm->id]->alert->type);
        unset($SESSION->arupmyproxy[$cm->id]->alert);
    }

    // Choose user to login as (already had proxy accepted).
    $prewrapper .= $renderer->proxy_loginas($arupmyproxyclass->get_loginas_users(), $cm->id);

    // Refused requests.
    $wrapped .= $renderer->proxy_refused($arupmyproxyclass->get_refused_users(), $cm->id);

    // Show pending requests.
    $wrapped .= $renderer->proxy_pending($arupmyproxyclass->get_pending_users(), $cm->id);

    // Request to be proxy.
    $wrapped .= $renderer->proxy_request($arupmyproxyclass->get_request_users(), $cm->id);

    $output = '';
    if (empty($prewrapper) && empty($wrapped) && !$cm->has_view()) {
        $cm->set_extra_classes('hide');
    } else {
        $PAGE->requires->js_call_amd('mod_arupmyproxy/enhance', 'initialise');
        $output .= $prewrapper;
        if (!empty($wrapped)) {
            $output .= $renderer->view_wrapper($wrapped, $wrappedalert);
        }
    }

    $cm->set_content($output);
}

/**
 * Informs of supported features in arupmyproxy
 *
 * @param string $feature
 * @return int|boolean|null
 */
function arupmyproxy_supports($feature) {
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
            return false; // See arupmyproxy_cm_info_dynamic().
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
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        default:
            return null;
    }
}

/**
 * Trigger the course_module_viewed event.
 *
 * @param  stdClass $arupmyproxy arupmyproxy object
 * @param  stdClass $course course object
 * @param  stdClass $cm course module object
 * @param  stdClass $context context object
 * @since Moodle 3.0
 */
function arupmyproxy_view($arupmyproxy, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $arupmyproxy->id
    );

    $event = \mod_arupmyproxy\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('arupmyproxy', $arupmyproxy);
    $event->trigger();
}