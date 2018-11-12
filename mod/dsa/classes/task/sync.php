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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

namespace mod_dsa\task;

use core\task\scheduled_task;
use mod_dsa\apiclient;
use mod_dsa\event\user_dsa_assessments_updated;

defined('MOODLE_INTERNAL') || die();

class sync extends scheduled_task {
    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('sync', 'mod_dsa');
    }

    private $apiclient;
    private $coursesandcms = [];
    private $context;
    private $dateformatstring = 'Y-m-d H:i:s';
    private $synclimit = 100;

    public function __construct($apiclient = null) {
        global $DB;

        if ($apiclient == null) {
            $this->apiclient = new apiclient();
        } else {
            $this->apiclient = $apiclient;
        }
        $this->context = \context_system::instance();
    }

    public function execute() {
        global $DB, $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $instances = $DB->get_records('dsa');
        foreach ($instances as $instance) {
            $this->coursesandcms[] = get_course_and_cm_from_instance($instance->id, 'dsa');
        }

        mtrace("Checking for active users");

        $now = time();

        // On first run just get last 24hrs then let the rest come in piece-meal.
        if ($this->get_last_run_time() == 0) {
            $this->set_last_run_time($now - DAYSECS);
        }

        $newrecords = $this->apiclient->checkforupdate($now, $this->get_last_run_time());

        mtrace("Loaded records: " . count($newrecords));

        foreach ($newrecords as $newrecord) {
            $user = $DB->get_record('user', ['idnumber' => str_pad($newrecord->StaffNumber, 6, '0', STR_PAD_LEFT)]);

            if (!$user) {
                mtrace('Unable to find staff number: ' . $newrecord->StaffNumber);
                continue;
            }

            $this->sync_user($user);

        }
        mtrace("Checking for users that have never been synced.");

        $countsql = "select count(u.id)
                from {user} u
                       left join {logstore_standard_log} l on l.relateduserid = u.id and component = 'mod_dsa' and action = 'updated'
                       left join {dsa_assessment} da on da.userid = u.id
                where da.id is null
                  and l.id is null
                  and u.idnumber <> ''
                  and u.deleted = 0";

        $neversyncedcount = $DB->count_records_sql($countsql);
        mtrace("Found $neversyncedcount that have never been synced. Will sync the first $this->synclimit");

        $sql = "select u.*
                from {user} u
                       left join {logstore_standard_log} l on l.relateduserid = u.id and component = 'mod_dsa' and action = 'updated'
                       left join {dsa_assessment} da on da.userid = u.id
                where da.id is null
                  and l.id is null
                  and u.idnumber <> ''
                  and u.deleted = 0";
        $neversynced = $DB->get_records_sql($sql, [], 0, $this->synclimit);

        foreach ($neversynced as $user) {
            $this->sync_user($user);
        }

        mtrace("Done");

        $this->set_last_run_time($now);
        return true;
    }

    /**
     * @param $user
     * @param $DB
     * @throws \coding_exception
     */
    private function sync_user($user) {
        global $DB;

        $progresses = $this->apiclient->staffprogress(ltrim($user->idnumber, 0));

        $complete = !empty($progresses); // If no assessments then activity is incopmlete.

        $liveassessmentids = [];

        foreach ($progresses as $progress) {
            $started = $this->parsedate($progress->Started);
            $completed = $this->parsedate($progress->Completed);
            $closed = $this->parsedate($progress->Closed);

            $todb = $DB->get_record('dsa_assessment', ['assessmentid' => $progress->AssessmentID]);

            $create = empty($todb);

            if ($create) {
                $todb = new \stdClass();
            }

            $todb->userid = $user->id;
            $todb->assessmentid = $progress->AssessmentID;
            $todb->state = $progress->State;
            $todb->started = $started;
            $todb->completed = $completed;
            $todb->closed = $closed;
            $todb->locationcode = $progress->LocationCode;
            $todb->officename = $progress->OfficeName;
            $todb->machineidentification = $progress->MachineIdentification;
            $todb->reason = $progress->Reason;
            $todb->assessorfirstname = $progress->AssessorFirstName;
            $todb->assessorlastname = $progress->AssessorLastName;
            $todb->assessoremailaddress = $progress->AssessorEmailAddress;
            $todb->assessorphonenumber = $progress->AssessorPhoneNumber;
            $todb->status = $progress->Status;

            if ($todb->state !== 'closed' && $todb->state !==
                    'abandoned') { // All assessments must be abandoned or closed for the activity to be complete.
                $complete = false;
            }

            if (!$create) {
                $DB->update_record('dsa_assessment', $todb);
            } else {
                $todb->id = $DB->insert_record('dsa_assessment', $todb);
            }

            $liveassessmentids[] = $progress->AssessmentID;
        }

        list($insql, $params) = $DB->get_in_or_equal($liveassessmentids, SQL_PARAMS_NAMED, 'param', false);
        $sql = 'DELETE FROM {dsa_assessment} WHERE userid = :userid AND assessmentid ' . $insql;
        $params['userid'] = $user->id;
        $DB->execute($sql, $params);

        $expectedstate = $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;

        foreach ($this->coursesandcms as $courseandcm) {
            $course = $courseandcm[0];
            $cm = $courseandcm[1];

            $completion = new \completion_info($course);

            if (!$complete && $completion->is_course_complete($user->id)) {
                \local_custom_certification\completion::reset_course_for_user($course->id, $user->id);
            } else {
                $completion->update_state($cm, $expectedstate, $user->id);
            }
        }

        user_dsa_assessments_updated::create(['relateduserid' => $user->id, 'context' => $this->context])->trigger();
    }

    /**
     * @param $progress
     * @return array
     */
    private function parsedate($date) {
        if (!empty($date)) {
            $completed = \DateTime::createFromFormat($this->dateformatstring, $date);
            $completed = $completed->getTimestamp();
        } else {
            $completed = 0;
        }
        return $completed;
    }
}
