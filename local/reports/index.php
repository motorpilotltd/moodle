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
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/reports/index.php');
$PAGE->set_pagelayout('frametop');
$PAGE->navbar->add(get_string('pluginname', 'local_reports'), new moodle_url('/local/reports/view.php'));
$page = optional_param('page', 'learninghistory', PARAM_TEXT);
$PAGE->navbar->add(get_string($page, 'local_reports'));
$PAGE->blocks->show_only_fake_blocks();

$page = optional_param('page', 'learninghistory', PARAM_TEXT);

$report = new \local_reports\report($page);
$renderer = $PAGE->get_renderer('local_reports', 'report');

$PAGE->set_title(get_string('pluginname', 'local_reports'));
$PAGE->set_heading(get_string('pluginname', 'local_reports'));

$PAGE->requires->strings_for_js(
    array(
        'admin:processingdots',
        'admin:savingdots',
        'error:f2fdate',
        'error:request',
    ), 'local_onlineappraisal');

$PAGE->requires->js_call_amd('local_reports/exportreport', 'init', array());

echo $OUTPUT->header();

echo $report->main_content();

echo $OUTPUT->footer();