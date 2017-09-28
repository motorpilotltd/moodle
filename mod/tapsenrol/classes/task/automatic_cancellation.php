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
 * The mod_tapsenrol automatic cancellation task.
 *
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapsenrol\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapsenrol automatic cancellation task class.
 *
 * @package    mod_tapsenrol
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class automatic_cancellation extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskautomaticcancellation', 'mod_tapsenrol');
    }

    /**
     * Run automatic cancellation task.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/tapsenrol/classes/tapsenrol.php');

        $now = time();

        $taps = new \local_taps\taps();

        list($in, $inparams) = $DB->get_in_or_equal(
            $taps->get_statuses('requested'),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');

        // Only cancel classroom based enrolments based on start time.
        list($in2, $inparams2) = $DB->get_in_or_equal(
            $taps->get_classtypes('classroom'),
            SQL_PARAMS_NAMED, 'type'
        );
        $compare2 = $DB->sql_compare_text('lte.classtype');

        $from = <<<EOS
    {local_taps_enrolment} lte
JOIN
    {tapsenrol_iw_tracking} iwt
    ON iwt.enrolmentid = lte.enrolmentid
JOIN
    {tapsenrol} t
    ON t.tapscourse = lte.courseid
JOIN
    {tapsenrol_iw} iw
    ON iw.id = t.internalworkflowid
EOS;
        // Not approved and not already cancelled.
        // Check if cancelling a certain period after enrolment.
        // Check if cancelling a certain period before the class starts.
        $where = <<<EOS
    (lte.archived = 0 OR lte.archived IS NULL)
    AND iwt.approved IS NULL AND iwt.timecancelled IS NULL
    AND
    (
        (
            iw.cancelafter > 0
            AND iwt.timeenrolled < ({$now} - iw.cancelafter)
        )
        OR
        (
            lte.classstarttime != 0
            AND iw.cancelbefore > 0
            AND lte.classstarttime < ({$now} + iw.cancelbefore)
            AND {$compare2} {$in2}
        )
    )
    AND {$compare} {$in}
EOS;

        $params = array_merge($inparams, $inparams2);

        // Process necessary records.
        $sql = <<<EOS
SELECT
    lte.*,
    t.id as tid,
    iw.cancelafter as iwcancelafter,
    iw.cancelbefore as iwcancelbefore
FROM
    {$from}
WHERE
    {$where}
ORDER BY
    t.id ASC
EOS;
        $records = $DB->get_records_sql($sql, $params);
        $prevtapsenrolid = 0;
        foreach ($records as $record) {
            if ($record->tid != $prevtapsenrolid) {
                $tapsenrol = new \tapsenrol($record->tid, 'instance');
                $prevtapsenrolid = $record->tid;
            }

            $cancelresult = $tapsenrol->cancel_enrolment($record->enrolmentid);
            if ($cancelresult->success) {
                // Variable $record will have original booking status which is required.
                $isclassroom = $taps->get_classtype_type($record->classtype) == 'classroom';
                $cancancelbefore = $record->classstarttime != 0 && $record->iwcancelbefore;
                if ($isclassroom && $cancancelbefore && $record->classstarttime < ($now + $record->iwcancelbefore)) {
                    $a = ($record->iwcancelbefore / (60 * 60)) . ' hours';
                    $message = get_string('iw:autocancellation_classstarted', 'tapsenrol', $a);
                    $email = 'cancelled_classstart';
                } else {
                    $a = ($record->iwcancelafter / (24 * 60 * 60)) . ' days';
                    $message = get_string('iw:autocancellation_notapproved', 'tapsenrol', $a);
                    $email = 'cancelled';
                }
                $tapsenrol->cancel_workflow($record, $message, $email);
            }
        }
    }
}
