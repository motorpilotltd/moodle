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

require_once(dirname(__FILE__) . '/../../config.php');

$id = optional_param('id', false, PARAM_INT);

if (!empty($id)) {
    $class = $DB->get_record('local_taps_class', ['id' => $id]);
    $course = get_course($class->courseid);

    // Needs refactoring so that local_taps_class links to cm not course.
    $cms = get_coursemodules_in_course('tapsenrol', $class->courseid);
    $cm = reset($cms);

    $duplicate = optional_param('duplicate', false, PARAM_BOOL);
    if ($duplicate) {
        unset($class->id);
    }
} else {
    $cmid = required_param('cmid', PARAM_INT);
    list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'tapsenrol');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$params = ['cmid' => $cm->id];
if ($id) {
    $params['id'] = $id;
    require_capability('mod/tapsenrol:createclass', $context);
} else {
    require_capability('mod/tapsenrol:editclass', $context);
}

$baseurl = new moodle_url('/mod/tapsenrol/editclass.php', $params);

$PAGE->set_context($context);
$PAGE->set_url($baseurl);

if ($id) {
    $PAGE->set_title(get_string('editclass', 'tapsenrol'));
    $PAGE->set_heading(get_string('editclass', 'tapsenrol'));
} else {
    $PAGE->set_title(get_string('addnewclass', 'tapsenrol'));
    $PAGE->set_heading(get_string('addnewclass', 'tapsenrol'));
}

if (!isset($class)) {
    $class = new stdClass();
    $class->classtype = optional_param('classtype', \mod_tapsenrol\cmform_class::CLASS_TYPE_SCHEDULED, PARAM_TEXT);
    $class->classstatus = optional_param('classstatus', \mod_tapsenrol\cmform_class::CLASS_STATUS_NORMAL, PARAM_TEXT);
} else {
    $class->classtype = optional_param('classtype', $class->classtype, PARAM_TEXT);
    $class->classstatus = optional_param('classstatus', $class->classstatus, PARAM_TEXT);
}
$class->courseid = $cm->course;
$class->cmid = $cm->id;

$form = \mod_tapsenrol\cmform_class::get_form_instance($class);

$data = $form->get_data();

if ($data) {
    $form->store_data($data);
    $params =  ['cmid' => $cm->id];
    if ($form->alertrequired) {
        $params['resendinvitesclassid'] = $form->classid;
    }
    redirect(new moodle_url('/mod/tapsenrol/classoverview.php', $params));
}

echo $OUTPUT->header();

$form->set_data((array)$class);

echo $form->render();

echo $OUTPUT->footer();