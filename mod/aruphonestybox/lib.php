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
 * Library of interface functions and constants for module aruphonestybox.
 *
 * @package    mod_aruphonestybox
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/aruphonestybox/forms/completion_form.php');

defined('MOODLE_INTERNAL') || die();

define('ARUPHONESTYBOX_COMPLETE', 1);

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function aruphonestybox_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 *
 * @param object $aruphonestybox An object from the form in mod_form.php
 * @param mod_newmodule_mod_form $mform
 * @return int The id of the newly inserted newmodule record
 */
function aruphonestybox_add_instance(stdClass $aruphonestybox, mod_aruphonestybox_mod_form $mform = null) {
    global $DB;

    $aruphonestybox->learningdesc = $aruphonestybox->learningdesc['text'];
    $aruphonestybox->timecreated = time();

    return $DB->insert_record('aruphonestybox', $aruphonestybox);
}

/**
 *
 * @param object $aruphonestybox An object from the form in mod_form.php
 * @param mod_aruphonestybox_mod_form $mform
 * @return boolean Success/Fail
 */
function aruphonestybox_update_instance(stdClass $aruphonestybox, mod_aruphonestybox_mod_form $mform = null) {
    global $DB;

    $aruphonestybox->learningdesc = $aruphonestybox->learningdesc['text'];
    $aruphonestybox->timemodified = time();
    $aruphonestybox->id = $aruphonestybox->instance;

    return $DB->update_record('aruphonestybox', $aruphonestybox);
}

