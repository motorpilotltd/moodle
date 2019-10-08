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

/**
 * Page for setting up scheduled reports
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir  . '/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/scheduled_forms.php');
require_once($CFG->dirroot . '/local/reportbuilder/js/lib/setup.php');
require_once($CFG->dirroot . '/local/reportbuilder/email_setting_schedule.php');

require_login();
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/local/reportbuilder/scheduled.php');
navigation_node::override_active_url(new moodle_url('/local/reportbuilder/myreports.php'));

// Get the report id. This can be in one of two variables because the two forms are constructed differently.
$reportid = optional_param('reportid', 0, PARAM_INT); //report that a schedule is being added for
$formdata = optional_param_array('addanewscheduledreport', null, PARAM_INT);
$reportid = $reportid ? $reportid : intval($formdata['reportid']);
// Get the id of a scheduled report that's being edited.
$id = optional_param('id', 0, PARAM_INT);

$myreportsurl = $CFG->wwwroot . '/local/reportbuilder/myreports.php';
$returnurl = $CFG->wwwroot . '/local/reportbuilder/scheduled.php';
$output = $PAGE->get_renderer('local_reportbuilder');

if ($id == 0) {
    // Try to create report object to catch invalid data.
    $report = new reportbuilder($reportid);
    $schedule = new stdClass();
    $schedule->id = 0;
    $schedule->reportid = $reportid;
    $schedule->frequency = null;
    $schedule->schedule = null;
    $schedule->format = null;
    $schedule->exporttofilesystem = null;
    $schedule->userid = $USER->id;
} else {
    if (!$schedule = $DB->get_record('report_builder_schedule', array('id' => $id))) {
        print_error('error:invalidreportscheduleid', 'local_reportbuilder');
    }

    $report = new reportbuilder($schedule->reportid);
}

if (!reportbuilder::is_capable($schedule->reportid)) {
    print_error('nopermission', 'local_reportbuilder');
}
if ($schedule->userid != $USER->id) {
    require_capability('local/reportbuilder:managereports', context_system::instance());
}

$savedsearches = $report->get_saved_searches($schedule->reportid, $USER->id);
if (!isset($report->src->redirecturl)) {
    $savedsearches[0] = get_string('alldata', 'local_reportbuilder');
}

// Get list of emails settings for this schedule report.
$schedule->systemusers = array_keys(email_setting_schedule::get_system_users_to_email($id));
$schedule->externalemails = implode(', ', email_setting_schedule::get_external_users_to_email($id));

// Load JS for lightbox.
local_js(array(
    TOTARA_JS_DIALOG,
    TOTARA_JS_TREEVIEW
));

// Form definition.
$mform = new scheduled_reports_new_form(
    null,
    array(
        'id' => $id,
        'report' => $report,
        'frequency' => $schedule->frequency,
        'schedule' => $schedule->schedule,
        'format' => $schedule->format,
        'savedsearches' => $savedsearches,
        'exporttofilesystem' => $schedule->exporttofilesystem,
        'ownerid' => $schedule->userid,
    )
);

$mform->set_data($schedule);

if ($mform->is_cancelled()) {
    redirect($myreportsurl);
}
if ($fromform = $mform->get_data()) {
    if (empty($fromform->submitbutton)) {
        notice(get_string('error:unknownbuttonclicked', 'local_reportbuilder'), $returnurl);
    }

    if ($fromform->id) {
        if ($newid = add_scheduled_report($fromform)) {
            notice(get_string('updatescheduledreport', 'local_reportbuilder'), $myreportsurl, array('class' => 'notifysuccess'));
        }
        else {
            notice(get_string('error:updatescheduledreport', 'local_reportbuilder'), $returnurl);
        }
    }
    else {
        if ($newid = add_scheduled_report($fromform)) {
            notice(get_string('addedscheduledreport', 'local_reportbuilder'), $myreportsurl, array('class' => 'notifysuccess'));
        }
        else {
            notice(get_string('error:addscheduledreport', 'local_reportbuilder'), $returnurl);
        }
    }
}

if ($id == 0) {
    $pagename = 'addscheduledreport';
} else {
    $pagename = 'editscheduledreport';
}

$PAGE->set_title(get_string($pagename, 'local_reportbuilder'));
$PAGE->set_cacheable(true);
$PAGE->navbar->add(get_string($pagename, 'local_reportbuilder'));
echo $output->header();

echo $output->heading(get_string($pagename, 'local_reportbuilder'));

$mform->display();

echo $output->footer();

function add_scheduled_report($fromform) {
    global $DB, $USER;

    if (isset($fromform->reportid) && isset($fromform->format) && isset($fromform->frequency)) {
        $report = new stdClass();
        $report->schedule = $fromform->schedule;
        $report->frequency = $fromform->frequency;
        $scheduler = new scheduler($report);
        $nextevent = $scheduler->next(time(), false, core_date::get_user_timezone());

        $transaction = $DB->start_delegated_transaction();
        $todb = new stdClass();
        if ($id = $fromform->id) {
            $todb->id = $id;
        }
        $todb->reportid = $fromform->reportid;
        $todb->savedsearchid = $fromform->savedsearchid;
        $todb->userid = $USER->id;
        $todb->format = $fromform->format;
        $todb->exporttofilesystem = $fromform->emailsaveorboth;
        $todb->frequency = $fromform->frequency;
        $todb->schedule = $fromform->schedule;
        $todb->nextreport = $nextevent->get_scheduled_time();
        if (!$id) {
            $newid = $DB->insert_record('report_builder_schedule', $todb);
        } else {
            $DB->update_record('report_builder_schedule', $todb);
            $newid = $todb->id;
        }

        // Get audiences, system users and external users and update email tables.
        $systemusers = (!empty($fromform->systemusers)) ? $fromform->systemusers : array();
        $externalusersraw = (!empty($fromform->externalemails)) ? explode(',', $fromform->externalemails) : array();
        $externalusers = [];

        foreach($externalusersraw as $email) {
            $email = strtolower($email);
            $email = trim($email);

            if (empty($email)) {
                continue;
            }

            $externalusers[] = $email;
        }

        $scheduleemail = new email_setting_schedule($newid);
        $scheduleemail->set_email_settings($systemusers, $externalusers);

        $transaction->allow_commit();

        return $newid;
    }
    return false;
}
