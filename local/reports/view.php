<?php
// This file is part of the Arup Reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/reports/view.php'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo html_writer::tag('h1', get_string('pluginname', 'local_reports'));
echo html_writer::link(new moodle_url('/local/reports/index.php'),
    get_string('learninghistory', 'local_reports'),
    array('class' => 'btn btn-default m-r-5'));

echo html_writer::link(new moodle_url('/local/reports/index.php', ['page' => 'elearningstatus']),
    get_string('elearningstatus', 'local_reports'),
    array('class' => 'btn btn-default m-r-5'));

echo html_writer::link(new moodle_url('/local/reports/index.php', ['page' => 'daterangelearning']),
    get_string('daterangelearning', 'local_reports'),
    array('class' => 'btn btn-default m-r-5'));

echo $OUTPUT->footer();