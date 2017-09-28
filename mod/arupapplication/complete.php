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
 * English strings for arupapplication
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_arupapplication
 * @copyright  2014 Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once($CFG->libdir . '/completionlib.php');

require_once('technicalreference_form.php');
require_once('submission_details_form.php');
require_once('statement_form.php');
require_once('qualifications_form.php');
require_once('declaration_form.php');
require_once('sponsorstatement_form.php');
require_once('viewsubmission_form.php');

arupapplication_init_arupapplication_session();

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', false, PARAM_INT);
$submissionid = optional_param('submissionid', 0, PARAM_INT);
$gopage = optional_param('gopage', -1, PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

if (! $cm = get_coursemodule_from_id('arupapplication', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $arupapplication = $DB->get_record("arupapplication", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext');
}

//check whether the feedback is located and! started from the mainsite
if ($course->id == SITEID AND !$courseid) {
    $courseid = SITEID;
}

if ($course->id == SITEID) {
    require_course_login($course, true);
} else {
    require_course_login($course, true, $cm);
}

//check whether the given courseid exists
if ($courseid AND $courseid != SITEID) {
    if ($course2 = $DB->get_record('course', array('id'=>$courseid))) {
        require_course_login($course2); //this overwrites the object $course :-(
        $course = $DB->get_record("course", array("id"=>$cm->course)); // the workaround
    } else {
        print_error('invalidcourseid');
    }
}

if (!$submissiondetails = $DB->get_record('arupsubmissions', array('applicationid'=>$arupapplication->id, 'userid'=>$USER->id))) {
    $submissionrec = new stdClass();
    $submissionrec->applicationid = $arupapplication->id;
    $submissionrec->userid = $USER->id;
    $submissionrec->timecreated = time();
    $DB->insert_record('arupsubmissions', $submissionrec);
    $submissiondetails = $DB->get_record('arupsubmissions', array('applicationid'=>$arupapplication->id, 'userid'=>$USER->id));

    $trackingrecord = new stdClass();
    $trackingrecord->userid = $USER->id;
    $trackingrecord->applicationid = $arupapplication->id;
    $trackingrecord->submissionid = $submissiondetails->id;
    $trackingrecord->completed = 0;
    $trackingrecord->timemodified = time();
    $DB->insert_record('arupapplication_tracking', $trackingrecord);
    $subject = get_string('instancename', 'arupapplication') .
        ' - ' .
        $course->fullname .
        ' - ' .
        get_string('progress:started', 'arupapplication');
    $from = get_admin();
    $from->maildisplay = true;
    arupapplication_sendemail(
        $USER,
        $from,
        $subject,
        $arupapplication->email_startnotification,
        $USER,
        $course->fullname,
        $CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $cm->id
    );
}

$user = $DB->get_record('user', array('id'=>$submissiondetails->userid));

$trackingrecord = $DB->get_record('arupapplication_tracking', array('userid'=>$user->id, 'applicationid'=>$arupapplication->id));

// Object to only update necessary fields
$submissionupdate = new stdClass();
$submissionupdate->id = $submissiondetails->id;

if ($gopage == -1 || $trackingrecord->completed < $gopage) {
    $gopage = $trackingrecord->completed;
}

// Mark activity viewed for completion-tracking
$completion = new completion_info($course);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

$urlparams = array('id'=>$cm->id, 'gopage'=>$gopage, 'courseid'=>$course->id);
$PAGE->set_url('/mod/arupapplication/complete.php', $urlparams);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($arupapplication->name));

//ishidden check.
//feedback in courses
if ((empty($cm->visible) AND
        !has_capability('moodle/course:viewhiddenactivities', $context)) AND
        $course->id != SITEID) {
    notice(get_string("activityiscurrentlyhidden"));
}

//ishidden check.
//feedback on mainsite
if ((empty($cm->visible) AND
        !has_capability('moodle/course:viewhiddenactivities', $context)) AND
        $courseid == SITEID) {
    notice(get_string("activityiscurrentlyhidden"));
}

$pageurl = $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cm->id . '&gopage=' . $gopage . '&courseid=' . $course->id;

if (!is_siteadmin()) {
    if ($trackingrecord->completed == 6) {
        switch($gopage) {
            case 0:
                if ($submissiondetails->referencesubmitted) {
                    redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
                }
                break;
            case 5:
                if ($submissiondetails->sponsorsubmitted) {
                    redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
                }
                break;
            case 6:
                //continue
                break;
            default:
                redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
                break;
        }
    }
}

switch($gopage) {
    case 1:
        //Applicant details
        if ($submissiondetails->joiningdate) {
            $submissiondetails->joiningmonth = gmdate('m', $submissiondetails->joiningdate);
            $submissiondetails->joiningyear = gmdate('Y', $submissiondetails->joiningdate);
        }
        $mform = new submission_form($pageurl);
        break;
    case 2:
        //Statement question answers
        $sql = "SELECT sqs.id as questionid, ap.id as applicationid, san.answer, san.id
FROM   {arupapplication} ap
INNER JOIN {arupstatementquestions} sqs ON ap.id = sqs.applicationid
LEFT JOIN {arupstatementanswers} san ON sqs.id = san.questionid AND san.userid = " . $user->id . "
WHERE ap.id = " . $arupapplication->id;
        if ($questionsanswers = $DB->get_records_sql($sql)) {
            foreach($questionsanswers as $questionsanswer) {
                $submissiondetails->{"qidanswer$questionsanswer->questionid"} = $questionsanswer->answer;
            }
        }
        $mform = new statement_form($pageurl,array('applicationid'=>$arupapplication->id));
        break;
    case 3:
        //CV and degree info
        $mform = new qualifications_form($pageurl,array('applicationid'=>$arupapplication->id));

        $draftitemid = file_get_submitted_draft_itemid('cv');
        file_prepare_draft_area($draftitemid, $context->id, 'mod_arupapplication', 'submission', $submissiondetails->id,
                array('subdirs' => 0, 'maxbytes' => ARUPAPPLICATION_MAX_FILESIZE, 'maxfiles' => ARUPAPPLICATION_MAX_FILES, 'accepted_types' => ARUPAPPLICATION_MAX_FILETYPE));
        $submissiondetails->cv = $draftitemid;

        break;
    case 4:
        //Declaration statements
        $sql = "SELECT dqs.id as declarationid, ap.id as applicationid, dac.answer, dac.id
FROM {arupapplication} ap
INNER JOIN {arupdeclarations} dqs ON ap.id = dqs.applicationid
LEFT JOIN {arupdeclarationanswers} dac ON dqs.id = dac.declarationid AND dac.userid = " . $user->id . "
WHERE ap.id = " . $arupapplication->id;
        if ($declarationanswers = $DB->get_records_sql($sql)) {
            foreach($declarationanswers as $declarationanswer) {
                $submissiondetails->{"declarationid$declarationanswer->declarationid"} = $declarationanswer->answer;
            }
        }
        $mform = new declaration_form($pageurl,array('applicationid'=>$arupapplication->id));
        break;
    case 5:
        //Sponsor details and form validation for the entire application
        $sponsor_audit = $submissiondetails->sponsor_audit;
        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_sponsor_footer);
        $footermessage = str_replace('[[user]]', fullname($user), $footermessage);
        $footermessage = str_replace('[[user:firstname]]', $user->firstname, $footermessage);

        $mform = new sponsorstatement_form($pageurl,array('contextid'=>$context->id, 'cmid'=>$id, 'applicationid'=>$arupapplication->id, 'submissionid'=>$submissiondetails->id, 'sponsorrequired'=>$arupapplication->sponsorstatementreq, 'sponsormessage_hint'=>$arupapplication->sponsormessage_hint, 'sponsor_audit'=>$sponsor_audit, 'submission_hint'=>$arupapplication->submission_hint, 'footermessage'=>$footermessage, 'submissionstate'=>$trackingrecord->completed, 'sponsor_audit'=>$sponsor_audit));
        break;
    case 6:
    case 7:
        //View complete application form
        $mform = new viewsubmission_form($pageurl, array('contextid'=>$context->id, 'cmid'=>$id, 'userid'=>$user->id, 'applicationid'=>$arupapplication->id, 'submissionid'=>$submissiondetails->id));
        break;
    default:
        //Referee details
        $referee_email = $submissiondetails->referee_email;
        $referee_message = $submissiondetails->referee_message;
        $referee_audit = $submissiondetails->referee_audit;
        $refereemessage_hint = $arupapplication->refereemessage_hint;

        $referencereceived = arupapplication_referencesponsorfeedback($arupapplication->id, $user->id, 'referee');
        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_referee_footer);
        $footermessage = str_replace('[[user]]', fullname($user), $footermessage);
        $footermessage = str_replace('[[user:firstname]]', $user->firstname, $footermessage);
        $mform = new technicalreference_form($pageurl, array('refereemessage_hint'=>$refereemessage_hint, 'referee_email'=>$referee_email, 'referee_audit'=>$referee_audit, 'footermessage'=>$footermessage, 'referencereceived'=>$referencereceived));
        break;
}
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    if (isset($fromform->thispage)) {
        switch ($fromform->thispage) {
            case 'referee':
                if (isset($fromform->continuebutton)) {
                    if ($trackingrecord->completed < 1) {
                        $trackingrecord->completed = 1;
                        $trackingrecord->timemodified = time();
                        $DB->update_record('arupapplication_tracking', $trackingrecord);
                    }
                    redirect($CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $id . '&gopage=1');
                } else {
                    $submissionupdate->referee_email = $fromform->referee_email;
                    $submissionupdate->referee_message = $fromform->referee_message;

                    if (isset($fromform->resendrefemail) || isset($fromform->gonextpage)) {
                        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_referee_footer);
                        $footermessage = str_replace('[[user]]', fullname($user), $footermessage);
                        $footermessage = str_replace('[[user:firstname]]', $user->firstname, $footermessage);
                        $auditcontent = $fromform->referee_email . '||' . fullname($user) . '||' . gmdate("d/M/Y H:i", time()) . '||' . text_to_html($fromform->referee_message) . text_to_html($footermessage);
                        if (empty($submissiondetails->referee_audit)) {
                            $submissionupdate->referee_audit = $auditcontent;
                        } else {
                            $submissionupdate->referee_audit = $submissiondetails->referee_audit . '$$$' . $auditcontent;
                        }
                        // Don't increment if status is already higher
                        if ($trackingrecord->completed < 1) {
                            $appstatus = 1;
                        } else {
                            $appstatus = $trackingrecord->completed;
                        }

                        $obj_refereeemail = arupapplication_user::get_dummy_arupapplication_user($submissionupdate->referee_email);

                        $subject = get_string('instancename', 'arupapplication') .
                            ' - ' .
                            fullname($user) .
                            ' - ' .
                            get_string('heading:technicalreference', 'arupapplication');
                        $body = $fromform->referee_message . chr(10). chr(10) . $arupapplication->email_referee_footer;
                        $body = str_ireplace('[[referee:email]]', $submissionupdate->referee_email, $body);

                        $USER->maildisplay = true;
                        arupapplication_sendemail(
                            $obj_refereeemail,
                            $USER,
                            $subject,
                            $body,
                            $user,
                            $course->fullname,
                            $CFG->wwwroot . '/mod/arupapplication/referee.php?id=' . $cm->id . '&appid=' . $submissiondetails->id
                        );
                    } else {
                        $appstatus = $trackingrecord->completed;
                    }

                    if ($trackingrecord->completed == 6) {
                        $submissiondetails->timemodified = time();
                        $DB->update_record('arupsubmissions', $submissionupdate);

                        $trackingrecord->timemodified = time();
                        $DB->update_record('arupapplication_tracking', $trackingrecord);
                        redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
                        exit;
                    }
                }
                break;
            case 'details':
                foreach($fromform as $key=>$value) {
                    switch($key){
                        case 'title':
                        case 'passportname':
                        case 'knownas':
                        case 'knownas':
                        case 'dateofbirth':
                        case 'countryofresidence':
                        case 'requirevisa':
                        case 'grade':
                        case 'jobtitle':
                        case 'discipline':
                        case 'joiningdate':
                        case 'arupgroup':
                        case 'businessarea':
                        case 'officelocation':
                        case 'otherofficelocation':
                            $submissionupdate->{$key} = $value;
                            break;
                        case 'joiningmonth':
                            $joiningmonth = $value;
                            break;
                        case 'joiningyear':
                            $joiningyear = $value;
                            break;
                    }
                }
                $joiningdate = gmmktime(0, 0, 0, $joiningmonth, 1, $joiningyear);
                $submissionupdate->joiningdate = $joiningdate;
                $appstatus = 2;
                break;
            case 'statements':
                foreach($questionsanswers as $questionsanswer) {
                    if(isset($fromform->{"qidanswer$questionsanswer->questionid"})) {
                        $questionsanswer->answer = $fromform->{"qidanswer$questionsanswer->questionid"};
                        if(empty($questionsanswer->id)) {
                            $questionsanswer->userid = $user->id;
                            $questionsanswer->timecreated = time();
                            if (!empty($questionsanswer->answer)) {
                                $DB->insert_record('arupstatementanswers', $questionsanswer);
                            }
                        } else {
                            $questionsanswer->timemodified = time();
                            $DB->update_record('arupstatementanswers', $questionsanswer);
                        }
                    }
                }
                $appstatus = 3;
                break;
            case 'qualification':
                $fs = get_file_storage();
                $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission', $submissiondetails->id);
                if ($newfilename = $mform->get_new_filename('cv')) {
                    $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission', $submissiondetails->id); // Omitting itemid deletes all files from the specified file area

                    $file = $mform->save_stored_file('cv', $context->id, 'mod_arupapplication', 'submission',
                        $submissiondetails->id, '/', $newfilename);
                    $submissionupdate->cv = $submissiondetails->id;
                }
                $submissionupdate->degree = $fromform->degree;
                $appstatus = 4;
                break;
            case 'declarations':
                foreach($declarationanswers as $declarationanswer) {
                    if(isset($fromform->{"declarationid$declarationanswer->declarationid"})) {
                        $declarationanswer->answer = $fromform->{"declarationid$declarationanswer->declarationid"};
                        if(empty($declarationanswer->id)) {
                            $declarationanswer->userid = $user->id;
                            $declarationanswer->timecreated = time();
                            if ($declarationanswer->answer != 0) {
                                $DB->insert_record('arupdeclarationanswers', $declarationanswer);
                            }
                        } else {
                            $declarationanswer->timemodified = time();
                            $DB->update_record('arupdeclarationanswers', $declarationanswer);
                        }
                    }
                }
                $appstatus = 5;
                break;
            case 'sponsor':
                if (isset($fromform->submitapplication)) {
                    $alreadysubmitted = $trackingrecord->completed == 6;
                    if ($arupapplication->sponsorstatementreq) {
                        $submissionupdate->sponsor_email = $fromform->sponsor_email;
                        $submissionupdate->sponsor_message = $fromform->sponsor_message;
                        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_sponsor_footer);
                        $footermessage = str_replace('[[user]]', fullname($user), $footermessage);
                        $footermessage = str_replace('[[user:firstname]]', $user->firstname, $footermessage);
                        $auditcontent = $fromform->sponsor_email . '||' . fullname($USER) . '||' . gmdate("d/M/Y H:i", time()) . '||' . $fromform->sponsor_message . text_to_html($footermessage);
                        if (empty($submissiondetails->sponsor_audit)) {
                            $submissionupdate->sponsor_audit = $auditcontent;
                        } else {
                            $submissionupdate->sponsor_audit = $submissiondetails->sponsor_audit . '$$$' . $auditcontent;
                        }
                    }
                    $submissionupdate->timemodified = time();
                    $DB->update_record('arupsubmissions', $submissionupdate);
                    $trackingrecord->completed = 6;
                    $trackingrecord->timemodified = time();
                    $DB->update_record('arupapplication_tracking', $trackingrecord);

                    if ($arupapplication->sponsorstatementreq) {
                        $obj_sponsoremail = arupapplication_user::get_dummy_arupapplication_user($submissionupdate->sponsor_email);

                        $subject = get_string('instancename', 'arupapplication') .
                            ' - ' .
                            fullname($user) .
                            ' - ' .
                            get_string('heading:sponsorstatement', 'arupapplication');
                        $body = $fromform->sponsor_message . chr(10). chr(10) . $arupapplication->email_sponsor_footer;
                        $body = str_ireplace('[[sponsor:email]]', $submissionupdate->sponsor_email, $body);

                        $USER->maildisplay = true;
                        arupapplication_sendemail(
                            $obj_sponsoremail,
                            $USER,
                            $subject,
                            $body,
                            $user,
                            $course->fullname,
                            $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $cm->id . '&appid=' . $submissiondetails->id
                        );
                    }

                    if (!$alreadysubmitted) {
                        $subject = get_string('instancename', 'arupapplication') .
                            ' - ' .
                            $course->fullname .
                            ' - ' .
                            get_string('progress:submitted', 'arupapplication');
                        $from = get_admin();
                        $from->maildisplay = true;
                        arupapplication_sendemail(
                            $user,
                            $from,
                            $subject,
                            $arupapplication->email_submissionnotification,
                            $user,
                            $course->fullname,
                            $CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $cm->id
                        );
                    }

                    redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
                    exit;
                } elseif ($arupapplication->sponsorstatementreq) {
                    $submissionupdate->sponsor_email = $fromform->sponsor_email;
                    $submissionupdate->sponsor_message = $fromform->sponsor_message;
                }
                $appstatus = 6;
                break;
        }
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        // Handle saving and exiting or going back (but not too far)
        if (isset($fromform->savevalues) && $appstatus > 0) {
            $appstatus--;
        } elseif (isset($fromform->gopreviouspage)) {
            if ($appstatus > 2) {
                $appstatus = $appstatus - 2;
            } elseif ($appstatus > 1) {
                $appstatus--;
            }
        }

        $trackingrecord->completed = $appstatus;
        $trackingrecord->timemodified = time();
        $DB->update_record('arupapplication_tracking', $trackingrecord);

        if (isset($fromform->savevalues)) {
            redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id);
        } elseif (isset($fromform->resendrefemail) || isset($fromform->submitapplication)) {
            redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
        } elseif (isset($fromform->gonextpage)) {
            $gopage = $gopage + 1;
            redirect($CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $id . '&gopage=' . $gopage);
        } elseif (isset($fromform->gopreviouspage)) {
            $gopage = $gopage - 1;
            redirect($CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $id . '&gopage=' . $gopage);
        }
    }
}
echo $OUTPUT->header();
$mform->set_data($submissiondetails);
$mform->display();
echo html_writer::tag('div', $arupapplication->footer, array('class' => 'hint'));
if ($gopage < 6) {
    echo html_writer::tag('p', get_string('progressindication', 'arupapplication', array('pagenumber'=>$gopage + 1)), array('class'=>'progressinfo'));
}

echo $OUTPUT->footer();