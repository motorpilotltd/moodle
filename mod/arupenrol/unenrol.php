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
 * MODIFIED version of enrol/self/unenrolself.php
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT);
$enrolid = required_param('enrolid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$arupenrol = $DB->get_record('arupenrol', array('id' => $id), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('id' => $enrolid, 'enrol' => 'self'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login();
if (!is_enrolled($context)) {
    redirect(new moodle_url('/'));
}
require_login($course);

// Only allow it if the activity allows it.
if (!$arupenrol->enroluser || !$arupenrol->unenroluser) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

$plugin = enrol_get_plugin('self');

$PAGE->set_url('/mod/arupenrol/unenrol.php', array('id' => $id, 'enrolid' => $instance->id));
$PAGE->set_title($plugin->get_instance_name($instance));

if ($confirm and confirm_sesskey()) {
    $plugin->unenrol_user($instance, $USER->id);

    redirect(new moodle_url('/index.php'));
}

echo $OUTPUT->header();
$yesurl = new moodle_url($PAGE->url, array('confirm' => 1, 'sesskey' => sesskey()));
$nourl = new moodle_url('/course/view.php', array('id' => $course->id));
$message = get_string('unenrolselfconfirm', 'enrol_self', format_string($course->fullname));
echo $OUTPUT->confirm($message, $yesurl, $nourl);
echo $OUTPUT->footer();
