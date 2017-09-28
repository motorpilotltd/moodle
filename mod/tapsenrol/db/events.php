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
 * This file definies observers needed by mod_tapsenrol.
 *
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of observers.
$observers = array(
    array(
        'eventname'   => '\core\event\user_enrolment_deleted',
        'priority'    => 1,
        'callback'    => '\mod_tapsenrol\eventobservers::user_enrolment_deleted',
    ),
    array(
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => '\mod_tapsenrol\eventobservers::course_module_deleted',
    ),
    array(
        'eventname'   => '\local_coursemanager\event\class_updated',
        'callback'    => '\mod_tapsenrol\eventobservers::class_updated',
    ),
    array(
        'eventname'   => '\local_custom_certification\event\certification_course_reset',
        'callback'    => '\mod_tapsenrol\eventobservers::certification_course_reset',
    ),
);
