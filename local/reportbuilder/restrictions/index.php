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
 * Page containing list of available reports and new report form
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/report_forms.php');
require_once($CFG->dirroot . '/local/reportbuilder/classes/rb_global_restriction.php');

$id = optional_param('id', null, PARAM_INT); // Restriction id.
$action = optional_param('action', null, PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

admin_externalpage_setup('rbmanageglobalrestrictions');

if (empty($CFG->enableglobalrestrictions)) {
    print_error('globalrestrictionsdisabled', 'local_reportbuilder');
}

$restriction = new rb_global_restriction($id);
$returnurl = new moodle_url('/local/reportbuilder/restrictions/index.php', array('page' => $page));

if ($action && $restriction->id) {
    require_sesskey();
    switch ($action) {
        case 'up':
            $restriction->up();
            redirect($returnurl);
            break;

        case 'down':
            $restriction->down();
            redirect($returnurl);
            break;

        case 'activate':
            $restriction->activate();
            notice(get_string('restrictionactivated', 'local_reportbuilder', $restriction->name),
                $returnurl, array('class' => 'notifysuccess'));
            break;

        case 'deactivate':
            $restriction->deactivate();
            notice(get_string('restrictiondeactivated', 'local_reportbuilder', $restriction->name),
                $returnurl, array('class' => 'notifysuccess'));
            break;

        case 'delete':
            if ($confirm) {
                $restriction->delete();
                notice(get_string('restrictiondeleted', 'local_reportbuilder', $restriction->name),
                    $returnurl, array('class' => 'notifysuccess'));
            }
            break;
    }
}

/** @var local_reportbuilder_renderer|core_renderer $output */
$output = $PAGE->get_renderer('local_reportbuilder');
echo $output->header();

if ($action === 'delete' && !$confirm) {

    echo $output->heading(get_string('confirmdeleterestrictionheader', 'local_reportbuilder', $restriction->name));
    echo html_writer::tag('p', get_string('confirmdeleterestriction', 'local_reportbuilder'));

    $buttons = $output->single_button(
        new moodle_url('/local/reportbuilder/restrictions/index.php', array('action' => 'delete', 'confirm' => 1, 'id' => $id, 'page' => $page)),
        get_string('delete', 'local_reportbuilder'), 'post'
    );
    $buttons .= $output->single_button(
        new moodle_url('/local/reportbuilder/restrictions/index.php'),
        get_string('cancel', 'moodle'), 'get'
    );
    echo html_writer::tag('div', $buttons, array('class' => 'buttons'));

} else {

    echo $output->heading(get_string('globalrestrictions', 'local_reportbuilder'));

    // Get list of unsupported source and display if any.
    $unsupportedlist = rb_global_restriction::get_unsupported_sources();
    if ($unsupportedlist) {
        $warnstr = get_string('nonglobalrestrictionsources', 'local_reportbuilder', '"' . implode('", "', $unsupportedlist) . '"');
        echo html_writer::div($warnstr, 'notice');
    }

    echo html_writer::tag('p', get_string('globalrestrictiondescription', 'local_reportbuilder'));
    echo $output->single_button(
        new moodle_url('/local/reportbuilder/restrictions/edit_general.php'),
        get_string('globalrestrictionnew', 'local_reportbuilder')
    );


    $count = 0;
    $perpage = get_config('reportbuilder', 'globalrestrictionrecordsperpage');
    $globalrestrictions = rb_global_restriction::get_all($page, $perpage, $count);
    $paging = new paging_bar($count, $page, $perpage, $returnurl);
    echo $output->render($paging);
    echo $output->global_restrictions_table($globalrestrictions);
    echo $output->render($paging);
}

echo $output->footer();
