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

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');

$hash = required_param('r', PARAM_ALPHANUM);

$url = new moodle_url($CFG->wwwroot.'/mod/questionnaire/printout.php');
$url->param('r', $hash);
$PAGE->set_url($url);
$PAGE->set_pagelayout('embedded');

try {
    $resphash = $DB->get_record('questionnaire_resp_hashes', array('hash' => $hash), '*', MUST_EXIST);
    $questionnairerecord = $DB->get_record('questionnaire', array('id' => $resphash->qid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $questionnairerecord->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('questionnaire', $questionnairerecord->id, $course->id, false, MUST_EXIST);
} catch (Exception $e) {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('error'));
    echo $OUTPUT->header();
    echo html_writer::tag('p', 'Sorry, the response requested could not be loaded.');
    echo $OUTPUT->footer();
    exit;
}

// Needed as not being triggered as a popup like the regular print page.
if (!isset($SESSION->questionnaire)) {
    $SESSION->questionnaire = new stdClass();
}
$SESSION->questionnaire->current_tab = '';

$questionnaire = new questionnaire(0, $questionnairerecord, $course, $cm);

$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title($questionnaire->survey->title);
echo $OUTPUT->header();
$questionnaire->survey_print_render('', 'print', $course->id, $resphash->rid, false);
echo $OUTPUT->footer();
