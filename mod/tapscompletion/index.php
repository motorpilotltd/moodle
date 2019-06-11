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

\mod_tapscompletion\event\course_module_instance_list_viewed::create_from_course($course);

$strtapscompletions = get_string('modulenameplural', 'tapscompletion');

$PAGE->set_url('/mod/tapscompletion/index.php', array('id' => $course->id));
$title = $course->shortname . ': ' . $strtapscompletions;
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strtapscompletions);

$output = $PAGE->get_renderer('mod_tapscompletion');

echo $OUTPUT->header();

if (!$tapscompletions = get_all_instances_in_course('tapscompletion', $course)) {
    notice(get_string('thereareno', 'moodle', $strtapscompletions), '$CFG->wwwroot/course/view.php?id='.$course->id);
    exit;
}

$usesections = course_format_uses_sections($course->format);
$sections = $usesections ? get_all_sections($course->id) : array();

$modinfo = get_fast_modinfo($course);

echo $output->index($course, $tapscompletions, $modinfo, $usesections, $sections);

echo $OUTPUT->footer();