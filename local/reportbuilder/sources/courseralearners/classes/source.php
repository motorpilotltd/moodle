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

namespace rbsource_courseralearners;
use http\Exception;
use local_courseralearners\course;
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

        $this->base = '(select u.idnumber as id, u.idnumber as externalid
                        from {courseramoduleaccess} cma
                                 inner join {user} u on u.id = cma.userid
                         union
                        select u.idnumber as id, u.idnumber as externalid
                        from {coursera} i
                                 inner join {enrol} me on i.course = me.courseid
                                 inner join {course} c on c.id = me.courseid and c.visible = 1
                                 inner join {user_enrolments} ue on ue.enrolid = me.id
                                 inner join {user} u on u.id = ue.userid
                         union
                         select externalid as id, externalid from {courseraprogress}
                         union
                         select externalid as id, externalid from {courseraprogrammember})';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_courseralearners');

        parent::__construct();
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new \rb_param_option(
                        'cminstanceid',
                        'courseenrolments.cminstanceid',
                        'courseenrolments'
                ),
        );

        return $paramoptions;
    }

    /**
     * Define join list
     * @return array
     */
    protected function define_joinlist() {
        $now = time();

        $progid = get_config('mod_coursera', 'programid');

        $joinlist = array(
            // Join assignment.
                new rb_join(
                        'courseraprogrammemberdurationused',
                        'LEFT',
                        "(
                                select 
                                    externalid, 
                                    sum(CASE WHEN dateleft = 0 THEN $now - datejoined ELSE dateleft - datejoined END) as totaltime, 
                                    min(datejoined) as datejoined,
                                    max(dateleft) as dateleft 
                                    FROM {courseraprogrammember} 
                                    group by externalid)",
                        'courseraprogrammemberdurationused.externalid = base.externalid',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        "courseracourserelationships",
                        'LEFT',
                        "(
                        select u.idnumber as externalid, cc.contentid
                        from {courseramoduleaccess} cma
                                INNER JOIN {coursera} ce ON ce.course = cma.courseraid
                                INNER JOIN {courseracourse} cc ON ce.contentid = cc.id
                                INNER JOIN {courseraprogramlink} cpl on cc.id = cpl.courseracourseid  AND cpl.programid = '$progid'
                                INNER JOIN {user} u on u.id = cma.userid
                         union
                        select u.idnumber as externalid, cc.contentid
                        from {coursera} i
                                 inner join {enrol} me on i.course = me.courseid
                                 inner join {course} c on c.id = me.courseid and c.visible = 1
                                 inner join {user_enrolments} ue on ue.enrolid = me.id
                                 inner join {user} u on u.id = ue.userid
                                INNER JOIN {courseracourse} cc ON i.contentid = cc.id
                                INNER JOIN {courseraprogramlink} cpl on cc.id = cpl.courseracourseid  AND cpl.programid = '$progid'
                         union
                         select externalid, cc.contentid from {courseraprogress} cp
                                LEFT JOIN {courseraprogramlink} cpl on cp.courseracourseid = cpl.courseracourseid  AND cpl.programid = '$progid'
                                LEFT JOIN {courseracourse} cc ON cpl.courseracourseid = cc.id
                        )",
                        "courseracourserelationships.externalid = base.externalid",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        "courseracourse",
                        'LEFT',
                        '{courseracourse}',
                        "courseracourse.contentid = courseracourserelationships.contentid",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'courseracourserelationships'
                ),
                new rb_join(
                        'courseraprogress',
                        'LEFT',
                        '{courseraprogress}',
                        'courseraprogress.externalid = base.externalid and courseraprogress.contentid = courseracourserelationships.contentid',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'courseracourserelationships'
                ),
                new rb_join(
                        'courseenrolments',
                        'LEFT',
                        "(SELECT ue.userid            as userid,
                                       ce.id                as cminstanceid,
                                       e.courseid           as course,
                                       ce.contentid,
                                       min(ue.timestart)    as timestart,
                                       min(ue.timecreated)  as timecreated,
                                       max(ue.timemodified) as timemodified,
                                       ce.moduleaccessperiod,
                                       cma.timeend          as extensionend
                                FROM {user_enrolments} ue
                                         INNER JOIN {enrol} e ON e.id = ue.enrolid
                                         INNER JOIN {coursera} ce ON ce.course = e.courseid
                                         LEFT JOIN {courseramoduleaccess} cma on cma.userid = ue.userid and cma.courseraid = ce.id
                                GROUP BY ue.userid, e.courseid, ce.id, ce.contentid, ce.moduleaccessperiod, cma.timeend)",
                        'auser.id = courseenrolments.userid AND courseenrolments.contentid = courseracourse.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        ['courseracourserelationships', 'courseracourse', 'auser']
                ),
        );

        // join users, courses and categories
        $this->add_user_table_to_joinlist_on_idnumber($joinlist, 'base', 'externalid');
        $this->add_course_table_to_joinlist($joinlist, 'courseenrolments', 'course');
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
                        get_string('title', 'rbsource_courseralearners'),
                        'courseracourse.title',
                        array(
                                'joins' => 'courseracourse',
                                'dbdatatype' => 'char',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'externalid',
                        get_string('externalid', 'rbsource_courseralearners'),
                        'base.externalid',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'estimatedlearningtime',
                        get_string('estimatedlearningtime', 'rbsource_courseralearners'),
                        'courseracourse.estimatedlearningtime / 60',
                        array(
                                'joins' => 'courseracourse',
                                'displayfunc' => 'duration_hours_minutes'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'totaltime',
                        get_string('totaltime', 'rbsource_courseralearners'),
                        'courseraprogrammemberdurationused.totaltime / 60',
                        array(
                                'joins' => 'courseraprogrammemberdurationused',
                                'displayfunc' => 'duration_hours_minutes'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'datejoined',
                        get_string('datejoined', 'rbsource_courseralearners'),
                        'courseraprogrammemberdurationused.datejoined',
                        array(
                                'joins' => 'courseraprogrammemberdurationused',
                                'displayfunc' => 'nice_datetime'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'dateleft',
                        get_string('dateleft', 'rbsource_courseralearners'),
                        'courseraprogrammemberdurationused.dateleft',
                        array(
                                'joins' => 'courseraprogrammemberdurationused',
                                'displayfunc' => 'nice_datetime'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'iscompleted',
                        get_string('iscompleted', 'rbsource_courseralearners'),
                        'courseraprogress.iscompleted',
                        array(
                                'joins' => 'courseraprogress',
                                'displayfunc' => 'yes_or_no'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'overallprogress',
                        get_string('overallprogress', 'rbsource_courseralearners'),
                        'courseraprogress.overallprogress',
                        array(
                                'joins' => 'courseraprogress',
                                'displayfunc' => 'progressbarsimple',
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'timestart',
                        get_string('timestart', 'rbsource_courseralearners'),
                        'courseenrolments.timestart',
                        array(
                                'joins' => 'courseenrolments',
                                'displayfunc' => 'nice_datetime'
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'durationofeligibility',
                        get_string('durationofeligibility', 'rbsource_courseralearners'),
                        'courseenrolments.timestart',
                        array(
                                'extrafields'  => array(
                                        'moduleaccessperiod'  => 'courseenrolments.moduleaccessperiod',
                                        'extensionend'  => 'courseenrolments.extensionend',
                                ),
                                'joins' => 'courseenrolments',
                                'displayfunc' => 'durationofeligibility',
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'timeend',
                        get_string('timeend', 'rbsource_courseralearners'),
                        'courseenrolments.timestart',
                        array(
                                'extrafields'  => array(
                                        'moduleaccessperiod'  => 'courseenrolments.moduleaccessperiod',
                                        'extensionend'  => 'courseenrolments.extensionend',
                                ),
                                'joins' => 'courseenrolments',
                                'displayfunc' => 'endofeligibility',
                        )
                ),
                new rb_column_option(
                        'coursera',
                        'extendeligibility',
                        get_string('extendeligibility', 'rbsource_courseralearners'),
                        'courseenrolments.cminstanceid',
                        array(
                                'extrafields' => [
                                        'userid' => 'auser.id',
                                ],
                                'joins'       => ['courseenrolments', 'auser'],
                                'displayfunc' => 'extendeligibility',
                                'capability'  => 'mod/coursera:extendeligibility'
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

    public function rb_display_extendeligibility($cminstanceid, $row) {
        global $OUTPUT;

        if (empty($cminstanceid)) {
            return '';
        }

        $url = new \moodle_url('/mod/coursera/extendeligibility.php', ['cminstanceid' => $cminstanceid, 'userid' => $row->userid]);
        return \html_writer::link($url,  $OUTPUT->pix_icon('i/settings', ''), ['title' => get_string('extend', 'rbsource_courseralearners')]);
    }

    public function rb_display_durationofeligibility($timestart, $row) {
        if (!empty($row->extensionend)) {
            return $this->rb_display_duration($row->extensionend - $timestart, $row);
        } else if (!empty($row->moduleaccessperiod)) {
            return $this->rb_display_duration($row->moduleaccessperiod, $row);
        } else {
            return '';
        }
    }

    public function rb_display_endofeligibility($timestart, $row) {
        if (!empty($row->extensionend)) {
            return userdate($row->extensionend);
        } else if (!empty($row->moduleaccessperiod)) {
            return userdate($timestart + $row->moduleaccessperiod);
        } else {
            return '';
        }
    }

    /**
     * define filter options
     * @return array
     */
    protected function define_filteroptions() {
        $filteroptions = array(
            // Assignment columns.
                new rb_filter_option(
                        'coursera',
                        'title',
                        get_string('title', 'rbsource_courseralearners'),
                        'text'
                )
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

        $contentoptions[] = new rb_content_option(
                'courseregion',
                get_string('courseregion', 'local_reportbuilder'),
                ['courseid' => "arupadvert.course"],
                'arupadvert'
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
