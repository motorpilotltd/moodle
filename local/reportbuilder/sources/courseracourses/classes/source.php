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

namespace rbsource_courseracourses;
use local_courseracourses\course;
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
        global $PAGE;

        $this->base = '{courseracourse}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->contentoptions = $this->define_contentoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_courseracourses');

        $PAGE->requires->js_call_amd('local_courseracourses/manage', 'initialise');

        parent::__construct();
    }

    /**
     * Define join list
     * @return array
     */
    protected function define_joinlist() {
        $progid = get_config('mod_coursera', 'programid');

        $joinlist = [
                new rb_join(
                        "courserainstance",
                        'LEFT',
                        '{coursera}',
                        "courserainstance.contentid = base.id",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        "courseraprogramlink",
                        'LEFT',
                        '{courseraprogramlink}',
                        "courseraprogramlink.courseracourseid = base.id AND courseraprogramlink.programid = '$progid'",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
        ];

        // join users, courses and categories
        $this->add_course_table_to_joinlist($joinlist, 'courserainstance', 'course');
        $this->add_course_category_table_to_joinlist($joinlist, 'course', 'category');

        return $joinlist;
    }

    /**
     * define column options
     * @return array
     */
    protected function define_columnoptions() {
        global $CFG;

        $columnoptions = array(
            // Assignment name.
                new rb_column_option(
                        'coursera',
                        'title',
                        get_string('title', 'rbsource_courseracourses'),
                        'base.title',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'estimatedlearningtime',
                        get_string('estimatedlearningtime', 'rbsource_courseracourses'),
                        'base.estimatedlearningtime / 60',
                        array(
                                'displayfunc' => 'duration_hours_minutes'
                        )
                ),
        );

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
                        'coursera',
                        'title',
                        get_string('title', 'rbsource_courseracourses'),
                        'text'
                )
        );

        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'courseregion',
                get_string('courseregion', 'local_reportbuilder'),
                ['courseid' => "arupadvert.course"],
                'arupadvert'
        );

        $contentoptions[] = new rb_content_option(
                'courseracourse',
                get_string('courseracourse', 'local_reportbuilder'),
                'courseraprogramlink.id',
                'courseraprogramlink'
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
