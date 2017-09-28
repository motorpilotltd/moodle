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

define('NO_MOODLE_COOKIES', true);
define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');

$PAGE->set_url(new moodle_url('/local/lunchandlearn/help_ajax.php'));
$PAGE->set_context(context_system::instance());

$session = new lunchandlearn(required_param('id', PARAM_INT));

$sessioninfo = empty($session->sessioninfo) ? get_string('nosessioninfo', 'local_lunchandlearn') : $session->sessioninfo;

echo json_encode(array('heading' => get_string('eventrequirements', 'local_lunchandlearn'),  'text' => $sessioninfo));