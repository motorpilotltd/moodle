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

require(dirname(__FILE__).'/../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');
// include library to reset course here...

admin_externalpage_setup('local_admin_resetcourse');

// Required CSS and JS.
$PAGE->requires->css(new moodle_url('/local/admin/css/select2.min.css'));
$PAGE->requires->css(new moodle_url('/local/admin/css/select2-bootstrap.min.css'));
$PAGE->requires->js_call_amd('local_admin/enhance', 'initialise');

$output = $PAGE->get_renderer('local_admin');

$resetcourse = new \local_admin\reset_course();

// Output View.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('resetcourse', 'local_admin'));

echo $output->session_messages('processingerrors', 'alert-danger');
echo $output->session_messages('processingresults', 'alert-success');

$resetcourse->display_form();

echo $OUTPUT->footer();
