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
 * prints the form to edit the statement questions such moving, deleting and so on
 *
 * @author Jackson D'souza
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package arupapplication
 */

require_once("../../config.php");

$id = required_param('id', PARAM_INT);
$do_show = required_param('do_show', PARAM_RAW);
$deleterecord = required_param('deleterecord', PARAM_INT);
$confirm = optional_param('confirm', 0 ,PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

$url = new moodle_url('/mod/arupapplication/delete_record.php', array('id'=>$id, 'do_show'=>$do_show, 'deleterecord'=>$deleterecord));
$cancelurl = new moodle_url('/mod/arupapplication/questions.php', array('id'=>$id, 'do_show'=>$do_show));

$PAGE->set_url($url);
if ($courseid !== false) {
    $url->param('courseid', $courseid);
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

require_login($course, true, $cm);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

require_capability('mod/arupapplication:edititems', $context);

$coursecontext = context_course::instance($course->id);

switch($do_show) {
    case 'questions':
        $pageheading = get_string('heading:statementquestions', 'arupapplication');
        $record = $DB->get_record('arupstatementquestions', array('id'=>$deleterecord, 'applicationid'=>$arupapplication->id));
        $statement_declaration = $record->question;

        if ($formdata) {
            $DB->delete_records('arupstatementquestions', array('id'=>$deleterecord));
            redirect($CFG->wwwroot . '/mod/arupapplication/questions.php?id=' . $id . '&do_show=questions');
        }

        break;
    case 'declarations':
        $pageheading = get_string('heading:declarations', 'arupapplication');
        $record = $DB->get_record('arupdeclarations', array('id'=>$deleterecord, 'applicationid'=>$arupapplication->id));
        $statement_declaration = $record->declaration;
        if ($formdata) {
            $DB->delete_records('arupdeclarations', array('id'=>$deleterecord));
            redirect($CFG->wwwroot . '/mod/arupapplication/declarations.php?id=' . $id . '&do_show=declarations');
        }
        break;
    default:
        print_error('invalidcoursemodule');
        break;
}

$PAGE->set_url('/mod/arupapplication/delete_item.php', array('id'=>$id, 'do_show'=>$do_show, 'deleterecord'=>$deleterecord));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($arupapplication->name));
echo $OUTPUT->header();

echo $OUTPUT->heading($pageheading);

echo $OUTPUT->confirm(get_string('confirmdelete', 'arupapplication') . $statement_declaration, $url . '&confirm=1', $cancelurl);

echo $OUTPUT->footer();