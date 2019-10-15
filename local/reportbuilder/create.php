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

/**
 * Page containing new report form
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/report_forms.php');

admin_externalpage_setup('rbcreatereport');

$output = $PAGE->get_renderer('local_reportbuilder');

$returnurl = $CFG->wwwroot . '/local/reportbuilder/index.php';

// form definition
$mform = new report_builder_new_form();

// form results check
if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($fromform = $mform->get_data()) {

    if (empty($fromform->submitbutton)) {
        notice(
            get_string('error:unknownbuttonclicked', 'local_reportbuilder'),
            $returnurl);
    }
    // create new record here
    $todb = new stdClass();
    $todb->fullname = $fromform->fullname;
    $todb->shortname = reportbuilder::create_shortname($fromform->fullname);
    $todb->source = ($fromform->source != '0') ? $fromform->source : null;
    $todb->hidden = $fromform->hidden;
    $todb->recordsperpage = 40;
    $todb->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;
    $todb->accessmode = REPORT_BUILDER_ACCESS_MODE_ANY; // default to limited access
    $todb->embedded = 0;
    $todb->timemodified = time();

    try {
        $transaction = $DB->start_delegated_transaction();

        $newid = $DB->insert_record('report_builder', $todb);

        // by default we'll require a role but not set any, which will restrict report access to
        // the site administrators only
        reportbuilder_set_default_access($newid);

        // create columns for new report based on default columns
        $src = reportbuilder::get_source_object($fromform->source);
        if (isset($src->defaultcolumns) && is_array($src->defaultcolumns)) {
            $defaultcolumns = $src->defaultcolumns;
            $so = 1;
            foreach ($defaultcolumns as $option) {
                $heading = isset($option['heading']) ? $option['heading'] :
                    null;
                $hidden = isset($option['hidden']) ? $option['hidden'] : 0;
                $column = $src->new_column_from_option($option['type'],
                    $option['value'], null, null, $heading, !empty($heading), $hidden);
                $todb = new stdClass();
                $todb->reportid = $newid;
                $todb->type = $column->type;
                $todb->value = $column->value;
                $todb->heading = $column->heading;
                $todb->hidden = $column->hidden;
                $todb->transform = $column->transform;
                $todb->aggregate = $column->aggregate;
                $todb->sortorder = $so;
                $todb->customheading = 0; // initially no columns are customised
                $DB->insert_record('report_builder_columns', $todb);
                $so++;
            }
        }
        // create filters for new report based on default filters
        if (isset($src->defaultfilters) && is_array($src->defaultfilters)) {
            $defaultfilters = $src->defaultfilters;
            $so = 1;
            foreach ($defaultfilters as $option) {
                $todb = new stdClass();
                $todb->reportid = $newid;
                $todb->type = $option['type'];
                $todb->value = $option['value'];
                $todb->advanced = isset($option['advanced']) ? $option['advanced'] : 0;
                $todb->sortorder = $so;
                $todb->region = isset($option['region']) ? $option['region'] : rb_filter_type::RB_FILTER_REGION_STANDARD;
                $DB->insert_record('report_builder_filters', $todb);
                $so++;
            }
        }
        // Create toolbar search columns for new report based on default toolbar search columns.
        if (isset($src->defaulttoolbarsearchcolumns) && is_array($src->defaulttoolbarsearchcolumns)) {
            foreach ($src->defaulttoolbarsearchcolumns as $option) {
                $todb = new stdClass();
                $todb->reportid = $newid;
                $todb->type = $option['type'];
                $todb->value = $option['value'];
                $DB->insert_record('report_builder_search_cols', $todb);
            }
        }
        $report = new reportbuilder($newid);
        \local_reportbuilder\event\report_created::create_from_report($report, false)->trigger();
        $transaction->allow_commit();
        redirect($CFG->wwwroot . '/local/reportbuilder/general.php?id='.$newid);
    } catch (ReportBuilderException $e) {
        $transaction->rollback($e);
        trigger_error($e->getMessage(), E_USER_WARNING);
    } catch (Exception $e) {
        $transaction->rollback($e);
        redirect($returnurl, get_string('error:couldnotcreatenewreport', 'local_reportbuilder'));
    }
}

/** @var local_reportbuilder_renderer $output */
echo $output->header();

// User generated reports.
echo $output->heading(get_string('createreport', 'local_reportbuilder'));

// display mform
$mform->display();

echo $output->footer();
