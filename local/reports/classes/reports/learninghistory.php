<?php
// This file is part of the Arup Reports system
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
 *
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports\reports;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;
use html_writer;
use dml_read_exception;

class learninghistory {

    private $report;

    public $displayfields;
    public $sortfields;
    public $sort;
    public $start;
    public $search;
    public $hassearch;
    public $setfilters;
    public $showfilters;
    public $direction;
    public $limit;
    public $textsearch;
    public $table;
    public $numrecords;
    public $exportxlsurl;
    public $searchform;
    public $showall;
    public $reportname;
    public $errors;

    private $strings;

    public function __construct(\local_reports\report $report) {
        $this->reportname = 'learninghistory';
        $this->report = $report;
        $this->reportfields();

        $this->start = optional_param('start', 0, PARAM_INT);
        $this->limit = optional_param('limit', 100, PARAM_INT);
        $this->sort = optional_param('sort', '', PARAM_TEXT);
        $this->direction = optional_param('dir', 'ASC', PARAM_TEXT);
        $this->search = optional_param('search', '', PARAM_TEXT);
        $this->action = optional_param('action', '', PARAM_TEXT);
        $this->exportxlsurl = new moodle_url('/local/reports/export.php', array('page' => 'learninghistory'));
        $this->showall = false;

        if (!empty($this->action)) {
            $this->action($this->action);
        }

        if (!empty($this->search)) {
            $this->currentsearchkey = $this->search;
            $this->currentsearch = $this->mystr($this->search);
        }
        
        $this->get_filters();
        $this->set_filter();
        // Fix array for usage in mustache.
        foreach ($this->setfilters as $filter) {
            $this->showfilters[] = $filter;
        }
    }

    private function reportfields() {
        $this->displayfields = array(
            'staffid',
            'first_name',
            'last_name',
            'email_address',
            'grade',
            'employment_category',
            'discipline_name',
            'group_name',
            'companycentrearupunit',
            'location_name',
            'classname',
            'coursename',
            'classstatus',
            'classstartdate', 
            'classenddate',
            'duration',
            'durationunits',
            'bookingstatus',
            'classcost',
            'classcostcurrency',
            'cpd',  
            'learningdesc',
            'classtype',
            'bookingplaceddate',
            'coursecode',
            'provider',
            'expirydate',
            'location',
            'region_name',
            'geo_region',
            'company_code',
            'centre_code');

        $this->sortfields = array(
            'classname');

        $this->specialfields = array(
            'cpd',
            'classstartdate',
            'classenddate',
            'bookingstatus',
            'coursename');

        $this->textfilterfields = array(
            'actualregion' => 'dropdown',
            'georegion' => 'dropdown',
            'cpd' => 'dropdown',
            'coursename' => 'char',
            'classname' => 'char',
            'location_name' => 'char',
            'staffid' => 'int',
            'costcentre' => 'costcentre',
            'groupname' => 'char',
            'leaver_flag' => 'yn',
            );

        $this->filtertodb = array(
            'actualregion' => 'staff.REGION_NAME',
            'georegion' => 'staff.GEO_REGION',
            'staffid' => 'lte.staffid',
            'classname' => 'lte.classname',
            'coursename' => 'lte.coursename',
            'provider' => 'lte.provider',
            'location' => 'lte.location',
            'location_name' => 'staff.LOCATION_NAME',
            'groupname' => 'staff.GROUP_NAME',
            'leaver_flag' => 'staff.LEAVER_FLAG',
            'company_code' => 'staff.COMPANY_CODE',
            'centre_code' => 'staff.CENTRE_CODE');

        $this->datefields = array(
            'classstartdate',
            'classenddate',
            'bookingplaceddate',
            'expirydate',
            'classcompletiondate'
            );

        $this->numericfields = array(
            'staffid',
            'company_code',
            'centre_code');
    }

    private function action($action) {
        switch ($action) {
            case 'removefilter':
                $this->removefilter();
                break;
        }
    }

    private function removefilter() {
        global $SESSION;
        $filter = optional_param('filter', '', PARAM_TEXT);
        if (!empty($filter)) {
            if (array_key_exists($filter, $this->textfilterfields)) {

                $sessionfilters = unserialize($SESSION->lhfilters);
                if (is_array($sessionfilters)) {
                    if (array_key_exists($filter, $sessionfilters)) {
                        unset($sessionfilters[$filter]);
                    }
                }
                $SESSION->lhfilters = serialize($sessionfilters);
            }
        }
    }

