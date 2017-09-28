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
 * Prints a particular instance of arupapplication
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_arupapplication
 * @copyright  2014 Epic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace arupapplication with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit_form.php');

$id = required_param('id', PARAM_INT);   // course module
$submissionid = required_param('submissionid', PARAM_INT);   // submission id
$edit = optional_param('edit', 0,PARAM_INT);   // edit submission

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
if ($course->id AND $course->id != SITEID) {
    if ($course2 = $DB->get_record('course', array('id'=>$course->id))) {
        require_course_login($course2); //this overwrites the object $course :-(
        $course = $DB->get_record("course", array("id"=>$cm->course)); // the workaround
    } else {
        print_error('invalidcourseid');
    }
}

$context = context_module::instance($cm->id);

if (!has_capability('mod/arupapplication:edititems', $context)) {
    notice(get_string('cannotviewsubmission', 'arupapplication'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$event = \mod_arupapplication\event\submission_viewed::create(array(
    'context' => $context,
    'objectid' => $submissionid,
));
$event->trigger();

$submissiondetails = arupapplication_submissionsdetails($submissionid);

$userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));

if ($userdetails) {
    $submissiondetails->firstname = $userdetails->firstname;
    $submissiondetails->lastname = $userdetails->lastname;
    $submissiondetails->staffid = $userdetails->idnumber;
}

if ($submissiondetails->joiningdate) {
    $submissiondetails->joiningmonth = gmdate('m', $submissiondetails->joiningdate);
    $submissiondetails->joiningyear = gmdate('Y', $submissiondetails->joiningdate);
}

//Statement question answers
if ($questionsanswers = arupapplication_submissionsstatementans($arupapplication->id, $submissiondetails->userid)) {
    foreach($questionsanswers as $questionsanswer) {
        $submissiondetails->{"qidanswer$questionsanswer->questionid"} = $questionsanswer->answer;
    }
}

//Declaration statements
if ($declarationanswers = arupapplication_declarationans($arupapplication->id, $submissiondetails->userid)) {
    foreach($declarationanswers as $declarationanswer) {
        $submissiondetails->{"declarationid$declarationanswer->declarationid"} = $declarationanswer->answer;
    }
}

$completion = new completion_info($course);

$PAGE->set_url('/mod/arupapplication/edit.php', array('id' => $id, 'submissionid' => $submissionid));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

$mform = new editsubmission_form($CFG->wwwroot .'/mod/arupapplication/edit.php?id=' . $id . '&edit=' . $edit . '&submissionid=' . $submissionid,
            array('edit'=>$edit,
                'status'=> $submissiondetails->completed,
                'submissionid'=>$submissiondetails->id,
                'contextid'=>$context->id,
                'applicationid'=>$arupapplication->id,
                'userid'=>$submissiondetails->userid,
                'referencesubmitted'=>$submissiondetails->referencesubmitted,
                'referee_audit'=>$submissiondetails->referee_audit,
                'sponsorsubmitted'=>$submissiondetails->sponsorsubmitted,
                'sponsor_audit'=>$submissiondetails->sponsor_audit,
                'sponsordeclarationlabel'=>$arupapplication->sponsordeclarationlabel));
if ($edit) {
    $draftitemid = file_get_submitted_draft_itemid('cv');
    file_prepare_draft_area($draftitemid, $context->id, 'mod_arupapplication', 'submission', $submissiondetails->id,
        array('subdirs' => 0, 'maxbytes' => ARUPAPPLICATION_MAX_FILESIZE, 'maxfiles' => ARUPAPPLICATION_MAX_FILES, 'accepted_types' => ARUPAPPLICATION_MAX_FILETYPE));
    $submissiondetails->cv = $draftitemid;
}
if ($mform->is_cancelled()) {
    //Handle form cancel operation
    redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);
} else if ($fromform = $mform->get_data()) {
    // Object to only update necessary fields
    $submissionupdate = new stdClass();
    $submissionupdate->id = $submissiondetails->id;

    $sendrefereeemail = false;
    $sendsponsoremail = false;

/// Resend email to technical referee checks ///
    // if technical reference has not been submitted
    // OR
    // on submission if no email sent yet
    if ((isset($fromform->resendreferenceemail) && !$submissiondetails->referencesubmitted)
        || (isset($fromform->submitapplication)) && empty($submissiondetails->referee_audit)) {
        $sendrefereeemail = true;
    }
/// Resend email to sponsor checks ///
    // if sponsor statement of support has not been submitted
    // AND form has been completed OR on submission
    if (((isset($fromform->resendsponsoremail) && $submissiondetails->completed == 6) || isset($fromform->submitapplication)) && !$submissiondetails->sponsorsubmitted) {
        $sendsponsoremail = true;
    }

    $userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));

