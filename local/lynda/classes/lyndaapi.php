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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

class lyndaapi {

    public function getcourses($start) {
        $start = $start + 1; // The API isn't 0 based.
        // Max value for limit appears to be 41 (seems pretty random to me...) but it's really unreliable if we go over 25.
        // It's probably unreliable due to the MASSIVE json docs that come back being unparsable.
        $url = "/courses?order=ByTitle&sort=asc&start=$start&limit=25";
        $results = $this->callapi($url);
        return $results;
    }

    public function individualusagedetail($startdate, $enddate, $start) {
        $start = $start + 1; // The API isn't 0 based.

        $startdate = date('m-d-Y', $startdate);
        $enddate = date('m-d-Y', $enddate);

        $url =
                "/reports/IndividualUsageDetail?startDate=$startdate&endDate=$enddate&start=$start&limit=100&order=LastViewed&sort=desc";
        $results = $this->callapi($url);
        return $results;
    }

    public function certficateofcompletion($startdate, $enddate, $start) {
        $start = $start + 1; // The API isn't 0 based.

        $startdate = date('m-d-Y', $startdate);
        $enddate = date('m-d-Y', $enddate);

        $url =
                "/reports/CertificateOfCompletion?startDate=$startdate&endDate=$enddate&start=$start&limit=100&order=LastViewed&sort=desc";
        $results = $this->callapi($url);
        return $results;
    }

    private $config;

    public function __construct() {
        $this->config = get_config('local_lynda');
    }

