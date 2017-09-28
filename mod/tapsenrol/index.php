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

$id = required_param('id', PARAM_INT); // Course ID.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_login($course, false);
$PAGE->set_pagelayout('incourse');

\mod_tapsenrol\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

$strtapsenrols = get_string('modulenameplural', 'tapsenrol');

$PAGE->set_url('/mod/tapsenrol/index.php', array('id' => $course->id));
$title = $course->shortname . ': ' . $strtapsenrols;
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strtapsenrols);

$output = $PAGE->get_renderer('mod_tapsenrol');

echo $OUTPUT->header();

if (!$tapsenrols = get_all_instances_in_course('tapsenrol', $course)) {
    notice(get_string('thereareno', 'moodle', $strtapsenrols), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
$sections = $usesections ? get_all_sections($course->id) : array();

$modinfo = get_fast_modinfo($course);

echo $output->index($course, $tapsenrols, $modinfo, $usesections, $sections);

echo $OUTPUT->footer();