<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\printer;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use DateTimeZone;
use DateTime;

abstract class base implements renderable, templatable {
    /**
     * Printer class.
     * @var \local_onlineappraisal\printer $printer
     */
    protected $printer;
    /**
     * Appraisal record.
     * @var stdClass $appraisal
     */
    protected $appraisal;
    /**
     * Context for template.
     * @var stdClass $data
     */
    protected $data;

    /**
     * Constructor.
     * 
     * @param \local_onlineappraisal\printer $printer
     */
    public function __construct(\local_onlineappraisal\printer $printer) {
        $this->printer = $printer;
        $this->appraisal = $printer->appraisal->appraisal;

        $this->data = new stdClass();
    }

    /**
     * Provides data for pre-processing and export.
     */
    abstract protected function get_data();

    /**
     * Pre-process and export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        // Get deep clone of generic appraisal data.
        $this->data->appraisal = unserialize(serialize($this->appraisal));
        
        // Format dates.
        $this->data->appraisal->due_date = userdate($this->data->appraisal->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        $this->data->appraisal->held_date = empty($this->data->appraisal->held_date) ? get_string('pdf:notset', 'local_onlineappraisal') : userdate($this->data->appraisal->held_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        $this->data->appraisal->completed_date = empty($this->data->appraisal->completed_date) ? get_string('pdf:notcomplete', 'local_onlineappraisal') : userdate($this->data->appraisal->completed_date, get_string('strftimedate'));

        // Add data specific to renderer.
        $this->get_data();

        return $this->data;
    }

    /**
     * Inject learning history into data object.
     *
     * @global \moodle_database $DB
     * @return void
     */
    protected function get_learning_history() {
        global $DB;

        $this->data->learninghistory = array();

        $staffid = $this->appraisal->appraisee->idnumber;

        $mylearninginstalled = get_config('block_arup_mylearning', 'version');
        $tapsinstalled = get_config('local_taps', 'version');
        $arupadvertinstalled = get_config('arupadvertdatatype_taps', 'version');
        
        if (empty($staffid) || !$mylearninginstalled || !$tapsinstalled || !$arupadvertinstalled) {
            return;
        }

        $taps = new \local_taps\taps();

        list($usql, $params) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
        $sql = <<<EOS
SELECT
    lte.id, lte.classtype, lte.classname, lte.coursename, lte.classcategory, lte.classcompletiondate, lte.duration, lte.durationunits,
        lte.expirydate, lte.cpdid, lte.provider, lte.location, lte.classstartdate, lte.certificateno, lte.learningdesc,
        lte.learningdesccont1, lte.learningdesccont2, lte.healthandsafetycategory, lte.usedtimezone,
    ltcc.categoryhierarchy,
    a.course,
    cat.id as categoryid, cat.name as categoryname
FROM
    {local_taps_enrolment} lte
LEFT JOIN
    {local_taps_course_category} ltcc
    ON ltcc.courseid = lte.courseid
        AND {$DB->sql_compare_text('ltcc.primaryflag', 1)} = :primaryflag
LEFT JOIN
    {arupadvertdatatype_taps} at
    ON at.tapscourseid = lte.courseid
LEFT JOIN
    {arupadvert} a
    ON a.id = at.arupadvertid
LEFT JOIN
    {course} c
    ON c.id = a.course
LEFT JOIN
    {course_categories} cat
    ON cat.id = c.category
WHERE
    lte.staffid = :staffid
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND lte.classcompletiondate > :threeyearsago
    AND (
        {$DB->sql_compare_text('lte.bookingstatus')} {$usql}
        OR lte.bookingstatus IS NULL
    )
ORDER BY
    lte.classcompletiondate DESC
EOS;
        $params['staffid'] = $staffid;
        $params['primaryflag'] = 'Y';
        $params['threeyearsago'] = strtotime('3 years ago');

        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $timezone = new DateTimeZone($record->usedtimezone);

            $learninghistory = new stdClass();

            $learninghistory->course = format_string($record->coursename ? $record->coursename : $record->classname);
            $type = $taps->get_classtype_type($record->classtype);
            $typestr = "pdf:learninghistory:type:{$type}";
            if ($type && $type != 'cpd' && get_string_manager()->string_exists($typestr, 'local_onlineappraisal')) {
                $learninghistory->type = get_string($typestr, 'local_onlineappraisal');
            }
            if (!empty($record->course)) {
                $learninghistory->category = format_string($record->categoryname);
            } elseif (empty($record->classcategory)) {
                $categories = explode('->', $record->categoryhierarchy);
                $learninghistory->category = array_pop($categories);
            } else {
                $learninghistory->category = format_string($record->classcategory);
            }
            $learninghistory->duration = $record->duration ? (float) $record->duration . '&nbsp;' . $record->durationunits : '';
            if ($record->classcompletiondate) {
                $date = new DateTime(null, $timezone);
                $date->setTimestamp($record->classcompletiondate);
                $learninghistory->completiondate = $date->format('d M Y');
            }
            if ($record->expirydate) {
                $date = new DateTime(null, $timezone);
                $date->setTimestamp($record->expirydate);
                $learninghistory->expirydate = $date->format('d M Y');
            }

            $this->data->learninghistory[] = clone($learninghistory);
        }
        $this->data->haslearninghistory = (bool) count($this->data->learninghistory);
    }
}
