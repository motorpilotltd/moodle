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
 * The local_coursemanager\local Oracle CPD record object.
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
 * The local_coursemanager\local Oracle CPD record object class.
 */
class oracle_cpd extends oracle_record {
    /** @var array Moodle table. */
    public static $table = 'local_taps_enrolment';

    /** @var array Field mappings. */
    public static $fields = [
        'ENR_EMP_NUMBER' => 'staffid',
        'CPD_ID' => 'cpdid',
        'TRAINING_TITLE' => 'classname',
        'CENTRE' => 'location',
        'LEARNING_METHOD' => 'classtype',
        'SUBJECT_CATEGORY' => 'classcategory', // Needs mapping from short version to long version.
        'COURSE_START_DATE' => 'classstartdate', // Midnight on day (UTC).
        'DURATION' => 'duration',
        'CLASS_DURATION_UNITS' => 'durationunits',
        'CLASS_DUR_UNIT_CODE' => 'durationunitscode',
        'PROVIDER' => 'provider',
        'CERTIFICATE_NUMBER' => 'certificateno',
        'CERTIFICATE_EXPIRY_DATE' => 'expirydate',
        'EMP_PERSON_ID' => 'personid',
        'COMPLETION_DATE' => 'classcompletiondate', // Midnight on day (UTC).
        'HEALTH_SAFETY_SUBCAT' => 'healthandsafetycategory',
        'COURSE_COST' => 'classcost',
        'COURSE_COST_CUR' => 'classcostcurrency',
        'LEARNING_DESCRIPTION' => 'learningdesc',
        'DESCRIPTION1' => 'learningdesccont1',
        'DESCRIPTION2' => 'learningdesccont2',
        // Not in Oracle but derived locally or just need to be forced to null.
        'LOCAL_1' => 'enrolmentid',
        'LOCAL_2' => 'classid',
        'LOCAL_3' => 'courseid',
        'LOCAL_4' => 'coursename',
        'LOCAL_5' => 'classenddate', // Set to classstartdate.
        'LOCAL_6' => 'courseobjectives',
        'LOCAL_7' => 'bookingstatus',
        'LOCAL_8' => 'classstarttime', // Set to classstartdate.
        'LOCAL_9' => 'classendtime', // 23:59:59 on day of classenddate (UTC).
        'LOCAL_10' => 'classcompletiontime', // Set to classcompletiondate.
        'LOCAL_11' => 'timezone',
        'LOCAL_12' => 'usedtimezone',
        'LOCAL_13' => 'pricebasis',
        'LOCAL_14' => 'currencycode',
        'LOCAL_15' => 'price',
        'LOCAL_16' => 'trainingcenter',
        'LOCAL_17' => 'classcontext',
        'LOCAL_18' => 'bookingplaceddate',
        'LOCAL_19' => 'lastupdatedate',
        'LOCAL_20' => 'active',
        'LOCAL_21' => 'archived',
        'LOCAL_22' => 'timemodified',
    ];

    /** @var array Required conversions. */
    protected $conversions = [
        'staffid' => 'padstaffid',
        'classstartdate' => 'datetotimestampstart',
        'expirydate' => 'datetotimestampstart',
        'classcompletiondate' => 'datetotimestampstart',
    ];

    /** @var array Class categories (lifted from local/taps to avoid loading class). */
    private $categories = [
        'EMS' => 'Environmental Management System',
        'Engineers Australia CPD Area 1' => 'Engineers Australia CPD Area 1: Risk Management',
        'Engineers Australia CPD Area 2' => 'Engineers Australia CPD Area 2: Business and Management',
        'Engineers Australia CPD Area 3' => 'Engineers Australia CPD Area 3: Area of practise',
        'HS' => 'Health and Safety',
        'Mentorship Program' => 'Mentorship Program',
        'Other' => 'Other',
        'PD' => 'Professional Development',
        'PDC' => 'Professional Development (Certified)',
        'QMS' => 'Quality Management System',
        'RedR' => 'RedR',
        'Sustainability' => 'Sustainability'
    ];

    /**
     * Preprocess incoming data before conversion.
     *
     * @return void
     */
    protected function preprocessing() {
        $this->data['active'] = 1;
        
        $usedtimezone = new DateTimeZone('UTC');
        $this->data['usedtimezone'] = $usedtimezone->getName();
    }

    /**
     * Postprocess data after conversion.
     *
     * @return void
     */
    protected function postprocessing() {
        parent::postprocessing();
        
        $this->data['classstarttime'] = $this->data['classstartdate'];
        $this->data['classenddate'] = $this->data['classstartdate'];
        if ($this->data['classenddate'] > 0) {
            $this->data['classendtime'] = $this->data['classenddate'] + (23 * 60 * 60) + (59 * 60) + 59;
        } else {
            $this->data['classendtime'] = $this->data['classenddate'];
        }
        $this->data['classcompletiontime'] = $this->data['classcompletiondate'];

        // Map category short name to long name.
        $this->data['classcategory'] = isset($this->categories[$this->data['classcategory']]) ? $this->categories[$this->data['classcategory']] : $this->data['classcategory'];

        // Set default currency and null class cost if 0.
        if ($this->data['classcost'] == 0) {
            $this->data['classcost'] = null;
        } else if ($this->data['classcost'] > 0 && is_null($this->data['classcostcurrency'])) {
            // Set GBP as default currency.
            $this->data['classcostcurrency'] = 'GBP';
        }
    }

    /**
     * Returns query to get data from Oracle.
     *
     * @return string
     */
    public static function get_query() {
        //$s = oci_parse($c, "select * from ARUP_OLM_COMPLETE_CPD_DATA WHERE ENR_EMP_NUMBER = 30557");
        $fields = array_keys(static::get_fields(true));
        $fieldselect = implode(', ', $fields);
        $query = <<<EOQ
SELECT
    DISTINCT {$fieldselect}
FROM
    ARUP_OLM_COMPLETE_CPD_DATA
WHERE
    CPD_ID IS NOT NULL
ORDER BY
    CPD_ID ASC
EOQ;
        return $query;
    }
}