    private function callapi($url) {
        $timestamp =
                time(); // Note that the timestamp must be no more than 5 minutes off from the actual timestamp on the target host.
        $api_hash = md5($this->config->appkey . $this->config->secretkey . $this->config->apiurl . $url . $timestamp);

        $curl_headers = array(
                "appkey: " . $this->config->appkey,
                "timestamp: " . $timestamp,
                "hash: " . $api_hash
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Moodle');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_URL, $this->config->apiurl . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_headers);
        $curlresult = curl_exec($curl);

        mtrace('Called Lynda API: ' . $url);

        return json_decode($curlresult);
    }

    public function synccourseprogress($lastruntime, $thisruntime) {
        $tz = new \DateTimeZone('UTC');
        foreach ($this->getcourseprogressiterator($lastruntime, $thisruntime) as $raw) {
            $datetime = \DateTime::createFromFormat('m/d/Y H:i:s', $raw->LastViewed, $tz);
            $timestamp = $datetime->getTimestamp();

            $progressrecord = lyndacourseprogress::fetch(['userid'          => $raw->Username,
                                                          'remotecourseid'  => $raw->CourseID]);
            if ($progressrecord) {;
                mtrace('Updated course progress record for ' . $raw->Username);
                $progressrecord->lastviewed = $timestamp;
                $progressrecord->percentcomplete = $raw->PercentComplete;
                $progressrecord->update();
            } else {
                mtrace('Created course progress record for ' . $raw->Username);
                $progressrecord = new lyndacourseprogress();
                $progressrecord->userid = $raw->Username;
                $progressrecord->remotecourseid = $raw->CourseID;
                $progressrecord->lastviewed = $timestamp;
                $progressrecord->percentcomplete = $raw->PercentComplete;
                $progressrecord->insert();
            }
        }
    }

    public function synccoursecompletion($lastruntime, $thisruntime) {
        global $DB;

        $tz = new \DateTimeZone('UTC');
        $taps = new \local_taps\taps();

        foreach ($this->getcoursecompletioniterator($lastruntime, $thisruntime) as $raw) {
            $user = self::getcacheduser($raw->Username);
            if ($user == null) {
                mtrace('Unable to find user account for ' . $raw->Username);
                continue;
            }

            $datetime = \DateTime::createFromFormat('m/d/Y H:i:s', $raw->CompleteDate, $tz);
            $timestamp = $datetime->getTimestamp();
            $lyndacourse = lyndacourse::fetchbyremotecourseid($raw->CourseID);

            if (!$lyndacourse) {
                throw new \moodle_exception('Unknown lynda course', 'local_lynda');
            }

            $classnamecompare = $DB->sql_compare_text('classname');
            $providercompare = $DB->sql_compare_text('provider');
            $sql = "SELECT * FROM {local_taps_enrolment} WHERE staffid = :staffid AND $classnamecompare = :classname AND $providercompare = :providername";
            if ($DB->record_exists_sql($sql,
                    ['staffid' => $user->idnumber, 'classname' => $raw->CourseName, 'providername' => 'Lynda.com'])
            ) {
                continue;
            }

            $taps->add_cpd_record(
                    $user->idnumber,
                    $raw->CourseName,
                    'Lynda.com',
                    $timestamp,
                    $raw->CourseDuration,
                    'MIN',
                    ['p_learning_method' => 'ECO', 'p_subject_catetory' => 'PD', 'p_learning_desc' => $lyndacourse->description]
            );
            mtrace('Created CPD record for ' . $raw->Username);
        }

        /*
        avoid double counting in case where lynda course has been completed as a real LTI object
        api keys per region
        */
    }

    public function synccourses() {
        global $DB;

        $tags = [];
        $tagtypes = [];
        $lyndaidsreceived = [];

        $existingtags = $DB->get_records_sql('SELECT remotetagid, name, remotetypeid, id FROM {local_lynda_tags}');
        $existingtagtypes = $DB->get_records_sql('SELECT remotetypeid, name, id FROM {local_lynda_tagtypes}');
        $coursehashes = $DB->get_records_sql('SELECT remotecourseid, lyndadatahash FROM {local_lynda_course}');

        foreach ($this->getcourseiterator() as $raw) {
            $course = $this->buildcourse($raw);

            if (!isset($coursehashes[$course->remotecourseid])) {
                $course->insert();
            } else if (!empty($course->deletedbylynda) || $course->lyndadatahash != $coursehashes[$course->remotecourseid]) {
                $course->deletedbylynda = false;
                $course->update();
            }
            $lyndaidsreceived[] = $course->remotecourseid;

            foreach ($raw->Tags as $tag) {
                if (isset($tags[$tag->ID])) {
                    continue;
                }

                $tags[$tag->ID] = $tag;
                $tagtypes[$tag->Type] = $tag->TypeName;
            }
        }

        $toinsert = [];
        foreach ($tagtypes as $remoteid => $typename) {
            if (!isset($existingtagtypes[$remoteid])) {
                $toinsert[] = (object) ['remotetypeid' => $remoteid, 'name' => $typename];
            }
        }
        $DB->insert_records('local_lynda_tagtypes', $toinsert);

        $toinsert = [];
        foreach ($tags as $remoteid => $tag) {
            if (!isset($existingtags[$remoteid])) {
                $toinsert[] = (object) ['remotetagid' => $remoteid, 'name' => $tag->Name, 'remotetypeid' => $tag->Type];
            }
        }
        $DB->insert_records('local_lynda_tags', $toinsert);

        $deletedlyndacourses = array_diff(array_keys($coursehashes), $lyndaidsreceived);

        if (!empty($deletedlyndacourses)) {
            list($sql, $params) = $DB->get_in_or_equal($deletedlyndacourses);
            $DB->execute("UPDATE {local_lynda_course} SET deletedbylynda = 1 WHERE remotecourseid $sql", $params);
        }
    }

    private function getcourseiterator() {
        return new lyndacourseiterator($this);
    }

    private function getcourseprogressiterator($lastruntime, $thisruntime) {
        return new lyndacourseprogressiterator($lastruntime, $thisruntime, $this);
    }

    private function getcoursecompletioniterator($lastruntime, $thisruntime) {
        return new lyndacoursecompletioniterator($lastruntime, $thisruntime, $this);
    }

    private static function getcacheduser($userid) {
        $cache = \cache::make('local_lynda', 'users');
        $user = $cache->get($userid);
        if ($user === false) {
            $user = \core_user::get_user($userid);
            $cache->set($userid, $user);
        }

        return $user;
    }

    /**
     * @param $raw
     * @return lyndacourse
     */
    private function buildcourse($raw) {

        $lyndacourse = new lyndacourse();
        $lyndacourse->description = $raw->Description;
        $lyndacourse->remotecourseid = $raw->ID;
        $lyndacourse->title = $raw->Title;
        $lyndacourse->lyndadatahash = md5(serialize($raw));

        $lyndacourse->lyndatags = [];
        foreach ($raw->Tags as $tag) {
            $lyndacourse->lyndatags[] = $tag->ID;
        }

        $lyndacourse->thumbnail = $raw->Thumbnails[0]->FullURL;

        foreach ($raw->Thumbnails as $thumbnail) {
            if ($thumbnail->Height == 130 && $thumbnail->Width == 230) {
                $lyndacourse->thumbnail = $thumbnail->FullURL;
                break;
            }
        }

        return $lyndacourse;
    }
}