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

require_once(dirname(dirname(__DIR__)).'/config.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

$debug  = optional_param('debug', 0, PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);
$format = optional_param('format', '', PARAM_TEXT); // export format

$context = context_system::instance();
$PAGE->set_context($context);

$pageparams = [
    'format' => $format,
    'debug' => $debug,
];

$shortname = 'manage_user_reports';

if (!$report = reportbuilder_get_embedded_report($shortname, $pageparams, false, $sid)) {
    print_error('error:couldnotgenerateembeddedreport', 'local_reportbuilder');
}

$url = new moodle_url('/local/reportbuilder/index.php', $pageparams);
admin_externalpage_setup('rbmanagereports', '', null, $url);

$PAGE->set_button($PAGE->button . $report->edit_button());

/** @var local_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('local_reportbuilder');

if ($format != '') {
    $report->export_data($format);
    die;
}

\local_reportbuilder\event\report_viewed::create_from_report($report)->trigger();

$report->include_js();

echo $OUTPUT->header();

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

echo $OUTPUT->heading(get_string('manageuserreports','local_reportbuilder'));

echo html_writer::tag('div', $renderer->single_button(new moodle_url('/local/reportbuilder/create.php'), get_string('createreport', 'local_reportbuilder'), 'get'), ['class'=>'pull-right']);

$report->display_restrictions();

$heading = $renderer->result_count_info($report);
echo $OUTPUT->heading($heading, 3);
echo $renderer->print_description($report->description, $report->_id);

$report->display_search();

// Print saved search buttons if appropriate.
echo $report->display_saved_search_options();
echo $reporthtml;

// Export button.
$renderer->export_select($report, $sid);

echo $OUTPUT->footer();
