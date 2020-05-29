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
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Page for displaying user generated reports
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

$format = optional_param('format', '', PARAM_ALPHANUM);
$id = required_param('id', PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);
$debug = optional_param('debug', 1, PARAM_INT);
$simplesearch = optional_param('simplesearch', 0, PARAM_TEXT);
$fullscreen = optional_param('fullscreen', 0, PARAM_ALPHANUM);

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/reportbuilder/report.php', array('id' => $id));
navigation_node::override_active_url(new moodle_url('/local/reportbuilder/myreports.php'));
$PAGE->set_pagelayout('base');

// We can rely on the report builder record existing here as there is no way to get directly to report.php.
$reportrecord = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

// Embedded reports can only be viewed through their embedded url.
if ($reportrecord->embedded) {
    print_error('cannotviewembedded', 'local_reportbuilder');
}

// New report object.
$report = new reportbuilder($id, null, false, $sid, null, false, array());

if (!$report->is_capable($id)) {
    print_error('nopermission', 'local_reportbuilder');
}
$report->handle_pre_display_actions();

if ($format != '') {
    $report->export_data($format);
    die;
}

\local_reportbuilder\event\report_viewed::create_from_report($report)->trigger();

$report->include_js();

// display results as graph if report uses the graphical_feedback_questions source
$graph = (substr($report->source, 0, strlen('graphical_feedback_questions')) ==
    'graphical_feedback_questions');

$fullname = format_string($report->fullname, true, ['context' => $context]);
$pagetitle = get_string('report', 'local_reportbuilder').': '.$fullname;

$PAGE->set_title($pagetitle);
$PAGE->navbar->add($fullname);
$PAGE->set_heading(format_string($SITE->fullname));

/** @var local_reportbuilder_renderer $output */
$output = $PAGE->get_renderer('local_reportbuilder');

echo $output->header();

$template = new stdClass();
$template->formurl = new moodle_url('/local/reportbuilder/report.php', ['id' => $id]);
$template->hasdisabledfilters = $report->has_disabled_filters();
$template->initiallyhidden = $report->is_initially_hidden();
$template->simplesearch = $simplesearch;

if ($report->has_disabled_filters()) {
    $template->notification = $OUTPUT->notification(get_string('filterdisabledwarning', 'local_reportbuilder'), 'warning');
}

// This must be done after the header and before any other use of the report.
list($tablehtml, $debughtml) = $output->report_html($report, $debug);

$template->editbutton = $report->edit_button();
$template->toolbarsearch = $report->toolbarsearch;
$template->table = $tablehtml;
$template->debug =  $debughtml;
$template->directlink = $report->display_redirect_link();
$report->display_redirect_link();

// Display heading including filtering stats.
$template->heading = $fullname;
$template->numrecords = $output->result_count_info($report);

// print report description if set
$template->description = $output->print_description($report->description, $report->_id);

$template->search = $report->display_search(false);
$template->sidebar = $report->display_sidebar_search(false);

// print saved search buttons if appropriate
$template->savedsearch = $report->display_saved_search_options();
// Show results.
if ($graph) {
    $template->feedback = $report->print_feedback_results();
} else {
    $columns = [];
    $hiddencolumns = $report->js_get_hidden_columns();
    foreach ($report->get_columns() as $machinename => $column) {
        if (empty($column->heading)) {
            continue;
        }
        $machinename = str_replace($column->type . '-', $column->type . '_', $machinename);
        $columns[] = (object)['name' => $column->heading, 'machinename' => $machinename, 'checked' => !in_array($machinename, $hiddencolumns)];
    }
    $PAGE->requires->js_call_amd('local_reportbuilder/showhidecolumsmodal', 'init',
            ['id' => $report->_id,
             'shortname' => $report->shortname]);
    $template->button = $OUTPUT->single_button('', get_string('showhidecolumns', 'local_reportbuilder'), 'get', ['class' => 'showhidecolumns']);
}

// Export button.
$template->export = $output->export_select($report, $sid, false);
$template->fullscreen = $fullscreen;

echo $OUTPUT->render_from_template('local_reportbuilder/report', $template);

echo $output->footer();
