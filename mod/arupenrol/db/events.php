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
 * This file definies observers needed by mod_arupenrol.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of observers.
$observers = array(
    array(
        'eventname'   => '\core\event\user_enrolment_created',
        'priority'    => 1,
        'callback'    => '\mod_arupenrol\eventobservers::user_enrolment',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_updated',
        'priority'    => 1,
        'callback'    => '\mod_arupenrol\eventobservers::user_enrolment',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_deleted',
        'priority'    => 1,
        'callback'    => '\mod_arupenrol\eventobservers::user_enrolment',
    ),
    array(
        'eventname'   => '\core\event\course_module_created',
        'callback'    => '\mod_arupenrol\eventobservers::course_module',
    ),
    array(
        'eventname'   => '\core\event\course_module_updated',
        'callback'    => '\mod_arupenrol\eventobservers::course_module',
    ),
);
