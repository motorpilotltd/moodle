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
 * @package    mod_tapscompletion
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapscompletion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_tapscompletion
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        $ue = (object)$event->other['userenrolment'];
        if ($ue->lastenrol) {
            $course = $DB->get_record('course', array('id' => $event->courseid));
            $completion = new \completion_info($course);
            $cms = get_fast_modinfo($event->courseid, -1)->get_instances_of('tapscompletion');
            foreach ($cms as $cm) {
                $DB->delete_records('tapscompletion_completion', array('tapscompletionid' => $cm->instance, 'userid' => $ue->userid));
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $ue->userid);
            }
        }
    }

    /**
     * Triggered via course_module_deleted event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        if ($event->other['modulename'] == 'arupadvert') {
            $cms = get_fast_modinfo($event->courseid, -1)->get_instances_of('tapscompletion');
            foreach ($cms as $cm) {
                course_delete_module($cm->id);
            }
        }
    }

    /**
     * Triggered via course_completed event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $CFG, $DB;

        $cms = get_fast_modinfo($event->courseid, -1)->get_instances_of('tapscompletion');

        if (empty($cms)) {
            // Not applicable to this course.
            return;
        }

        require_once($CFG->libdir . '/completionlib.php');

        $tc = new tapscompletion();
        $tc->set_course($event->courseid);
        if (!$tc->course) {
            return;
        }
        if (!$tc->check_installation(true)) {
            return;
        }
        // Should only be one, but returned as an array so looping.
        foreach ($cms as $cm) {
            if ($cm->get_course()->enablecompletion != COMPLETION_ENABLED
                    || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
                return;
            }
            if (!$tc->set_cm('id', $cm->id)) {
                continue;
            }
            if (!$tc->set_tapscompletion($tc->cm->instance)) {
                continue;
            }
            $tccompletion = $DB->get_record(
                'tapscompletion_completion',
                array(
                    'tapscompletionid' => $tc->tapscompletion->id,
                    'userid' => $event->relateduserid
                )
            );
            if (!$tc->tapscompletion->autocompletion) {
                // We need to update course completion time if applicable.
                // Use completed field (stores enrolmentid), ignore if 1 for legacy (actual enrolmentid 1 is historic).
                if ($tccompletion && $tccompletion->completed > 1) {
                    $completiontime = $DB->get_field('local_taps_enrolment', 'classcompletiontime', ['enrolmentid' => $tccompletion->completed]);
                    if ($completiontime) {
                        // Update Moodle course completion date.
                        // Record should exist as we're observing course completion.
                        $ccompletion = new \completion_completion(['course' => $tc->course->id, 'userid' => $event->relateduserid]);
                        $ccompletion->timecompleted = $completiontime;
                        $ccompletion->update();
                    }
                }
                return;
            }
            list($instatement, $inparams) = $DB->get_in_or_equal(
                $tc->taps->get_statuses('placed'),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('lte.bookingstatus');
            $sql = <<<EOS
SELECT
    lte.*
FROM {local_taps_enrolment} lte
JOIN {user} u
    ON u.idnumber = lte.staffid
WHERE
    u.id = :userid
    AND lte.courseid = :courseid
    AND lte.active = 1
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$instatement}
EOS;
            $params = array('userid' => $event->relateduserid, 'courseid' => $tc->tapscompletion->tapscourse);
            $enrolments = $DB->get_records_sql($sql, array_merge($params, $inparams));
            // Should only be one but returned as array, so looping.
            foreach ($enrolments as $enrolment) {
                // Set completion time appropriately.
                $tc->tapscompletion->completiontimetype;
                if ($enrolment->classendtime > 0 && $tc->tapscompletion->completiontimetype == tapscompletion::$completiontimetypes['classendtime']) {
                    $completiontime = $enrolment->classendtime;
                } else {
                    $completiontime = time();
                }
                $result = $tc->taps->set_status($enrolment->enrolmentid, 'Full Attendance', $completiontime);
                if ($result->success) {
                    if (!$tccompletion) {
                        $record = new \stdClass();
                        $record->tapscompletionid = $tc->tapscompletion->id;
                        $record->userid = $event->relateduserid;
                        $record->completed = $enrolment->enrolmentid;
                        $record->timemodified = time();
                        $DB->insert_record('tapscompletion_completion', $record);
                    } else if (!$tccompletion->completed) {
                        $tccompletion->completed = $enrolment->enrolmentid;
                        $tccompletion->timemodified = time();
                        $DB->update_record('tapscompletion_completion', $tccompletion);
                    }
                    $completion = new \completion_info($tc->course);
                    $completion->update_state($cm, COMPLETION_COMPLETE, $event->relateduserid);

                    // Update Moodle course completion date.
                    // Record should exist as we're observing course completion.
                    $ccompletion = new \completion_completion(array('course' => $tc->course->id, 'userid' => $event->relateduserid));
                    $ccompletion->timecompleted = $completiontime;
                    $ccompletion->update();
                }
            }
        }
    }
}
