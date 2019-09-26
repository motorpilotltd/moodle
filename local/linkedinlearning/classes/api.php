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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

class api {

    /**
     * @param $start
     * @return mixed
     */
    public function getcourses($start, $since = 0) {
        raise_memory_limit(MEMORY_HUGE);
        $url = "https://api.linkedin.com/v2/learningAssets?";
        $params = [
                'q' => 'criteria',
                'assetFilteringCriteria.assetTypes[0]' => 'COURSE',
                'assetRetrievalCriteria.includeRetired' => 'true',
                'count' => '100',
                'start' => $start,
                'fields' => 'urn,title,details:(availability,classifications,publishedAt,lastUpdatedAt,images:(primary),description,shortDescription,timeToComplete,urls:(aiccLaunch))',
        ];

        if ($since !== 0) {
            $params['assetFilteringCriteria.lastModifiedAfter'] = $since * 1000;
        }

        $processedparams = [];
        foreach ($params as $key => $value) {
            $processedparams[] = "$key=$value";
        }
        $processedparams = implode('&', $processedparams);

        $results = $this->callapi($url . $processedparams);

        if ($results === null) {
            mtrace('Failed to load or parse response');
        }

        return $results->elements;
    }

    private $config;

    public function __construct() {
        $this->config = get_config('local_linkedinlearning');
    }

    private $tokenexpirytime = null;
    private $token = null;

    private function getaccesstoken() {
        global $CFG;

        if (isset($this->token) && $this->tokenexpirytime > time()) {
            return $this->token;
        }

        require_once($CFG->libdir . '/filelib.php');

        $url = new \moodle_url('https://www.linkedin.com/oauth/v2/accessToken', ['grant_type'    => 'client_credentials',
                                                                                 'client_id'     => $this->config->client_id,
                                                                                 'client_secret' => $this->config->client_secret
        ]);

        $curl = new \curl();
        $result = $curl->post($url);

        $response = json_decode($result);
        $this->tokenexpirytime = time() + $response->expires_in;
        $this->token = $response->access_token;

        return $this->token;
    }

    private function callapi($url) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $token = $this->getaccesstoken();
        $curl = new \curl();
        $options = array(
                'RETURNTRANSFER' => true,
                'USERAGENT'      => 'Moodle',
                'HTTPHEADER'     => ["Authorization: Bearer $token"],
                'CONNECTTIMEOUT' => 0,
                'TIMEOUT'        => 240, // Fail if data not returned within 10 seconds.
        );
        $result = $curl->get($url, '', $options);

        mtrace('Called Lynda API: ' . $url);
        if ($curl->info['http_code'] != 200) {
            mtrace("Error calling web service: \n" . print_r($curl->get_raw_response(), true));

            $result = json_decode($result);

            if (isset($result) && isset($result->serviceErrorCode)) {
                mtrace("Error calling web service: \n" . print_r($result, true));
                die();
            }
        }

        return json_decode($result);
    }

    public function synccourses($since) {
        global $DB;

        $classifications = classification::fetch_all_key_on_urn([]);

        foreach (new courseiterator($this, $since) as $raw) {
            $course = course::fetchbyurn($raw->urn);
            if (empty($course) && $raw->details->availability !== 'AVAILABLE') {
                continue;
            } else if (empty($course)) {
                $course = new course();
            }

            $course->urn = $raw->urn;
            $course->title = $raw->title->value;
            $course->primaryimageurl = $raw->details->images->primary;

            if ($raw->details->availability == 'AVAILABLE') {
                $course->aicclaunchurl = $raw->details->urls->aiccLaunch;
            }
            $course->publishedat = $raw->details->publishedAt / 1000;
            $course->lastupdatedat = $raw->details->lastUpdatedAt / 1000;
            $course->description = $raw->details->description->value;
            $course->shortdescription = $raw->details->shortDescription->value;
            $course->available = $raw->details->availability == 'AVAILABLE';

            switch ($raw->details->timeToComplete->unit) {
                case 'SECOND':
                    $multiplier = 1;
                    break;
                case 'MINUTE':
                    $multiplier = 60;
                    break;
                case 'HOUR':
                    $multiplier = 60 * 60;
                    break;
            }
            $course->timetocomplete = $raw->details->timeToComplete->duration * $multiplier;

            if (!isset($course->id)) {
                $course->insert();
            } else {
                $course->update();
            }

            $courseclassificationids = [];
            foreach ($raw->details->classifications as $rawclassification) {
                if (key_exists($rawclassification->associatedClassification->urn, $classifications)) {
                    $classification = $classifications[$rawclassification->associatedClassification->urn];
                } else {
                    $classification = new classification();
                    $classification->urn = $rawclassification->associatedClassification->urn;
                    $classification->name = $rawclassification->associatedClassification->name->value;
                    $classification->type = $rawclassification->associatedClassification->type;
                    $classification->insert();
                    $classifications[$classification->urn] = $classification;
                }
                $courseclassificationids[$classification->id] = $classification->id;
            }
            $course->updateclassifications($courseclassificationids);

            $course->update_moodle_course();
        }
    }
}