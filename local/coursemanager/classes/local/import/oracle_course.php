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
 * The local_coursemanager\local Oracle course record object.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager\local\import;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_coursemanager\local Oracle course record object class.
 */
class oracle_course extends oracle_record {
    /** @var array Moodle table. */
    public static $table = 'local_taps_course';

    /** @var array field mappings. */
    public static $fields = [
        'COURSE_ID' => 'courseid',
        'COURSE_CODE' => 'coursecode',
        'COURSE_NAME' => 'coursename',
        'COURSE_START_DATE' => 'startdate',
        'COURSE_END_DATE' => 'enddate',
        'COURSE_DESCRIPTION' => 'coursedescription',
        'COURSE_OBJECTIVES' => 'courseobjectives',
        'COURSE_AUDIENCE' => 'courseaudience',
        'COURSE_GLB_LEAR_STDS' => 'globallearningstandards',
        'COURSE_ONE_LINE_DESC' => 'onelinedescription',
        'COURSE_BUSINESS_NEED' => 'businessneed',
        'COURSE_ACC_GIVEN_DATE' => 'accreditationgivendate',
        'COURSE_KEYWORDS' => 'keywords',
        'COURSE_DURATION' => 'duration',
        'COURSE_DURATION_UNITS' => 'durationunits',
        'COURSE_DURATION_IDUNITS' => 'durationunitscode',
        'COURSE_SPONSOR' => 'sponsorname',
        'COURSE_ADMINISTRATOR_EMPNO' => 'courseadminempno',
        'COURSE_ADMINISTRATOR' => 'courseadminempname',
        'COURSE_MAXIMUM_ATTENDEES' => 'maximumattendees',
        'COURSE_MINIMUM_ATTENDEES' => 'minimumattendees',
        'COURSE_FURTHER_REV_DATE' => 'futurereviewdate',
        'COURSE_PROJJOB_NUM' => 'jobnumber',
        // Not in Oracle but derived locally or just need to be forced to null.
        'LOCAL_1' => 'courseregion',
        'LOCAL_2' => 'tapsurllink',
        'LOCAL_3' => 'activecourse',
        'LOCAL_4' => 'usedtimezone',
        'LOCAL_5' => 'timemodified',
        'LOCAL_6' => 'archived',
    ];

    /** @var array required conversions. */
    protected $conversions = [
        'startdate' => 'datetotimestampstart',
        'enddate' => 'datetotimestampend',
        'accreditationgivendate' => 'datetotimestampstart',
        'futurereviewdate' => 'datetotimestampstart'
    ];

    /**
     * Preprocess incoming data before conversion.
     *
     * @return void
     */
    protected function preprocessing() {
        // Force timezone.
        $this->data['usedtimezone'] = 'UTC';

        // Determine owner region.
        $regions = [
            'Europe' => 'Europe Courses',
            'UKMEA' => 'Europe & UK-MEA',
            'Americas' => 'Americas',
            'Australasia' => 'Australasia',
            'East Asia' => 'East Asia',
            'Global' => '', // Anything left mop up as global.
        ];
        foreach ($regions as $region => $needle) {
            if ($needle === '' || stripos($this->row['COURSE_PRIM_CAT'], $needle) !== false) {
                $this->data['courseregion'] = $region;
                break;
            }
        }
    }

    /**
     * Returns query to get data from Oracle.
     *
     * @return string
     */
    public static function get_query() {
        $fields = array_keys(static::get_fields(true, false));
        $fieldselect = 'A.' . implode(', A.', $fields);
        $query = <<<EOQ
SELECT
    DISTINCT {$fieldselect}, B.COURSE_PRIM_CAT
FROM
    ARUP_OLM_COMPLETE_DATA A
JOIN
    ARUP_OLM_COURSE_PRIM_CAT B
    ON A.COURSE_ID = B.COURSE_ID
WHERE
    A.COURSE_ID IS NOT NULL
GROUP BY
    {$fieldselect}, B.COURSE_PRIM_CAT
ORDER BY
    A.COURSE_ID ASC
EOQ;
        return $query;
    }
}