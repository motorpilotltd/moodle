<?php
// This file is part of the learningrecordstore plugin for Moodle
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
 * @package    local_learningrecordstore
 * @copyright  2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/adminlib.php');

$importid = optional_param('importid', '', PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);
$anchor = optional_param('anchor', '', PARAM_TEXT);
$addinput = optional_param('addinput',  false,  PARAM_BOOL);
$inputtype = optional_param('inputtype', '', PARAM_TEXT);
$showassign = optional_param('showassign', 0, PARAM_INT);
$showcrud =  optional_param('showcrud', 0, PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);


admin_externalpage_setup('learningrecordstorecsvimport');

if (!has_capability('local/learningrecordstore:addcpd', context_system::instance())) {
    echo $OUTPUT->header();
    echo html_writer::tag('div', get_string('form:csv:notallowed', 'local_learningrecordstore'), array('class' => 'alert alert-warning'));
    echo $OUTPUT->footer();
    die();
}

$cpdimport = new \local_learningrecordstore\csvimportforms($importid, $showcrud);

// Get the froms before any output is send.
$form1 = $cpdimport->admin_form1();
$form2 = $cpdimport->admin_form2();

echo $OUTPUT->header();

echo html_writer::tag('h1', get_string('cpduploadheading', 'local_learningrecordstore'));
$sample = new moodle_url('/local/learningrecordstore/CPD-Bulk-Template.xlsx');
echo html_writer::tag('div', get_string('cpduploaddesc', 'local_learningrecordstore', $sample->out()), array('class' => 'well'));

if ($form1) {
    $form1->display();
} else {
    $cpdimport->csv_preview($previewrows);
    if ($form2) {
        $form2->display();
    } else {
        $cpdimport->csv_process();

        $returnurl = new moodle_url('/local/learningrecordstore/csvimport.php');
        echo $OUTPUT->single_button($returnurl, get_string('form:csv:startnewimport', 'local_learningrecordstore'));
    }
}

echo $OUTPUT->footer();