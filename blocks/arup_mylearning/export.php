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

require_once("../../config.php");

require_once($CFG->dirroot.'/blocks/arup_mylearning/tabs.php');
require_once($CFG->dirroot.'/blocks/arup_mylearning/content.php');

require_login();

$tab = required_param('tab', PARAM_ALPHA);
$instance = required_param('instance', PARAM_INT);

$PAGE->set_context(context_block::instance($instance));
$PAGE->set_url('/blocks/arup_mylearning/export.php');

$content = new block_arup_mylearning_content($instance);

if (!$content->get_export($tab)) {
    $url = new moodle_url('/my/index.php', array('tab' => $tab));
    redirect($url, get_string('exportfailed', 'block_arup_mylearning'), 5);
}