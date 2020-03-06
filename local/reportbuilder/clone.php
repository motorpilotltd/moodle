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

$currentdata = [
    'id' => $id,
    'returnurl' => $returnurl,
];
$form = new \local_reportbuilder\form\clone_report_form();
$form->set_data($currentdata);

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot . $returnurl);
} else if ($data = $form->get_data()) {
    // Clone report, then redirect.

    $origname = $report->fullname;

    if (reportbuilder_clone_report($report, get_string('clonenamepattern', 'local_reportbuilder', $origname))) {
        \local_reportbuilder\event\report_cloned::create_from_report($report)->trigger();
        notice(get_string('clonecompleted', 'local_reportbuilder'), $CFG->wwwroot . $returnurl,
                array('class' => 'notifysuccess'));

    } else {
        notice(get_string('clonefailed', 'local_reportbuilder'), $CFG->wwwroot . $returnurl);
    }
}

echo $output->header();
echo $output->heading(get_string('clonereport', 'local_reportbuilder'));
echo $output->confirm_clone($report);

echo $form->render();

echo $output->footer();
