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

    /**
     * @param $start
     * @return mixed
     */
    public function getcourses($start) {
        $start = $start + 1; // The API isn't 0 based.
        // Max value for limit appears to be 41 (seems pretty random to me...) but it's really unreliable if we go over 25.
        // It's probably unreliable due to the MASSIVE json docs that come back being unparsable.
        raise_memory_limit(MEMORY_HUGE);
        $url = "/courses?order=ByTitle&sort=asc&start=$start&limit=250&filter.includes=ID,Title,Description,DurationInSeconds,Tags.ID,Tags.Name,Tags.Type,Tags.TypeName,Thumbnails.FullURL";
        $results = $this->callapi($url);

        if ($results === null) {
            mtrace('Failed to load or parse response');
        }

        return $results;
    }

    /**
     * Runs from 00:00:00 on start date to 23:59:59 on enddate.
     *
     * @param $startdate
     * @param $enddate
     * @param $start
     * @return array
     */
    public function individualusagedetail($startdate, $enddate, $start) {
        $start = $start + 1; // The API isn't 0 based.
        $startdate = date('Y-m-d', $startdate);
        $enddate = date('Y-m-d', $enddate);

        $url =
                "/reports/IndividualUsageDetail?startDate=$startdate&endDate=$enddate&start=$start&limit=250";
        $results = $this->callapi($url);

        // GRIM HACK TO ALLOW FOR PAGINATION NOT WORKING. - Check to see if we have seen this exact response before.
        $resulthash = md5(json_encode($results));
        if (!isset($this->previoushash_ud) || $this->previoushash_ud !== $resulthash) {
            $this->previoushash_ud = $resulthash;
        } else {
            return [];
        }
        // END HACK

        if (!isset($results->ReportData)) {
            return [];
        }

        return $results->ReportData;
    }

    /**
     * Runs from 00:00:00 on start date to 23:59:59 on enddate.
     *
     * @param $startdate
     * @param $enddate
     * @param $start
     * @return array
     */
    public function certficateofcompletion($startdate, $enddate, $start) {
        $start = $start + 1; // The API isn't 0 based.
        $startdate = date('Y-m-d', $startdate);
        $enddate = date('Y-m-d', $enddate);

        $url =
                "/reports/CertificateOfCompletion?startDate=$startdate&endDate=$enddate&start=$start&limit=250";
        $results = $this->callapi($url);

        // GRIM HACK TO ALLOW FOR PAGINATION NOT WORKING. - Check to see if we have seen this exact response before.
        $resulthash = md5(json_encode($results));
        if (!isset($this->previoushash_coc) || $this->previoushash_coc !== $resulthash) {
            $this->previoushash_coc = $resulthash;
        } else {
            return [];
        }
        // END HACK

        if (!isset($results->ReportData)) {
            return [];
        }

        return $results->ReportData;
    }

    private $config;
    private $appkey;
    private $secretkey;

    public function __construct() {
        $this->config = get_config('local_lynda');
    }

    protected function setregion($regionid) {
        if (empty($this->config->{"appkey_$regionid"}) || empty($this->config->{"secretkey_$regionid"})) {
            return false;
        }
        $this->appkey = $this->config->{"appkey_$regionid"};
        $this->secretkey = $this->config->{"secretkey_$regionid"};
        return true;
    }

    protected function setanyregion() {
        global $DB;
        $regions = $DB->get_records_menu('local_regions_reg', ['userselectable' => true]);

        foreach ($regions as $id => $name) {
            if ($this->setregion($id)) {
                return true;
            }
        }

        mtrace('Unable to load any API keys');
        return false;
    }

    private function callapi($url) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $timestamp =
                time(); // Note that the timestamp must be no more than 5 minutes off from the actual timestamp on the target host.
        $api_hash = md5($this->appkey . $this->secretkey . $this->config->apiurl . $url . $timestamp);

        $curl_headers = array(
                "appkey: " . $this->appkey,
                "timestamp: " . $timestamp,
                "hash: " . $api_hash
        );
        $curl = new \curl();
        $url = $this->config->apiurl . $url;
        $options = array(
                'RETURNTRANSFER' => true,
                'USERAGENT'      => 'Moodle',
                'HTTPHEADER'     => $curl_headers,
                'CONNECTTIMEOUT' => 0,
                'TIMEOUT'        => 240, // Fail if data not returned within 10 seconds.
        );
        $result = $curl->get($url, '', $options);

        mtrace('Called Lynda API: ' . $url);
        if ($curl->info['http_code'] != 200) {
            mtrace("Error calling web service: \n" . print_r($curl->get_raw_response(), true));
        }

        return json_decode($result);
    }

    /**
     * Runs from 00:00:00 on start date to 23:59:59 on enddate.
     * Is idempotent so can be run multiple times over the same date range.
     *
     */
    public function synccourseprogress() {
        global $DB;
        $regions = $DB->get_records_menu('local_regions_reg', ['userselectable' => true]);
        $thisruntime = time();

        foreach ($regions as $id => $name) {
            $lastruntimeprogress = get_config('local_lynda', 'lastruntimeprogress_' . $id);
            if (empty($lastruntimeprogress)) {
                $lastruntimeprogress = 0;
            }

            if (!$this->setregion($id)) {
                mtrace('No keys for ' . $name);
                continue;
            }
            mtrace('Started synccourseprogress for ' . $name);

            $tz = new \DateTimeZone('America/Los_Angeles');
            foreach ($this->getcourseprogressiterator($lastruntimeprogress - DAYSECS, $thisruntime) as $raw) {
                $datetime = \DateTime::createFromFormat('m/d/Y H:i:s', $raw->LastViewed, $tz);
                $timestamp = $datetime->getTimestamp();

                $progressrecord = lyndacourseprogress::fetch(['userid'         => $raw->Username,
                                                              'remotecourseid' => $raw->CourseID]);
                if ($progressrecord) {
                    ;
                    mtrace('Updated course progress record for ' . $raw->Username);
                    $progressrecord->lastviewed = $timestamp;
                    $progressrecord->percentcomplete = $raw->PercentComplete;
                    $progressrecord->regionid = $id;
                    $progressrecord->update();
                } else {
                    mtrace('Created course progress record for ' . $raw->Username);
                    $progressrecord = new lyndacourseprogress();
                    $progressrecord->userid = $raw->Username;
                    $progressrecord->remotecourseid = $raw->CourseID;
                    $progressrecord->lastviewed = $timestamp;
                    $progressrecord->percentcomplete = $raw->PercentComplete;
                    $progressrecord->regionid = $id;
                    $progressrecord->insert();
                }
            }

            set_config('lastruntimeprogress_' . $id, $thisruntime, 'local_lynda');

            mtrace('Finished synccourseprogress for ' . $name);
        }
    }

    /**
     * Runs from 00:00:00 on start date to 23:59:59 on enddate.
     * Is idempotent so can be run multiple times over the same date range.
     *
     * @param $lastruntime
     * @param $thisruntime
     */
    public function synccoursecompletion() {
        global $DB;
        $regions = $DB->get_records_menu('local_regions_reg', ['userselectable' => true]);
        $thisruntime = time();

        foreach ($regions as $id => $name) {
            if (!$this->setregion($id)) {
                mtrace('No keys for ' . $name);
                continue;
            }
            mtrace('Started synccourseprogress for ' . $name);

            $tz = new \DateTimeZone('America/Los_Angeles');
            $taps = new \local_taps\taps();

            $lastruntimecompletion = get_config('local_lynda', 'lastruntimecompletion_' . $id);
            if (empty($lastruntimecompletion)) {
                $lastruntimecompletion = 0;
            }

            foreach ($this->getcoursecompletioniterator($lastruntimecompletion - DAYSECS, $thisruntime) as $raw) {
                $user = self::getcacheduser($raw->Username);
                if ($user == null) {
                    mtrace('Unable to find user account for ' . $raw->Username);
                    continue;
                }

                $datetime = \DateTime::createFromFormat('m/d/Y H:i:s', $raw->CompleteDate, $tz);
                $timestamp = $datetime->getTimestamp();

                $classnamecompare = $DB->sql_compare_text('classname', 64);
                $classnamevaluecompare = $DB->sql_compare_text(':classname', 64);
                $providercompare = $DB->sql_compare_text('provider');
                $sql =
                        "SELECT * FROM {local_taps_enrolment} WHERE staffid = :staffid AND $classnamecompare = $classnamevaluecompare AND $providercompare = :providername";
                if ($DB->record_exists_sql($sql,
                        ['staffid' => $user->idnumber, 'classname' => $raw->CourseName, 'providername' => 'Lynda.com'])
                ) {
                    continue;
                }
                $lyndacourse = lyndacourse::fetchbyremotecourseid($raw->CourseID);

                if ($lyndacourse) {
                    $description = $lyndacourse->description;
                } else {
                    $description = '';
                }

                $taps->add_cpd_record(
                        $user->idnumber,
                        $raw->CourseName,
                        'Lynda.com',
                        $timestamp,
                        $raw->CourseDuration,
                        'MIN',
                        ['p_learning_method' => 'ECO', 'p_subject_catetory' => 'PD', 'p_learning_desc' => $description,
                         'p_providerid'      => $raw->CourseID]
                );
                mtrace('Created CPD record for ' . $raw->Username);
            }

            set_config('lastruntimecompletion_' . $id, $thisruntime, 'local_lynda');

            mtrace('Finished synccourseprogress for ' . $name);
        }
    }

    public function synccourses() {
        global $DB;

        if (!$this->setanyregion()) {
            return;
        }

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
                $sql =
                        "UPDATE {local_taps_enrolment} SET learningdesc = :coursedescription WHERE provider = 'Lynda.com' AND providerid = :remotecourseid";
                $DB->execute($sql, ['coursedescription' => $course->description, 'remotecourseid' => $course->remotecourseid]);
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
        $lyndacourse->durationinseconds = $raw->DurationInSeconds;

        $lyndacourse->lyndatags = [];
        foreach ($raw->Tags as $tag) {
            $lyndacourse->lyndatags[] = $tag->ID;
        }

        $lyndacourse->thumbnail = $raw->Thumbnails[0]->FullURL;

        foreach ($raw->Thumbnails as $thumbnail) {
            if (stristr($thumbnail->FullURL, '130x230') !== false) {
                $lyndacourse->thumbnail = $thumbnail->FullURL;
                break;
            }
        }

        return $lyndacourse;
    }
}