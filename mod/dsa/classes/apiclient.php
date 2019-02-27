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

namespace mod_dsa;

use mod_dsa\event\user_dsa_assessments_updated;

class apiclient {
    private $coursesandcms = [];
    private $dateformatstring = 'Y-m-d H:i:s';
    private $context;

    public static function getapiclient() {
        if (empty(PHPUNIT_TEST)) {
            return new self();
        } else {
            return new testapiclient();
        }
    }

    public function __construct() {
        global $DB;

        $this->config = get_config('mod_dsa');
        $this->context = \context_system::instance();

        $instances = $DB->get_records('dsa');
        foreach ($instances as $instance) {
            $this->coursesandcms[] = get_course_and_cm_from_instance($instance->id, 'dsa');
        }
    }

    public function staffprogress($staffid) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $basic_hash = base64_encode($this->config->username . ':' . $this->config->key);
        $authheader = "Basic {$basic_hash}";

        $url = "/api/progress/{$staffid}";

        $curl = new \curl();
        $curl->setHeader('Authorization: ' . $authheader);
        $curlresponse = $curl->get($this->config->apihost . $url, ['format' => 'json']);

        $response = json_decode($curlresponse);

        if ($response === null) {
            throw new \Exception('Unable to parse response from webservice');
        }

        return $response->assessmentDetails;
    }

    public function checkforupdate($time, $lastrun) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $basic_hash = base64_encode($this->config->username . ':' . $this->config->key);
        $authheader = "Basic {$basic_hash}";

        $minutes = ($time - $lastrun) / MINSECS;

        $url = "/api/updates/$minutes";

        $curl = new \curl();
        $curl->setHeader('Authorization: ' . $authheader);
        $curlresponse = $curl->get($this->config->apihost . $url, ['format' => 'json']);

        $response = json_decode($curlresponse);

        if ($response === null) {
            throw new \Exception('Unable to parse response from webservice');
        }

        return $response->staffNumbers;
    }

    /**
     * @param $user
     * @param $DB
     * @throws \coding_exception
     */
    public function sync_user($user) {
        global $DB;

        $progresses = $this->staffprogress(ltrim($user->idnumber, 0));

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

        if (!empty($liveassessmentids)) {
            list($insql, $params) = $DB->get_in_or_equal($liveassessmentids, SQL_PARAMS_NAMED, 'param', false);
        } else {
            $params = [];
            $insql = " IS NOT NULL ";
        }

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

    public function sync_course($courseid, $userid = null) {
        global $DB;

        $context = \context_course::instance($courseid);

        if ($userid == null) {
            $users = get_enrolled_users($context);
        } else {
            $users = [\core_user::get_user($userid)];
        }

        foreach ($users as $user) {
            $incompleterecords =
                    $DB->get_records_sql('SELECT * FROM {dsa_assessment} WHERE state <> "closed" AND state <> "abandoned" AND userid = :userid',
                            ['userid' => $user->id]);
            $complete = empty($incompleterecords);

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
        }
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