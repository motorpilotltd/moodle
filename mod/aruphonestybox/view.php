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
 *
 * @package    mod_aruphonestybox
 * @copyright  2014 Paul Stanyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

if (!$cm = get_coursemodule_from_id('aruphonestybox', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

$PAGE->set_url(new moodle_url('/mod/aruphonestybox/view.php', array('id' => $id)));

$url = new moodle_url('/course/view.php', array('id' => $course->id));
$message = get_string('viewnotimplemented', 'aruphonestybox', core_text::strtolower(get_string('course')));
redirect($url, $message, 3);
