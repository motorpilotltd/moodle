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
    global $COURSE, $DB;

    $aruphonestybox->classname = $COURSE->fullname;
    $aruphonestybox->learningdesc = $aruphonestybox->learningdesc['text'];
    $aruphonestybox->timecreated = time();
    $aruphonestybox->durationunitscode = 'H'; // Set to H/Hour(s) as default
    $aruphonestybox->duration = \mod_tapsenrol\taps::combine_duration_hours($aruphonestybox->duration);

    return $DB->insert_record('aruphonestybox', $aruphonestybox);
}

/**
 *
 * @param object $aruphonestybox An object from the form in mod_form.php
 * @param mod_aruphonestybox_mod_form $mform
 * @return boolean Success/Fail
 */
function aruphonestybox_update_instance(stdClass $aruphonestybox, mod_aruphonestybox_mod_form $mform = null) {
    global $COURSE, $DB;

    $aruphonestybox->classname = $COURSE->fullname;
    $aruphonestybox->learningdesc = $aruphonestybox->learningdesc['text'];
    $aruphonestybox->timemodified = time();
    $aruphonestybox->duration = \mod_tapsenrol\taps::combine_duration_hours($aruphonestybox->duration);
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
    global $DB, $PAGE, $USER;

    $ahb = $DB->get_record('aruphonestybox',  array('id' => $cm->instance));

    $params = array('aruphonestyboxid' => $cm->instance, 'userid' => $USER->id);
    $ahbuser = $DB->get_record('aruphonestybox_users', $params, '*', IGNORE_MULTIPLE);

    $iscomplete = (!empty($ahbuser) && $ahbuser->taps == '1');

    if (empty($ahb->manualindicate)) {
        aruphonestybox_cm_info_view_auto($cm, $iscomplete);
        return;
    }

    $PAGE->requires->js_call_amd('mod_aruphonestybox/main', 'initialise');

    $attributes = array('data-instance' => $cm->instance, 'class' => 'tapscomplete');
    $hbform  = $modal = $msg = '';

    if (false === $iscomplete) {
        $msg = html_writer::div(get_string('msgincomplete', 'mod_aruphonestybox'), "alert alert-warning", array('role' => "alert"));

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
    }

    $msg .= html_writer::div(get_string('msgsuccess', 'mod_aruphonestybox'), "alert alert-success" . ($iscomplete ? '' : ' hide'), array('role' => "alert"));
    $msg .= html_writer::div(get_string('msgerror', 'mod_aruphonestybox'), "alert alert-danger hide", array('role' => "alert"));

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

function aruphonestybox_sendtotaps($id, $user, &$debug=array()) {
    global $DB;

    $data = $DB->get_record('aruphonestybox', array('id' => $id));

    $lrsrecord = new \local_learningrecordstore\lrsentry();

    $lrsrecord->staffid = $user->idnumber; // Set staffid (Only used to add CPD).
    $lrsrecord->provider = $data->provider;
    $lrsrecord->origin = 'mod_arupevidence';
    $lrsrecord->originid = $data->id;
    $lrsrecord->originname = $data->classname;
    $lrsrecord->starttime = $lrsrecord->completiontime = usergetmidnight(time(), new DateTimeZone('UTC'));
    $lrsrecord->duration = $data->duration;
    $lrsrecord->durationunits = $data->durationunitscode;
    $lrsrecord->location = $data->location;
    $lrsrecord->classcost = $data->classcost;
    $lrsrecord->classcostcurrency = $data->classcostcurrency;
    $lrsrecord->certificateno = $data->certificateno;
    $lrsrecord->expirydate = empty($data->expirydate) ? null : $data->expirydate;
    $lrsrecord->description = $data->learningdesc;
    $lrsrecord->healthandsafetycategory = $data->healthandsafetycategory;
    $lrsrecord->locked = true;

    $lrsrecord->insert();

    $debug[] = 'added cpd record: ' . print_r($lrsrecord, true);

    return $lrsrecord->id;
}