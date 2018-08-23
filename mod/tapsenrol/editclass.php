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

if (isset($id)) {
    $class = $DB->get_record('local_taps_class', ['id' => $id]);
    $course = get_course($class->courseid);

    // Needs refactoring so that local_taps_class links to cm not course.
    $cms = get_coursemodules_in_course('tapsenrol', $class->courseid);
    $cm = reset($cms);
} else {
    $cmid = required_param('cmid', null, PARAM_TEXT);
    list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'tapsenrol');
}

$duplicate = optional_param('duplicate', false, PARAM_BOOL);
if ($duplicate) {
    unset($class->id);
    unset($class->classid);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$params = ['cmid' => $cmid];
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

echo $OUTPUT->header();
$form = \mod_tapsenrol\cmform_class::get_form_instance($cm, $class);

if ($form->store_data() == true) {
    $params =  ['cmid' => $cm->id];
    if ($form->alertrequired) {
        $params['resendinvitesclassid'] = $form->classid;
    }
    redirect(new moodle_url('/mod/tapsenrol/classoverview.php', $params));
}

echo $form->render();

echo $OUTPUT->footer();