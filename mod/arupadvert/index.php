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
 * Index page for mod_arupadvert.
 *
 * @package     mod_arupadvert
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

\mod_arupadvert\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

$strarupadverts = get_string('modulenameplural', 'arupadvert');

$PAGE->set_url('/mod/arupadvert/index.php', array('id' => $course->id));
$title = $course->shortname . ': ' . $strarupadverts;
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strarupadverts);

$output = $PAGE->get_renderer('mod_arupadvert');

echo $OUTPUT->header();

if (!$arupadverts = get_all_instances_in_course('arupadvert', $course)) {
    notice(get_string('thereareno', 'moodle', $strarupadverts), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$modinfo = get_fast_modinfo($course);

echo $output->index($course, $arupadverts, $modinfo, $usesections);

echo $OUTPUT->footer();