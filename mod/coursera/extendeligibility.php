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
 * A form for certification upload.
 *
 * @package    core_certification
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_coursera\courseramoduleaccess;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$cminstanceid = required_param('cminstanceid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$cm = get_coursemodule_from_instance('coursera', $cminstanceid);
$context = context_module::instance($cm->id, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
require_capability('mod/coursera:extendeligibility', $context);
require_login($course, false, $cm);

$instance = $DB->get_record('coursera', ['id' => $cminstanceid]);

$user = core_user::get_user($userid);

$title = get_string('extendtitle', 'mod_coursera', (object) ['name' => fullname($user), 'cmname' => $instance->name]);

$cma = new courseramoduleaccess(['userid' => $userid, 'courseraid' => $instance->id]);

$PAGE->set_url('/mod/coursera/extendeligibility.php');
$PAGE->set_title($title);

$maxenrolmentstart = $DB->get_field_sql(
        'SELECT max(timestart) from {user_enrolments} ue inner join {enrol} e on e.id = ue.enrolid where userid = :userid and courseid = :courseid',
        ['userid' => $userid, 'courseid' => $cm->course]
);

$default = $maxenrolmentstart + $instance->moduleaccessperiod;
if (isset($cma->id)) {
    $timeend = $cma->timeend;
} else {
    $timeend = $default;
}

$form = new \mod_coursera\extendeligibilityform(null, ['showdelete' => !empty($cma->id)]);

if ($data = $form->get_data()) {
    if (isset($data->deleteextension)) {
        $cma->delete();
        $timeend = $default;
        unset($cma->id);
    } else {
        $cma->timeend = $data->timeend;
        $timeend = $data->timeend;
        if (empty($cma->id)) {
            $cma->insert();
        } else {
            $cma->update();
        }
    }
    redirect(new moodle_url('/mod/coursera/manageextensions.php', ['cminstanceid' => $instance->id]));
} else if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/coursera/manageextensions.php', ['cminstanceid' => $instance->id]));
}

$params = ['userid' => $userid, 'cminstanceid' => $cminstanceid, 'timeend' => $timeend];
if (isset($cma->id)) {
    $intro = get_string('extendedto', 'mod_coursera', ['timeend' => userdate($timeend), 'default' => userdate($default)]);
} else {
    $intro = get_string('defaultedto', 'mod_coursera', userdate($default));
}

$form->set_data($params);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo html_writer::tag('h3', $intro);
$form->display();

echo $OUTPUT->footer();