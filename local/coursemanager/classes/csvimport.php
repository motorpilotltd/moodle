<?php
// This file is part of the register plugin for Moodle
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
 * @package     local_coursemanager
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/csvlib.class.php');
use moodle_exception;
use html_writer;
use stdClass;
use DateTimeZone;

class csvimport {

    protected $courseid;
    protected $reset;
    protected $cir;
    protected $errors = array();
    protected $processstarted = false;
    protected $filecolumns = array(
        "staffid",
        "classname",
        "classcompletiondate",
        "classtype",
        "classcategory",
        "duration",
        "location",
        "provider",
        "durationunits",
        "certificateno",
        "expirydate",
        "classstarttime",
        "healthandsafetycategory",
        "classcost",
        "classcostcurrency",
        "learningdesc");
    protected $requiredcolumns = array(
        "staffid",
        "classname",
        "location",
        "classcompletiondate",
        "provider",
        "duration",
        "durationunits",
        );
    private $filter;
    private $taps;
    private $useridnumbers = array();

    /**
     * Constructor
     *
     * @param csv_import_reader $cir import reader object
     * @param array $options options of the process
     * @param array $defaults default data value
     */
    public function __construct(\csv_import_reader $cir) {
        $this->cir = $cir;
        $this->columns = $cir->get_columns();
        $this->validate();
        $this->reset();
        $this->taps = new \local_taps\taps();
    }

    /**
     * Execute the process.
     *
     * @param object $tracker the output tracker to use.
     * @return void
     */
    public function execute($tracker = null) {
        if ($this->processstarted) {
            throw new coding_exception('Process has already been started');
        }
        $this->processstarted = true;

        if (empty($tracker)) {
            $tracker = new \local_coursemanager\csvimporttracker();
        }
        $tracker->start();

        $categorytotal = 0;
        $itemstotal = 0;

        // We will most certainly need extra time and memory to process big files.
        @set_time_limit(0);
        raise_memory_limit(MEMORY_EXTRA);

        $existingcategories = array();

        // Loop over the CSV lines.
        $dataset = array();
        $count = 0;
        while ($line = $this->cir->next()) {
            $count++;
            if ($count == 1) {
                continue;
            }
            $status = '';
            if ($data = $this->parse_line($line)) {
                $this->linenb++;
                $missing = $this->check_required_fields($data);
                if (!empty($missing)) {
                    $missingfields = implode(', ', $missing);
                    $status = $this->mkerr('statusmissingfields', $missingfields);
                    $tracker->output($this->linenb, false, $status, $data);
                    continue;
                }
                $status = $this->validate_data($data);
                if (!empty($status)) {
                    $tracker->output($this->linenb, false, $status, $data);
                    continue;
                }

                $dataset[] = $data;
                $itemstotal++;
                $tracker->output($this->linenb, true, $status, $data);
            }
        }
        $this->save_data($dataset);

        $tracker->finish();
    }

    /**
     * Save data from CSV
     */
    public function save_data($dataset) {
        $unitcodescodes = $this->taps->get_durationunitscode();
        $classcodes = $this->taps->get_classtypes('cpd');
        foreach ($dataset as $data) {
            $data['durationunits'] = array_search($data['durationunits'], $unitcodescodes);
            $data['classtype'] = array_search($data['classtype'], $classcodes);
            if (!empty($data['expirydate'])) {
                if ($expirydate = strtotime($data['expirydate'] . ' UTC')) {
                    $data['expirydate'] = usergetmidnight($expirydate , new DateTimeZone('UTC'));
                }
            }
            if (!empty($data['classcompletiondate'])) {
                if ($classcompletiondate = strtotime($data['classcompletiondate'] . ' UTC')) {
                    $data['classcompletiondate'] = usergetmidnight($classcompletiondate, new DateTimeZone('UTC'));
                }
            }
            $result = $this->taps->add_cpd_record(
                $data['staffid'],
                $data['classname'],
                $data['provider'],
                $data['classcompletiondate'],
                $data['duration'],
                $data['durationunits'],
                array(
                    'p_location' => $data['location'],
                    'p_learning_method' => $data['classtype'],
                    'p_subject_catetory' => $data['classcategory'],
                    'p_course_cost' => $data['classcost'],
                    'p_course_cost_currency' => $data['classcostcurrency'],
                    'p_certificate_number' => $data['certificateno'],
                    'p_certificate_expiry_date' => $data['expirydate'],
                    'p_learning_desc' => $data['learningdesc'],
                    'p_health_and_safety_category' => $data['healthandsafetycategory']
                )
            );
        }
    }


