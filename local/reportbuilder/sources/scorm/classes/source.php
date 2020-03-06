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

namespace rbsource_scorm;
use rb_base_source;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_content_option;
use rb_param_option;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        // scorm base table is a sub-query
        $this->base = '(SELECT max(id) as id, userid, scormid, scoid, attempt ' .
                "from {scorm_scoes_track} " .
                'GROUP BY userid, scormid, scoid, attempt)';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_scorm');

        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    protected function define_joinlist() {
        $joinlist = array(
                new rb_join(
                        'scorm',
                        'LEFT',
                        '{scorm}',
                        'scorm.id = base.scormid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'sco',
                        'LEFT',
                        '{scorm_scoes}',
                        'sco.id = base.scoid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
        );

        // because of SCORMs crazy db design we have to self-join the table every
        // time we want a field - horribly inefficient, but should be okay until
        // scorm gets redesigned
        $elements = array(
                'starttime' => "'x.start.time'",
                'totaltime' => "'cmi.core.total_time', 'cmi.total_time'",
                'status' => "'cmi.core.lesson_status', 'cmi.completion_status'",
                'scoreraw' => "'cmi.core.score.raw', 'cmi.score.raw'",
                'scoremin' => "'cmi.core.score.min', 'cmi.score.min'",
                'scoremax' => "'cmi.core.score.max', 'cmi.score.max'",
        );
        foreach ($elements as $name => $element) {
            $key = "sco_$name";
            $joinlist[] = new rb_join(
                    $key,
                    'LEFT',
                    '{scorm_scoes_track}',
                    "($key.userid = base.userid AND $key.scormid = base.scormid" .
                    " AND $key.scoid = base.scoid AND $key.attempt = " .
                    " base.attempt AND $key.element IN ($element))",
                    REPORT_BUILDER_RELATION_ONE_TO_ONE
            );
        }

        // include some standard joins
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'scorm', 'course');
        // requires the course join
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'scorm', 'course');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'scorm', 'course');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array(
            /*
            // array of rb_column_option objects, e.g:
            new rb_column_option(
                '',         // type
                '',         // value
                '',         // name
                '',         // field
                array()     // options
            )
            */
                new rb_column_option(
                        'scorm',
                        'title',
                        get_string('scormtitle', 'rbsource_scorm'),
                        'scorm.name',
                        array('joins' => 'scorm',
                              'dbdatatype' => 'char',
                              'outputformat' => 'text')
                ),
                new rb_column_option(
                        'sco',
                        'title',
                        get_string('title', 'rbsource_scorm'),
                        'sco.title',
                        array('joins' => 'sco',
                              'dbdatatype' => 'char',
                              'outputformat' => 'text')
                ),
                new rb_column_option(
                        'sco',
                        'starttime',
                        get_string('time', 'rbsource_scorm'),
                        $DB->sql_cast_char2int('sco_starttime.value', true),
                        array(
                                'joins' => 'sco_starttime',
                                'displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp',
                        )
                ),
                new rb_column_option(
                        'sco',
                        'status',
                        get_string('status', 'rbsource_scorm'),
                        $DB->sql_compare_text('sco_status.value', 1024),
                        array(
                                'joins' => 'sco_status',
                                'displayfunc' => 'ucfirst',
                                'dbdatatype' => 'text',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'sco',
                        'totaltime',
                        get_string('totaltime', 'rbsource_scorm'),
                        $DB->sql_compare_text('sco_totaltime.value', 1024),
                        array('joins' => 'sco_totaltime')
                ),
                new rb_column_option(
                        'sco',
                        'scoreraw',
                        get_string('score', 'rbsource_scorm'),
                        $DB->sql_compare_text('sco_scoreraw.value', 1024),
                        array('joins' => 'sco_scoreraw')
                ),
                new rb_column_option(
                        'sco',
                        'statusmodified',
                        get_string('statusmodified', 'rbsource_scorm'),
                        'sco_status.timemodified',
                        array(
                                'joins' => 'sco_status',
                                'displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'sco',
                        'scoremin',
                        get_string('minscore', 'rbsource_scorm'),
                        $DB->sql_compare_text('sco_scoremin.value', 1024),
                        array('joins' => 'sco_scoremin')
                ),
                new rb_column_option(
                        'sco',
                        'scoremax',
                        get_string('maxscore', 'rbsource_scorm'),
                        $DB->sql_compare_text('sco_scoremax.value', 1024),
                        array('joins' => 'sco_scoremax')
                ),
                new rb_column_option(
                        'sco',
                        'attempt',
                        get_string('attemptnum', 'rbsource_scorm'),
                        'base.attempt',
                        array('dbdatatype' => 'integer')
                ),
        );

        // include some standard columns
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_core_tag_fields_to_columns('core', 'course', $columnoptions);
        $this->add_cohort_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
            /*
            // array of rb_filter_option objects, e.g:
            new rb_filter_option(
                '',       // type
                '',       // value
                '',       // label
                '',       // filtertype
                array()   // options
            )
            */
                new rb_filter_option(
                        'scorm',
                        'title',
                        get_string('scormtitle', 'rbsource_scorm'),
                        'text'
                ),
                new rb_filter_option(
                        'sco',
                        'title',
                        get_string('title', 'rbsource_scorm'),
                        'text'
                ),
                new rb_filter_option(
                        'sco',
                        'starttime',
                        get_string('attemptstart', 'rbsource_scorm'),
                        'date'
                ),
                new rb_filter_option(
                        'sco',
                        'attempt',
                        get_string('attemptnum', 'rbsource_scorm'),
                        'select',
                        array('selectfunc' => 'scorm_attempt_list')
                ),
                new rb_filter_option(
                        'sco',
                        'status',
                        get_string('status', 'rbsource_scorm'),
                        'select',
                        array('selectfunc' => 'scorm_status_list')
                ),
                new rb_filter_option(
                        'sco',
                        'statusmodified',
                        get_string('statusmodified', 'rbsource_scorm'),
                        'date'
                ),
                new rb_filter_option(
                        'sco',
                        'scoreraw',
                        get_string('rawscore', 'rbsource_scorm'),
                        'number'
                ),
                new rb_filter_option(
                        'sco',
                        'scoremin',
                        get_string('minscore', 'rbsource_scorm'),
                        'number'
                ),
                new rb_filter_option(
                        'sco',
                        'scoremax',
                        get_string('maxscore', 'rbsource_scorm'),
                        'number'
                ),
        );

        // include some standard filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'course', $filteroptions);
        $this->add_cohort_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        global $DB;

        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('thedate', 'rbsource_scorm'),
                $DB->sql_cast_char2int('sco_starttime.value', true),
                'sco_starttime'
        );

        // Add the time created content option.
        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'base.userid']
        );



        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'scorm.course',
                'scorm'
        );

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );

        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'userid',       // parameter name
                        'base.userid',  // field
                        null            // joins
                ),
                new rb_param_option(
                        'courseid',
                        'scorm.course',
                        'scorm'
                ),
        );
        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelink',
                ),
                array(
                        'type' => 'scorm',
                        'value' => 'title',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'title',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'attempt',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'starttime',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'totaltime',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'status',
                ),
                array(
                        'type' => 'sco',
                        'value' => 'scoreraw',
                ),
        );

        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'job_assignment',
                        'value' => 'allpositions',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'job_assignment',
                        'value' => 'allorganisations',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'sco',
                        'value' => 'status',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'sco',
                        'value' => 'starttime',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'sco',
                        'value' => 'attempt',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'sco',
                        'value' => 'scoreraw',
                        'advanced' => 1,
                ),
        );

        return $defaultfilters;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array(
            /*
            // array of rb_column objects, e.g:
            new rb_column(
                '',         // type
                '',         // value
                '',         // heading
                '',         // field
                array(),    // options
            )
            */
        );
        return $requiredcolumns;
    }

    //
    //
    // Source specific column display methods
    //
    //

    // add methods here with [name] matching column option displayfunc
    /*
    function rb_display_[name]($item, $row) {
        // variable $item refers to the current item
        // $row is an object containing the whole row
        // which will include any extrafields
        //
        // should return a string containing what should be displayed
    }
    */

    //
    //
    // Source specific filter display methods
    //
    //

    function rb_filter_scorm_attempt_list() {
        global $DB;

        if (!$max = $DB->get_field_sql('SELECT MAX(attempt) FROM {scorm_scoes_track}')) {
            $max = 10;
        }
        $attemptselect = array();
        foreach( range(1, $max) as $attempt) {
            $attemptselect[$attempt] = $attempt;
        }
        return $attemptselect;
    }

    function rb_filter_scorm_status_list() {
        global $DB;

        // get all available options
        $records = $DB->get_records_sql("SELECT DISTINCT " .
                $DB->sql_compare_text("value") . " AS value FROM " .
                "{scorm_scoes_track} " .
                "WHERE element = 'cmi.core.lesson_status'");
        if (!empty($records)) {
            $statusselect = array();
            foreach ($records as $record) {
                $statusselect[$record->value] = ucfirst($record->value);
            }
        } else {
            // a default set of options
            $statusselect = array(
                    'passed' => get_string('passed', 'rbsource_scorm'),
                    'completed' => get_string('completed', 'rbsource_scorm'),
                    'not attempted' => get_string('notattempted', 'rbsource_scorm'),
                    'incomplete' => get_string('incomplete', 'rbsource_scorm'),
                    'failed' => get_string('failed', 'rbsource_scorm'),
            );
        }
        return $statusselect;
    }


} // end of rb_source_scorm class