/// Resend email to technical referee ///

    if ($sendrefereeemail) {
        $submissionupdate->referee_email = $fromform->referee_email;
        $submissionupdate->referee_message = $fromform->referee_message;

        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_referee_footer);
        $footermessage = str_replace('[[user]]', fullname($userdetails), $footermessage);
        $footermessage = str_replace('[[user:firstname]]', $userdetails->firstname, $footermessage);
        $footermessage = str_replace('[[link]]', get_string('clickhere', 'arupapplication', $CFG->wwwroot . '/mod/arupapplication/referee.php?id=' . $cm->id . '&appid=' . $submissionid), $footermessage);
        $footermessage = str_replace('[[linkurl]]', $CFG->wwwroot . '/mod/arupapplication/referee.php?id=' . $cm->id . '&appid=' . $submissionid, $footermessage);

        $auditcontent = $fromform->referee_email . '||' . fullname($USER) . '||' . gmdate("d/M/Y H:i", time()) . '||' . $fromform->referee_message . $footermessage;
        if (empty($submissiondetails->referee_audit)) {
            $submissionupdate->referee_audit = $auditcontent;
        } else {
            $submissionupdate->referee_audit = $submissiondetails->referee_audit . '$$$' . $auditcontent;
        }
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        $obj_refereeemail = arupapplication_user::get_dummy_arupapplication_user($submissionupdate->referee_email);

        $subject = get_string('instancename', 'arupapplication') .
            ' - ' .
            fullname($userdetails) .
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
            $userdetails,
            $course->fullname,
            $CFG->wwwroot . '/mod/arupapplication/referee.php?id=' . $cm->id . '&appid=' . $submissionid
        );
    }

    if ($sendsponsoremail) {
        $submissionupdate->sponsor_email = $fromform->sponsor_email;
        $submissionupdate->sponsor_message = $fromform->sponsor_message;
        $footermessage = str_replace('[[course]]', $course->fullname, $arupapplication->email_sponsor_footer);
        $footermessage = str_replace('[[user]]', fullname($userdetails), $footermessage);
        $footermessage = str_replace('[[user:firstname]]', $userdetails->firstname, $footermessage);
        $footermessage = str_replace('[[link]]', get_string('clickhere', 'arupapplication', $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $cm->id . '&appid=' . $submissionid), $footermessage);
        $footermessage = str_replace('[[linkurl]]', $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $cm->id . '&appid=' . $submissionid, $footermessage);

        //$footermessage = str_replace('[[link]]', $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $cm->id . '&appid=' . $submissionid, $footermessage);
        $auditcontent = $fromform->sponsor_email . '||' . fullname($USER) . '||' . gmdate("d/M/Y H:i", time()) . '||' . $fromform->sponsor_message . text_to_html($footermessage);
        if (empty($submissiondetails->sponsor_audit)) {
            $submissionupdate->sponsor_audit = $auditcontent;
        } else {
            $submissionupdate->sponsor_audit = $submissiondetails->sponsor_audit . '$$$' . $auditcontent;
        }
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        $obj_sponsoremail = arupapplication_user::get_dummy_arupapplication_user($submissionupdate->sponsor_email);

        $subject = get_string('instancename', 'arupapplication') .
            ' - ' .
            fullname($userdetails) .
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
            $userdetails,
            $course->fullname,
            $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $cm->id . '&appid=' . $submissionid
        );

        if ($trackingrecord = $DB->get_record('arupapplication_tracking', array('userid'=>$submissiondetails->userid, 'applicationid'=>$submissiondetails->applicationid))) {
            $trackingrecord->completed = 6;
            $trackingrecord->timemodified = time();
            $DB->update_record('arupapplication_tracking', $trackingrecord);
        }
    }

/// Submit technical reference ///

    if (isset($fromform->submitreference)) {
        foreach($fromform as $key=>$value) {
            switch($key) {
                case 'reference_phone':
                case 'referenceposition':
                case 'referenceknown':
                case 'referencetalent':
                case 'referenceperformance':
                case 'referencemotivation':
                case 'referenceknowledge':
                case 'referencecomments':
                    $submissionupdate->{$key} = $value;
                    break;
            }
        }
        $submissionupdate->referencesubmitted = 1;
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        $completetracking = false;
        if($arupapplication->sponsorstatementreq) {
            if($submissiondetails->sponsorsubmitted) {
                $completetracking = true;
            }
        } else {
            $completetracking = true;
        }

        if($completetracking) {
            if ($trackingrecord = $DB->get_record('arupapplication_tracking', array('userid'=>$submissiondetails->userid, 'applicationid'=>$submissiondetails->applicationid))) {
                $trackingrecord->completed = 7;
                $trackingrecord->timemodified = time();
                $DB->update_record('arupapplication_tracking', $trackingrecord);

                $subject = get_string('instancename', 'arupapplication') .
                    ' - ' .
                    $course->fullname .
                    ' - ' .
                    get_string('progress:complete', 'arupapplication');
                $from = get_admin();
                $from->maildisplay = true;
                arupapplication_sendemail(
                    $userdetails,
                    $from,
                    $subject,
                    $arupapplication->email_completenotification,
                    $userdetails,
                    $course->fullname,
                    $CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $cm->id
                );
            }
            $completion->update_state($cm,COMPLETION_COMPLETE);
        }
    }

