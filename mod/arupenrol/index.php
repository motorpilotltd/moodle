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
 * Index page for mod_arupenrol.
 *
 * @package     mod_arupenrol
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_login($course, false);
$PAGE->set_pagelayout('incourse');

\mod_arupenrol\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

$strarupenrols    = get_string('modulenameplural', 'arupenrol');

$PAGE->set_url('/mod/arupenrol/index.php', array('id' => $course->id));
$title = $course->shortname . ': ' . $strarupenrols;
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strarupenrols);

$output = $PAGE->get_renderer('mod_arupenrol');

echo $OUTPUT->header();

if (!$arupenrols = get_all_instances_in_course('arupenrol', $course)) {
    notice(get_string('thereareno', 'moodle', $strarupenrols), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$modinfo = get_fast_modinfo($course);

echo $output->index($course, $arupenrols, $modinfo, $usesections);

echo $OUTPUT->footer();