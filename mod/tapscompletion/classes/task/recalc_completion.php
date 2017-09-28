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
 * The mod_tapscompletion recalculate completion task.
 *
 * @package    mod_tapscompletion
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapscompletion\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * The mod_tapscompletion recalculate completion task class.
 *
 * @package    mod_tapscompletion
 * @since      Moodle 3.0
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recalc_completion extends \core\task\scheduled_task {

    private $cms = [];
    private $completions = [];
    private $courses = [];
    private $courseusersprocessed = [];
    private $taps;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskrecalccompletion', 'mod_tapscompletion');
    }

    /**
     * Run recalculate completion task.
     */
    public function execute() {
        global $CFG, $DB;

        list($instatement, $inparams) = $DB->get_in_or_equal(
            array_merge($this->get_taps()->get_statuses('attended')),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');
        $sql = <<<EOS
SELECT
    DISTINCT
    lte.enrolmentid,
    u.id as uid,
    t.id as tid,
    cm.id as cmid,
    c.id as cid,
    tc.id as tcid, tc.completed as tccompleted,
    cmc.completionstate as cmccompletionstate
FROM {local_taps_enrolment} lte
JOIN {user} u
    ON u.idnumber = lte.staffid
JOIN {tapscompletion} t
    ON t.tapscourse = lte.courseid
JOIN {course_modules} cm
    ON cm.instance = t.id
JOIN {course} c
    ON c.id = cm.course
JOIN {modules} m
    ON m.id = cm.module

JOIN {user_enrolments} ue
    ON ue.userid = u.id
JOIN {enrol} e
    ON e.id = ue.enrolid AND e.courseid = c.id

LEFT JOIN {tapscompletion_completion} tc
    ON tc.tapscompletionid = t.id
    AND tc.userid = u.id
LEFT JOIN {course_modules_completion} cmc
    ON cmc.coursemoduleid = cm.id
    AND cmc.userid = u.id
WHERE
    u.deleted = 0
    AND u.id <> :guestid
    AND ue.status = :active
    AND e.status = :enabled
    AND ue.timestart < :now1
    AND (ue.timeend = 0 OR ue.timeend > :now2)
    AND lte.classstarttime < :now3
    AND lte.active = 1
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$instatement}
    AND t.completionattended = 1
    AND (tc.completed IS NULL or tc.completed = 0)
    AND m.name = :modulename
    AND c.enablecompletion = :enablecompletion
    AND cm.completion = :completion
ORDER BY
    c.id ASC, lte.enrolmentid DESC
EOS;
        $now = time();
        $params = [
            'guestid' => $CFG->siteguest,
            'active' => ENROL_USER_ACTIVE,
            'enabled' => ENROL_INSTANCE_ENABLED,
            'now1' => $now,
            'now2' => $now,
            'now3' => $now,
            'modulename' => 'tapscompletion',
            'enablecompletion' => COMPLETION_ENABLED,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
        ];

        // Enrolled users with 'attended' TAPS enrolments and not completed.
        $validenrolments = $DB->get_records_sql($sql, array_merge($params, $inparams));

        // Reset processed tarcking.
        $this->courseusersprocessed = [];
        foreach ($validenrolments as $validenrolment) {
            if (!$this->to_process($validenrolment->uid, $validenrolment->cid)) {
                continue;
            }
            // Mark as complete.
            if (is_null($validenrolment->tcid)) {
                $record = new \stdClass();
                $record->tapscompletionid = $validenrolment->tid;
                $record->userid = $validenrolment->uid;
                $record->completed = $validenrolment->enrolmentid;
                $record->timemodified = time();
                $DB->insert_record('tapscompletion_completion', $record);
            } else if (!$validenrolment->tccompleted) {
                $record = new \stdClass();
                $record->id = $validenrolment->tcid;
                $record->completed = $validenrolment->enrolmentid;
                $record->timemodified = time();
                $DB->update_record('tapscompletion_completion', $record);
            }
            if ($validenrolment->cmccompletionstate != COMPLETION_COMPLETE) {
                $this->get_completion($validenrolment->cid)->update_state($this->get_cm($validenrolment->cmid), COMPLETION_COMPLETE, $validenrolment->uid);
            }
        }
    }

    private function get_taps() {
        if (empty($this->taps)) {
            $this->taps = new \local_taps\taps();
        }
        return $this->taps;
    }

    private function to_process($userid, $courseid) {
        if (!empty($this->courseusersprocessed[$courseid][$userid])) {
            return false;
        }
        $this->courseusersprocessed[$courseid][$userid] = true;
        return true;
    }

    private function get_course($id) {
        global $DB;
        if (empty($this->courses[$id])) {
            $this->courses[$id] = $DB->get_record('course', array('id' => $id));
        }
        return $this->courses[$id];
    }

    private function get_cm($id) {
        global $DB;
        if (empty($this->cms[$id])) {
            $this->cms[$id] = $DB->get_record('course_modules', array('id' => $id));
        }
        return $this->cms[$id];
    }

    private function get_completion($courseid) {
        if (empty($this->completions[$courseid])) {
            $this->completions[$courseid] = new \completion_info($this->get_course($courseid));
        }
        return $this->completions[$courseid];
    }
}
