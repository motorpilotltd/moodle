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
 * @author Alastair Munro <alastair.munro@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

require_login();

// Get params
$id = required_param('id', PARAM_INT); //ID
$confirm = optional_param('confirm', '', PARAM_INT); // Delete confirmation hash

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/local/reportbuilder/deletescheduled.php', array('id' => $id));
navigation_node::override_active_url(new moodle_url('/local/reportbuilder/myreports.php'));

if (!$scheduledreport = $DB->get_record('report_builder_schedule', array('id' => $id))) {
    print_error('error:invalidreportscheduleid', 'local_reportbuilder');
}

if (!reportbuilder::is_capable($scheduledreport->reportid)) {
    print_error('nopermission', 'local_reportbuilder');
}
if ($scheduledreport->userid != $USER->id) {
    require_capability('local/reportbuilder:managereports', context_system::instance());
}

$reportname = $DB->get_field('report_builder', 'fullname', array('id' => $scheduledreport->reportid));

$returnurl = new moodle_url('/local/reportbuilder/myreports.php');
$deleteurl = new moodle_url('/local/reportbuilder/deletescheduled.php', array('id' => $scheduledreport->id, 'confirm' => '1', 'sesskey' => $USER->sesskey));

if ($confirm == 1) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    } else {
        $select = "scheduleid = ?";
        $DB->delete_records_select('report_builder_schd_eml_aud', $select, array($scheduledreport->id));
        $DB->delete_records_select('report_builder_schd_eml_user', $select, array($scheduledreport->id));
        $DB->delete_records_select('report_builder_schd_eml_ext', $select, array($scheduledreport->id));
        $DB->delete_records('report_builder_schedule', array('id' => $scheduledreport->id));
        $report = new reportbuilder($scheduledreport->reportid);
        \local_reportbuilder\event\report_updated::create_from_report($report, 'scheduled')->trigger();
        notice(get_string('deletedscheduledreport', 'local_reportbuilder', format_string($reportname)),
                                $returnurl, array('class' => 'notifysuccess'));
    }
}
/// Display page
$PAGE->set_title(get_string('deletescheduledreport', 'local_reportbuilder'));
$PAGE->set_heading(format_string($SITE->fullname));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('deletescheduledreport', 'local_reportbuilder'));
if (!$confirm) {
    echo $OUTPUT->confirm(get_string('deletecheckschedulereport', 'local_reportbuilder', format_string($reportname)), $deleteurl, $returnurl);

    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->footer();
