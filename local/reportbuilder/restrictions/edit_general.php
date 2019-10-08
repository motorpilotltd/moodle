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
 * @author Rob Tyler <rob.tyler@t0taralms.com>
 * @package local_reportbuilder
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/report_forms.php');
require_once($CFG->dirroot . '/local/reportbuilder/classes/rb_global_restriction.php');

$id = optional_param('id', 0, PARAM_INT); // Restriction id.

admin_externalpage_setup('rbmanageglobalrestrictions');

if (empty($CFG->enableglobalrestrictions)) {
    print_error('globalrestrictionsdisabled', 'local_reportbuilder');
}

/** @var local_reportbuilder_renderer|core_renderer $output */
$output = $PAGE->get_renderer('local_reportbuilder');

$restriction = new rb_global_restriction($id);
$returnurl = new moodle_url('/local/reportbuilder/restrictions/index.php');

$data = $restriction->get_record_data();
$data->format = FORMAT_HTML;
$data = file_prepare_standard_editor($data, 'description', array());
$form = new report_builder_restrictions_edit_general_form(null, $data);

if ($form->is_cancelled()) {
    // Form is cancelled, redirect. This ends processing.
    redirect($returnurl);
}

if ($fromform = $form->get_data()) {
    if (empty($fromform->submitbutton)) {
        notice(get_string('error:unknownbuttonclicked', 'local_reportbuilder'), $returnurl);
    }

    $fromform = file_postupdate_standard_editor($fromform, 'description', array(), null);
    if ($restriction->id) {
        $restriction->update($fromform);
    } else {
        $restriction->insert($fromform);
    }

    $continueurl = new moodle_url('/local/reportbuilder/restrictions/edit_recordstoview.php', array('id' => $restriction->id));
    $string = empty($fromform->id) ? 'restrictioncreated' : 'restrictionupdated';
    notice(get_string($string, 'local_reportbuilder', $restriction->name),
            $continueurl, array('class' => 'notifysuccess'));
}

echo $output->edit_restriction_header($restriction, 'general');

$form->display();

echo $output->footer();
