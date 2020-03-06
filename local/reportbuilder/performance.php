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
 * @author Valerii Kuznetsov <valerii.kuznetsov@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Page containing performance report settings
 */

define('REPORTBUIDLER_MANAGE_REPORTS_PAGE', true);
define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/report_forms.php');

$id = required_param('id', PARAM_INT); // report id
$rawreport = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

$adminpage = $rawreport->embedded ? 'rbmanageembeddedreports' : 'rbmanagereports';
admin_externalpage_setup($adminpage);

$output = $PAGE->get_renderer('local_reportbuilder');

$returnurl = new moodle_url('/local/reportbuilder/performance.php', array('id' => $id));

$report = new reportbuilder($id);

$schedule = array();
if ($report->cache) {
    $cache = reportbuilder_get_cached($id);
    $scheduler = new \local_reportbuilder\scheduler($cache, array('nextevent' => 'nextreport'));
    $schedule = $scheduler->to_array();
}
// form definition
$mform = new report_builder_edit_performance_form(null, array('id' => $id, 'report' => $report, 'schedule' => $schedule));

// form results check
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/reportbuilder/index.php');
}
if ($fromform = $mform->get_data()) {

    if (empty($fromform->submitbutton)) {
        notice(get_string('error:unknownbuttonclicked', 'local_reportbuilder'), $returnurl);
    }

    $todb = new stdClass();
    $todb->id = $id;
    $todb->cache = isset($fromform->cache) ? $fromform->cache : 0;
    // Only update this setting if we expect to be able to see it. Otherwise we could loose the setting.
    if (get_config('local_reportbuilder', 'allowtotalcount')) {
        $todb->showtotalcount = !empty($fromform->showtotalcount) ? 1 : 0;
    }
    if (totara_is_clone_db_configured()) {
        $todb->useclonedb = empty($fromform->useclonedb) ? 0 : 1;
    }
    if (get_config('local_reportbuilder', 'globalinitialdisplay') && !$report->embedded) {
        // Do nothing, don't overwrite initial value.
    } else {
        $todb->initialdisplay = isset($fromform->initialdisplay) ? $fromform->initialdisplay : 0;
    }
    $todb->timemodified = time();
    $DB->update_record('report_builder', $todb);

    if ($fromform->cache) {
        reportbuilder_schedule_cache($id, $fromform);
        if (isset($fromform->generatenow) && $fromform->generatenow) {
            reportbuilder_generate_cache($id);
        }
    } else {
        reportbuilder_purge_cache($id, true);
    }

    $report = new reportbuilder($id);
    \local_reportbuilder\event\report_updated::create_from_report($report, 'performance')->trigger();
    notice(get_string('reportupdated', 'local_reportbuilder'), $returnurl, array('class' => 'notifysuccess'));
}

echo $output->header();

echo $output->container_start('reportbuilder-navlinks');
echo $output->view_all_reports_link($report->embedded) . ' | ';
echo $output->view_report_link($report->report_url());
echo $output->container_end();

echo $output->heading(get_string('editreport', 'local_reportbuilder', format_string($report->fullname)));

$currenttab = 'performance';
require('tabs.php');

// display the form
$mform->display();

echo $output->footer();
