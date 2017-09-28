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

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib.php');

admin_externalpage_setup('local_admin_index');

$heading = get_string('pluginname', 'local_admin');

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

$url = new moodle_url('/local/admin/user_report.php');
$link = html_writer::link($url, get_string('userreport', 'local_admin'));
echo html_writer::tag('p', $link);

echo $OUTPUT->footer();
