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
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/local/timezones/edit.php', array('id' => $id));

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

$title = get_string('addtimezone', 'local_timezones');

require_login($SITE);

$renderer = $PAGE->get_renderer('local_timezones');
$mform = $renderer->get_edit_form();

if (!empty($id)) {
    $timezone = timezone::load($id);
    $timezone->form($mform);
}

if ($data = $mform->get_data()) {
    $timezone = new timezone($data->timezone, $data->display, $data->id);
    $timezone->save();
    redirect(new moodle_url('/local/timezones/'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform->display();
echo $OUTPUT->footer();
