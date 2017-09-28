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
require_once("$CFG->libdir/pdflib.php");

$id = required_param('id', PARAM_INT);   // course module
$submissionid = required_param('submissionid', PARAM_INT);   // submission id

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

if (!has_capability('mod/arupapplication:printapplication', $context)) {
    notice(get_string('cannotviewsubmission', 'arupapplication'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$event = \mod_arupapplication\event\submission_viewed::create(array(
    'context' => $context,
    'objectid' => $submissionid,
));
$event->trigger();

$submissiondetails = arupapplication_submissionsdetails($submissionid);

if ($submissiondetails) {
    $userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));
    $modname = get_string('modulename', 'arupapplication') . $submissiondetails->id;
    $filename = str_replace(' ', '_', clean_filename(fullname($userdetails). '_' . $modname . '.pdf'));
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    arupapplication_generatepdf($pdf, $submissiondetails->id, $context->id );

    $file_contents = $pdf->Output('', 'S');

    arupapplication_save_pdf($file_contents, $submissiondetails->id, $submissiondetails->userid, $filename, $context->id, ARUPAPPLICATION_APP_FILEAREA);
    //Merge files
    arupapplication_mergepdfs($context->id, $submissiondetails->id, $submissiondetails->userid, true);

} else {
    notice(get_string('cannotviewsubmission', 'arupapplication'), new moodle_url('/course/view.php', array('id' => $course->id)));
}