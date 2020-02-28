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

namespace rbsource_tapsenrolmoodlecourse;

use rb_base_source;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_content_option;
use rb_column;

defined('MOODLE_INTERNAL') || die();

class source extends \rbsource_tapsenrol\source {
    public function __construct() {
        parent::__construct();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_tapsenrolmoodlecourse');
    }

    protected function define_columnoptions() {
        $columnoptions = parent::define_columnoptions();

        $columnoptions[] = new rb_column_option(
                'tapsenrol_iw_tracking',
                "sponsoremail",
                get_string('sponsoremail', 'rbsource_tapsenrolmoodlecourse'),
                "tapsenrol_iw_tracking.sponsoremail",
                [
                        'joins' => 'tapsenrol_iw_tracking']
        );

        $columnoptions[] = new rb_column_option(
                'tapsenrol_iw_tracking',
                "sponsorname",
                get_string('sponsorname', 'rbsource_tapsenrolmoodlecourse'),
                "tapsenrol_iw_tracking.sponsorfirstname",
                [
                        'extrafields'     => [
                                'sponsorlastname' => 'tapsenrol_iw_tracking.sponsorlastname'
                        ],
                        'displayfunc' => 'sponsorname',
                        'joins'           => 'tapsenrol_iw_tracking'
                ]
        );

        $columnoptions[] = new rb_column_option(
                'tapsenrol_iw_tracking',
                "timeapproved",
                get_string('timeapproved', 'rbsource_tapsenrolmoodlecourse'),
                "tapsenrol_iw_tracking.timeapproved",
                [
                        'displayfunc' => 'nice_date',
                        'dbdatatype'  => 'timestamp',
                        'joins'       => 'tapsenrol_iw_tracking'
                ]
        );

        $columnoptions[] = new rb_column_option(
                'tapsenrol_iw_tracking',
                "timecancelled",
                get_string('timecancelled', 'rbsource_tapsenrolmoodlecourse'),
                "tapsenrol_iw_tracking.timecancelled",
                [
                        'displayfunc' => 'nice_date',
                        'dbdatatype'  => 'timestamp',
                        'joins'       => 'tapsenrol_iw_tracking'
                ]
        );

        $columnoptions[] = new rb_column_option(
                'class',
                "coursenameincmoodle",
                get_string('coursenameincmoodle', 'rbsource_tapsenrolmoodlecourse'),
                "coalesce(course.fullname, base.coursename, base.classname)",
                array(
                        'joins'       => 'course',
                        'extrafields' => array('moodlecourseid' => 'course.id', 'enrolmentid' => 'base.id', 'learningdesc' => 'base.learningdesc'),
                        'displayfunc' => 'coursenamelink'
                )
        );

        // Include some standard columns, override parent so they say certification.
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_joinlist() {
        $joinlist = parent::define_joinlist();

        $joinlist[] = new rb_join(
                'tapsenrol_iw_tracking',
                'LEFT',
                '{tapsenrol_iw_tracking}',
                "base.enrolmentid = tapsenrol_iw_tracking.enrolmentid",
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
        $contentoptions = parent::define_contentoptions();

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'course.id',
                'course'
        );

        return $contentoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = parent::define_filteroptions();

        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    public function rb_display_sponsorname($sponsorfirstname, $row) {
        return $sponsorfirstname . ' ' . $row->sponsorlastname;
    }

    public function rb_display_coursenamelink($coursename, $row) {
        if (!empty($row->moodlecourseid)) {
            $url = new \moodle_url(
                    '/course/view.php',
                    array('id' => $row->moodlecourseid)
            );

            return $this->create_expand_link($coursename, 'course_details', array('expandcourseid' => $row->moodlecourseid), $url);
        } else if (!empty($row->learningdesc)) {
            return $this->create_expand_link($coursename, 'tapslearningdescription', array('expandenrolmentid' => $row->enrolmentid));
        }

        return $coursename;
    }

    /**
     * Expanding content to display when clicking a course.
     * Will be placed inside a table cell which is the width of the table.
     * Call required_param to get any param data that is needed.
     * Make sure to check that the data requested is permitted for the viewer.
     *
     * @return string
     */
    public function rb_expand_tapslearningdescription() {
        global $DB;

        $courseid = required_param('expandenrolmentid', PARAM_INT);

        return $DB->get_field('local_taps_enrolment', 'learningdesc', ['id' => $courseid]);
    }
}
