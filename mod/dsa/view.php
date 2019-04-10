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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('dsa', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('dsa', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($d) {
    $moduleinstance = $DB->get_record('dsa', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('dsa', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', dsa));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_dsa\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('dsa', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/dsa/view.php', array('id' => $cm->id));
$PAGE->set_context($modulecontext);

redirect(get_config('mod_dsa', 'redirecturl'));