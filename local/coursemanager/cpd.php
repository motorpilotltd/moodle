<?php
// This file is part of the coursemanager plugin for Moodle
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
 * @package    local_coursemanager
 * @copyright  2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$importid = optional_param('importid', '', PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);
$anchor = optional_param('anchor', '', PARAM_TEXT);
$addinput = optional_param('addinput',  false,  PARAM_BOOL);
$inputtype = optional_param('inputtype', '', PARAM_TEXT);
$showassign = optional_param('showassign', 0, PARAM_INT);
$showcrud =  optional_param('showcrud', 0, PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/coursemanager/cpd.php'));
$PAGE->set_pagelayout('frametop');
$PAGE->navbar->add(get_string('pluginname', 'local_coursemanager'), new moodle_url('/local/coursemanager/view.php'));
$PAGE->navbar->add(get_string('cpduploadheading', 'local_coursemanager'));
$PAGE->blocks->show_only_fake_blocks();

if (!has_capability('local/coursemanager:addcpd', context_system::instance())) {
    echo $OUTPUT->header();
    echo html_writer::tag('div', get_string('form:csv:notallowed', 'local_coursemanager'), array('alert alert-warning'));
    echo $OUTPUT->footer();
    die();
}

$cpdimport = new \local_coursemanager\csvimportforms($importid, $showcrud);

// Get the froms before any output is send.
$form1 = $cpdimport->admin_form1();
$form2 = $cpdimport->admin_form2();

echo $OUTPUT->header();

echo html_writer::tag('h1', get_string('cpduploadheading', 'local_coursemanager'));
$sample = new moodle_url('/local/coursemanager/CPD-Bulk-Template.xlsx');
echo html_writer::tag('div', get_string('cpduploaddesc', 'local_coursemanager', $sample->out()), array('class' => 'well'));

if ($form1) {
    $form1->display();
        $returnurl = new moodle_url('/local/coursemanager/view.php');
        echo $OUTPUT->single_button($returnurl, get_string('form:csv:backtocm', 'local_coursemanager'));
} else {
    $cpdimport->csv_preview($previewrows);
    if ($form2) {
        $form2->display();
    } else {
        $cpdimport->csv_process();
        $returnurl = new moodle_url('/local/coursemanager/view.php');
        echo $OUTPUT->single_button($returnurl, get_string('form:csv:backtocm', 'local_coursemanager'));
    }
}

echo $OUTPUT->footer();