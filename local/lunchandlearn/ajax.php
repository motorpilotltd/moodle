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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');

$session = new lunchandlearn(required_param('id', PARAM_INT));

$potential = (array)$session->attendeemanager->get_potential_attendees(optional_param('q', '', PARAM_TEXT), 20);
$json = array();
foreach ($potential as $pid => $potentialtext) {
    $json[] = array('text' => $potentialtext, 'id' => $pid);
}
echo json_encode($json);
exit;