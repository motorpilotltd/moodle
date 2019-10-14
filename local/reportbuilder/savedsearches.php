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
 * @author Maria Torres <maria.torres@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Page containing list of saved searches for this report
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/utils.php');
require_once('report_forms.php');

require_login();
if (isguestuser()) {
    redirect(get_login_url());
}

// This is the custom half ajax Totara page, we MUST send some headers here at least...
send_headers('text/html', true);

$id = optional_param('id', null, PARAM_INT); // Id for report.
$sid = optional_param('sid', null, PARAM_INT); // Id for saved search.
$action = optional_param('action', 'show', PARAM_ALPHANUMEXT); // Action to be executed.
$confirm = optional_param('confirm', false, PARAM_BOOL); // Confirm delete.
$returnurl = new moodle_url('/local/reportbuilder/savedsearches.php', array('id' => $id));;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/reportbuilder/savedsearches.php', array('id' => $id, 'sid' => $sid));
navigation_node::override_active_url(new moodle_url('/local/reportbuilder/myreports.php'));

$output = $PAGE->get_renderer('local_reportbuilder');

$report = new reportbuilder($id, null, false, $sid);

// Get info about the saved search we are dealing with.
if ($sid) {
    $conditions = array('id' => $sid, 'reportid' => $id, 'userid' => $USER->id);
    $search = $DB->get_record('report_builder_saved', $conditions, '*');
    if (!$search) {
        print_error('error:invalidsavedsearchid', 'local_reportbuilder');
    }
}

if (!$report->is_capable($id)) {
    print_error('nopermission', 'local_reportbuilder');
}

$pagetitle = format_string(get_string('savesearch', 'local_reportbuilder') . ': ' . $report->fullname);
$PAGE->set_title($pagetitle);

if ($action === 'delete') {
    if ($confirm) {
        require_sesskey();
        $transaction = $DB->start_delegated_transaction();
        $select = "scheduleid IN (SELECT s.id FROM {report_builder_schedule} s WHERE s.savedsearchid = ?)";
        $DB->delete_records_select('report_builder_schd_eml_aud', $select, array($sid));
        $DB->delete_records_select('report_builder_schd_eml_user', $select, array($sid));
        $DB->delete_records_select('report_builder_schd_eml_ext', $select, array($sid));
        $DB->delete_records('report_builder_schedule', array('savedsearchid' => $sid));
        $DB->delete_records('report_builder_saved', array('id' => $sid));
        $transaction->allow_commit();
        redirect($returnurl);
    }
    $messageend = '';
    // Is this saved search being used in any scheduled reports?
    if ($scheduled = $DB->get_records('report_builder_schedule', array('savedsearchid' => $sid))) {
        // Display a message and list of scheduled reports using this saved search.
        ob_start();
        totara_print_scheduled_reports(false, false, array("rbs.savedsearchid = ?", array($sid)));
        $out = ob_get_contents();
        ob_end_clean();

        $messageend = get_string('savedsearchinscheduleddelete', 'local_reportbuilder', $out) . str_repeat(html_writer::empty_tag('br'), 2);
    }

    $messageend .= get_string('savedsearchconfirmdelete', 'local_reportbuilder', format_string($search->name));

    echo $messageend;
    die;
}

if ($action === 'edit') {
    $name = optional_param('name', null, PARAM_TEXT);
    $ispublic = optional_param('ispublic', null, PARAM_BOOL);

    if (isset($name)) {
        require_sesskey();
        $search->name = $name;
        $search->ispublic = $ispublic;
        $search->timemodified = time();
        $DB->update_record('report_builder_saved', $search);
    } else {
        $data = clone($search);
        $data->sid = $sid;
        $data->headerandactions = false;
        $mform = new report_builder_save_form(null, array('report' => $report, 'data' => $data));
        $mform->display();
    }

    die;
}

// Show users searches.

$searches = $DB->get_records('report_builder_saved', array('userid' => $USER->id, 'reportid' => $id));
if (!empty($searches)) {
    echo $output->saved_searches_table($searches, $report);
} else {
    echo html_writer::tag('p', get_string('error:nosavedsearches', 'local_reportbuilder'));
}
