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

use local_linkedinlearning\exceptions\LilApiBackingOffException;
use local_linkedinlearning\exceptions\LilApiGenericException;
use local_linkedinlearning\exceptions\LilApiRateLimitException;

class api {

    /**
     * @param $start
     * @return mixed
     */
    public function getcourses($start, $since = 0) {
        raise_memory_limit(MEMORY_HUGE);
        $url = "https://api.linkedin.com/v2/learningAssets?";
        $params = [
                'q'                                     => 'criteria',
                'assetFilteringCriteria.assetTypes[0]'  => 'COURSE',
                'assetRetrievalCriteria.includeRetired' => 'true',
                'count'                                 => '100',
                'start'                                 => $start,
                'fields'                                => 'urn,title,details:(availability,classifications,publishedAt,lastUpdatedAt,images:(primary),descriptionIncludingHtml,shortDescriptionIncludingHtml,timeToComplete,urls:(aiccLaunch,ssoLaunch))',
        ];

        $languages = [
                'de',
                'en',
                'es',
                'fr',
                'ja',
                'pt',
                'zh',
        ];

        for ($i = 0; $i < count($languages); $i++) {
            $params["assetFilteringCriteria.locales[$i].language"] = $languages[$i];
        }

        if ($since !== 0) {
            $params['assetFilteringCriteria.lastModifiedAfter'] = $since * 1000;
        }

        $processedparams = [];
        foreach ($params as $key => $value) {
            $processedparams[] = "$key=$value";
        }
        $processedparams = implode('&', $processedparams);

        $results = $this->callapi($url . $processedparams);

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function getcourseprogress($start, $since) {
        raise_memory_limit(MEMORY_HUGE);

        $url = "https://api.linkedin.com/v2/learningActivityReports?";
        $params = [
                'q'                             => 'criteria',
                'count'                         => '500',
                'timeOffset.unit'               => 'WEEK',
                'timeOffset.duration'           => '2',
                'aggregationCriteria.primary'   => 'INDIVIDUAL',
                'aggregationCriteria.secondary' => 'CONTENT',
                'assetType'                     => 'COURSE',
                'start'                         => $start,
                'startedAt'                     => $since * 1000
        ];

        $processedparams = [];
        foreach ($params as $key => $value) {
            $processedparams[] = "$key=$value";
        }
        $processedparams = implode('&', $processedparams);

        $results = $this->callapi($url . $processedparams);

        if (!$results) {
            return false;
        }

        return $results;
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

        if ($curl->info['http_code'] != 200) {
            mtrace("Error fetching token: \n" . print_r($curl->get_raw_response(), true));
            return false;
        }

        $response = json_decode($result);

        if (empty($response)) {
            mtrace("Error fetching token: Can't parse JSON");
            return false;
        }

        $this->tokenexpirytime = time() + $response->expires_in;
        $this->token = $response->access_token;

        return $this->token;
    }

    private function callapi($url) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');

        $token = $this->getaccesstoken();

        if (!$token) {
            return false;
        }

        $backoffuntil = get_config('local_linkedinlearning', 'backoffuntil');
        $now = time();
        if (!empty($backoffuntil) && $backoffuntil > $now) {
            if ($DB->is_transaction_started()) {
                $DB->force_transaction_rollback();
            }

            mtrace("Backing off from API until " . userdate($backoffuntil));
            throw new LilApiBackingOffException();
        }

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
        if ($curl->info['http_code'] == 429) { // Rate limit.
            mtrace("Hit rate limit: $url");

            if ($DB->is_transaction_started()) {
                $DB->force_transaction_rollback();
            }

            $ex = new LilApiRateLimitException($url);

            $midnightutc = date_create( date('Y-m-d'), timezone_open( 'UTC' ) )->add(new \DateInterval('P1D'))->getTimestamp();
            set_config('backoffuntil', $midnightutc, 'local_linkedinlearning'); // Rate limit resets at midnight

            $event = \local_linkedinlearning\event\api_ratelimit::create(array(
                    'other' => array(
                            'url' => $url
                    )
            ));
            $event->trigger();

            throw $ex;
        }

        if ($curl->info['http_code'] != 200) {
            mtrace("Error calling web service: \n" . print_r($curl->get_raw_response(), true));

            if ($DB->is_transaction_started()) {
                $DB->force_transaction_rollback();
            }

            $result = json_decode($result);

            if (isset($result) && isset($result->serviceErrorCode)) {
                mtrace("Error calling web service: \n" . print_r($result, true));
            }

            $ex = new LilApiGenericException($url);
            $event = \local_linkedinlearning\event\api_error::create(array(
                    'other' => array(
                            'exception' => $ex->getMessage()
                    )
            ));
            $event->trigger();

            throw $ex;
        }

        return json_decode($result);
    }

