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
require_once($CFG->libdir.'/completionlib.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once('sponsor_form.php');

$id = optional_param('id', 0, PARAM_INT);   // course module id
$appid = optional_param('appid', 0, PARAM_INT);   // arup application submission id

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

if ($id) {
    $cm         = get_coursemodule_from_id('arupapplication', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $arupapplication  = $DB->get_record('arupapplication', array('id' => $cm->instance), '*', MUST_EXIST);
    $submissiondetails  = $DB->get_record('arupsubmissions', array('id' => $appid, 'applicationid' => $cm->instance));
    if (!$context = context_module::instance($cm->id)) {
        print_error('badcontext');
    }
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login();

$systemcontext = context_system::instance();

$event = \mod_arupapplication\event\reference_page_viewed::create(array(
    'context' => $systemcontext,
    'objectid' => $appid,
    'other' => array(
        'type' => 'SPONSOR',
    ),
));
$event->trigger();

$PAGE->set_context($systemcontext);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

$PAGE->set_url('/mod/arupapplication/sponsor.php', array('id' => $id, 'appid' => $appid));
$PAGE->set_title(format_string($arupapplication->name));
$PAGE->set_heading(format_string($course->fullname));


if ($submissiondetails) {
    if (strtolower($USER->email) == strtolower($submissiondetails->sponsor_email)) {
        //Module completion state
        $completion = new completion_info($course);

        $submissiondetails->moduletitle = $arupapplication->name;
        $userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));
        $submissiondetails->applicantname = fullname($userdetails);
        $sponsorform = new sponsor_form($CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $id . '&appid=' . $appid, array('contextid'=>$context->id, 'cmid'=>$id, 'userid'=>$submissiondetails->userid, 'applicationid'=>$submissiondetails->applicationid, 'submissionid'=>$submissiondetails->id, 'sponsorsubmitted'=>$submissiondetails->sponsorsubmitted, 'sponsorstatement_hint'=>$arupapplication->sponsorstatement_hint, 'sponsordeclarationlabel'=>$arupapplication->sponsordeclarationlabel, 'footermessage'=>$arupapplication->footer));
        if ($sponsorform->is_cancelled()) {
            //Handle form cancel operation, if cancel button is present on form
            redirect($CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $id);
        } elseif ($fromform = $sponsorform->get_data()) {
            if ($submissiondetails->sponsorsubmitted) {
                echo get_string('error:sponsor:nothingtodo', 'arupapplication');
            } else {
                // Object to only update necessary fields
                $submissionupdate = new stdClass();
                $submissionupdate->id = $submissiondetails->id;

                foreach ($fromform as $key => $value) {
                    switch ($key) {
                        case 'savevalues':
                        case 'confirmsubmit':
                            break;
                        default:
                            $submissionupdate->{$key} = $value;
                            break;
                    }
                }

                if (isset($fromform->confirmsubmit)) {
                    $submissionupdate->sponsorsubmitted = 1;

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
                            $userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));

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
                $submissionupdate->timemodified = time();
                $DB->update_record('arupsubmissions', $submissionupdate);
                redirect($CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $id);
                exit;
            }
        }

        // Output starts here
        echo $OUTPUT->header();
        $sponsorform->display();
    } else {
        echo $OUTPUT->header();
        echo get_string('error:sponsor:notassigned', 'arupapplication');
    }
} else {

    $sql = "";
    $content = "";
    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT s.id, a.id as applicationid, a.name, {$usernamefields}, s.sponsorsubmitted, cm.id as cmid
        FROM {arupsubmissions} as s
        INNER JOIN {arupapplication} a ON s.applicationid = a.id
        INNER JOIN {course_modules} cm ON a.id = cm.instance
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {user} u ON s.userid = u.id
        WHERE m.name = 'arupapplication' AND s.sponsor_email='".$USER->email."'";

    $sponsorrequests = $DB->get_records_sql($sql);
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('referencerequest');
    echo '<h2>' . get_string('heading:sponsorsummary', 'arupapplication') . '</h2>';

    $table = new html_table();
    $table->cellpadding = 4;
    $table->attributes['class'] = 'generaltable boxalignleft';
    $table->head = array(get_string('heading:name', 'arupapplication'), get_string('heading:applyingfor', 'arupapplication'), get_string('heading:status', 'arupapplication'), get_string('actions', 'arupapplication'));

    if ($sponsorrequests) {
        foreach ($sponsorrequests as $sponsorrequest) {
            $url = $CFG->wwwroot . '/mod/arupapplication/sponsor.php?id=' . $sponsorrequest->cmid . '&appid=' . $sponsorrequest->id;
            if ($sponsorrequest->sponsorsubmitted == 1) {
                $action = get_string('action:view', 'arupapplication');
                $progress = get_string('progress:complete', 'arupapplication');
            } else {
                $action = get_string('action:complete', 'arupapplication');
                $progress = get_string('progress:notcomplete', 'arupapplication');
            }
            $content .= fullname($sponsorrequest) . '||' . $sponsorrequest->name . '||' . $progress . '||<a href="' . $url . '">' . $action . '</a>$$$';
        }

        if ($content) {
            $records = explode('$$$', $content);
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }
            echo html_writer::table($table);
        }
    } else {
        echo get_string('error:sponsor:nothingtodo', 'arupapplication');
    }

    echo $OUTPUT->box_end();
}

// Finish the page
echo $OUTPUT->footer();