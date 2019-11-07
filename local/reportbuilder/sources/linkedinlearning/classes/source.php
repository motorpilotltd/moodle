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

namespace rbsource_linkedinlearning;
use rb_base_source;
use rb_content_option;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_column;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $defaultcolumns, $defaultfilters, $requiredcolumns;
    public $sourcetitle, $contentoptions;

    public function __construct() {
        $this->base = '{linkedinlearning_course}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->contentoptions = $this->define_contentoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_linkedinlearning');

        parent::__construct();
    }

    /**
     * Define join list
     * @return array
     */
    protected function define_joinlist() {
        global $DB;

        $concatname = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat('class.name', ', ', 'class.name ASC');

        $joinlist = array(
            // Join assignment.
                new rb_join(
                        'progress',
                        'INNER',
                        '{linkedinlearning_progress}',
                        'progress.urn = base.urn',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'classification',
                        'INNER',
                        "(SELECT linkedinlearningcourseid, $concatname as classificationnames
                                FROM {linkedinlearning_crs_class} crsclass
                                INNER JOIN {linkedinlearning_class} class ON crsclass.classificationid = class.id
                                GROUP BY linkedinlearningcourseid)",
                        'classification.linkedinlearningcourseid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'crsclass',
                        'INNER',
                        "{linkedinlearning_crs_class}",
                        'crsclass.linkedinlearningcourseid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'class',
                        'INNER',
                        "{linkedinlearning_class}",
                        'class.id = crsclass.classificationid',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'crsclass'
                ),
                new rb_join(
                        'local_taps_course',
                        'INNER',
                        '{local_taps_course}',
                        'local_taps_course.coursecode = base.urn',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'arupadvertdatatype_taps',
                        'INNER',
                        '{arupadvertdatatype_taps}',
                        'arupadvertdatatype_taps.tapscourseid = local_taps_course.courseid',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'local_taps_course'
                ),
                new rb_join(
                        'arupadvert',
                        'INNER',
                        '{arupadvert}',
                        'arupadvert.id = arupadvertdatatype_taps.arupadvertid',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'arupadvertdatatype_taps'
                ),
        );

        // join users, courses and categories
        $this->add_user_table_to_joinlist($joinlist, 'progress', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'arupadvert', 'course');
        $this->add_course_category_table_to_joinlist($joinlist, 'course', 'category');

        return $joinlist;
    }

    /**
     * define column options
     * @return array
     */
    protected function define_columnoptions() {
        global $CFG;
        include_once($CFG->dirroot.'/mod/assign/locallib.php');

        $columnoptions = array(
            // Assignment name.
                new rb_column_option(
                        'linkedincourse',
                        'title',
                        get_string('title', 'rbsource_linkedinlearning'),
                        'base.title',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'urn',
                        get_string('urn', 'rbsource_linkedinlearning'),
                        'base.urn',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'publishedat',
                        get_string('publishedat', 'rbsource_linkedinlearning'),
                        'base.publishedat',
                        array(
                                'displayfunc' => 'nice_datetime'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'lastupdatedat',
                        get_string('lastupdatedat', 'rbsource_linkedinlearning'),
                        'base.lastupdatedat',
                        array(
                                'displayfunc' => 'nice_datetime'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'timetocomplete',
                        get_string('timetocomplete', 'rbsource_linkedinlearning'),
                        'base.timetocomplete / 60',
                        array(
                                'displayfunc' => 'duration_hours_minutes'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'available',
                        get_string('available', 'rbsource_linkedinlearning'),
                        "available",
                        [
                                'displayfunc' => 'yes_no',
                        ]
                ),
                new rb_column_option(
                        'linkedincourse',
                        'classificationnames',
                        get_string('classificationnames', 'rbsource_linkedinlearning'),
                        'classification.classificationnames',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                            'joins' => 'classification'
                        )
                ),
                new rb_column_option(
                        'linkedincourse',
                        'classificationname',
                        get_string('classificationname', 'rbsource_linkedinlearning'),
                        'class.name',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                            'joins' => 'class'
                        )
                ),
                new rb_column_option(
                        'linkedincourseprogress',
                        'progresspercentagebar',
                        get_string('progresspercentagebar', 'rbsource_linkedinlearning'),
                        'progress.progress_percentage',
                        array(
                                'displayfunc' => 'progressbarsimple',
                                'joins'       => ['progress'],
                                'dbdatatype'  => 'integer',
                        )
                ),
                new rb_column_option(
                        'linkedincourseprogress',
                        'progresspercentage',
                        get_string('progresspercentage', 'rbsource_linkedinlearning'),
                        'progress.progress_percentage',
                        array(
                                'displayfunc' => 'percent',
                                'joins'       => ['progress'],
                                'dbdatatype'  => 'integer',
                                'extrafields' => ['green' => 'progress.progress_percentage'],

                        )
                ),
                new rb_column_option(
                        'linkedincourseprogress',
                        'timeincourse',
                        get_string('timeincourse', 'rbsource_linkedinlearning'),
                        'progress.seconds_viewed',
                        array(
                                'displayfunc' => 'duration',
                                'dbdatatype' => 'integer',
                                'joins'       => 'progress',
                        )
                ),
        );

        // User, course and category fields.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    /**
     * define filter options
     * @return array
     */
    protected function define_filteroptions() {
        global $DB;

        $filteroptions = array(
            // Assignment columns.
                new rb_filter_option(
                        'linkedincourse',
                        'title',
                        get_string('title', 'rbsource_linkedinlearning'),
                        'text'
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'urn',
                        get_string('urn', 'rbsource_linkedinlearning'),
                        'text'
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'publishedat',
                        get_string('publishedat', 'rbsource_linkedinlearning'),
                        'date'
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'lastupdatedat',
                        get_string('lastupdatedat', 'rbsource_linkedinlearning'),
                        'date'
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'timetocomplete',
                        get_string('timetocomplete', 'rbsource_linkedinlearning'),
                        'number'
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'available',
                        get_string('available', 'rbsource_linkedinlearning'),
                        'select',
                        array(
                                'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                                'simplemode' => true,
                        )
                ),
                new rb_filter_option(
                        'linkedincourse',
                        'classificationname',
                        get_string('classificationname', 'rbsource_linkedinlearning'),
                        'select',
                        array(
                                'selectchoices' => $DB->get_records_menu('linkedinlearning_class', [], 'name', 'id,name'),
                        ),
                        'class.id',
                        'class'
                ),
        );

        // user, course and category filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        // Add the time created content option.
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

        return $contentoptions;
    }

    /**
     * define required columns
     * @return array
     */
    protected function define_requiredcolumns() {
        $requiredcolumns = [];

        return $requiredcolumns;
    }

    /**
     * define default columns
     * @return array
     */
    protected function define_defaultcolumns() {
        $defaultcolumns = [];
        return $defaultcolumns;
    }

    /**
     * Define default filters
     * @return array
     */
    protected function define_defaultfilters(){
        $defaultfilters = [];

        return $defaultfilters;
    }
}