/// Submit sponsor statement of support ///

    if (isset($fromform->submitsponsor)) {
        foreach($fromform as $key=>$value) {
            switch($key) {
                case 'sponsorstatement':
                case 'sponsordeclaration':
                    $submissionupdate->{$key} = $value;
                    break;
            }
        }
        $submissionupdate->sponsorsubmitted = 1;
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        $completetracking = false;
        if($arupapplication->technicalreferencereq) {
            if($submissiondetails->referencesubmitted) {
                $completetracking = true;
            }
        } else {
            $completetracking = true;
        }

        if($completetracking) {
            if ($trackingrecord = $DB->get_record('arupapplication_tracking', array('userid'=>$submissiondetails->userid, 'applicationid'=>$submissiondetails->applicationid))) {
                $trackingrecord->completed = 7;
                $trackingrecord->timemodified = time();
                $DB->update_record('arupapplication_tracking', $trackingrecord);

                $subject = get_string('instancename', 'arupapplication') .
                    ' - ' .
                    $course->fullname .
                    ' - ' .
                    get_string('progress:complete', 'arupapplication');
                $from = get_admin();
                $from->maildisplay = true;
                arupapplication_sendemail(
                    $userdetails,
                    $from,
                    $subject,
                    $arupapplication->email_completenotification,
                    $userdetails,
                    $course->fullname,
                    $CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $cm->id
                );
            }
            $completion->update_state($cm,COMPLETION_COMPLETE);
        }
    }

/// Submit application ///
/// Save values ///

    if (isset($fromform->savevalues) || isset($fromform->submitapplication)) {
        // CV
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission', $submissiondetails->id);
        if ($newfilename = $mform->get_new_filename('cv')) {
            $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission', $submissiondetails->id); // Omitting itemid deletes all files from the specified file area

            $file = $mform->save_stored_file('cv', $context->id, 'mod_arupapplication', 'submission',
                $submissiondetails->id, '/', $newfilename);
            $submissionupdate->cv = $submissiondetails->id;
        }

        // Data
        foreach($fromform as $key=>$value) {
            switch($key) {
                case 'reference_phone':
                case 'referenceposition':
                case 'referenceknown':
                case 'referencetalent':
                case 'referenceperformance':
                case 'referencemotivation':
                case 'referenceknowledge':
                case 'referencecomments':
                case 'sponsorstatement':
                case 'sponsordeclaration':
                    //Ignore referee and sponsor fields
                    break;
                case 'joiningmonth':
                    $joiningmonth = $value;
                    break;
                case 'joiningyear':
                    $joiningyear = $value;
                    break;
                default:
                    $submissionupdate->{$key} = $value;
                    break;
            }
        }
        $joiningdate = gmmktime(0, 0, 0, $joiningmonth, 1, $joiningyear);
        $submissionupdate->joiningdate = $joiningdate;
        $submissionupdate->timemodified = time();
        $DB->update_record('arupsubmissions', $submissionupdate);

        foreach($questionsanswers as $questionsanswer) {
            if(isset($fromform->{"qidanswer$questionsanswer->questionid"})) {
                $questionsanswer->answer = $fromform->{"qidanswer$questionsanswer->questionid"};
                if(empty($questionsanswer->id)) {
                    $questionsanswer->userid = $submissiondetails->userid;
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
        foreach($declarationanswers as $declarationanswer) {
            if(isset($fromform->{"declarationid$declarationanswer->declarationid"})) {
                $declarationanswer->answer = $fromform->{"declarationid$declarationanswer->declarationid"};
                if(empty($declarationanswer->id)) {
                    $declarationanswer->userid = $USER->id;
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
    }

    redirect($CFG->wwwroot . '/mod/arupapplication/view.php?id=' . $id);

}
echo $OUTPUT->header();
$mform->set_data($submissiondetails);
$mform->display();
echo $OUTPUT->footer();