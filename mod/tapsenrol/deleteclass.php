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
$class = \mod_tapsenrol\enrolclass::fetch(['id' => $id]);
$course = get_course($class->courseid);

// Needs refactoring so that local_taps_class links to cm not course.
$cms = get_coursemodules_in_course('tapsenrol', $class->courseid);
$cm = reset($cms);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/tapsenrol:deleteclass', $context);

$baseurl = new moodle_url('/mod/tapsenrol/deleteclass.php', ['id' => $id]);

$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('deleteclass', 'tapsenrol'));
$PAGE->set_heading(get_string('deleteclass', 'tapsenrol'));

$form = new \mod_tapsenrol\cmform_class_delete(null, ['class' => $class]);

$redirecturl = new moodle_url("/mod/tapsenrol/classoverview.php", ['cmid' => $cm->id]);
if ($form->get_data()) {
    $form->dodelete();
    redirect($redirecturl);
} else if ($form->is_cancelled()) {
    redirect($redirecturl);
}

echo $OUTPUT->header();

echo $OUTPUT->box(get_string('deleteclassintro', 'tapsenrol', $class->classname));

echo $form->render();

echo $OUTPUT->footer();