<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralearning.com>
 * @package local_reportbuilder
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

$cminstanceid = required_param('cminstanceid', PARAM_INT);

$cm = get_coursemodule_from_instance('coursera', $cminstanceid);
$context = context_module::instance($cm->id, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
require_capability('mod/coursera:extendeligibility', $context);
require_login($course, false, $cm);

$PAGE->set_url('/mod/coursera/manageextensions.php');
$PAGE->set_title(get_string('manageextensions', 'rbsource_courseralearners'));
$PAGE->set_heading(get_string('manageextensions', 'rbsource_courseralearners'));

if (!$report = reportbuilder_get_embedded_report('rbsource_courseralearners\embedded\manageextensions', ['cminstanceid' => $cminstanceid], false, 0)) {
    print_error('error:couldnotgenerateembeddedreport', 'local_reportbuilder');
}

$url = new moodle_url('/mod/coursera/manageextensions.php');

$PAGE->set_button($PAGE->button . $report->edit_button());

/** @var local_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('local_reportbuilder');

\local_reportbuilder\event\report_viewed::create_from_report($report)->trigger();

$report->include_js();

echo $OUTPUT->header();

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report);

echo $OUTPUT->heading(get_string('manageextensions','rbsource_courseralearners'));
$heading = $renderer->result_count_info($report);

echo $renderer->print_description($report->description, $report->_id);

$report->display_search();

// Print saved search buttons if appropriate.
echo $report->display_saved_search_options();
echo $reporthtml;

echo $OUTPUT->footer();
