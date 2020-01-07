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

class elearningstatus extends base {

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
        global $DB;
        parent::__construct($report);
        $this->reportname = 'elearningstatus';
        $this->report = $report;
        $this->reportfields();

        $this->start = optional_param('start', 0, PARAM_INT);
        $this->limit = optional_param('limit', 100, PARAM_INT);
        $this->sort = optional_param('sort', '', PARAM_TEXT);
        $this->direction = optional_param('dir', 'ASC', PARAM_TEXT);
        $this->search = optional_param('search', '', PARAM_TEXT);
        $this->action = optional_param('action', '', PARAM_TEXT);
        $this->exportxlsurl = new moodle_url('/local/reports/export.php', array('page' => 'elearningstatus'));
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
            if ($filter->field == 'classname') {
                $filter->displayvalue = $DB->get_field('local_taps_class', 'classname', array('classid' => $filter->value[0]));
            }
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
            'classcategory',
            'classtype',
            'bookingplaceddate',
            'coursecode',
            'provider',
            'expirydate',
            'location',
            'region_name',
            'geo_region',
            'company_code',
            'centre_code',
            'courseregion');

        $this->sortfields = array(
            'classname');

        $this->specialfields = array(
            'cpd',
            'classstartdate',
            'classenddate',
            'bookingstatus',
            'coursename',
            'classcategory',
            'classcost',
            'classcostcurrency',
            'learningdesc');

        $this->textfilterfields = array(
            'actualregion' => 'dropdown',
            'georegion' => 'dropdown',
            'classname' => 'autocomplete',
            'exclusion' => 'dropdown',
            'location_name' => 'char',
            'staffid' => 'int',
            'costcentre' => 'costcentre',
            'groupname' => 'char',
            'leaver_flag' => 'yn',
            );

        $this->filtertodb = array(
            'classname' => 'lte.classid',
            'actualregion' => 'staff.REGION_NAME',
            'georegion' => 'staff.GEO_REGION',
            'staffid' => 'lte.staffid',
            'full_name' => 'staff.FULL_NAME',
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

    public function querydata($limited = true) {
        global $DB;

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        if (empty($this->sort)) {
            $this->sort = 'lte.staffid';
        }

        // Catching the booking status filter and unsetting it
        // Booking not okay can not be translated to a query
        // So instead we get all users and substract the bookingokay list from the
        // all users list.
        $exclusion = false;
        if (isset($this->setfilters['exclusion'])) {
            if ($this->setfilters['exclusion']->displayvalue == get_string('learninghistory:bookingnotokay', 'local_reports')) {
                $exclusion = true;
            }
        }
        unset($this->setfilters['exclusion']);
        // Values to store when generating an exclusion list
        $exclusionmissingfields = array ();
        if ($exclusion && isset($this->setfilters['classname'])) {
            $filter = $this->setfilters['classname'];
            $classinfo = $DB->get_record('local_taps_class', array('classid' => $filter->value[0]));

            $exclusionmissingfields = array (
                'coursename' => $classinfo->coursename,
                'classname' => $classinfo->classname,
                'classtype' => 'Self Paced',
                'classstartdate' => $classinfo->classstartdate,
                'classenddate' => $classinfo->classenddate,
                'classcost' => $classinfo->classcost,
                'classcostcurrency' => $classinfo->classcostcurrency,
                'bookingstatus' => 'No Enrolment',
                'bookingplaceddate' => '',
                'classcompletiondate' => ''
            );

        }

        $enrolmentswhere = "WHERE lte.classtype = 'Self Paced' ";
        $waitlisted = $this->taps->get_statuses('waitlisted');
        $placed = $this->taps->get_statuses('placed');
        $attended = $this->taps->get_statuses('attended');
        $statusok = "'" .
            implode("', '", $waitlisted) . "', '".
            implode("', '", $placed) . "', '".
            implode("', '", $attended) . "'";
        $enrolmentswhere .= " AND lte.bookingstatus in ($statusok) ";

        $wherestring = " AND ( lte.archived = '' OR lte.archived IS NULL ) ";
        $params = array();

        // Classnames are only used in the inclusion query.
        if (!isset($this->setfilters['classname'])) {
            $this->errors[] = 'Select one or more courses';
            return array();
        } else  {
            $filter = $this->setfilters['classname'];
            $numfilters = count($filter->value);
            $loopcount = 0;
            foreach ($filter->value as $filtervalue) {
                if ($loopcount == 0) {
                    $enrolmentswhere .= ' AND ';
                    if ($numfilters > 1) {
                        $enrolmentswhere .= '( ';
                    }
                } else {
                    $enrolmentswhere .= ' OR ';
                }
                $loopcount++;
                $fieldname = $this->filtertodb[$filter->field];
                $enrolmentswhere .= " $fieldname = $filtervalue ";
            }
            if ($numfilters > 1) {
                $enrolmentswhere .= ' ) ';
            }
        }
        // Unset this filter before it is added to the $wherestring
        unset($this->setfilters['classname']);

        if (!$this->showall) {
            $wherestring .= ' AND ';
            $wherestring .= $DB->sql_like('staff.LEAVER_FLAG', ':' . 'staffleaver_flag', false);
            $params['staffleaver_flag'] = 'n';
        }

        foreach ($this->setfilters as $filtername => $filter) {
            $loopcount = 0;
            $numfilters = count($filter->value);
            foreach ($filter->value as $filtervalue) {
                if ($loopcount == 0) {
                    $wherestring .= ' AND ';
                    if ($numfilters > 1) {
                        $wherestring .= ' ( ';
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

                if (in_array($filter->field, $this->numericfields)) {
                    $value = intval($filtervalue);
                    $wherestring .= " $filter->field = $value ";
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

        $sql = "SELECT lte.*, staff.*, ltc.classstatus, ltco.coursecode, ltco.courseregion
                  FROM {local_taps_enrolment} as lte
             LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V as staff
                    ON lte.staffid = staff.EMPLOYEE_NUMBER
             LEFT JOIN {local_taps_class} as ltc
                    ON ltc.classid = lte.classid
             LEFT JOIN {local_taps_course} as ltco
                    ON lte.courseid = ltco.courseid
                       $enrolmentswhere
                       $wherestring
              ORDER BY " . $this->sort . ' ' . $this->direction;


        // Leave out the joins for taps_class and taps_course to speed up this query
        $sqlcount = "SELECT count(lte.id) as recnum
                       FROM {local_taps_enrolment} as lte
                  LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V as staff
                         ON lte.staffid = staff.EMPLOYEE_NUMBER
                  LEFT JOIN {local_taps_class} as ltc
                         ON ltc.classid = lte.classid
                            $enrolmentswhere
                            $wherestring";

        //$DB->set_debug(1);
        $enrolments = array();
        $all = array();
        $this->errors = array();
        if ($exclusion) {
            $allstaff = [];
            try {
                $staffsql = "SELECT *, EMPLOYEE_NUMBER as staffid from SQLHUB.ARUP_ALL_STAFF_V as staff WHERE 1 = 1 $wherestring";
                $allstaff = $DB->get_records_sql($staffsql, $params);
            } catch (dml_read_exception $e) {
                $this->errors[] = $e;
            }
            try {
                $enrolments = $DB->get_records_sql($sql, $params);
            } catch (dml_read_exception $e) {
                $this->errors[] = $e;
            }
            // Unset allstaff records that have an enrolment.
            foreach ($enrolments as $enrolment) {
                if (array_key_exists(intval($enrolment->staffid), $allstaff)) {
                    unset($allstaff[intval($enrolment->staffid)]);
                }
            }

            // Add missing table info for staff.
            foreach($allstaff as &$as) {
                foreach ($exclusionmissingfields as $mk => $val) {
                    $as->$mk = $val;
                }
            }

            $this->numrecords = count($allstaff);
            if ($limited) {
                $chunks = array_chunk($allstaff, $this->limit);
                return $chunks[$this->start];
            } else {
                return $allstaff;
            }
        } else if ($limited) {
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

    public function get_csv_data() {
        global $CFG, $USER;
        require("$CFG->dirroot/lib/xsendfilelib.php");

        $returnfile = new stdClass();

        $filename = 'report_'.(time()).'.csv';
        $tempfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
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
            'filearea'  => 'elearningstatus',
            'itemid'    => $USER->id,
            'filepath'  => '/',
            'filename'  => $filename
        );
        $fs->create_file_from_pathname($record, $tempfile);
        unlink($tempfile);
        $returnfile->url = $CFG->wwwroot ."/pluginfile.php/" . $usercontext->id . "/local_reports/elearningstatus/" . $USER->id . "/" . $filename;
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
                $date = ($this->taps->is_status($row->bookingstatus, ['cancelled']) ? 0 : $row->classcompletiondate);
                return $this->myuserdate($date, $row);
            }
            if (!empty($row->cpdid)) {
                return $this->myuserdate($row->classcompletiondate, $row);
            }
            // Default
            return $this->myuserdate($row->$key, $row);
        }

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

        // Show classcategory for CPD records
        if ($key == 'classcategory') {
            if (!empty($row->cpdid)) {
                return $row->$key;
            } else {
                return '';
            }
        }

        if ($key == 'classcost') {
            if (isset($row->pricebasis) && $row->pricebasis == 'No Charge') {
                return '';
            } else if (!empty($row->price)) {
                return $row->price;
            } else {
                return $row->$key;
            }
        }

        if ($key == 'classcostcurrency') {
            if (isset($row->pricebasis) && $row->pricebasis == 'No Charge') {
                return '';
            } else if (!empty($row->price)) {
                return $row->currencycode;
            } else if (!empty($row->classcost)) {
                return $row->$key;
            } else {
                return '';
            }
        }

        if ($key == 'learningdesc' && isset($row->$key)) {
            return html_to_text($row->$key);
        }
    }

    public function get_dropdown($field) {
        global $DB;
        $options = array();
        if ($field == 'actualregion' || $field == 'georegion') {
            return $this->get_regions($field);
        }
        if ($field == 'cpd') {
            $options[''] = $this->mystr('cpdandlms');
            $options['cpd'] = $this->mystr('cpd');
            $options['lms'] = $this->mystr('lms');
            return $options;
        }
        if ($field == 'exclusion') {
            $options[''] = $this->mystr('bookingok');
            $options[$this->mystr('bookingnotokay')] = $this->mystr('bookingnotokay');
            return $options;
        }
        if ($field == 'classname') {
            $sql = "SELECT classid, classname from {local_taps_class} where classtype = ? ORDER BY classname ASC";
            $classes = $DB->get_records_sql($sql, array('Self Paced'));
            foreach ($classes as $class) {
                $options[$class->classid] = $class->classname;
            }
            return ['0' => ''] + $options;
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