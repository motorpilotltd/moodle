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
 * This file defines observers needed by mod_arupevidence.
 *
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of observers.
$observers = array(
    array(
        'eventname'   => '\core\event\course_completed',
        'priority'    => 1,
        'callback'    => '\mod_arupevidence\eventobservers::course_completed',
    ),
    array(
        'eventname'   => '\local_custom_certification\event\certification_completed',
        'priority'    => 1,
        'callback'    => '\mod_arupevidence\eventobservers::certification_completed',
    ),
);
