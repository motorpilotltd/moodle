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
 * @package    arupadvertdatatype_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace arupadvertdatatype_taps;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    arupadvertdatatype_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via \local_coursemanager\event\course_updated event.
     * 
     * @param stdClass $event
     * @return void
     */
    public static function course_updated($event) {
        global $DB;

        $sql = <<<EOS
SELECT
    a.id as arupadvertid, c.id, ltc.courseid, ltc.coursename, ltc.onelinedescription
FROM
    {arupadvert} a
JOIN
    {course} c
    ON c.id = a.course
JOIN
    {arupadvertdatatype_taps} at
    ON at.arupadvertid = a.id
JOIN
    {local_taps_course} ltc
    ON ltc.courseid = at.tapscourseid
WHERE
    a.datatype = :datatype
    AND ltc.courseid = :courseid
EOS;
        $params = array(
            'datatype' => 'taps',
            'courseid' => $event->other['courseid'],
        );
        $records = $DB->get_records_sql($sql, $params);
        $now = time();
        foreach ($records as $record) {
            $course = new \stdClass();
            $course->id = $record->id;
            $course->fullname = strip_tags($record->coursename);
            $course->idnumber = $record->courseid;
            $course->summary = \html_writer::tag('p', strip_tags($record->onelinedescription));
            $course->summaryformat = FORMAT_HTML;
            $course->timemodified = $now;
            $DB->update_record('course', $course);
        }
    }
}