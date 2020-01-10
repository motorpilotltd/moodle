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
        $sql = '(base.archived = 0 or base.archived is null)';

        return array("($sql)", []);
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = [];
        // Include some standard columns, override parent so they say certification.
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);

        $classfields = ['classname', 'classtype', 'location'];

        foreach($classfields as $stafffield) {
            $columnoptions[] = new rb_column_option(
                    'class',
                    "$stafffield",
                    get_string($stafffield, 'local_reportbuilder'),
                    "base.$stafffield",
                    array(
                            'displayfunc'  => 'plaintext',
                            'dbdatatype'   => 'char',
                            'outputformat' => 'text')
            );
        }
        $columnoptions[] = new rb_column_option(
                'class',
                "coursename",
                get_string('coursename', 'local_reportbuilder'),
                "base.coursename",
                array(
                        'displayfunc'  => 'coalescecoursename',
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
                                'usedtimezone' => 'base.usedtimezone',
                                'classtype' => 'base.classtype',
                                'bookingstatus' => 'base.bookingstatus',
                                'classcompletiondate' => 'base.classcompletiondate',
                                'cpdid' => 'base.cpdid'
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
                                'classtype' => 'base.classtype',
                                'usedtimezone' => 'base.usedtimezone',
                                'bookingplaceddate' => 'base.bookingplaceddate'
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

        $enrolmentfields = ['learningdesc', 'classcategory', 'provider'];

        foreach($enrolmentfields as $enrolmentfield) {
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
                        'extrafields' => array('cpdid' => 'base.cpdid')
                )
        );

        $columnoptions[] = new rb_column_option(
                'class',
                'cpd',
                get_string('cpdorlms', 'rbsource_tapsenrol'),
                "base.cpdid",
                array(
                        'displayfunc' => 'cpdorlms',
                        'dbdatatype' => 'boolean',
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
                        'dbdatatype' => 'timestamp'
                )
        );
        $columnoptions[] = new rb_column_option(
                'class',
                'expirydate',
                get_string('expirydate', 'local_reportbuilder'),
                "base.expirydate",
                array(
                        'displayfunc' => 'nice_datetime',
                        'dbdatatype' => 'timestamp'
                )
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'coursecode',
                get_string('coursecode', 'local_reportbuilder'),
                'tapscourse.coursecode',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'courseregion',
                get_string('courseregion', 'local_reportbuilder'),
                'tapscourse.courseregion',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'tapscourse',
                'tapscoursename',
                get_string('coursename', 'local_reportbuilder'),
                'tapscourse.coursename',
                array('joins'        => 'tapscourse',
                      'displayfunc'  => 'plaintext',
                      'dbdatatype'   => 'char',
                      'outputformat' => 'text')
        );

        return $columnoptions;
    }

    protected function define_joinlist() {
        $joinlist = [];

        $this->add_user_table_to_joinlist_on_idnumber($joinlist, 'base', 'staffid');

        $joinlist[] = new rb_join(
                'tapscourse',
                'LEFT',
                '{local_taps_course}',
                "base.courseid = tapscourse.courseid",
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );
        $joinlist[] = new rb_join(
                'arupadvertdatatype_taps',
                'LEFT',
                '{arupadvertdatatype_taps}',
                "base.courseid = arupadvertdatatype_taps.tapscourseid",
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );
        $joinlist[] = new rb_join(
                'arupadvert',
                'LEFT',
                '{arupadvert}',
                "arupadvertdatatype_taps.arupadvertid = arupadvert.id",
                REPORT_BUILDER_RELATION_MANY_TO_ONE,
                'arupadvertdatatype_taps'
        );

        $this->add_course_table_to_joinlist($joinlist, 'arupadvert', 'course', 'LEFT');
        $this->add_context_table_to_joinlist($joinlist, 'course', 'id', CONTEXT_COURSE, 'LEFT');
        // requires the course join
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'course', 'id');

        $this->add_cohort_course_tables_to_joinlist($joinlist, 'course', 'id');

        return $joinlist;
    }

    protected function define_contentoptions() {
        $contentoptions = [
                new rb_content_option(
                        'archived',
                        get_string('archived', 'rbsource_tapsenrol'),
                        'base.archived'
                ),
        ];

        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'auser.id'],
                'auser'
        );

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'course.id',
                'course'
        );

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );

        return $contentoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
                'class',
                'cpdorlms',
                get_string('cpdorlms', 'rbsource_tapsenrol'),
                'select',
                array(
                        'selectchoices' => array(0 => get_string('lms', 'rbsource_tapsenrol'), 1 => get_string('cpd', 'rbsource_tapsenrol')),
                        'simplemode' => true
                )
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
                'select',
                array(
                        'selectchoices' => $options,
                        'simplemode' => true
                )
        );

        $filteroptions[] = new rb_filter_option(
                'tapscourse',
                'tapscoursename',
                get_string('coursename', 'local_reportbuilder'),
                'text'
        );

        $filteroptions[] = new rb_filter_option(
                'tapscourse',
                'coursecode',
                get_string('coursecode', 'local_reportbuilder'),
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
                'select',
                array(
                        'selectchoices' => $options,
                        'simplemode' => true
                )
        );

        return $filteroptions;
    }

    public function rb_display_coalescecoursename($data, $row) {
        if (!empty($data)) {
            return $data;
        } else {
            return $row->classcoursename;
        }
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
}