    /**
     * Get the filters in use and set the available filter options.
     */
    private function get_filters() {
        global $SESSION;

        $used = array();
        $this->setfilters = array();
        // Get the set filters from session
        if (isset($SESSION->lhfilters)) {
            $hassessiondata = true;
            $filters = unserialize($SESSION->lhfilters);
            if (is_array($filters)) {
                foreach ($filters as $key => $value) {
                    if (!array_key_exists($key, $this->textfilterfields)) {
                        continue;
                    }
                    $used[] = $key;
                    $this->make_filter($key, $value);
                    $this->hassearch = true;
                }
            }
        }
        if (isset($SESSION->showall)) {
            $this->showall = true;
        }

        // Get the available filter options for usage in search form.
        $this->filteroptions = array();
        foreach ($this->textfilterfields as $key => $type) {
            $this->filteroptions[] = $this->make_filter($key, 'none');
        }

        $this->searchform = new \local_reports\searchform(null, $this);
        // Are we doing a text search?
        $this->textsearch = true;
        
    }

    /**
     * Add a new filter for this report. This function is used when getting and setting
     * filters stored in our session. 
     *
     * @param string $key the field to filter on
     * @param string $value the contents of the filter
     * @return object $filter if value = none, else void and sets $this->setfilters
     * and $this->showfilters (for mustache)
     */
    private function make_filter($key, $value) {
        $filter = new stdClass();
        $filter->field = $key;
        $filter->name = $this->mystr($key);
        $filter->type = $this->textfilterfields[$key];
        // $value will be array when make_filter is called from
        // function get_filters
        if (is_array($value)) {
            $filter->displayvalue = implode(", ", $value);
            $filter->value = $value;
        } else {
            $filter->value = array($value);
        }
        $params = array('action' => 'removefilter', 'filter' => $key);
        $filter->urlremove = new moodle_url($this->report->baseurl, $params);
        $params= array('report' => 'learninghistory', 'search' => $key);
        $filter->url = new moodle_url($this->report->baseurl, $params);
        // We can fill setfilters when make_filter is called from
        // function get_filters, these filters were stored in session
        if (is_array($value)) {
            $this->setfilters[$key] = $filter;
        } elseif ($value == 'none') {
            return $filter;
        } else {
            // If filters are added from form we need to combine
            // the filter->values into an array.
            // If the filter was not set yet it we can just add the filter to setfilters
            if (!array_key_exists($key, $this->setfilters)) {
                $filter->displayvalue = $value;
                $this->setfilters[$key] = $filter;
            } else {
                $oldfilter = $this->setfilters[$key];
                if (!in_array($value, $oldfilter->value)) {
                    array_push($oldfilter->value, $value);
                    $filter->value = $oldfilter->value;
                    $filter->displayvalue = implode(", ", $filter->value);
                    $this->setfilters[$key] = $filter;
                }
            }
        }
    }

    /**
     * Set a filter
     */
    private function set_filter() {
        global $SESSION;

        // Get new search fields from form.
        $data = false;
        if ($this->searchform->is_submitted() && ($data = $this->searchform->get_data())) {
            // Learver flags will not be shown in the filter list
            if ($data->leaver_flag == 0) {
                $SESSION->showall = true;
                $this->showall = true;
            } else if ($data->leaver_flag == 1) {
                $this->showall = false;
                unset($SESSION->showall);
            }
            unset($data->leaver_flag);

            foreach ($data as $search => $value) {
                if (array_key_exists($search, $this->textfilterfields) && !empty($value)) {
                    $this->make_filter($search, $value);
                } 
            }
        }

        // Check if search for validated okay, if not it needs to be displayed without collapse
        $this->searchformokay = true;
        if ($this->searchform->is_submitted() && !$this->searchform->is_validated()) {
            $this->searchformokay = false;
        } 
        $this->searchformhtml = $this->report->get_form_html($this->searchform);

        // Add all setfilters to sessions
        $sfilters = array();
        foreach ($this->setfilters as $filter) {
            $sfilters[$filter->field] = $filter->value;
        }
        $this->hassearch = true;
        $SESSION->lhfilters = serialize($sfilters);
        
    }

