<?php
// This file is part of Moodle - http://moodle.org/
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
 * prints the tabbed bar
 *
 * @author Jackson D'souza
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package arupapplication
 */
defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row  = array();
$inactive = array();
$activated = array();

//some pages deliver the cmid instead the id
if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

if (!$context = context_module::instance($usedid)) {
    print_error('badcontext');
}

$courseid = optional_param('courseid', false, PARAM_INT);

if (!isset($current_tab)) {
    $current_tab = '';
}

$viewurl = new moodle_url('/mod/arupapplication/view.php', array('id'=>$usedid, 'do_show'=>'view'));
$row[] = new tabobject('view', $viewurl->out(), get_string('heading:overview', 'arupapplication'));

if (has_capability('mod/arupapplication:edititems', $context)) {
    $editurl = new moodle_url('/mod/arupapplication/questions.php', array('id'=>$usedid, 'do_show'=>'questions'));
    $row[] = new tabobject('questions', $editurl->out(), get_string('heading:statementquestions', 'arupapplication'));

    $templateurl = new moodle_url('/mod/arupapplication/declarations.php', array('id'=>$usedid, 'do_show'=>'declarations'));
    $row[] = new tabobject('declarations', $templateurl->out(), get_string('heading:declarations', 'arupapplication'));
}

if (count($row) > 1) {
    $tabs[] = $row;
    print_tabs($tabs, $current_tab, $inactive, $activated);
}

