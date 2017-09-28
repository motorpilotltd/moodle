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
 * @package    mod_arupapplication
 * @copyright  2014 Epic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace arupapplication with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once("$CFG->libdir/pdflib.php");
// more efficient to load this here
require_once($CFG->libdir.'/filelib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // arupapplication instance ID - it should be named as the first character of the module

$action  = optional_param('action', '', PARAM_RAW);  // arupapplication instance ID - it should be named as the first character of the module

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$current_tab = 'view';

if ($id) {
    $cm         = get_coursemodule_from_id('arupapplication', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $arupapplication  = $DB->get_record('arupapplication', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $arupapplication  = $DB->get_record('arupapplication', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $arupapplication->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('arupapplication', $arupapplication->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

arupapplication_view($arupapplication, $course, $cm, $context);

/// Print the page header

$PAGE->set_url('/mod/arupapplication/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($arupapplication->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$submissiondetails = $DB->get_record('arupsubmissions', array('applicationid'=>$cm->instance, 'userid'=>$USER->id));

if ($submissiondetails) {
    //Module completion state
    $completion = new completion_info($course);
    if ($arupapplication->sponsorstatementreq) {
        if($submissiondetails->referencesubmitted && $submissiondetails->sponsorsubmitted) {
            $completion->update_state($cm,COMPLETION_COMPLETE);
        }
    } else if ($submissiondetails->referencesubmitted) {
        $completion->update_state($cm,COMPLETION_COMPLETE);
    }
}

//ishidden check.
//arup application in courses
$cap_viewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $context);
if ((empty($cm->visible) and !$cap_viewhiddenactivities) AND $course->id != SITEID) {
    notice(get_string("activityiscurrentlyhidden"));
}

//ishidden check.
//feedback on mainsite
if ((empty($cm->visible) and !$cap_viewhiddenactivities) AND $courseid == SITEID) {
    notice(get_string("activityiscurrentlyhidden"));
}

if (has_capability('mod/arupapplication:printapplication', $context)) {
    //name of new zip file.
    switch ($action) {
        case 'all':
            $getrecords = $DB->get_records('arupsubmissions', array('applicationid'=>$cm->instance));
            break;
        case 'completed':
            $sql = "SELECT s.id, s.userid FROM {arupsubmissions} s
                INNER JOIN {arupapplication_tracking} t ON s.applicationid = t.applicationid AND s.userid = t.userid
                WHERE t.completed = 7 AND s.applicationid = " . $cm->instance;
            $getrecords = $DB->get_records_sql($sql);
            break;
    }
    if (isset($getrecords)) {
        if ($getrecords) {
            $itemids = array();
            foreach ($getrecords as $getrecord) {
                $userdetails = $DB->get_record('user', array('id'=>$getrecord->userid));
                $modname = get_string('modulename', 'arupapplication') . $getrecord->id;
                $filename = str_replace(' ', '_', clean_filename(fullname($userdetails). '_' . $modname . '.pdf'));
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                arupapplication_generatepdf($pdf, $getrecord->id, $context->id );
                $file_contents = $pdf->Output('', 'S');
                arupapplication_save_pdf($file_contents, $getrecord->id, $getrecord->userid, $filename, $context->id, ARUPAPPLICATION_APP_FILEAREA);
                //Merge files
                arupapplication_mergepdfs($context->id, $getrecord->id, $getrecord->userid);
                $itemids[] = $getrecord->id;
            }
            $filename = str_replace(' ', '_', clean_filename($arupapplication->name) .'.zip');
            arupapplication_downloadzip($context->id, $filename, $itemids);
        } else {
            notice(get_string('norecords', 'arupapplication'), new moodle_url('/mod/arupapplication/view.php', array('id' => $id)));
        }
    }
}
// Output starts here
echo $OUTPUT->header();

/// print the tabs
require('tabs.php');

// Replace the following lines with you own code
echo $OUTPUT->heading(get_string('instancename', 'arupapplication'));

if ($arupapplication->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('arupapplication', $arupapplication, $cm->id), 'generalbox mod_introbox', 'arupapplicationintro');
}

$lstapplications = '';

if (has_capability('mod/arupapplication:edititems', $context)) {
    $lstapplications = arupapplication_listsubmissions($cm, $context);
    if ($lstapplications) {
        echo $lstapplications;
    }
}
if (has_capability('mod/arupapplication:printapplication', $context)) {
    $select = new single_select($PAGE->url, 'action', array(''=>get_string('action:download', 'arupapplication'), 'all'=>get_string('action:all', 'arupapplication'), 'completed'=>get_string('action:complete', 'arupapplication')), null, null);
    echo html_writer::tag('p', $OUTPUT->render($select));
}

$applicationstatus = arupapplication_application_status($cm, $USER->id);

$progressextraclass = '';
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

echo html_writer::tag('div', $bar.$srspan, array('class' => "progress {$progressextraclass}"));

echo $applicationstatus['status'];

if ($applicationstatus['gopage'] != 0) {
    if (arupapplication_referencesponsorfeedback($arupapplication->id, $USER->id, 'referee')) {
        echo get_string('progress:verbose:receivedtechnicalreference', 'arupapplication');
    } else {
        echo get_string('progress:verbose:awaitingtechnicalreference', 'arupapplication');
    }
}

if ($arupapplication->sponsorstatementreq && $applicationstatus['gopage'] >= 6) {
    if (arupapplication_referencesponsorfeedback($arupapplication->id, $USER->id, 'sponsor')) {
        echo get_string('progress:verbose:receivedsponsorstatement', 'arupapplication');
    } else {
        echo get_string('progress:verbose:awaitingsponsorstatement', 'arupapplication');
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
$url = new moodle_url('/mod/arupapplication/complete.php', array('id' => $id, 'gopage' => $applicationstatus['gopage']));
$link = html_writer::link($url, $applicationstatus['button'], array('class' => $class));
echo html_writer::div($link, 'text-right');

// Finish the page
echo $OUTPUT->footer();