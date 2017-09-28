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

// Called by AJAX only, displays nothing.
require_once('../../config.php');
require_once('lib.php');

$timezone = timezone::load(required_param('id', PARAM_INT));
$USER->timezone = $timezone->get_timezone();
$DB->set_field('user', 'timezone', $timezone->get_timezone(), array ('id' => $USER->id));
exit;