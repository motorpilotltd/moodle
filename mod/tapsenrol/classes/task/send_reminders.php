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
 * The mod_tapsenrol send reminders task.
 *
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapsenrol\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapsenrol send reminders task class.
 *
 * @package    mod_tapsenrol
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_reminders extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasksendreminders', 'mod_tapsenrol');
    }

    /**
     * Run send reminders task.
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
        list($in2, $inparams2) = $DB->get_in_or_equal(
            $taps->get_statuses('placed'),
            SQL_PARAMS_NAMED, 'status2'
        );
        $compare2 = $DB->sql_compare_text('lte.bookingstatus');
        list($in3, $inparams3) = $DB->get_in_or_equal(
            $taps->get_statuses('placed'),
            SQL_PARAMS_NAMED, 'status3'
        );
        $compare3 = $DB->sql_compare_text('lte.bookingstatus');

        $from = <<<EOS
    {tapsenrol_iw_tracking} iwt
JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = iwt.enrolmentid AND (lte.archived = 0 OR lte.archived IS NULL)
JOIN
    {tapsenrol} t
    ON t.tapscourse = lte.courseid
JOIN
    {tapsenrol_iw} iw
    ON iw.id = t.internalworkflowid
EOS;
        $wheres = array();
        // Requires approval reminder.
        // Reminder not already sent.
        // Not already approved.
        // Enrolment in 'requested' state.
        // Currently in the approval reminder sending window.
        // Class is in the future (or is a waiting list, i.e. start time usually 0).
        $wheres['approvalreminder'] = <<<EOS
    (
        iw.approvalreminder > 0
        AND (iwt.remindersent < 1 OR iwt.remindersent IS NULL)
        AND iwt.approved IS NULL
        AND {$compare} {$in}
        AND iwt.timeenrolled < ({$now} - iw.approvalreminder)
        AND iwt.timeenrolled > ({$now} - (iw.approvalreminder + 24*60*60))
        AND (lte.classstarttime > {$now} OR lte.classstarttime = 0)
    )
EOS;
        // Requires the first reminder to be sent to applicant.
        // Reminder not already sent.
        // Enrolment in 'approved' state.
        // Enrolment approved long enough ago to warrant reminder.
        // Currently in the first reminder sending window.
        $wheres['firstreminder'] = <<<EOS
    (
        iw.firstreminder > 0
        AND (iwt.remindersent < 2 OR iwt.remindersent IS NULL)
        AND {$compare2} {$in2}
        AND iwt.timeenrolled < ({$now} - iw.noreminder)
        AND iwt.timeapproved < ({$now} - iw.noreminder)
        AND lte.classstarttime < ({$now} + iw.firstreminder)
        AND lte.classstarttime > ({$now} + (iw.firstreminder - 24*60*60))
    )
EOS;
        // Requires the second reminder to be sent to applicant.
        // Reminder not already sent.
        // Enrolment in 'approved' state.
        // Enrolment approved long enough ago to warrant reminder.
        // Currently in the first reminder sending window.
        $wheres['secondreminder'] = <<<EOS
    (
        iw.secondreminder > 0
        AND (iwt.remindersent < 3 OR iwt.remindersent IS NULL)
        AND {$compare3} {$in3}
        AND iwt.timeenrolled < ({$now} - iw.noreminder)
        AND iwt.timeapproved < ({$now} - iw.noreminder)
        AND lte.classstarttime < ({$now} + iw.secondreminder)
        AND lte.classstarttime > ({$now} + (iw.secondreminder - 24*60*60))
    )
EOS;

        $params = array_merge($inparams, $inparams2, $inparams3);

        // Process necessary records.
        $sql = <<<EOS
SELECT
    iwt.*,
    t.id as tid,
    lte.classstarttime as lteclassstarttime,
    lte.classtype as lteclasstype,
    iw.firstreminder as iwfirstreminder,
    iw.secondreminder as iwsecondreminder
FROM
    {$from}
WHERE
    {$wheres['approvalreminder']}
    OR
    {$wheres['firstreminder']}
    OR
    {$wheres['secondreminder']}
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

            $emailsent = false;
            if (is_null($record->approved)) {
                $email = 'approval_request_reminder';
                $record->remindersent = 1;
                $emailsent = $tapsenrol->send_sponsor_reminder($record->enrolmentid, $email);
            } else if ($tapsenrol->taps->is_classtype($record->lteclasstype, 'classroom')) {
                // Only send reminders for classroom based classes.
                $firstreminder = $record->iwfirstreminder && $record->lteclassstarttime > ($now + ($record->iwfirstreminder - (24 * 60 * 60)));
                $secondreminder = $record->iwsecondreminder && $record->lteclassstarttime > ($now + ($record->iwsecondreminder - (24 * 60 * 60)));
                if ($firstreminder && ($record->remindersent < 2 || is_null($record->remindersent))) {
                    $email = 'reminder_first';
                    $record->remindersent = 2;
                } else if ($secondreminder && ($record->remindersent < 3 || is_null($record->remindersent))) {
                    $email = 'reminder_second';
                    $record->remindersent = 3;
                }
                if (!empty($email)) {
                    $emailsent = $tapsenrol->send_applicant_reminder($record->enrolmentid, $email);
                }
            }

            if ($emailsent) {
                $record->timemodified = $now;
                $DB->update_record('tapsenrol_iw_tracking', $record);
            }
        }
    }
}
