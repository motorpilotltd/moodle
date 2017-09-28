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

global $SESSION, $CFG;
require_once("../../config.php");
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');


if (!isset($SESSION->questionnaire)) {
    $SESSION->questionnaire = new stdClass();
}
$SESSION->questionnaire->current_tab = 'reset';

$id = optional_param('id', null, PARAM_INT);    // Course Module ID.
$a = optional_param('a', null, PARAM_INT);      // questionnaire ID.

$rid = optional_param('rid', false, PARAM_INT);  // Response id.
$self = optional_param('self', false, PARAM_INT);

if ($id) {
    if (! $cm = get_coursemodule_from_id('questionnaire', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    if (! $questionnaire = $DB->get_record("questionnaire", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    if (! $questionnaire = $DB->get_record("questionnaire", array("id" => $a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $questionnaire->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("questionnaire", $questionnaire->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

// Check login and get context.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

$questionnaire = new questionnaire(0, $questionnaire, $course, $cm);

$valid = $questionnaire->qtype == QUESTIONNAIREONCE
    && $questionnaire->respondenttype != 'anonymous'
    && $questionnaire->is_open()
    && !$questionnaire->is_closed();

if ($valid && $rid && $self) {
    $response = $DB->get_record('questionnaire_response', array('id' => $rid, 'survey_id' => $questionnaire->survey->id, 'username' => $USER->id, 'complete' => 'y'));
    if ($response) {
        $response = $DB->get_record('questionnaire_response', array('id' => $rid));
        $response->complete = 'n';
        $DB->update_record('questionnaire_response', $response);
        $DB->delete_records('questionnaire_attempts', array('rid' => $rid));
        // Update completion state.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $questionnaire->completionsubmit) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $response->username);
        }
        $redirecturl = new moodle_url($CFG->wwwroot.'/mod/questionnaire/complete.php', array('id' => $cm->id));
        redirect($redirecturl);
        exit;
    }
}

require_capability('mod/questionnaire:manage', $context);

$url = new moodle_url($CFG->wwwroot.'/mod/questionnaire/reset.php');
$url->param('id', $cm->id);

$PAGE->set_url($url);
$PAGE->set_context($context);

// Print the page header.
$PAGE->set_title(get_string('resetresponses', 'questionnaire'));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Print the tabs.
include('tabs.php');

if (!$valid) {
    echo questionnaire_alert(get_string('resetresponses:notvalid', 'questionnaire'), 'alert-danger', false);
    echo $OUTPUT->footer($course);
    exit;
}

echo questionnaire_alert(get_string('resetresponses:instructions', 'questionnaire'), 'alert-info', false);

// Instructions.
$sql = <<<EOS
SELECT
    r.id,
    r.survey_id,
    r.submitted,
    u.id as userid,
    u.firstname,
    u.lastname
FROM
    {questionnaire_response} r
JOIN
    {user} u
    ON u.id = r.username
LEFT JOIN
    {questionnaire_response} r2
    ON r2.survey_id = r.survey_id AND r2.username = r.username AND r2.id != r.id AND r2.complete = 'n'
WHERE
    r.survey_id = ?
    AND r.complete='y'
    AND r2.id IS NULL
ORDER BY
    u.lastname ASC, u.firstname ASC, r.submitted DESC
EOS;
$responses = $DB->get_records_sql($sql, array($questionnaire->survey->id));
$currentuserid = 0;
foreach ($responses as $responseid => $response) {
    if ($response->userid == $currentuserid) {
        unset($responses[$responseid]);
        continue;
    }
    $currentuserid = $response->userid;
}

if ($rid && array_key_exists($rid, $responses)) {
    $response = $DB->get_record('questionnaire_response', array('id' => $rid));
    $response->complete = 'n';
    $DB->update_record('questionnaire_response', $response);
    $DB->delete_records('questionnaire_attempts', array('rid' => $rid));
    // Update completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $questionnaire->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $response->username);
    }
    unset($responses[$rid]);
    echo questionnaire_alert(get_string('resetresponses:success', 'questionnaire'), 'alert-success');
} elseif ($rid) {
    echo questionnaire_alert(get_string('resetresponses:invalidid', 'questionnaire'), 'alert-danger');
}

$table = new html_table();
$table->head = array(
    get_string('name', 'questionnaire'),
    get_string('resetresponses:completed', 'questionnaire'),
    get_string('resetresponses:reset', 'questionnaire')
);
if (empty($responses)) {
    $cell = new html_table_cell(get_string('resetresponses:noresponses', 'questionnaire'));
    $cell->colspan = count($table->head);
    $table->data[] = new html_table_row(array($cell));
}
foreach ($responses as $response) {
    $cells = array();
    $cells[] = fullname($response);
    $cells[] = userdate($response->submitted);
    $url->param('rid', $response->id);
    $cells[] = html_writer::link($url, get_string('resetresponses:reset', 'questionnaire'), array('class' => 'btn btn-small'));
    $table->data[] = new html_table_row($cells);
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer($course);

function questionnaire_alert($message, $type = 'alert-warning', $exitbutton = true) {
    $class = "alert {$type} fade in";
    $button = '';
    if ($exitbutton) {
        $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
    }
    return html_writer::tag('div', $button.$message, array('class' => $class, 'style' => 'margin: 10px 0;'));
}