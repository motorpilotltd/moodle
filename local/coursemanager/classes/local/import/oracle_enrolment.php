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
 * The local_coursemanager\local Oracle enrolment record object.
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
 * The local_coursemanager\local Oracle enrolment record object class.
 */
class oracle_enrolment extends oracle_record {
    /** @var array Moodle table. */
    public static $table = 'local_taps_enrolment';

    /** @var array field mappings. */
    public static $fields = [
        'ENR_EMP_NUMBER' => 'staffid',
        'ENR_ENROLLMENT_ID' => 'enrolmentid',
        'CLASS_ID' => 'classid',
        'CLASS_NAME' => 'classname',
        'COURSE_ID' => 'courseid',
        'COURSE_NAME' => 'coursename',
        'CLASS_LOCATION' => 'location',
        'CLASS_TYPE' => 'classtype',
        'CLASS_START_DATE' => 'classstartdate',
        'CLASS_END_DATE' => 'classenddate',
        'CLASS_DURATION' => 'duration',
        'CLASS_DURATION_UNITS' => 'durationunits',
        'CLASS_DURATION_IDUNITS' => 'durationunitscode',
        'COURSE_OBJECTIVES' => 'courseobjectives',
        'CLASS_SUPPLIER_NAME' => 'provider',
        'ENR_HSCERT_NO' => 'certificateno',
        'ENR_HSEXPIRY_DATE' => 'expirydate',
        'ENR_ENROLLMENT_STATUS' => 'bookingstatus',
        'CLASS_START_TIME' => 'classstarttime',
        'CLASS_END_TIME' => 'classendtime',
        'CLASS_COMPLETION_DATE' => 'classcompletiondate',
        'CLASS_BUD_CURR_CODE' => 'classcostcurrency',
        'CLASS_TIME_ZONE' => 'timezone',
        'CLASS_PRICE_BASIS' => 'pricebasis',
        'CURRENCY_CODE' => 'currencycode',
        'STANDARD_PRICE' => 'price',
        'CLASS_TRAINING_CENT' => 'trainingcenter',
        'CLASS_CONTEXT' => 'classcontext',
        'ENR_DATE_BOOK_PLACED' => 'bookingplaceddate',
        'ENR_LAST_UPDATE_DATE' => 'lastupdatedate',
        'CLASS_ACTUAL_COST' => null, // Used to derive 'classcost' field.
        'CLASS_BUDGET_COST' => null, // Used to derive 'classcost' field.
        // Not in Oracle but derived locally or just need to be forced to null.
        'LOCAL_1' => 'cpdid',
        'LOCAL_2' => 'classcategory', // Use CLASS_CONTEXT.
        'LOCAL_3' => 'personid',
        'LOCAL_4' => 'classcompletiontime',
        'LOCAL_5' => 'healthandsafetycategory',
        'LOCAL_6' => 'classcost', // Derived from CLASS_ACTUAL_COST and CLASS_BUDGET_COST.
        'LOCAL_7' => 'learningdesc',
        'LOCAL_8' => 'learningdesccont1',
        'LOCAL_9' => 'learningdesccont2',
        'LOCAL_10' => 'usedtimezone',
        'LOCAL_11' => 'active',
        'LOCAL_12' => 'archived',
        'LOCAL_13' => 'timemodified',
    ];

    /** @var array required conversions. */
    protected $conversions = [
        'staffid' => 'padstaffid',
        'classstartdate' => 'datetotimestampstart',
        'classenddate' => 'datetotimestampend',
        'expirydate' => 'datetimetotimestamp',
        'classcompletiondate' => 'datetotimestampstart',
        'bookingplaceddate' => 'datetotimestampstart',
        'lastupdatedate' => 'datetotimestampstart',
    ];

    /**
     * Preprocess incoming data before conversion.
     *
     * @return void
     */
    protected function preprocessing() {
        $this->data['classcost'] = !empty($this->row['CLASS_ACTUAL_COST']) ? $this->row['CLASS_ACTUAL_COST'] : $this->row['CLASS_BUDGET_COST'];
        $this->data['classcategory'] = $this->row['CLASS_CONTEXT'];
        $this->data['active'] = 1;
        
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
            'classendtime' => new stdClass(),
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

        // Force completion time to same as date.
        if (!is_null($this->row['CLASS_COMPLETION_DATE'])) {
            $datetime = new DateTime($this->row['CLASS_COMPLETION_DATE'].' 00:00:00', $this->timezone);
            $this->data['classcompletiontime'] = $datetime->getTimestamp();
        } else {
            $this->data['classcompletiontime'] = 0;
        }

        // Set default currency and null class cost if 0.
        if ($this->data['classcost'] == 0) {
            $this->data['classcost'] = null;
        } else if ($this->data['classcost'] > 0 && is_null($this->data['classcostcurrency'])) {
            // Set GBP as default currency.
            $this->data['classcostcurrency'] = 'GBP';
        }
        // Same for price.
        if ($this->data['price'] == 0) {
            $this->data['price'] = null;
        } else if ($this->data['price'] > 0 && is_null($this->data['currencycode'])) {
            // Set GBP as default currency.
            $this->data['currencycode'] = 'GBP';
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
    ENR_ENROLLMENT_ID IS NOT NULL
ORDER BY
    ENR_ENROLLMENT_ID ASC
EOQ;
        return $query;
    }
}