/**
 * Removes an instance of the aruphonestybox from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function aruphonestybox_delete_instance($id) {
    global $DB;

    if (! $aruphonestybox = $DB->get_record('aruphonestybox', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('aruphonestybox', array('id' => $aruphonestybox->id));

    return true;
}

/**
 * Obtains the automatic completion state for this module based on any conditions
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function aruphonestybox_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    return $DB->record_exists('aruphonestybox_users', array(
            'aruphonestyboxid' => $cm->instance,
            'userid' => $userid,
            'completion' => ARUPHONESTYBOX_COMPLETE));
}

function aruphonestybox_cm_info_dynamic(cm_info $cm) {
    global $CFG, $PAGE, $COURSE;
    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        if (!$PAGE->user_is_editing() || !can_update_moduleinfo($cm)) {
            $cm->set_no_view_link();
        }
    }
}

function aruphonestybox_cm_info_view(cm_info $cm) {
    global $DB, $PAGE, $USER, $CFG;

    $ahb = $DB->get_record('aruphonestybox',  array('id' => $cm->instance));

    $params = array('aruphonestyboxid' => $cm->instance, 'userid' => $USER->id);
    $ahbuser = $DB->get_record('aruphonestybox_users', $params, '*', IGNORE_MULTIPLE);

    $iscomplete = (!empty($ahbuser) && $ahbuser->taps == '1');

    $context = context_module::instance($cm->id);


    if (empty($ahb->manualindicate)) {
        aruphonestybox_cm_info_view_auto($cm, $iscomplete);
        return;
    }

    $PAGE->requires->js_call_amd('mod_aruphonestybox/main', 'initialise');

    $attributes = array('data-instance' => $cm->instance, 'class' => 'tapscomplete');
    $hbform  = $modal = $msg = '';


    if (false === $iscomplete && (!$ahb->showcompletiondate && !$ahb->showcertificateupload)) {
        $msg .= html_writer::div(get_string('msgincomplete', 'mod_aruphonestybox'), "alert alert-warning", array('role' => "alert"));

        $checkbox2 = html_writer::checkbox('tapscomplete', true, false, get_string('ihavecompleted', 'aruphonestybox'), $attributes);
        $hbform .= html_writer::div($checkbox2, empty($attributes['disabled']) ? '' : 'disabledrow');

        // Add modal.
        $modal  = html_writer::start_div(
                'modal fade',
                array('id' => "myModal", 'tabindex' => "-1", 'role' => "dialog", 'aria-labelledby' => "myModalLabel", 'aria-hidden' => "true")
                );
        $modal .= html_writer::start_div('modal-dialog modal-sm');
        $modal .= html_writer::start_div('modal-content');
        $modal .= html_writer::div(get_string('pleaseconfirm', 'mod_aruphonestybox'), 'modal-body');
        $modal .= html_writer::div('<div class="modal-footer">
            <button type="button" class="btn btn-success">'.get_string('confirm').'</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">'.get_string('cancel').'</button>
          </div>');
        $modal .= html_writer::end_div();
        $modal .= html_writer::end_div();
    } else if (empty($ahbuser) && false === $iscomplete && ($ahb->showcompletiondate || $ahb->showcertificateupload)){
        $viewlink = new moodle_url('/mod/aruphonestybox/view.php', array('id' => $cm->id));
        $msg .= html_writer::div(get_string('msgincomplete:viewlink', 'mod_aruphonestybox', $viewlink->out()), "alert alert-warning", array('role' => "alert"));
    }

    if (has_capability('mod/aruphonestybox:approvecompletion', $context) && $ahb->approvalrequired) {
        $approvelink = new moodle_url('/mod/aruphonestybox/approve.php', array('id' => $cm->id));
        $msg .= html_writer::div(get_string('approvallink', 'mod_aruphonestybox', $approvelink->out()), "alert alert-warning", array('role' => "alert"));
    }

    $msg .= html_writer::div(get_string('msgsuccess', 'mod_aruphonestybox'), "alert alert-success" . ($iscomplete ? '' : ' hide'), array('role' => "alert"));
    $msg .= html_writer::div(get_string('msgerror', 'mod_aruphonestybox'), "alert alert-danger hide", array('role' => "alert"));

    if(!empty($ahbuser) && $ahb->approvalrequired && !$iscomplete) {
        $editurl = new moodle_url('/mod/aruphonestybox/view.php', array('id' => $cm->id, 'action' => 'edit', 'ahbuserid' => $ahbuser->id));
        $msg .= html_writer::div(get_string('msgpending:editlink', 'mod_aruphonestybox', $editurl->out()), "alert alert-warning", array('role' => "alert"));
    }

    $cm->set_content($msg . $hbform . $modal);
}

function aruphonestybox_cm_info_view_auto(cm_info $cm, $iscomplete) {
    if (empty($iscomplete)) {
        $msg = html_writer::div(get_string('msgauto', 'mod_aruphonestybox'), "alert alert-warning", array('role' => "alert"));
    } else {
        $msg = html_writer::div(get_string('msgautocomplete', 'mod_aruphonestybox'), "alert alert-success", array('role' => "alert"));
    }
    $cm->set_content($msg);
}


/**
 * Serves the aruphonestybox files.
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
function aruphonestybox_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $USER;
    require_login();
    if($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if($filearea !== 'certificate') {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $fs = get_file_storage();

    if($USER->id !== $itemid && !has_capability('mod/aruphonestybox:approvecompletion', $context)) {
        send_file_not_found();
    }

    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    if (!$file = $fs->get_file($context->id, 'mod_aruphonestybox', 'certificate', $itemid, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, null, 0, false, $options);
}

function aruphonestybox_sendtotaps($id, $user, &$debug=array()) {
    global $DB;

    $data = $DB->get_record('aruphonestybox', array('id' => $id));

    $midnight = usergetmidnight(time(), new DateTimeZone('UTC'));
    $params = array (
        'p_organization_name' => null,
        'p_location' => $data->location,
        'p_learning_method' => $data->classtype,
        'p_subject_catetory' => $data->classcategory,
        'p_course_cost' => $data->classcost,
        'p_course_cost_currency' => $data->classcostcurrency,
        'p_course_start_date' => $midnight,
        'p_certificate_number' => $data->certificateno,
        'p_certificate_expiry_date' => empty($data->expirydate) ? null : $data->expirydate,
        'p_learning_desc' => $data->learningdesc,
        'p_health_and_safety_category' => $data->healthandsafetycategory
    );

    $taps = new \local_taps\taps();
    $result = $taps->add_cpd_record(
            $user->idnumber,
            $data->classname,
            $data->provider,
            $midnight,
            $data->duration,
            $data->durationunitscode,
            $params
    );
    $debug[] = 'added cpd record: ' . "{$user->idnumber}, {$data->classname}, {$data->provider}, ".
        strtoupper(date('d-M-Y')) . ", {$data->duration}, {$data->durationunitscode}," .
        print_r($params, true);

    return $result;
}

function aruphonestybox_process_result($result, $debug=array()) {
    $return = new stdClass;
    $return->success = false;

    if ($result === false) {
        $return->error = get_string('alert:error:failedtoconnect', 'block_arup_mylearning');
    } else if (!empty($result['errorid']) && $result['errorid'] < 0) {
        if (get_string_manager()->string_exists($result['errormessage'], 'local_taps')) {
            $a = get_string($result['errormessage'], 'local_taps');
        } else {
            $a = $result['errormessage'];
        }
        $return->error = $a;
    } else {
        $return->success = true;
    }
    if (!empty($debug)) {
        $return->debug = $debug;
    }
    return $return;
}

/**
 * Send email notification
 *
 * @param $to
 * @param $from
 * @param $subject
 * @param $messagehtml
 * @param $cc
 * @return bool
 */
function aruphonestybox_send_email($to, $from, $subject, $messagehtml, $cc = array()) {
    // Force HTML...
    $to->mailformat = 1;
    // Force maildisplay...
    $from->maildisplay = true;
    $messagetext = html_to_text($messagehtml);

    return email_to_user($to, $from, $subject, $messagetext, $messagehtml, '', '', true, '', '', 79, $cc);

}

class aruphonestybox_user extends \core_user {
    public static function get_dummy_aruphonestybox_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'aruphonestyboxuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}