    public function synccourses($since) {
        global $DB;

        $classifications = classification::fetch_all_key_on_urn([]);

        foreach (new courseiterator($this, $since) as $raw) {
            $course = course::fetchbyurn($raw->urn);
            if (empty($course)) {
                $course = new course();
            }

            $course->urn = $raw->urn;
            $course->language = $raw->title->locale->language;
            $course->title = $raw->title->value;
            $course->primaryimageurl = isset($raw->details->images->primary) ? $raw->details->images->primary : '';

            if ($raw->details->availability == 'AVAILABLE') {
                $course->aicclaunchurl = $raw->details->urls->aiccLaunch;
                $course->ssolaunchurl = $raw->details->urls->ssoLaunch;
            } else {
                $course->aicclaunchurl = '';
                $course->ssolaunchurl = '';
            }
            $course->publishedat = $raw->details->publishedAt / 1000;
            $course->lastupdatedat = $raw->details->lastUpdatedAt / 1000;
            $course->description = isset($raw->details->descriptionIncludingHtml->value) ? $raw->details->descriptionIncludingHtml->value : '';
            $course->shortdescription = isset($raw->details->shortDescriptionIncludingHtml->value) ? $raw->details->shortDescriptionIncludingHtml->value : '';
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
                    $classification->language = $rawclassification->associatedClassification->name->locale->language;
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

    public function synccourseprogress($since) {
        global $DB;
        $courseprogressiterator = new courseprogressiterator($this, $since);

        $transaction = $DB->start_delegated_transaction();
        try {
            foreach ($courseprogressiterator as $raw) {
                $params = ['urn'   => $raw->contentDetails->contentUrn];

                $idparamsql = [];
                $idparams = [
                        'userurn' => !empty($raw->learnerDetails->entity->profileUrn) ? $raw->learnerDetails->entity->profileUrn:null,
                        'uniqueuserid' => !empty($raw->learnerDetails->uniqueUserId) ? $raw->learnerDetails->uniqueUserId:null,
                        'email' => !empty($raw->learnerDetails->email) ? $raw->learnerDetails->email:null,
                ];
                foreach ($idparams as $fieldname => $value) {
                    if ($value == null) {
                        continue;
                    }
                    $params[$fieldname] = $value;
                    $idparamsql[] = "$fieldname = :$fieldname";
                }

                if (count($idparamsql) == 0) {
                    throw new LilApiGenericException('Non user identifiable record received from API');
                }

                $idparamsql = implode(' OR ', $idparamsql);

                $sql = "SELECT * FROM {linkedinlearning_progress} WHERE urn = :urn AND ($idparamsql)";

                $records = $DB->get_records_sql($sql, $params);

                if (empty($records)) {
                    $record = (object) $params;
                    $record->seconds_viewed = 0;
                    $record->userid = 0;
                } else if (count($records) === 1) {
                    $record = reset($records);
                } else if (count($records) > 1) {
                    $record = $this->deduperecords($records);
                }

                foreach ($idparams as $fieldname => $value) {
                    if ($value == null) {
                        continue;
                    }
                    $record->$fieldname = $value;
                }

                foreach ($raw->activities as $datapoint) {
                    if ($datapoint->engagementType == 'SECONDS_VIEWED') {
                        $record->seconds_viewed += $datapoint->engagementValue;

                        $first_viewed = $datapoint->firstEngagedAt / 1000;

                        if (empty($record->first_viewed) || $record->first_viewed > $first_viewed) {
                            $record->first_viewed = $first_viewed;
                        }

                        $last_viewed = $datapoint->lastEngagedAt / 1000;
                        if (empty($record->last_viewed) || $record->last_viewed < $last_viewed) {
                            $record->last_viewed = $last_viewed;
                        }
                    } else if (
                            $datapoint->engagementType == 'PROGRESS_PERCENTAGE'
                            &&
                            (empty($record->progress_percentage) || $record->progress_percentage < $datapoint->engagementValue)
                    ) {
                        $record->progress_percentage = $datapoint->engagementValue;
                    }
                }

                if (!isset($record->id)) {
                    $DB->insert_record('linkedinlearning_progress', $record);
                } else {
                    $DB->update_record('linkedinlearning_progress', $record);
                }
            }

        } catch (LilApiRateLimitException | LilApiBackingOffException $ex) {
            return false;
        }

        $this->populatemoodleuserid();

        $transaction->allow_commit();
        return true;
    }

    /**
     * @param array $records
     * @param \moodle_database $DB
     * @return array
     * @throws \dml_exception
     */
    public function deduperecords(array $records) {
        global $DB;

        $record = array_pop($records);

        foreach ($records as $mergerecord) {
            if (empty($record->first_viewed) || $record->first_viewed > $mergerecord->first_viewed) {
                $record->first_viewed = $mergerecord->first_viewed;
            }
            if (empty($record->last_viewed) || $record->last_viewed < $mergerecord->last_viewed) {
                $record->last_viewed = $mergerecord->last_viewed;
            }
            if (empty($record->progress_percentage) || $record->progress_percentage < $mergerecord->progress_percentage) {
                $record->progress_percentage = $mergerecord->progress_percentage;
            }

            $record->seconds_viewed += $mergerecord->seconds_viewed;

            $DB->delete_records('linkedinlearning_progress', ['id' => $mergerecord->id]);
        }
        return $record;
    }

    public function populatemoodleuserid() {
        global $DB;

        $DB->execute("
        update {linkedinlearning_progress}
        set userid = coalesce((select id from {user} where idnumber is not null and idnumber <> '' and idnumber = uniqueuserid), 0)
        where userid = 0");

        $DB->execute("
        update {linkedinlearning_progress}
        set userid = coalesce((select id from {user} where {user}.email = {linkedinlearning_progress}.email), 0)
        where userid = 0");
    }
}