    /**
     * Return the errors.
     *
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Log errors on the current line.
     *
     * @param array $errors array of errors
     * @return void
     */
    protected function log_error($errors) {
        if (empty($errors)) {
            return;
        }

        foreach ($errors as $code => $langstring) {
            if (!isset($this->errors[$this->linenb])) {
                $this->errors[$this->linenb] = array();
            }
            $this->errors[$this->linenb][$code] = $langstring;
        }
    }

    /**
     * Parse a line to return an array(column => value)
     *
     * @param array $line returned by csv_import_reader
     * @return array
     */
    protected function parse_line($line) {
        $data = array();
        $valid = false;
        foreach ($line as $keynum => $value) {
            if (!isset($this->columns[$keynum])) {
                // This should not happen.
                continue;
            }
            if (!empty($value)) {
                $valid = true;
            }
            $key = $this->columns[$keynum];
            $data[$key] = $value;
        }
        if (!$valid) {
            return false;
        }
        foreach ($this->filecolumns as $col) {
            if (!isset($data[$col])) {
                $data[$col] = '';
            }
            if ($col === 'staffid') {
                $data[$col] = str_pad($data[$col], 6, '0', STR_PAD_LEFT);
            }
        }
        return $data;
    }

    /**
     * Return a preview of the import.
     *
     * This only returns passed data, along with the errors.
     *
     * @param integer $rows number of rows to preview.
     * @return array of preview data.
     */
    public function preview($rows = 10) {
        global $DB;

        echo html_writer::tag('h3', get_string('preview'));

        $tracker = new \local_coursemanager\csvimporttracker();

        if ($this->processstarted) {
            throw new coding_exception('Process has already been started');
        }

        $this->processstarted = true;

        // We might need extra time and memory depending on the number of rows to preview.
        @set_time_limit(0);
        raise_memory_limit(MEMORY_EXTRA);

        $tracker->start();
        // Loop over the CSV lines.
        $preview = array();
        $count = 0;
        while (($line = $this->cir->next())) {
            $count++;
            if ($count == 1) {
                continue;
            }
            if ($data = $this->parse_line($line)) {
                $this->linenb++;
                $result = true;
                $missing = $this->check_required_fields($data);
                $status = $this->validate_data($data);
                if (!empty($missing)) {
                    $missingfields = implode(', ', $missing);
                    $status['missing'] = $this->mkerr('statusmissingfields', $missingfields);
                }
                if (!empty($status)) {
                    $result = false;
                }
                // Stop showing lines that are okay after the number of preview rows has been reached.
                // Always show errors.
                if ( $rows <= $this->linenb && $result == true) {
                    continue;
                }
                $tracker->output($this->linenb, $result, $status, $data);
            }
        }
        $tracker->finish();
    }

