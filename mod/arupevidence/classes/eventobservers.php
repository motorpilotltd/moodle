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
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupevidence;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via course_completed event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $event->relateduserid));
        $module = $DB->get_record('modules', array('name' => 'arupevidence'));
        $cms = $DB->get_records('course_modules', array('course' => $event->courseid, 'module' => $module->id));
        if (empty($cms)) {
            return; // Silently ignore all courses without an arupevidence module.
        }
        $cm = array_shift($cms); // Take first match, should only be one.

        // Check: we only want to do this if setting courese completion is enabled.
        $ahb = $DB->get_record('arupevidence', array('id' => $cm->instance));
        if (false === $ahb || !$ahb->setcoursecompletion) {
            // If we don't match an arupevidence instance or we're not overwriting course completion, then do nothing here.
            return;
        }

        $params = array('arupevidenceid' => $cm->instance, 'userid' => $user->id, 'archived' => 0);
        $ahbuser = $DB->get_record('arupevidence_users', $params, '*', IGNORE_MULTIPLE);
        // Update Moodle course completion date base on users input in AHB view page.
        // Record should exist as we're observing course completion.
        if (!empty($ahbuser) && $ahbuser->completiondate) {
            $ccompletion = new \completion_completion(array('course' => $event->courseid, 'userid' => $user->id));
            // Logging (old) values.
            $other = [
                'old' => [
                    'timecompleted' => $ccompletion->timecompleted,
                ],
            ];
            $ccompletion->timecompleted = $ahbuser->completiondate;
            $ccompletion->update();
            // Logging (new) values.
            $other['new'] = [
                'timecompleted' => $ccompletion->timecompleted,
            ];

            $eventparams = [
                'context' => \context_module::instance($cm->id),
                'courseid' => $event->courseid,
                'objectid' => $ahb->id,
                'relateduserid' => $event->relateduserid,
                'other' => $other,
            ];
            $logevent = \mod_arupevidence\event\course_completion_updated::create($eventparams);
            $logevent->trigger();

            return;
        }
    }

    /**
     * Triggered via \local_custom_certification\event\certification_completed event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function certification_completed(\local_custom_certification\event\certification_completed $event) {
        global $DB;

        // Load certification completion record.
        $completion = $DB->get_record('certif_completions', ['id' => $event->objectid]);

        $courseselect = "cm.course IN (SELECT DISTINCT cc.courseid
                                       FROM {certif_coursesets} c
                                       JOIN {certif_courseset_courses} cc ON cc.coursesetid = c.id
                                       JOIN {certif_courseset_completions} ccomps ON ccomps.coursesetid = c.id
                                      WHERE c.certifid = :certifid
                                            AND c.certifpath = :certifpath
                                            AND ccomps.userid = :userid)";

        $params = [
            'module' => 'arupevidence',
            'certifid' => $completion->certifid,
            'certifpath' => $completion->certifpath,
            'userid' => $completion->userid,
            'userid2' => $completion->userid,
            ];

        $ausql = "SELECT au.*, cm.id as cmid
                       FROM {course_modules} cm
                       JOIN {modules} m ON m.id = cm.module
                       JOIN {arupevidence} a ON cm.instance = a.id
                       JOIN {arupevidence_users} au ON au.arupevidenceid = a.id
                      WHERE {$courseselect}
                            AND m.name = :module
                            AND a.setcertificationcompletion <> 1
                            AND au.userid = :userid2
                            AND au.completiondate > 0
                            AND au.archived <> 1
                   ORDER BY au.expirydate ASC";

        $aus = $DB->get_records_sql($ausql, $params, 0, 1);

        if (empty($aus)) {
            return; // Silently ignore if not applicable.
        }
        $au = array_shift($aus); // Take first match, earliest expiry date.

        // Update certification completion.
        // Logging (old) values.
        $other = [
            'old' => [
                'timecompleted' => $completion->timecompleted,
                'timeexpires' => $completion->timeexpires,
                'timewindowsopens' => $completion->timewindowsopens,
                'duedate' => $completion->duedate,
            ],
        ];

        // Udate values
        $windowopentimediff = $completion->timeexpires - $completion->timewindowsopens;
        $completion->timecompleted = $au->completiondate;
        $completion->timeexpires = $completion->duedate = $au->expirydate;
        $completion->timewindowsopens = $au->expirydate - $windowopentimediff;
        $DB->update_record('certif_completions', $completion);

        // Logging (new) values.
        $other['new'] = [
            'timecompleted' => $completion->timecompleted,
            'timeexpires' => $completion->timeexpires,
            'timewindowsopens' => $completion->timewindowsopens,
            'duedate' => $completion->duedate,
        ];

        // Add Certification ID.
        $other['certifid'] = $completion->certifid;

        $eventparams = array(
            'context' => \context_module::instance($au->cmid),
            'courseid' => $event->courseid,
            'objectid' => $au->arupevidenceid,
            'relateduserid' => $completion->userid,
            'other' => $other,
        );
        $logevent = \mod_arupevidence\event\certification_completion_updated::create($eventparams);
        $logevent->trigger();

        return;
    }
}
