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
 * @package   mod_coursera
 * @category  backup
 * @copyright 2018 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... coursera instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('coursera', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cminstance  = $DB->get_record('coursera', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $cminstance  = $DB->get_record('coursera', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $coursera->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('coursera', $coursera->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_url('/mod/coursera/view.php', array('id' => $cm->id));

require_login($course, true, $cm);

if (!\mod_coursera\courseramoduleaccess::hascourseramoduleaccess($USER->id, $cminstance->id)) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]), get_string('noaccesscontactadmin', 'mod_coursera'), 5);
}

$event = \mod_coursera\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $cminstance);
$event->trigger();


$coursera = new \mod_coursera\coursera();
$coursera->enrolonprogram($USER->id);
$url = $coursera->getcourselink( $cminstance->contentid);
redirect($url);