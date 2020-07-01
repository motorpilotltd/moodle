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
 * @package    mod_arupevidence
 * @copyright  2014 Paul Stanyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/arupevidence/forms/upload_form.php');
require_once($CFG->dirroot.'/mod/arupevidence/lib.php');

 // Course Module ID.
if(!$id = required_param('id', PARAM_INT)) {
    print_error('missingparameter');
}

if (!$cm = get_coursemodule_from_id('arupevidence', $id)) {
    print_error('invalidcoursemodule');
}

$cm = cm_info::create($cm);

if(!$course = $DB->get_record('course', array('id' => $cm->course))){
    print_error('coursemisconf');
}

require_login($course, false, $cm);

// Set up session variable for alert if not already set.
if (!isset($SESSION->arupevidence)) {
    $SESSION->arupevidence = new stdClass();
}

$context = context_module::instance($cm->id);
$contextcourse = context_course::instance($course->id);

require_capability('mod/arupevidence:addevidence', $context);

$ahb = $DB->get_record('arupevidence',  array('id' => $cm->instance));
$courseurl = course_get_format($course)->get_view_url($cm->sectionnum);
$uploadurl = new moodle_url('/mod/arupevidence/upload.php', array('id' => $id));
$approveurl = new moodle_url('/mod/arupevidence/approve.php', array('id' => $id));

$title = $course->fullname . ': ' . $ahb->name;
$PAGE->set_title($title);
$PAGE->set_url($uploadurl);

$output = $PAGE->get_renderer('mod_arupevidence');
$content = '';
$declarations = $DB->get_records('arupevidence_declarations', array('arupevidenceid' => $ahb->id), 'id ASC');
$customdata = array(
    'contextid' => $context->id,
    'arupevidence' => $ahb,
    'declarations' => $declarations
);

$taps = new \local_taps\taps();