    public function querydata($limited = true) {
        global $DB;

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        if (empty($this->sort)) {
            $this->sort = 'lte.staffid';
        }

        $params = array();
        $wherestring = '';

        $wherestring .= "WHERE ( lte.archived = '' OR lte.archived IS NULL )";
        if (!$this->showall) {
            $wherestring .= ' AND ';
            $wherestring .= $DB->sql_like('staff.LEAVER_FLAG', ':' . 'staffleaver_flag', false);
            $params['staffleaver_flag'] = 'n';
        }

        foreach ($this->setfilters as $filter) {
            $loopcount = 0;
            $numfilters = count($filter->value);
            foreach ($filter->value as $filtervalue) {
                if ($loopcount == 0) {
                    if (empty($wherestring)) {
                        $wherestring = ' WHERE ';
                    } else {
                        $wherestring .= ' AND ';
                    }
                    if ($numfilters > 1) {
                        $wherestring .= '( ';
                    }
                } else {
                    $wherestring .= ' OR ';
                }
                $loopcount++;
                if ($filter->type == 'costcentre') {
                    $splitval = explode("-", $filtervalue);
                    if (count($splitval) == 2) {
                        $wherestring .= ' ( ' . $this->filtertodb['company_code'] . ' = ' . $splitval[0];
                        $wherestring .= ' AND ' . $this->filtertodb['centre_code'] . ' = \'' . $splitval[1] . '\' ) ';
                    } else {
                        if (strlen($filtervalue) == 2) {
                            $wherestring .= $this->filtertodb['company_code'] . ' = ' . $filtervalue;
                        } else if (strlen($filtervalue) == 3) {
                            $wherestring .= $this->filtertodb['centre_code'] . ' = \'' . $filtervalue . '\'';
                        }
                    }
                    continue;
                }
                if ($filter->field == 'cpd') {
                    if ($filtervalue == 'cpd') {
                        $wherestring .= "cpdid > ''";
                    } else if ($filtervalue == 'lms') {
                        $wherestring .= "( cpdid = '' OR cpdid IS NULL )";
                    }
                    continue;
                }
                if (in_array($filter->field, $this->datefields)) {
                    $minoneday = $filtervalue - (60 * 60 * 24);
                    $plusoneday = $filtervalue + (60 * 60 * 24);
                    $wherestring = " $filter->field > $minoneday AND $filter->field < $plusoneday ";
                } else if (in_array($filter->field, $this->numericfields)) { 
                    $value = intval($filtervalue);
                    $wherestring .= " $filter->field = $value ";
                } else if ($filtervalue == 'NOT SET') {
                    // Looking for NULL or empty.
                    $fieldname = $this->filtertodb[$filter->field];
                    $wherestring .= "({$fieldname} IS NULL OR {$fieldname} = '')";
                } else {
                    $fieldname = $this->filtertodb[$filter->field];
                    $fieldnameparam = strtolower(str_replace('.', '', $fieldname));
                    $wherestring .= $DB->sql_like($fieldname, ':' . $fieldnameparam . $loopcount, false);
                    $params[$fieldnameparam . $loopcount] = '%' . $filtervalue . '%';
                }
                $first = false;
            }
            if ($numfilters > 1) {
                $wherestring .= ' ) ';
            }
        }

        // echo $wherestring;
        // $wherestring = '';

        $sql = "SELECT lte.*, staff.*, ltc.classstatus, ltco.coursecode
                  FROM {local_taps_enrolment} as lte
                  JOIN SQLHUB.ARUP_ALL_STAFF_V as staff 
                    ON lte.staffid = staff.EMPLOYEE_NUMBER
             LEFT JOIN {local_taps_class} as ltc 
                    ON ltc.classid = lte.classid 
             LEFT JOIN {local_taps_course} as ltco 
                    ON lte.courseid = ltco.courseid
                       $wherestring
              ORDER BY " . $this->sort . ' ' . $this->direction;

        // Leave out the joins for taps_class and taps_course to speed up this query
        $sqlcount = "SELECT count(lte.id) as recnum
                  FROM {local_taps_enrolment} as lte
                  JOIN SQLHUB.ARUP_ALL_STAFF_V as staff 
                    ON lte.staffid = staff.EMPLOYEE_NUMBER
                       $wherestring";

        //$DB->set_debug(1);
        $enrolments = array();
        $all = array();
        $this->errors = array();
        if ($limited) {
            if (!empty($wherestring)) {
                try {
                    $all = $DB->get_record_sql($sqlcount, $params);
                } catch (dml_read_exception $e) {
                    $this->errors[] = $e;
                }
                if (!$all) {
                    $this->numrecords = 0;
                } else {
                    $this->numrecords = $all->recnum;
                }
            } else {
                $this->numrecords = $DB->count_records('local_taps_enrolment');
            }
            // For the UI.
             try {
                $enrolments = $DB->get_records_sql($sql, $params, $this->start * $this->limit, $this->limit);
            } catch (dml_read_exception $e) {
                $this->errors[] = $e;
            }
            //$DB->set_debug(0);
            return $enrolments;
        } else {
            // For the Export.
            try {
                $enrolments = $DB->get_records_sql($sql, $params);
            } catch (dml_read_exception $e) {
                $this->errors[] = $e;
            }
            return $enrolments;
        }
        
    }

