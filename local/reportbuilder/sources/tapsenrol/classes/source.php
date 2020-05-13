<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package mod_assign
 */

namespace rbsource_tapsenrol;

use rb_base_source;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_content_option;
use rb_column;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {

    /**
     * Overwrite instance type value of totara_visibility_where() in rb_source_certification->post_config().
     */
    protected $instancetype = 'certification';

    public function __construct() {
        $this->base = '{local_taps_enrolment}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->contentoptions = $this->define_contentoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_tapsenrol');
        list($this->sourcewhere, $this->sourceparams) = $this->define_sourcewhere();

        $this->taps = new \local_taps\taps();

        parent::__construct();
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();

        $requiredcolumns[] = new rb_column(
                'auser',
                'employeenumber',
                '',
                "auserstaff.EMPLOYEE_NUMBER",
                array('joins' => 'auserstaff')
        );

        return $requiredcolumns;
    }

    /**
     * Define some extra SQL for the base to limit the data set.
     *
     * @return array The SQL and parmeters that defines the WHERE for the source.
     */
    protected function define_sourcewhere() {
        global $DB;
        $sql = '(base.archived = 0 or base.archived is null)';
        
        // Additional filterting for durationhours column
        $reportid = optional_param('id', null, PARAM_INT);
        $filter = array('reportid' => $reportid, 'value' => 'durationhours');
        $durationhours = $DB->get_record('report_builder_columns', $filter);
        $dh_aggregate = (!empty($durationhours) && !empty($durationhours->aggregate))? $durationhours->aggregate: ''; 
        
        $sql .= ($dh_aggregate == 'sum')? " AND base.durationunitscode = 'H'": '';
        
        return array("($sql)", []);
    }


    protected function define_columnoptions() {
        global $DB;

        $columnoptions = [];
        // Include some standard columns, override parent so they say certification.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);

        $classfields = ['classname', 'classtype', 'location'];

        foreach ($classfields as $stafffield) {
            $displayfunc = ($stafffield == 'classtype')? 'classtype': 'plaintext';
            $columnoptions[] = new rb_column_option(
                    'class',
                    "$stafffield",
                    get_string($stafffield, 'local_reportbuilder'),
                    "base.$stafffield",
                    array(
                            'displayfunc'  => $displayfunc,
                            'dbdatatype'   => 'char',
                            'outputformat' => 'text')
            );
        }
        $columnoptions[] = new rb_column_option(
                'class',
                "coursename",
                get_string('classcoursename', 'local_reportbuilder'),
                "coalesce(base.coursename, base.classname)",
                array(
                        'extrafields' => array('classcoursename' => 'base.classname'))
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'classenddate',
                get_string('classenddate', 'local_reportbuilder'),
                "base.classenddate",
                array(
                        'dbdatatype'  => 'timestamp',
                        'displayfunc' => 'classenddate',
                        'extrafields' => [
                                'usedtimezone'        => 'base.usedtimezone',
                                'classtype'           => 'base.classtype',
                                'bookingstatus'       => 'base.bookingstatus',
                                'classcompletiondate' => 'base.classcompletiondate',
                                'cpdid'               => 'base.cpdid'
                        ]
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'classstartdate',
                get_string('classstartdate', 'local_reportbuilder'),
                "base.classstartdate",
                array(
                        'dbdatatype'  => 'timestamp',
                        'displayfunc' => 'classstartdate',
                        'extrafields' => [
                                'classtype'         => 'base.classtype',
                                'usedtimezone'      => 'base.usedtimezone',
                                'bookingplaceddate' => 'base.bookingplaceddate'
                        ]
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'classcompletiondate',
                get_string('classcompletiondate', 'rbsource_tapsenrol'),
                "base.classcompletiondate",
                array(
                        'dbdatatype'  => 'timestamp',
                        'displayfunc' => 'classcompletiondate',
                        'extrafields' => [
                                'usedtimezone' => 'base.usedtimezone',
                        ]
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'classduration',
                get_string('classduration', 'local_reportbuilder'),
                $DB->sql_concat('base.duration', "' '", 'base.durationunits'),
                array(
                        'displayfunc'  => 'plaintext',
                        'dbdatatype'   => 'char',
                        'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'classcost',
                get_string('classcost', 'local_reportbuilder'),
                $DB->sql_concat('base.classcost', "' '", 'base.classcostcurrency'),
                array(
                        'displayfunc'  => 'plaintext',
                        'dbdatatype'   => 'char',
                        'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'classprice',
                get_string('classprice', 'rbsource_tapsenrol'),
                $DB->sql_concat('base.price', "' '", 'base.currencycode'),
                array(
                        'displayfunc'  => 'plaintext',
                        'dbdatatype'   => 'char',
                        'outputformat' => 'text')
        );

        $enrolmentfields = ['learningdesc', 'classcategory', 'provider'];

        foreach ($enrolmentfields as $enrolmentfield) {
            $columnoptions[] = new rb_column_option(
                    'class',
                    "$enrolmentfield",
                    get_string($enrolmentfield, 'local_reportbuilder'),
                    "base.$enrolmentfield",
                    array(
                            'displayfunc'  => 'plaintext',
                            'dbdatatype'   => 'char',
                            'outputformat' => 'text')
            );
        }
        $columnoptions[] = new rb_column_option(
                'class',
                "bookingstatus",
                get_string('bookingstatus', 'local_reportbuilder'),
                "base.bookingstatus",
                array(
                        'displayfunc'  => 'bookingstatus',
                        'dbdatatype'   => 'char',
                        'outputformat' => 'text',
                        'extrafields'  => array('cpdid' => 'base.cpdid')
                )
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'cpd',
                get_string('cpdorlms', 'rbsource_tapsenrol'),
                "base.cpdid",
                array(
                        'displayfunc' => 'cpdorlms',
                        'dbdatatype'  => 'boolean',
                )
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'actions',
                get_string('actions', 'rbsource_tapsenrol'),
                "base.id",
                array(
                        'displayfunc' => 'actions',
                        'extrafields' => array('cpdid' => 'base.cpdid', 'locked' => 'base.locked')
                )
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'cpdorlms',
                get_string('cpdorlmsbool', 'rbsource_tapsenrol'),
                "CASE WHEN base.cpdid = '' OR base.cpdid is null THEN 0 ELSE 1 END",
                array(
                        'dbdatatype' => 'boolean',
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'bookingplaceddate',
                get_string('bookingplaceddate', 'local_reportbuilder'),
                "base.bookingplaceddate",
                array(
                        'displayfunc' => 'nice_datetime',
                        'dbdatatype'  => 'timestamp'
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'expirydate',
                get_string('expirydate', 'local_reportbuilder'),
                "base.expirydate",
                array(
                        'displayfunc' => 'nice_datetime',
                        'dbdatatype'  => 'timestamp'
                )
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'coursecode',
                get_string('tapscoursecode', 'local_reportbuilder'),
                'tapscourse.coursecode',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'courseregion',
                get_string('tapscourseregion', 'local_reportbuilder'),
                'tapscourse.courseregion',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'tapscoursename',
                get_string('tapscoursename', 'local_reportbuilder'),
                'tapscourse.coursename',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'durationhours',
                get_string('durationhours', 'local_reportbuilder'),
                'base.duration',
                array(
                        'displayfunc'  => 'durationhours',
                        'dbdatatype'  => 'decimal',
                        'outputformat' => 'text',
                        'extrafields' => [
                                'durationunitscode' => 'base.durationunitscode',
                        ]
                )
        );

        return $columnoptions;
    }

    protected function define_joinlist() {
        $joinlist = [];

        $joinlist[] = new rb_join(
                'tapscourse',
                'LEFT',
                '{local_taps_course}',
                "base.courseid = tapscourse.courseid",
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        $this->add_user_table_to_joinlist_on_idnumber($joinlist, 'base', 'staffid');

        return $joinlist;
    }

    protected function define_contentoptions() {
        $contentoptions = [
                new rb_content_option(
                        'bookingstatus',
                        get_string('bookingstatus', 'rbsource_tapsenrol'),
                        'base.bookingstatus'
                ),
        ];

        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'auser.id'],
                'auser'
        );

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );

        $contentoptions[] = new rb_content_option(
                'leaver',
                get_string('leaver', 'local_reportbuilder'),
                ['leaver' => "auserstaff.LEAVER_FLAG"],
                'auserstaff'
        );

        $contentoptions[] = new rb_content_option(
                'iscpd',
                get_string('iscpd', 'local_reportbuilder'),
                'base.cpdid'
        );

        return $contentoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
                'class',
                'cpdorlms',
                get_string('cpdorlms', 'rbsource_tapsenrol'),
                'select',
                array(
                        'selectchoices' => array(0 => get_string('lms', 'rbsource_tapsenrol'),
                                                 1 => get_string('cpd', 'rbsource_tapsenrol')),
                        'simplemode'    => true
                )
        );

        $filteroptions[] = new rb_filter_option(
                'class',
                'coursename',
                get_string('classcoursename', 'local_reportbuilder'),
                'text'
        );

        $filteroptions[] = new rb_filter_option(
                'class',
                'classname',
                get_string('classname', 'local_reportbuilder'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
                'class',
                'classstartdate',
                get_string('classstartdate', 'local_reportbuilder'),
                'date',
                array('castdate' => true)
        );
        $filteroptions[] = new rb_filter_option(
                'class',
                'classenddate',
                get_string('classenddate', 'local_reportbuilder'),
                'date',
                array('castdate' => true)
        );
        $filteroptions[] = new rb_filter_option(
                'class',
                'classcompletiondate',
                get_string('classcompletiondate', 'rbsource_tapsenrol'),
                'date',
                array('castdate' => true)
        );

        $statuses = [
                'W:Requested',
                'Requested',
                'Waiting Listed',
                'Reserve',
                'Wait1',
                'Wait2',
                'Wait3',
                'Wait-Computing',
                'W:Wait Listed',
                'Wait Listed',
                'Approved Place',
                'Offered Place',
                'Assessed',
                'Full Attendance',
                'Partial Attendance',
                'Cancelled',
                'Withdrawn',
                'No Place',
                'Dropped Out',
                'Class Postponed',
                'Class No Longer Required',
                'Date Inappropriate',
                'No Response',
                'No Show',
                'Course Full'];
        $options = array_combine($statuses, $statuses);

        $filteroptions[] = new rb_filter_option(
                'class',
                'bookingstatus',
                get_string('bookingstatus', 'local_reportbuilder'),
                'selectbookingstatus',
                array(
                        'selectchoices' => $options,
                        'simplemode'    => true
                )
        );

        $filteroptions[] = new rb_filter_option(
                'tapscourse',
                'tapscoursename',
                get_string('tapscoursename', 'local_reportbuilder'),
                'text'
        );

        $filteroptions[] = new rb_filter_option(
                'tapscourse',
                'coursecode',
                get_string('tapscoursecode', 'local_reportbuilder'),
                'text'
        );

        $filteroptions[] = new rb_filter_option(
                'class',
                'classprice',
                get_string('classprice', 'rbsource_tapsenrol'),
                'number',
                [],
                'base.price'
        );

        $filteroptions[] = new rb_filter_option(
                'class',
                'classcost',
                get_string('classcost', 'local_reportbuilder'),
                'number',
                [],
                'base.classcost'
        );
        return $filteroptions;
    }

    public function rb_display_bookingstatus($data, $row) {
        if (!empty($row->cpdid)) {
            return 'Full Attendance';
        } else {
            return $data;
        }
    }

    public function rb_display_classstartdate($timestamp, $row) {
        // e-Learning records use bookingplaceddate instead of classstartdate
        if ($row->classtype == 'Self Paced') {
            $timestamp = $row->bookingplaceddate;
        }

        if (empty($timestamp)) {
            return '';
        }

        if (empty($row->usedtimezone)) {
            return userdate($timestamp, get_string('strftimedate'), 'UTC');
        } else {
            return userdate($timestamp, get_string('strftimedate'), $row->usedtimezone);
        }
    }

    public function rb_display_classcompletiondate($timestamp, $row) {
        if (empty($timestamp)) {
            return '';
        }

        if (empty($row->usedtimezone)) {
            return userdate($timestamp, get_string('strftimedate'), 'UTC');
        } else {
            return userdate($timestamp, get_string('strftimedate'), $row->usedtimezone);
        }
    }

    public function rb_display_classenddate($timestamp, $row) {
        if ($row->classtype == 'Self Paced') {
            $timestamp = ($this->taps->is_status($row->bookingstatus, ['cancelled']) ? 0 : $row->classcompletiondate);
        }
        if (!empty($row->cpdid)) {
            $timestamp = $row->classcompletiondate;
        }

        if (empty($timestamp)) {
            return '';
        }

        if (empty($row->usedtimezone)) {
            return userdate($timestamp, get_string('strftimedate'), 'UTC');
        } else {
            return userdate($timestamp, get_string('strftimedate'), $row->usedtimezone);
        }
    }

    public function rb_display_cpdorlms($item, $row) {
        if ($item) {
            return get_string('cpd', 'rbsource_tapsenrol');
        } else {
            return get_string('lms', 'rbsource_tapsenrol');
        }
    }

    public function rb_display_actions($item, $row) {
        global $OUTPUT;

        static $hascapabilities;

        if (!isset($hascapabilities)) {
            $hascapabilities = array('addcpd' => false, 'editcpd' => false, 'deletecpd' => false);
            foreach ($hascapabilities as $capability => &$hascapability) {
                $hascapability = has_capability('block/arup_mylearning:' . $capability, \context_system::instance());
            }
        }

        if (empty($row->cpdid)) {
            return '';
        }

        $reportid = optional_param('id', null, PARAM_INT);
        if (empty($reportid)) {
            return '';
        }

        $oput = '';

        if ($hascapabilities['editcpd'] && !$row->locked) {
            $editcpdurl = new \moodle_url(
                    '/blocks/arup_mylearning/editcpd.php',
                    array('cpdid' => $row->cpdid, 'tab' => 'rbreport', 'instance' => $reportid)
            );
            $oput .= $OUTPUT->action_icon(
                    $editcpdurl,
                    new \pix_icon(
                            't/editstring',
                            get_string('editcpd', 'block_arup_mylearning')
                    ),
                    null,
                    array('class' => 'action-icon extra-action')
            );
        }
        if ($hascapabilities['deletecpd'] && !$row->locked) {
            $deletecpdurl = new \moodle_url(
                    '/blocks/arup_mylearning/deletecpd.php',
                    array('cpdid' => $row->cpdid, 'tab' => 'rbreport', 'instance' => $reportid)
            );
            $oput .= $OUTPUT->action_icon(
                    $deletecpdurl,
                    new \pix_icon(
                            'icon_delete',
                            get_string('deletecpd', 'block_arup_mylearning'),
                            'block_arup_mylearning'
                    ),
                    null,
                    array('class' => 'action-icon extra-action')
            );
        }
        return $oput;
    }

    public function rb_display_durationhours($data, $row) {
        global $DB;
        // Check if duration hours has aggregation
        $reportid = optional_param('id', null, PARAM_INT);
        $filter = array('reportid' => $reportid, 'value' => 'durationhours');
        $durationhours = $DB->get_record('report_builder_columns', $filter);
        $dh_aggregate = (!empty($durationhours) && !empty($durationhours->aggregate))? $durationhours->aggregate: ''; 

        // Display 2 decimal places only
        $format_data = strpos($data, '.') === false ? $data: rtrim(number_format($data, 2),'0');

        if ($dh_aggregate == 'sum' || $row->durationunitscode == 'H') {
            return $format_data;
        }
        return '';
    }

    public function rb_display_classtype($data, $row) {
        if ($data == 'Self Paced') {
            return get_string('elearning', 'rbsource_tapsenrol') . '|' . $data;
        } else if ($data == 'Scheduled') {
            return get_string('classroom', 'rbsource_tapsenrol'). '|' . $data;
        } 
        return $data;
    }
}