$mform = new mod_arupevidence_upload_form($uploadurl, $customdata);
if ($mform->is_cancelled()) {
   redirect($courseurl);
} else if($data = $mform->get_data()) {
    $foruser = core_user::get_user($data->ahbuserid, '*', MUST_EXIST);

    // Replacements for language strings.
    $a = new stdClass();
    $a->userfullname = fullname($foruser);
    $a->userstaffid = $foruser->idnumber;
    $a->enrolmentdetails = '';

    // First enrol on the class if not already enrolled.
    if ($ahb->cpdlms == ARUPEVIDENCE_LMS) {
        $tapsenrols = $DB->get_records('tapsenrol', array('course' => $COURSE->id));
        require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');
        $tapsenrol = new \tapsenrol(reset($tapsenrols)->id, 'instance');
        $tapsenrol->enrolment_check($foruser->idnumber);

        // Are they already enrolled, and in a suitable state?
        $existingenrolments = $tapsenrol->taps->get_enroled_classes($foruser->idnumber, $tapsenrol->get_tapscourse()->courseid, true);
        foreach ($existingenrolments as $existingenrolment) {
            $a->enrolmentcoursename = $existingenrolment->coursename;
            $a->enrolmentclassname = $existingenrolment->classname;
            $a->enrolmentbookingstatus = $existingenrolment->bookingstatus;
            if (!$tapsenrol->taps->is_status($existingenrolment->bookingstatus, 'placed')) {
                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('uploadforuser:error:enrolmentnotplaced', 'mod_arupevidence', $a);
                $SESSION->arupevidence->alert->type = 'alert-danger';
                redirect($courseurl);
            }
            if ($existingenrolment->classid != $data->classid) {
                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('uploadforuser:error:enrolmentdiffclass', 'mod_arupevidence', $a);
                $SESSION->arupevidence->alert->type = 'alert-danger';
                redirect($courseurl);
            }
            unset($a->enrolmentcoursename);
            unset($a->enrolmentclassname);
            unset($a->enrolmentbookingstatus);
        }
        // Either false or single placed enrolment on correct class!
        $enrolment = reset($existingenrolments);
        if (!$enrolment) {
            $enrolresult = $tapsenrol->enrol_employee($data->classid, $foruser->idnumber, true);
            if (!$enrolresult->success) {
                $a->message = $enrolresult->message;
                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('uploadforuser:error:enrolmentfailed', 'mod_arupevidence', $a);
                $SESSION->arupevidence->alert->type = 'alert-danger';
                redirect($courseurl);
            }
            $enrolment = $enrolresult->enrolment;
        }
        $enrolmentdetails = new stdClass();
        $enrolmentdetails->enrolmentcoursename = $enrolment->coursename;
        $enrolmentdetails->enrolmentclassname = $enrolment->classname;
        $enrolmentdetails->enrolmentbookingstatus = $enrolment->bookingstatus;
        $a->enrolmentdetails = get_string('uploadforuser:success:enrolmentdetails', 'mod_arupevidence', $enrolmentdetails);
    }

    // Set itemid as the userid of the user being submitted for.
    $itemid = $foruser->id;
    file_save_draft_area_files(
        $data->completioncertificate,
        $context->id,
        'mod_arupevidence',
        'certificate',
        $itemid, // Set userid as itemid.
        array(
            'subdirs' => 0,
            'maxbytes' => $COURSE->maxbytes,
            'maxfiles' => 1
        )
    );

    try {
        // Escape/remove special characters.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_arupevidence', 'certificate', $itemid);
        if ($files) {
            foreach ($files as $file) {
                $pattern = "/[^A-Za-z0-9\_\s\-\.]/";
                if (($itemid == $file->get_itemid() && $file->get_source() != null && preg_match($pattern, $file->get_filename()))) {
                    $newfilename = core_text::specialtoascii($file->get_filename());
                    $file->rename($file->get_filepath(), $newfilename);
                }
            }
        }
    } catch (Exception $e) {}

    $arupevidencedata = array(
        'completiondate' => $data->completiondate,
        'validityexpirydate' => $data->validityexpirydate,
        'expirydate' => !empty($data->expirydate) ? $data->expirydate : 0,
        'validityperiod' => !empty($data->validityperiod) ? $data->validityperiod : '',
        'validityperiodunit' => !empty($data->validityperiodunit) ? $data->validityperiodunit : ''
    );

    if ($ahb->cpdlms == ARUPEVIDENCE_CPD) {
        $cpddetails = array(
            'provider' => $data->provider,
            'duration' => $taps->combine_duration_hours($data->duration),
            'durationunitscode' => 'H',
            'location' => $data->location,
            'classstartdate' => $data->classstartdate,
            'classcost' => !empty($data->classcost) ? $data->classcost : null,
            'classcostcurrency' => $data->classcostcurrency,
            'certificateno' => $data->certificateno);
        $arupevidencedata = array_merge($arupevidencedata, $cpddetails);
    }

    if ($ahb->exemption) {
        $exemptiondetails = [
            'exempt' => $data->exempt,
            'exemptreason' => $data->exemptreason,
        ];
        $arupevidencedata = array_merge($arupevidencedata, $exemptiondetails);
        if ($data->exempt && $ahb->exemptioncompletion) {
            $arupevidencedata['completiondate'] = null;
        }
    }

    // Saving declaration agreement
    $agreeddeclaration = [];
    if (!empty($declarations)) {
        foreach ($declarations as $declaration) {
            if (isset($data->{'declaration-'.$declaration->id}) && $data->{'declaration-'.$declaration->id}) {
                $agreeddeclaration[] = $declaration->id;

                // Tagging declaration to agreed
                if ($declaration_agreed = $DB->get_record('arupevidence_declarations', array('id' => $declaration->id, 'has_agreed' => 0))) {
                    // Update declaration has_agreed
                    $declaration_agreed->has_agreed = 1;
                    $DB->update_record('arupevidence_declarations', $declaration_agreed);
                }
            }
        }
    }

    // Archive any current records.
    $updatesql = "UPDATE {arupevidence_users} SET archived = 1, timemodified = :timemodified WHERE arupevidenceid = :arupevidenceid AND userid = :userid";
    $updateparams = [
        'arupevidenceid' => $cm->instance,
        'userid' => $foruser->id,
        'timemodified' => time(),
    ];
    $DB->execute($updatesql, $updateparams);

    $ahbuser = new stdClass;
    $ahbuser->arupevidenceid = $cm->instance;
    $ahbuser->userid = $foruser->id;
    $ahbuser->completion = ($ahb->approvalrequired)? 0 : 1 ;
    $ahbuser->itemid = ($ahb->cpdlms == ARUPEVIDENCE_LMS)? $enrolment->enrolmentid : null;
    $ahbuser->approved = null;
    $ahbuser->declarations = !empty($agreeddeclaration)? json_encode($agreeddeclaration): null;
    $ahbuser->timemodified = time();
    $ahbuser->uploadedby = $USER->id;

    // Appends input data from user.
    foreach($arupevidencedata as $key => $value) {
        $ahbuser->{$key} = $value;
    }

    $DB->insert_record('arupevidence_users', $ahbuser);

    // Send mail to the approvers.
    if($ahb->approvalrequired) {
        $approverlists = arupevidence_get_user_approvers($ahb, $contextcourse);

        $foruser->employmentcategory = $DB->get_field_sql(
            'SELECT EMPLOYMENT_CATEGORY FROM SQLHUB.ARUP_ALL_STAFF_V WHERE EMPLOYEE_NUMBER = :staffid',
            ['staffid' => (int) $foruser->idnumber]
        );
        foreach ($approverlists as $approverto) {
            $subject = get_string('email:subject', 'mod_arupevidence', array(
                'coursename' => $course->fullname,
            ));
            $messagebody = get_string('email:body', 'mod_arupevidence', array(
                'coursename' => $course->fullname,
                'approverfirstname' => $approverto->firstname,
                'approvalurl' => $approveurl->out(),
                'userfirstname' => $foruser->firstname,
                'employmentcategory' => $foruser->employmentcategory,
            ));
            $sendnotification = arupevidence_send_email($approverto, $foruser, $subject, $messagebody);
        }
    }
    // Add message to session.
    $SESSION->arupevidence->alert = new stdClass();
    $SESSION->arupevidence->alert->message = get_string('uploadforuser:success', 'mod_arupevidence', $a);
    $SESSION->arupevidence->alert->type = 'alert-success';
    redirect($courseurl);
} else {
    $content .= $mform->render();
}

$PAGE->requires->css(new moodle_url('/mod/arupevidence/css/select2.min.css'));
$PAGE->requires->css(new moodle_url('/mod/arupevidence/css/select2-bootstrap.min.css'));
$PAGE->requires->css('/mod/arupevidence/styles.css');
$PAGE->requires->js_call_amd('mod_arupevidence/upload', 'init', ['courseid' => $course->id]);

$arguments = array(
    'validityperiod' =>  $ahb->expectedvalidityperiod,
    'validityperiodunit' => $ahb->expectedvalidityperiodunit,
);
$PAGE->requires->js_call_amd('mod_arupevidence/view', 'init', $arguments);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('modulename', 'mod_arupevidence') . ': '. $ahb->name);
echo html_writer::tag('h3', get_string('uploadforuser', 'mod_arupevidence'));

echo $output->alert(get_string('uploadforuser:help:general', 'mod_arupevidence'), 'alert-warning', false);
if ($ahb->cpdlms == ARUPEVIDENCE_LMS) {
    echo $output->alert(get_string('uploadforuser:help:lms', 'mod_arupevidence'), 'alert-warning', false);
}

echo $content;

echo $output->return_to_course($course->id);

echo $OUTPUT->footer();