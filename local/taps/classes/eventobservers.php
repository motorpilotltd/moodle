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
 * Observer class containing methods monitoring various events.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taps;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via:
     * \local_coursemanager\event\course_updated event.
     * 
     * @param stdClass $event
     * @return void
     */
    public static function course_updated($event) {
        global $DB;
        
        $taps = new taps();

        // Load course.
        $course = $taps->get_course_by_id($event->other['courseid']);

        if (!$course) {
            return;
        }

        $now = time();

        // Find classes with linked courses.
        $classes = $DB->get_records('local_taps_class', array('courseid' => $event->other['courseid']));
        foreach ($classes as $class) {
            $class->coursename = $course->coursename;
            $class->timemodified = $now;
            $DB->update_record('local_taps_class', $class);
        }

        // Find enrolment records with linked courses.
        $enrolments = $DB->get_records('local_taps_enrolment', array('courseid' => $event->other['courseid']));
        foreach ($enrolments as $enrolment) {
            $enrolment->coursename = $course->coursename;
            $enrolment->courseobjectives = $course->courseobjectives;
            $enrolment->timemodified = $now;
            $DB->update_record('local_taps_enrolment', $enrolment);
        }
    }

    /**
     * Triggered via:
     * \local_coursemanager\event\class_updated event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function class_updated($event) {
        global $DB;
        
        $taps = new taps();

        // Load class.
        $class = $taps->get_class_by_id($event->other['classid']);

        if (!$class) {
            return;
        }

        // Find enrolment records with linked classes.
        $enrolments = $DB->get_records('local_taps_enrolment', array('classid' => $event->other['classid']));
        $now = time();
        foreach ($enrolments as $enrolment) {
            $enrolment->classname = $class->classname;
            $enrolment->location = $class->location;
            $enrolment->classtype = $class->classtype;
            $enrolment->classstartdate = $class->classstartdate;
            $enrolment->classenddate = $class->classenddate;
            $enrolment->duration = $class->classduration;
            $enrolment->durationunits = $class->classdurationunits;
            $enrolment->durationunitscode = $class->classdurationunitscode;
            $enrolment->classstarttime = $class->classstarttime;
            $enrolment->classendtime = $class->classendtime;
            $enrolment->classcost = $class->classcost;
            $enrolment->classcostcurrency = $class->classcostcurrency;
            $enrolment->timezone = $class->timezone;
            $enrolment->usedtimezone = $class->usedtimezone;
            $enrolment->pricebasis = $class->pricebasis;
            $enrolment->currencycode = $class->currencycode;
            $enrolment->price = $class->price;
            $enrolment->trainingcenter = $class->trainingcenter;
            $enrolment->timemodified = $now;
            $DB->update_record('local_taps_enrolment', $enrolment);
        }
    }
}