    public function get_table_data() {
        $rawdbdata = $this->querydata();
        $table = new stdClass();
        $table->heading = array();
        $table->rows = array();

        foreach ($this->displayfields as $key) {
            $th = new stdClass();
            $th->name = $this->mystr($key);
            $th->key = $key;
            $th->nosort = true;
            $table->heading[] = $th;
        }
        foreach ($rawdbdata as $rdbd) {
            $row = new stdClass();
            $row->values = array();
            foreach ($this->displayfields as $key) {
                $cell = new stdClass();
                $cell->data = $key;
                if (in_array($key, $this->specialfields)) {
                    $cell->value = $this->specialfield($key, $rdbd);
                } else {
                    if (isset($rdbd->$key)) {
                        $cell->value = $rdbd->$key;
                        if (in_array($key, $this->datefields)) {
                            $cell->value = $this->myuserdate($cell->value, $rdbd);
                        }
                    } else {
                        $cell->value = '';
                    }
                }
                $row->values[] = $cell;
            }

            $table->rows[] = $row;
        }
        $this->table = $table;
    }

    public function get_xls_data() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

        $matrix = array();
        $filename = 'report_'.(time()).'.xls';
        $rawdbdata = $this->querydata(false);

        $downloadfilename = clean_filename($filename);
        // Creating a workbook.
        $workbook = new \MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($downloadfilename);
        // Adding the worksheet.
        $myxls = $workbook->add_worksheet($filename);

        $head = 0;
        foreach ($this->displayfields as $key) {
            $myxls->write_string(0, $head, $this->mystr($key));
            $head++;
        }

