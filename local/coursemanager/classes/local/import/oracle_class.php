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
 * The local_coursemanager\local Oracle class record object.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager\local\import;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * The local_coursemanager\local Oracle class record object class.
 */
class oracle_class extends oracle_record {
    /** @var array Moodle table. */
    public static $table = 'local_taps_class';
    
    /** @var array field mappings. */
    public static $fields = [
        'CLASS_ID' => 'classid',
        'CLASS_NAME' => 'classname',
        'COURSE_ID' => 'courseid',
        'COURSE_NAME' => 'coursename',
        'CLASS_TYPE' => 'classtype',
        'CLASS_STATUS' => 'classstatus',
        'CLASS_DURATION_UNITS' => 'classdurationunits',
        'CLASS_DURATION_IDUNITS' => 'classdurationunitscode',
        'CLASS_DURATION' => 'classduration',
        'CLASS_START_DATE' => 'classstartdate',
        'CLASS_END_DATE' => 'classenddate',
        'CLASS_EN_START_DATE' => 'enrolmentstartdate',
        'CLASS_EN_END_DATE' => 'enrolmentenddate',
        'CLASS_TRAINING_CENT' => 'trainingcenter',
        'CLASS_LOCATION' => 'location',
        'CLASS_START_TIME' => 'classstarttime',
        'CLASS_END_TIME' => 'classendtime',
        'CLASS_MINIMUM_ATTENDEES' => 'minimumattendees',
        'CLASS_MAXIMUM_ATTENDEES' => 'maximumattendees',
        'CLASS_MAX_INTERNAL_ATTENDEES' => 'maximuminternalattendees',
        'CLASS_PRICE_BASIS' => 'pricebasis',
        'CURRENCY_CODE' => 'currencycode',
        'STANDARD_PRICE' => 'price',
        'CLASS_PROJJOB_NUM' => 'jobnumber',
        'CLASS_OWNER_EMP_NUM' => 'classownerempno',
        'CLASS_OWNER_NAME' => 'classownerfullname',
        'CLASS_SPONSOR' => 'classsponsor',
        'CLASS_USER_STATUS' => 'classuserstatus',
        'CLASS_SUPPLIER_NAME' => 'classsuppliername',
        'CLASS_TIME_ZONE' => 'timezone',
        'CLASS_BUD_CURR_CODE' => 'classcostcurrency',
        'CLASS_ACTUAL_COST' => null, // Used to derive 'classcost' field.
        'CLASS_BUDGET_COST' => null, // Used to derive 'classcost' field.
        // Not in Oracle but derived locally or just need to be forced to null.
        'LOCAL_1' => 'classcost', // Derived from CLASS_ACTUAL_COST and CLASS_BUDGET_COST.
        'LOCAL_2' => 'seatsremaining',
        'LOCAL_3' => 'restrictedflag',
        'LOCAL_4' => 'secureflag',
        'LOCAL_5' => 'offeringstartdate',
        'LOCAL_6' => 'offeringenddate',
        'LOCAL_7' => 'learningpathonlyflag',
        'LOCAL_8' => 'usedtimezone',
        'LOCAL_9' => 'timemodified',
        'LOCAL_10' => 'archived',
        'LOCAL_11' => 'classhidden',
    ];

    /** @var array required conversions. */
    protected $conversions = [
        'classstartdate' => 'datetotimestampstart',
        'classenddate' => 'datetotimestampend',
        'enrolmentstartdate' => 'datetotimestampstart',
        'enrolmentenddate' => 'datetotimestampend',
    ];

    /**
     * Preprocess incoming data before conversion.
     *
     * @return void
     */
    protected function preprocessing() {
        $this->data['classcost'] = !empty($this->row['CLASS_ACTUAL_COST']) ? $this->row['CLASS_ACTUAL_COST'] : $this->row['CLASS_BUDGET_COST'];

        try {
            if (!isset($this->row['CLASS_TIME_ZONE'])) {
                throw new Exception();
            }
            $usedtimezone = new DateTimeZone($this->row['CLASS_TIME_ZONE']);
        } catch (Exception $e) {
            $usedtimezone = new DateTimeZone('UTC');
        }
        $this->data['usedtimezone'] = $usedtimezone->getName();
    }

    /**
     * Postprocess data after conversion.
     *
     * @return void
     */
    protected function postprocessing() {
        parent::postprocessing();
        
        $fields = [
            'classstarttime' => new stdClass(),
            'classendtime' => new stdClass()
        ];
        $fields['classstarttime']->in = 'CLASS_START_TIME';
        $fields['classstarttime']->inparent = 'CLASS_START_DATE';
        $fields['classstarttime']->outparent = 'classstartdate';
        $fields['classendtime']->in = 'CLASS_END_TIME';
        $fields['classendtime']->inparent = 'CLASS_END_DATE';
        $fields['classendtime']->outparent = 'classenddate';

        foreach ($fields as $out => $details) {
            if (!is_null($this->row[$details->in])) {
                $datetime = new DateTime($this->row[$details->inparent].' '.$this->row[$details->in], $this->timezone);
                $this->data[$out] = $datetime->getTimestamp();
            } else if (!is_null($this->data[$details->outparent])) {
                $this->data[$out] = $this->data[$details->outparent];
            } else {
                $this->data[$out] = 0;
            }
        }
    }

    /**
     * Returns query to get data from Oracle.
     *
     * @return string
     */
    public static function get_query() {
        $fields = array_keys(static::get_fields(true));
        $fieldselect = implode(', ', $fields);
        $query = <<<EOQ
SELECT
    DISTINCT {$fieldselect}
FROM
    ARUP_OLM_COMPLETE_DATA
WHERE
    CLASS_ID IS NOT NULL
ORDER BY
    COURSE_ID ASC, CLASS_ID ASC
EOQ;
        return $query;
    }
}