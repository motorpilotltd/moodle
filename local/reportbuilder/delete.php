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
 * @package local_reportbuilder
 */

/**
 * Page for report cloning
 */

define('REPORTBUIDLER_MANAGE_REPORTS_PAGE', true);
define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '/', PARAM_LOCALURL);

$rawreport = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

$adminpage = $rawreport->embedded ? 'rbmanageembeddedreports' : 'rbmanagereports';
admin_externalpage_setup($adminpage);

$output = $PAGE->get_renderer('local_reportbuilder');

$report = new reportbuilder($id);
$type = $report->embedded ? 'reload' : 'delete';

$currentdata = [
    'id' => $id,
    'returnurl' => $returnurl,
];
$params = [
    'type' => $type,
];
$form = new \local_reportbuilder\form\delete_report_form(null, $params);
$form->set_data($currentdata);

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot . $returnurl);
} else if ($data = $form->get_data()) {
    // Delete report, then redirect.

    if (reportbuilder_delete_report($id)) {
        \local_reportbuilder\event\report_deleted::create_from_report($report, $report->embedded)->trigger();
        notice(get_string($type . 'report', 'local_reportbuilder'), $CFG->wwwroot . $returnurl, array('class' => 'notifysuccess'));

    } else {
        notice(get_string('no' . $type . 'report', 'local_reportbuilder'), $CFG->wwwroot . $returnurl);
    }
}

echo $output->header();
echo $output->heading(get_string('confirm' . $type . 'report', 'local_reportbuilder'));
echo $output->confirm_delete($report);

echo $form->render();

echo $output->footer();
