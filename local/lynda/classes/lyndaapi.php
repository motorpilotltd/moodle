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
/*
    // The search API cannot be used to just list out all courses, you have to provide a keyword.
    public function search($keywords) {
        $url = '/search/?order=ByDate&sort=desc&q=' . urlencode($keywords);
        $results = $this->callapi($url);
        return $results;
    }

    // Get a record for each course completed by each user - no way to do this for a specific user, could do this in batch and process?
    public function certficateofcompletion($enddate) {
        $url = '/reports/CertificateOfCompletion?startDate=2012-10-01&endDate=' . $enddate .
                '&start=1&limit=50&order=LastName&sort=asc';
        $results = $this->callapi($url);
        return $results;
    }

    // Get some high level stats for each user - probably not useful.
    public function userlist($enddate) {
        $url = '/reports/UserList?startDate=2012-10-01&endDate=' . $enddate .
                '&start=1&limit=500&order=TotalMovieHours&sort=desc';
        $results = $this->callapi($url);
        return $results;
    }

    // Get a record for each course viewed by each user including completion level - no way to do this for a specific user, could do this in batch and process?
    public function individualusagedetail($enddate) {
        $url = '/reports/IndividualUsageDetail?startDate=2012-11-01&endDate=' . $enddate .
                '&start=1&limit=20&order=LastViewed&sort=desc';
        $results = $this->callapi($url);
        return $results;
    }
*/
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
        return json_decode($curlresult);
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
                $toinsert[] = (object)['remotetypeid' => $remoteid, 'name' => $typename];
            }
        }
        $DB->insert_records('local_lynda_tagtypes', $toinsert);

        $toinsert = [];
        foreach ($tags as $remoteid => $tag) {
            if (!isset($existingtags[$remoteid])) {
                $toinsert[] = (object)['remotetagid' => $remoteid, 'name' => $tag->Name, 'remotetypeid' => $tag->Type];
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
        return $lyndacourse;
    }
}