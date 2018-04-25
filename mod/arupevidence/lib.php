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
 * Library of interface functions and constants for module arupevidence.
 *
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/arupevidence/forms/completion_form.php');

defined('MOODLE_INTERNAL') || die();

define('ARUPEVIDENCE_COMPLETE', 1);

if(!defined('ARUPEVIDENCE_CPD')) {
    define('ARUPEVIDENCE_CPD', "0");
}

if(!defined('ARUPEVIDENCE_LMS')) {
    define('ARUPEVIDENCE_LMS', "1");
}

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function arupevidence_supports($feature) {
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
 * @param object $arupevidence An object from the form in mod_form.php
 * @param mod_newmodule_mod_form $mform
 * @return int The id of the newly inserted newmodule record
 */
function arupevidence_add_instance(stdClass $arupevidence, mod_arupevidence_mod_form $mform = null) {
    global $DB;

    $arupevidence->learningdesc = $arupevidence->learningdesc['text'];
    $arupevidence->timecreated = time();

    return $DB->insert_record('arupevidence', $arupevidence);
}

/**
 *
 * @param object $arupevidence An object from the form in mod_form.php
 * @param mod_arupevidence_mod_form $mform
 * @return boolean Success/Fail
 */
function arupevidence_update_instance(stdClass $arupevidence, mod_arupevidence_mod_form $mform = null) {
    global $DB;

    $arupevidence->learningdesc = $arupevidence->learningdesc['text'];
    $arupevidence->timemodified = time();
    $arupevidence->id = $arupevidence->instance;

    return $DB->update_record('arupevidence', $arupevidence);
}

/**
 * Removes an instance of the arupevidence from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function arupevidence_delete_instance($id) {
    global $DB;

    if (! $arupevidence = $DB->get_record('arupevidence', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('arupevidence', array('id' => $arupevidence->id));

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
function arupevidence_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    return $DB->record_exists('arupevidence_users', array(
            'arupevidenceid' => $cm->instance,
            'userid' => $userid,
            'completion' => ARUPEVIDENCE_COMPLETE,
            'archived' => 0));
}

function arupevidence_cm_info_dynamic(cm_info $cm) {
    global $CFG, $PAGE, $COURSE;
    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        if (!$PAGE->user_is_editing() || !can_update_moduleinfo($cm)) {
            $cm->set_no_view_link();
        }
    }
}

function arupevidence_cm_info_view(cm_info $cm) {
    global $DB, $USER, $OUTPUT;
    $contextcourse = context_course::instance($cm->course);
    $ahb = $DB->get_record('arupevidence',  array('id' => $cm->instance));

    $params = array('arupevidenceid' => $cm->instance, 'userid' => $USER->id, 'archived' => 0);
    $ahbuser = $DB->get_record('arupevidence_users', $params, '*', IGNORE_MULTIPLE);

    $iscomplete = (!empty($ahbuser) && $ahbuser->completion == '1');

    arupevidence_cm_info_view_auto($cm, $iscomplete);

    $msg = '';
    $evidenceboxes = array();

    if (empty($ahbuser) && false === $iscomplete){
        $boxlink = new moodle_url('/mod/arupevidence/view.php', array('id' => $cm->id));
        $messagebox = new stdClass();
        $messagebox->boxclasses = 'alert-warning upload-evidence';
        $messagebox->boxicon = 'fa fa-upload';
        $messagebox->boxmsg = get_string('provideevidence', 'mod_arupevidence');
        $messagebox->boxstatus = get_string('status:awaiting','mod_arupevidence');
        $messagebox->boxbtn = get_string('button:uploadevidence', 'mod_arupevidence');
        $messagebox->boxbtntype = 'btn-primary';
        $messagebox->boxlink = $boxlink->out();
        $evidenceboxes[] = $messagebox;
    } else if(!empty($ahbuser) && $ahb->approvalrequired && !$iscomplete) {
        $boxlink = new moodle_url('/mod/arupevidence/view.php', array('id' => $cm->id, 'action' => 'edit', 'ahbuserid' => $ahbuser->id));
        $messagebox = new stdClass();
        $messagebox->boxclasses = 'alert-white submitted-evidence';
        $messagebox->boxicon = 'fa fa-clock-o';
        $messagebox->boxmsg = get_string('pending:submittedforvalidation', 'mod_arupevidence');
        $messagebox->boxmsg = get_string('pending:submittedforvalidation', 'mod_arupevidence');
        $messagebox->boxstatus = ($ahbuser->rejected) ? get_string('status:evidencerejected','mod_arupevidence') : get_string('status:evidencesubmitteed','mod_arupevidence');
        $messagebox->boxbtn = get_string('button:amendsubmission', 'mod_arupevidence');
        $messagebox->boxbtntype = 'btn-default';
        $messagebox->boxlink = $boxlink->out();
        $evidenceboxes[] = $messagebox;
    }

    // Display approver page link
    $isuserapprover = arupevidence_isapprover($ahb, $USER);
    if ($isuserapprover && $ahb->approvalrequired) {
        $sql = "SELECT COUNT('x') as num
             FROM {arupevidence_users} au
             JOIN {arupevidence} a ON a.id = au.arupevidenceid
             WHERE au.arupevidenceid = :ahbid AND au.archived <> :archived AND completion = :completed";
        // count the pendings requests
        $params = array('ahbid' => $ahb->id, 'archived' => 1, 'completed' => 0);
        $pendings = $DB->count_records_sql($sql, $params);

        // count the approved requests
        $params = array('ahbid' => $ahb->id, 'archived' => 1, 'completed' => 1);
        $approved = $DB->count_records_sql($sql, $params);
        if ($pendings) {
            $boxlink = new moodle_url('/mod/arupevidence/approve.php', array('id' => $cm->id));
            $messagebox = new stdClass();
            $messagebox->boxclasses = 'alert-warning';
            $messagebox->boxicon = 'fa fa-pencil-square-o';
            $messagebox->boxmsg = get_string('pending:completionrequests', 'mod_arupevidence');
            $messagebox->boxstatus = get_string('status:pendingvalidation','mod_arupevidence', array('numberofpending'=> $pendings));
            $messagebox->boxbtn = get_string('button:validate', 'mod_arupevidence');
            $messagebox->boxbtntype = 'btn-primary';
            $messagebox->boxlink = $boxlink->out();
            $evidenceboxes[] = $messagebox;
        } else if (!$pendings && $approved) {
            $boxlink = new moodle_url('/mod/arupevidence/approve.php', array('id' => $cm->id));
            $messagebox = new stdClass();
            $messagebox->boxclasses = 'alert-success';
            $messagebox->boxicon = 'fa fa-thumbs-up ';
            $messagebox->boxmsg = get_string('allrequestapproved', 'mod_arupevidence');
            $messagebox->boxstatus = get_string('status:approvedevidence','mod_arupevidence', array('numberofapproved'=> $approved));
            $messagebox->boxbtn = get_string('button:check', 'mod_arupevidence');
            $messagebox->boxbtntype = 'btn-primary';
            $messagebox->boxlink = $boxlink->out().'#approved';
            $evidenceboxes[] = $messagebox;
        }
    } else if ($iscomplete) {
        $boxlink = new moodle_url('/mod/arupevidence/view.php', array('id' => $cm->id));
        $messagebox = new stdClass();
        $messagebox->boxclasses = 'alert-success';
        $messagebox->boxicon = 'fa fa-check-square-o';
        $messagebox->boxmsg = get_string('completionevidence', 'mod_arupevidence');
        $messagebox->boxstatus = get_string('status:uploadcomplete','mod_arupevidence');
        $messagebox->boxbtn = get_string('button:validate', 'mod_arupevidence');
        $messagebox->boxbtntype = 'btn-default';
        $messagebox->boxlink = $boxlink->out();
        $evidenceboxes[] = $messagebox;
    }

    $msg .= html_writer::div(get_string('msgerror', 'mod_arupevidence'), "alert alert-danger hide", array('role' => "alert"));



    // Construct arup evidence message box
    if(!empty($evidenceboxes)) {
        foreach ($evidenceboxes as $evidencebox) {
            $msg .= $OUTPUT->render_from_template('mod_arupevidence/evidence_messagebox', $evidencebox);
        }
    }

    $cm->set_content($msg);
}

function arupevidence_cm_info_view_auto(cm_info $cm, $iscomplete) {
    if (empty($iscomplete)) {
        $msg = html_writer::div(get_string('msgauto', 'mod_arupevidence'), "alert alert-warning", array('role' => "alert"));
    } else {
        $msg = html_writer::div(get_string('msgautocomplete', 'mod_arupevidence'), "alert alert-success", array('role' => "alert"));
    }
    $cm->set_content($msg);
}


/**
 * Serves the arupevidence files.
 *
 * @package mod_arupevidence
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
function arupevidence_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $USER;
    require_login();

    if($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $fs = get_file_storage();

    if($filearea == 'certificate' && $USER->id !== $itemid && !has_capability('mod/arupevidence:approvecompletion', $context)) {
        send_file_not_found();
    }

    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    if (!$file = $fs->get_file($context->id, 'mod_arupevidence', $filearea, $itemid, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, null, 0, false, $options);
}

function arupevidence_sendtotaps($id, $user, &$debug=array()) {
    global $DB;

    $data = $DB->get_record('arupevidence', array('id' => $id));

    // Avoid taps submittion if evidence is lms
    if ($data->cpdlms != ARUPEVIDENCE_CPD) {
        return false;
    }

    $arupevidence_user = $DB->get_record('arupevidence_users', array('userid' => $user->id, 'arupevidenceid' => $id, 'archived' => 0));

    if (empty($arupevidence_user)) {
        return false;
    }

    $midnight = usergetmidnight(time(), new DateTimeZone('UTC'));
    $params = array (
        'p_organization_name' => null,
        'p_location' => $arupevidence_user->location,
        'p_learning_method' => '',
        'p_subject_catetory' => '',
        'p_course_cost' => $arupevidence_user->classcost,
        'p_course_cost_currency' => $arupevidence_user->classcostcurrency,
        'p_course_start_date' => $midnight,
        'p_certificate_number' => $arupevidence_user->certificateno,
        'p_certificate_expiry_date' => $arupevidence_user->expirydate,
        'p_learning_desc' => $data->learningdesc,
        'locked' => true,
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

/**
 * Get all approver users in the course
 *
 * @param $arupevidence
 * @param $contextcourse
 * @return array
 */
function arupevidence_get_user_approvers($arupevidence, $contextcourse) {
    global $DB;
    $approveruserids = array();
    $approverrolseusers = array();
    if (!empty($arupevidence->approvalrole)) {
        $approverrolseusers = get_role_users($arupevidence->approvalrole, $contextcourse);
        // Get approval role users id
        foreach ($approverrolseusers as $u) {
            $approveruserids[] = $u->id;
        }
    }

    $where = '';
    $params = array();
    $approverlists = array();
    $userlists = array();


    $approvalusers = json_decode($arupevidence->approvalusers);
    if (!empty($approvalusers)) {
        list($in, $userparams) = $DB->get_in_or_equal($approvalusers);
        $where .= " id {$in}";
        $params = array_merge($params,$userparams);

        // remove users that are already on the approval roles
        if (!empty($approveruserids)) {
            list($notin, $approverparams) = $DB->get_in_or_equal($approveruserids, SQL_PARAMS_QM, '', false);
            $where .= " AND id {$notin}";
            $params = array_merge($params,$approverparams);
        }
        $userlists = $DB->get_records_select('user', $where, $params);
    }

    return array_merge($approverrolseusers,$userlists);
}

/**
 * Checks if user was evidence approver or has capability
 *
 * @param arupevidence $ahb
 * @param USER $user
 * @return bool
 */
function arupevidence_isapprover($ahb, $user) {
    global $COURSE;
    $contextcourse = context_course::instance($COURSE->id);

    if ($approverlists = arupevidence_get_user_approvers($ahb, $contextcourse)) {
       foreach ($approverlists as $approver) {
           if ($approver->id == $user->id) {
                return true;
           }
       }
    }

    // user has capability
    if (has_capability('mod/arupevidence:admin', $contextcourse, $user)) {
        return true;
    }

    return false;
}

function arupevidence_process_result($result, $debug=array()) {
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
function arupevidence_send_email($to, $from, $subject, $messagehtml, $cc = array()) {
    // Force HTML...
    $to->mailformat = 1;
    // Force maildisplay...
    $from->maildisplay = true;
    $messagetext = html_to_text($messagehtml);

    return email_to_user($to, $from, $subject, $messagetext, $messagehtml, '', '', true, '', '', 79, $cc);

}

/**
 * Calculate the difference in months between two dates
 *
 * @param string $date1
 * @param string $date2
 * @return int month
 */
function arupevidence_diffdates_bymonth($date1, $date2)
{
    $timezone = \core_date::get_user_timezone_object();

    $dateTime1 = new \DateTime();
    $dateTime1->setTimezone($timezone);
    $dateTime1->setTimestamp($date1);

    $dateTime2 = new \DateTime();
    $dateTime2->setTimezone($timezone);
    $dateTime2->setTimestamp($date2);
    $diff =  $dateTime2->diff($dateTime1);

    // Expiry date should ahead of completion date
    if ($diff->invert) {
        return 0;
    }
    $months = $diff->y * 12 + $diff->m + $diff->d / 30;

    return (int) round($months);
}

/**
 * Return evidence file info and link
 *
 * @param $context
 * @param $userid
 * @param $filearea
 * @param $itemid
 *
 * @return file|null
 */
function arupevidence_fileinfo($context, $userid = null, $filearea = null, $itemid = null)
{
    // Show link to the uploaded certificate file
    $fs = get_file_storage();
    $filearea = empty($filearea)? 'certificate' : $filearea;
    $itemid = empty($itemid)? $userid : $itemid ;

    $files = $fs->get_area_files($context->id, 'mod_arupevidence', $filearea);
    if ($files) {

        foreach ($files as $file) {
            $file->fileevidencelink = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

            if (!empty($filearea != 'certificate') && $itemid == $file->get_itemid() && $file->get_filesize() != 0) {
                return $file;
            }

            if(($itemid == $file->get_itemid() && $file->get_source() != null)) {
               return $file;
            }
        }
    }
    return null;
}

function arupevidence_move_filearea($context, $file, $filearea, $itemid) {

    $fs = get_file_storage();

    $file_record =  array('contextid'=>$context->id, 'component'=>'mod_arupevidence', 'filearea'=>$filearea,
        'itemid'=>$itemid, 'filepath'=>'/', 'filename'=>$file->get_filename(),
        'timecreated'=>time(), 'timemodified'=>time());

    $fileevidencepath = $file->copy_content_to_temp();

    try {
        $fs->create_file_from_pathname($file_record, $fileevidencepath);
        $file->delete(); // delete the old filearea
    } catch (Exception $e) {
        return false;
    }

    return true;
}

function arupevidence_fileareaname($data) {
    switch ($data) {
        case ARUPEVIDENCE_CPD:
            $filearea = get_string('cpdevidence', 'mod_arupevidence');
            break;
        case ARUPEVIDENCE_LMS:
            $filearea = get_string('lmsevidence', 'mod_arupevidence');
            break;
        default:
            $filearea = 'certificate';
    }

    return $filearea;
}

class arupevidence_user extends \core_user {
    public static function get_dummy_arupevidence_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'arupevidenceuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}