    private function validate_data($data) {
        global $DB;
        $status = array();
        // Prevent having to do a query for each validation.
        if (empty($this->useridnumbers)) {
            $sql = "SELECT DISTINCT EMPLOYEE_NUMBER as staffid
                      FROM SQLHUB.ARUP_ALL_STAFF_V
                      WHERE EMPLOYEE_NUMBER IS NOT NULL";
            $useridnumbers = $DB->get_records_sql($sql);
            $this->useridnumbers = array_keys($useridnumbers);
            unset($useridnumbers);
        }

        // Check if the staffid exists in SQLHUB.ARUP_ALL_STAFF_V.
        if (!in_array((int) $data['staffid'], $this->useridnumbers)) {
            $status['staffid'] = $this->mkwarn('staffid');
        }
        // Check the duration units codes.
        if (!empty($data['durationunits'])) {
            $dcodes = $this->taps->get_durationunitscode();
            if (!in_array($data['durationunits'], $dcodes)) {
                $status['durationunits'] = $this->mkwarn('durationunits');
            }
        }
        // Check if the Learning Method (classtype) is known in Taps.
        if (!empty($data['classtype'])) {
            $lmethods = $this->taps->get_classtypes('cpd');
            if (!in_array($data['classtype'], $lmethods)) {
                $status['classtype'] = $this->mkwarn('classtype');
            }
        }
        // Check if the Subject Category (classcategory) is known in Taps.
        if (!empty($data['classcategory'])) {
            $ccategories = $this->taps->get_classcategory();
            if (!array_key_exists($data['classcategory'], $ccategories)) {
                $status['classcategory'] = $this->mkwarn('classcategory');
            }
        }
       // Check if the Health and Safety Category (healthandsafetycategory) is known in Taps.
        if (!empty($data['healthandsafetycategory'])) {
            $ccategories = $this->taps->get_healthandsafetycategory();
            if (!in_array($data['healthandsafetycategory'], $ccategories)) {
                $status['healthandsafetycategory'] = $this->mkwarn('healthandsafetycategory');
            }
        }
        // Check if Course cost is numeric.
        if (!empty($data['classcost'])) {
            if (!is_numeric($data['classcost'])) {
                $status['classcost'] = $this->mkwarn('classcost');
            }
        }
        // Check if the Currency is known in Taps.
        if (!empty($data['classcostcurrency'])) {
            $cccurrency = $this->taps->get_classcostcurrency();
            if (!array_key_exists($data['classcostcurrency'], $cccurrency)) {
                $status['classcostcurrency'] = $this->mkwarn('classcostcurrency');
            }
        }
        // Check if the Class start time uses a valid date string.
        if (!empty($data['classstarttime'])) {
            if (strtotime($data['classstarttime'] . ' UTC') === false) {
                $status['classstarttime'] = $this->mkwarn('classstarttime');
            }
        }
        // Check if the Class start time uses a valid date string.
        if (!empty($data['expirydate'])) {
            if (strtotime($data['expirydate'] . ' UTC') === false) {
                $status['expirydate'] = $this->mkwarn('expirydate');
            }
        }
        // Check if the Completion Date uses a valid date string.
        if (!empty($data['classcompletiondate'])) {
            if (strtotime($data['classcompletiondate'] . ' UTC') === false) {
                $status['classcompletiondate'] = $this->mkwarn('classcompletiondate');
            }
        }
        
        return $status;
    }

    private function mkwarn($string) {
        $string = get_string('cpd:error:' . $string, 'local_coursemanager');
        return html_writer::tag('span', $string, array('class' => 'label label-warning'));
    }
    private function mkerr($string, $vars) {
        $string = get_string('cpd:error:' . $string, 'local_coursemanager', $vars);
        return html_writer::tag('span', $string, array('class' => 'label label-danger'));
    }

    protected function check_required_fields($data) {
        $checkedfields = 0;
        $missing = $this->requiredcolumns;
        foreach ($data as $key => $field) {
            if (in_array($key, $this->requiredcolumns) && !empty($field)) {
                $checkedfields++;
                $missing = array_diff($missing, [$key]);
            }
        }
        return $missing;
    }

    /**
     * Reset the current process.
     *
     * @return void.
     */
    public function reset() {
        $this->processstarted = false;
        $this->linenb = 0;
        $this->cir->init();
        $this->errors = array();
    }

    /**
     * Validation.
     *
     * @return void
     */
    protected function validate() {
        foreach ($this->requiredcolumns as $requiredcolumn) {
            if (!in_array($requiredcolumn, $this->columns)) {
                throw new moodle_exception('csvloaderror', 'local_coursemanager');
            }
        }
        if (empty($this->columns)) {
            throw new moodle_exception('cannotreadtmpfile', 'error');
        } else if (count($this->columns) < 3) {
            throw new moodle_exception('csvfewcolumns', 'error');
        }
    }
}


