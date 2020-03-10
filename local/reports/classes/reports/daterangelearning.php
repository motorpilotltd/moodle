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

use mod_tapsenrol\enrolclass;
use stdClass;
use moodle_url;
use renderer_base;
use html_writer;
use dml_read_exception;

class daterangelearning extends base {

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
        parent::__construct($report);
        $this->reportname = 'daterangelearning';
        $this->report = $report;
        $this->reportfields();

        $this->start = optional_param('start', 0, PARAM_INT);
        $this->limit = optional_param('limit', 100, PARAM_INT);
        $this->sort = optional_param('sort', '', PARAM_TEXT);
        $this->direction = optional_param('dir', 'ASC', PARAM_TEXT);
        $this->search = optional_param('search', '', PARAM_TEXT);
        $this->action = optional_param('action', '', PARAM_TEXT);
        $this->visiblesearchfields = 3;
        $this->exportxlsurl = new moodle_url('/local/reports/export.php', array('page' => 'daterangelearning'));
        $this->showall = false;
        $this->taps = new \mod_tapsenrol\taps();

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
            'idnumber',
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
            'jobnumber',
            'classtype',
            'coursecode',
            'classsuppliername',
            'location',
            'region_name',
            'geo_region',
            'company_code',
            'centre_code',
            'timemodified',
            'completiontime',
            'courseregion');

        $this->sortfields = array(
            'classname');

        $this->specialfields = array(
            'classstartdate',
            'classenddate',
            'bookingstatus',
            'coursename',
            'completiontime',
            'classcost',
            'classcostcurrency');

        $this->textfilterfields = array(
            'startdate' => 'date',
            'enddate' => 'date',
            'bookingstatus' => 'dropdownmulti',
            'actualregion' => 'dropdown',
            'georegion' => 'dropdown',
            'coursename' => 'char',
            'classname' => 'char',
            'location_name' => 'char',
            'idnumber' => 'int',
            'costcentre' => 'costcentre',
            'groupname' => 'char',
            'leaver_flag' => 'yn'
            );

        $this->filtertodb = array(
            'actualregion' => 'staff.REGION_NAME',
            'georegion' => 'staff.GEO_REGION',
            'idnumber' => 'u.idnumber',
            'classname' => 'ltc.classname',
            'coursename' => 'c.fullname',
            'classsuppliername' => 'ltc.classsuppliername',
            'location' => 'ltc.location',
            'location_name' => 'staff.LOCATION_NAME',
            'groupname' => 'staff.GROUP_NAME',
            'leaver_flag' => 'staff.LEAVER_FLAG',
            'company_code' => 'staff.COMPANY_CODE',
            'centre_code' => 'staff.CENTRE_CODE',
            'timemodified' => 'lte.timemodified',
            'completiontime' => 'lte.completiontime',
            'classenddate' => 'ltc.classenddate',
            'classtype' => 'ltc.classtype',
            'bookingstatus' => 'lte.bookingstatus'
        );

        $this->datefields = array(
            'classstartdate',
            'classenddate',
            'bookingplaceddate',
            'completiontime',
            'startdate',
            'timemodified',
            'enddate'
            );

        $this->numericfields = array(
            'idnumber',
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
            $this->sort = 'staff.EMPLOYEE_NUMBER';
        }

        $params = array();
        $wherestring = " WHERE ( lte.archived = '' OR lte.archived IS NULL ) ";

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
                if (in_array($filter->field, $this->datefields)) {

                    $completiontime = $this->filtertodb['completiontime'];
                    $classenddate = $this->filtertodb['classenddate'];
                    $classtype = $this->filtertodb['classtype'];

                    if ($filter->field == 'startdate') {

                        $startdate = $filtervalue;

                        $end = isset($this->setfilters['enddate']) ? $this->setfilters['enddate'] : null ;
                        $classenddatestring = '';
                        $classcompletiondendstring = '';
                        if (!empty($end)) {
                            $enddate = $end->value[0];
                            $classenddatestring = " AND {$classenddate} <= $enddate";
                            $classcompletiondendstring = " AND completiontime <= $enddate";
                        }
                        $params['elearning'] = enrolclass::TYPE_ELEARNING;
                        $params['classroom'] = enrolclass::TYPE_CLASSROOM;
                        $wherestring .= "
                            (
                                (
                                    $classtype = :classroom
                                    AND
                                    ($classenddate >= $startdate" . $classenddatestring . ")
                                )
                                OR
                                (
                                    $classtype = :elearning
                                    AND
                                    ($completiontime >= $startdate" . $classcompletiondendstring . ")
                                )
                            )";
                    } else {
                        $wherestring .= "1 = 1";
                    }

                } else if (in_array($filter->field, $this->numericfields)) {
                    $value = intval($filtervalue);
                    $wherestring .= " $filter->field = $value ";
                } else if ($filtervalue == 'NOT SET') {
                    // Looking for NULL or empty.
                    $fieldname = $this->filtertodb[$filter->field];
                    $wherestring .= "({$fieldname} IS NULL OR {$fieldname} = '')";
                } else if ($filter->field == 'bookingstatus') {
                    // Bookingstatusus are configured in TAPS
                    $wherestring .=  '(';
                    $fieldname = $this->filtertodb[$filter->field];
                    $statuses = $this->taps->get_statuses($filtervalue);
                    $fieldnameparam = strtolower(str_replace('.', '', $fieldname));
                    if (!empty($statuses)) {
                        list($in, $inparams) = $DB->get_in_or_equal(
                            $statuses,
                            SQL_PARAMS_NAMED, 'status'
                        );
                        $compare = $DB->sql_compare_text($fieldname);
                        $wherestring .= "{$compare} {$in}";
                        $params = array_merge($params, $inparams);
                    }

                    if ($filtervalue == 'attended') {
                        $wherestring .= ' OR (
                            lte.id IS NOT NULL
                            AND
                            lte.bookingstatus IS NULL
                        )';
                    }
                    $wherestring .=  ' )';
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

        $remotetagidconcat = \local_mssql\dbshim::sql_group_concat('reg.name', ',', true);

        $sql = "SELECT 
                  lte.*, 
                  staff.*, 
                  ltc.classstatus,ltc.classduration as duration, ltc.classdurationunits as durationunits, ltc.classstartdate, ltc.classenddate, ltc.classtype, ltc.classcost, ltc.classcostcurrency, ltc.pricebasis, ltc.classname, ltc.jobnumber, ltc.classsuppliername, ltc.location,  
                  c.shortname as coursecode, c.fullname as coursename, 
                  u.idnumber, r.regions as courseregion
                  FROM {tapsenrol_class_enrolments} as lte
                  INNER JOIN {user} u ON lte.userid = u.id
                  INNER JOIN SQLHUB.ARUP_ALL_STAFF_V as staff
                    ON u.idnumber = staff.EMPLOYEE_NUMBER
             INNER JOIN {local_taps_class} as ltc
                    ON ltc.id = lte.classid
             INNER JOIN {course} as c
                    ON ltc.courseid = c.id
             LEFT JOIN (
                SELECT regcou.courseid, $remotetagidconcat as regions 
                FROM {local_regions_reg_cou} regcou
                INNER JOIN {local_regions_reg} reg ON reg.id = regcou.regionid
                GROUP BY regcou.courseid
             ) AS r ON r.courseid = c.id
                       $wherestring
              ORDER BY " . $this->sort . ' ' . $this->direction;

        // Leave out the joins for taps_class and taps_course to speed up this query
        $sqlcount = "SELECT count(lte.id) as recnum
                  FROM {tapsenrol_class_enrolments} as lte
                 INNER JOIN {local_taps_class} as ltc
                        ON ltc.id = lte.classid
                  INNER JOIN {user} u ON lte.userid = u.id
                  INNER JOIN SQLHUB.ARUP_ALL_STAFF_V as staff
                    ON u.idnumber = staff.EMPLOYEE_NUMBER
                       $wherestring";

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
                $this->numrecords = $DB->count_records('tapsenrol_class_enrolments');
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
            'filearea'  => 'daterangelearning',
            'itemid'    => $USER->id,
            'filepath'  => '/',
            'filename'  => $filename
        );
        $fs->create_file_from_pathname($record, $tempfile);
        unlink($tempfile);
        $returnfile->url = $CFG->wwwroot ."/pluginfile.php/" . $usercontext->id . "/local_reports/daterangelearning/" . $USER->id . "/" . $filename;
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

        if ($key == 'coursename') {
            return $row->coursename;
        }

        if ($key == 'classstartdate') {
            return $this->myuserdate($row->$key, $row);
        }

        if ($key == 'classenddate') {
            if ($row->classtype == enrolclass::TYPE_ELEARNING) {
                $date = ($this->taps->is_status($row->bookingstatus, ['cancelled']) ? 0 : $row->completiontime);
                return $this->myuserdate($date, $row);
            }
            // Default
            return $this->myuserdate($row->$key, $row);
        }

        if ($key == 'bookingstatus') {
            return $row->bookingstatus;
        }

        if ($key == 'classtype') {
            if ($row->classtype == enrolclass::TYPE_CLASSROOM) {
                return $this->mystr('classroom');
            } else if ($row->classtype == enrolclass::TYPE_ELEARNING) {
                return $this->mystr('elearning');
            }
        }

        if ($key == 'completiontime') {
            return $this->myuserdate($row->$key, $row);
        }

        if ($key == 'classcost') {
            if (!empty($row->price)) {
                return $row->price;
            } else {
                return $row->$key;
            }
        }

        if ($key == 'classcostcurrency') {
            if ($row->pricebasis == 'No Charge') {
                return '';
            } else if (!empty($row->price)) {
                return $row->currencycode;
            } else if (!empty($row->classcost)) {
                return $row->$key;
            } else {
                return '';
            }
        }
    }

    public function get_dropdown($field) {
        if ($field == 'actualregion' || $field == 'georegion') {
            return $this->get_regions($field);
        }

        if ($field == 'bookingstatus') {
            $options = array();
            $options['requested'] = $this->mystr('requested');
            $options['waitlisted'] = $this->mystr('waitlisted');
            $options['placed'] = $this->mystr('placed');
            $options['attended'] = $this->mystr('attended');
            $options['cancelled'] = $this->mystr('cancelled');
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