        $row = 1;
        foreach ($rawdbdata as $rdbd) {
            $cell = 0;
            foreach ($this->displayfields as $key) {
                if (in_array($key, $this->specialfields)) {
                    $cell->value = $this->specialfield($key, $rdbd);
                } else {
                    if (isset($rdbd->$key)) {
                        if (in_array($key, $this->datefields)) {
                            if (empty($rdbd->$key)) {
                                $myxls->write_string($row, $cell, $this->mystr('notset'));
                            } else {
                                $myxls->write_date($row, $cell, $rdbd->$key, array('num_format'=>15));
                            }
                        } else {
                            $myxls->write_string($row, $cell, $rdbd->$key);
                        }
                    } else {
                        $myxls->write_string($row, $cell, '');
                    }
                }
                $cell++;
            }
            $row++;
        }
        $workbook->close();
        exit;
    }

    public function get_csv_data() {
        global $CFG, $USER;
        require("$CFG->dirroot/lib/xsendfilelib.php");

        $returnfile = new stdClass();

        $filename = 'report_'.(time()).'.csv';
        $tempfile = $CFG->dataroot . '/temp/' . $filename;
        $returnfile->filename = $filename;
        $returnfile->tempfile = $tempfile;
        $delimiter = get_config('local_reports', 'csvseparator');
        $enclosure = '"';
        $fh = fopen($tempfile, 'w');

        $header = array();
        foreach ($this->displayfields as $key) {
            $header[] =  $this->mystr($key);
        }
        fputcsv($fh, $header, $delimiter, $enclosure);
        $rawdbdata = $this->querydata(false);

        foreach ($rawdbdata as &$rdbd) {
            $row = array();
            foreach ($this->displayfields as $key) {
                if (in_array($key, $this->specialfields)) {
                    $row[] = $this->specialfield($key, $rdbd);
                } else {
                    if (isset($rdbd->$key)) {
                        if (in_array($key, $this->datefields)) {
                            $row[] = $this->myuserdate($rdbd->$key, $rdbd);
                        } else {
                            $row[] = $rdbd->$key;
                        }
                    } else {
                        $row[] = '';
                    }
                }
            }
            fputcsv($fh, $row, $delimiter, $enclosure);
        }
        fclose($fh);

        $fs = get_file_storage();
        $usercontext = \context_user::instance($USER->id);
        $record = array(
            'contextid' =>  $usercontext->id,
            'component' => 'local_reports',
            'filearea'  => 'learninghistory',
            'itemid'    => $USER->id,
            'filepath'  => '/',
            'filename'  => $filename
        );
        $fs->create_file_from_pathname($record, $tempfile);
        unlink($tempfile);
        $returnfile->url = $CFG->wwwroot ."/pluginfile.php/" . $usercontext->id . "/local_reports/learninghistory/" . $USER->id . "/" . $filename;
        return $returnfile;
    }


    /**
     * Get the human readable date for a record in a row
     * @param $timestamp timestamp in db
     * @param $row row containing the timezone
     * @return human readable time
     */
    private function myuserdate($timestamp, $row) {
        if (empty($timestamp)) {
            return $this->mystr('notset');
        }
        if (empty($row->usedtimezone)) {
            return userdate($timestamp, get_string('strftimedate'), 'UTC');
        } else {
            return userdate($timestamp, get_string('strftimedate'), $row->usedtimezone);
        }
    }

    /**
     * get the language string from language file
     * @param $string simple string without learninghistory: prefix
     * @param $vars string variables
     * @return language string
     */
    private function mystr($string, $vars =  null) {
        if (empty($string)) {
            return 'nostring';
        }
        return get_string('learninghistory:' . $string, 'local_reports', $vars);
    }

    /**
     * Get values for fields that are not really in the DB results and are derived from
     * some other values
     *
     * @param $key
     * @param $row
     * @return value
     */
    private function specialfield($key, $row) {

        // Show classname in coursename column for CPD records
        if ($key == 'coursename') {
            if (!empty($row->cpdid)) {
                return $row->classname;
            } else {
                return $row->coursename;
            }
        }

        // Display rows with a cpdid as a CPD records, others as a LMS record
        if ($key == 'cpd') {
            if (!empty($row->cpdid)) {
                return $this->mystr('cpd');
            } else {
                return $this->mystr('lms');
            }
        }


        if ($key == 'classstartdate') {
            // e-Learning records use bookingplaceddate instead of classstartdate
            if ($row->classtype == 'Self Paced') {
                return $this->myuserdate($row->bookingplaceddate, $row);
            }
            // Default
            return $this->myuserdate($row->$key, $row);
        }

        if ($key == 'classenddate') {
            // CPD records use classcompletiondate instead of classenddate
            if ($row->classtype == 'Self Paced') {
                return $this->myuserdate($row->classcompletiondate, $row);
            }
            if (!empty($row->cpdid)) {
                return $this->myuserdate($row->classcompletiondate, $row);
            }
            // Default
            return $this->myuserdate($row->$key, $row);
        }

        // Always show Full Attendance on in the Booking Status column when there is a cpdid.
        if ($key == 'bookingstatus') {
            if (!empty($row->cpdid)) {
                return 'Full Attendance';
            } else {
                return $row->bookingstatus;
            }
        }

        if ($key == 'classtype') {
            if ($row->$key == 'Scheduled') {
                return $this->mystr('classroom');
            } else if ($row->$key == 'Self Paced') {
                return $this->mystr('elearning');
            } else {
                return $row->$key;
            }
        }

    }

    public function get_dropdown($field) {
        if ($field == 'actualregion' || $field == 'georegion') {
            return $this->get_regions($field);
        }
        if ($field == 'cpd') {
            $options = array();
            $options[''] = $this->mystr('cpdandlms');
            $options['cpd'] = $this->mystr('cpd');
            $options['lms'] = $this->mystr('lms');
            return $options;
        }
    }

    /**
     * Get regions from DB
     */
    function get_regions($infield) {
        global $DB;
        $dbfield = ($infield == 'actualregion') ? 'REGION_NAME' : 'GEO_REGION';
        $sql = "SELECT DISTINCT {$dbfield} as id, {$dbfield} as value FROM SQLHUB.ARUP_ALL_STAFF_V WHERE {$dbfield} IS NOT NULL AND {$dbfield} != '' ORDER BY {$dbfield} ASC";
        $regions = ['0' => get_string('allregions', 'local_reports')] + $DB->get_records_sql_menu($sql) + ['NOT SET' => 'NOT SET'];
        return $regions;
    }
}