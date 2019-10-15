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

namespace rbsource_certificationoverview;
use rb_base_source;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_column;
use rb_filter_option;
use html_writer;
use moodle_url;
use rb_course_sortorder_helper;
use rb_content_option;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/custom_certification/lib.php');

class source extends rb_base_source {
    public function __construct() {
        $this->base = '{certif_completions}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->usedcomponents[] = 'local_certification';
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_certificationoverview');

        parent::__construct();

    }

    protected function define_joinlist() {
        global $CFG;

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');
        $joinlist = [];

        /* Psuedo Explaination:
         *
         * if (window is open) {
         *      Use current record
         * } else {
         *      if (history record exists) {
         *          use history certifpath
         *      } else {
         *          default to certif
         *      }
         * }
         */
        $now = time();
        $path = 0;
        $joinlist[] = new rb_join(
                'certif_coursesets',
                'INNER',
                '{certif_coursesets}',
                "certif_coursesets.certifid = base.certifid
            AND (
                   (certif_completion.timewindowsopens < {$now} AND certif_coursesets.certifpath = certif_completion.certifpath)
                OR (certif_completion.timewindowsopens > {$now} AND history.certifpath IS NOT NULL AND certif_coursesets.certifpath = history.certifpath)
                OR (certif_completion.timewindowsopens > {$now} AND history.certifpath IS NULL AND certif_coursesets.certifpath = {$path})
            )
            ",
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                array('base', 'certif_completion', 'history')
        );

        $joinlist[] = new rb_join(
                'history',
                'LEFT',
                '{certif_completions_archive}',
                "certif_completion.userid = history.userid
             AND certif_completion.certifid = history.certifid
             AND history.timecompleted = (SELECT MAX(timecompleted)
                                            FROM {certif_completions_archive} cch
                                           WHERE cch.userid = history.userid
                                             AND cch.certifid = history.certifid)",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'certif_completion'
        );

        $joinlist[] = new rb_join(
                'certif_completion',
                'INNER',
                '{certif_completions}',
                "certif_completion.userid = base.userid AND certif_completion.certifid = certif.id",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'certif'
        );

        $joinlist[] = new rb_join(
                'certif_courseset_courses',
                'INNER',
                '{certif_courseset_courses}',
                "certif_courseset_courses.coursesetid = certif_coursesets.id",
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                'certif_coursesets'
        );

        $joinlist[] = new rb_join(
                'course',
                'INNER',
                '{course} ', // Intentional space to stop report builder adding unwanted custom course fields.
                "certif_courseset_courses.courseid = course.id",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'certif_courseset_courses'
        );

        $joinlist[] = new rb_join(
                'course_completions',
                'LEFT',
                '{course_completions}',
                "course_completions.course = course.id AND course_completions.userid = base.userid",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'course'
        );

        $joinlist[] = new rb_join(
                'grade_items',
                'LEFT',
                '{grade_items}',
                "grade_items.itemtype = 'course' AND grade_items.courseid = course.id",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'course'
        );

        $joinlist[] = new rb_join(
                'grade_grades',
                'LEFT',
                '{grade_grades}',
                "grade_grades.itemid = grade_items.id AND grade_grades.userid = base.userid",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'grade_items'
        );

        $joinlist[] = new rb_join(
                'criteria',
                'LEFT',
                '{course_completion_criteria}',
                "criteria.course = certif_courseset_courses.courseid AND criteria.criteriatype = " . COMPLETION_CRITERIA_TYPE_GRADE,
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'certif_courseset_courses'
        );

        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_category_table_to_joinlist($joinlist, 'course', 'category');
        $this->add_certification_table_to_joinlist($joinlist, 'base', 'certifid');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        global $DB, $CFG;
        require_once($CFG->dirroot.'/completion/completion_completion.php');

        $columnoptions = array();

        // Include some standard columns.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);

        // Programe completion cols.
        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'duedate',
                get_string('duedate', 'rbsource_certificationoverview'),
                'base.duedate',
                array(
                        'joins' => 'base',
                        'displayfunc' => 'nice_date',
                        'dbdatatype' => 'timestamp',
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'timeassigned',
                get_string('dateassigned', 'rbsource_certificationoverview'),
                'base.timeassigned',
                array(
                        'joins' => 'base',
                        'displayfunc' => 'nice_date',
                        'dbdatatype' => 'timestamp',
                        'extrafields' => array('prog_id' => 'certif.id')
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'timecompleted',
                get_string('timecompleted', 'rbsource_certificationoverview'),
                'base.timecompleted',
                array(
                        'joins' => 'base',
                        'displayfunc' => 'nice_date',
                        'dbdatatype' => 'timestamp',
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'timeenrolled',
                get_string('coursecompletiontimeenrolled', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('course_completions.timeenrolled')),
                array(
                        'joins' => ['course_completions', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline_date',
                        'style' => array('white-space' => 'pre'),
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'timestarted',
                get_string('coursecompletiontimestarted', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('course_completions.timestarted')),
                array(
                        'joins' => ['course_completions', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline_date',
                        'style' => array('white-space' => 'pre'),
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'timecompleted',
                get_string('coursecompletiontimecompleted', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('course_completions.timecompleted')),
                array(
                        'joins' => ['course_completions', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline_date',
                        'style' => array('white-space' => 'pre'),
                )
        );

        // Course grade.
        $columnoptions[] = new rb_column_option(
                'course',
                'finalgrade',
                get_string('finalgrade', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('grade_grades.finalgrade')),
                array(
                        'joins' => ['grade_grades', 'course', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'groupinginfo' => array(
                                'orderby' => array('certif_coursesets.sortorder', 'certif_courseset_courses.id'),
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline',
                        'style' => array('white-space' => 'pre'),
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'gradepass',
                get_string('gradepass', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('criteria.gradepass')),
                array(
                        'joins' => ['criteria', 'course', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline',
                        'style' => array('white-space' => 'pre'),
                )
        );


        // Course category.
        $columnoptions[] = new rb_column_option(
                'course',
                'name',
                get_string('coursecategory', 'local_reportbuilder'),
                rb_course_sortorder_helper::get_column_field_definition('course_category.name'),
                array(
                        'joins' => ['course_category', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline',
                        'style' => array('white-space' => 'pre'),
                        'dbdatatype' => 'char',
                        'outputformat' => 'text'
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'namelink',
                get_string('coursecategorylinked', 'local_reportbuilder'),
                rb_course_sortorder_helper::get_column_field_definition(
                        $DB->sql_concat_join(
                                "'|'",
                                array(
                                        \local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('course_category.id'),
                                        \local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char("course_category.visible"),
                                        'course_category.name'
                                )
                        )
                ),
                array(
                        'joins' => 'course_category',
                        'displayfunc' => 'course_category_link',
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'defaultheading' => get_string('category', 'local_reportbuilder'),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_category_link_list',
                        'style' => array('white-space' => 'pre'),
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'id',
                get_string('coursecategoryid', 'local_reportbuilder'),
                rb_course_sortorder_helper::get_column_field_definition('course_category.idnumber'),
                array(
                        'joins' => array('course', 'course_category'),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_newline',
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_courseset_courses.id'
                        ),
                        'style' => array('white-space' => 'pre'),
                        'dbdatatype' => 'char',
                        'outputformat' => 'text'
                )
        );

        $this->add_certification_fields_to_columns($columnoptions, 'certif');

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'duedatenice',
                get_string('duedateextra', 'rbsource_certificationoverview'),
                'base.duedate',
                array(
                        'joins' => array('base', 'certif_completion'),
                        'displayfunc' => 'certifduedate',
                        'extrafields' => array(
                                'status' => 'certif_completion.status',
                                'certifid' => 'base.certifid',
                                'certifpath' => 'certif_completion.certifpath',
                                'certifstatus' => 'certif_completion.status',
                                'userid' => 'base.userid',
                        )
                )
        );

        // Certification path col.
        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'certifpath',
                get_string('certifpath', 'rbsource_certificationoverview'),
                'certif_completion.certifpath',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'certif_certifpath'
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'status',
                get_string('status', 'rbsource_certificationoverview'),
                'certif_completion.status',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'certif_status',
                        'extrafields' => array(
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'timewindowsopens',
                get_string('timewindowsopens', 'rbsource_certificationoverview'),
                'certif_completion.timewindowsopens',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'nice_date',
                        'extrafields' => array(
                                'status' => 'certif_completion.status'
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif_completion',
                'timeexpires',
                get_string('timeexpires', 'rbsource_certificationoverview'),
                'certif_completion.timeexpires',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'nice_date',
                        'extrafields' => array(
                                'status' => 'certif_completion.status'
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'course',
                'shortname',
                get_string('courseshortname', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition('course.shortname'),
                array(
                        'joins' => ['course'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_coursesets_course.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_name_list',
                        'style' => array('white-space' => 'pre'),
                )

        );

        $columnoptions[] = new rb_column_option(
                'course',
                'status',
                get_string('coursecompletionstatus', 'rbsource_certificationoverview'),
                rb_course_sortorder_helper::get_column_field_definition(' CASE WHEN course_completions.timecompleted is null then -1 WHEN course_completions.timecompleted = 0 then 0 WHEN course_completions.timecompleted > 0 then 1 END '),
                array(
                        'joins' => ['course_completions', 'certif'],
                        'grouping' => 'sql_aggregate',
                        'grouporder' => array(
                                'csorder'  => 'certif_coursesets.sortorder',
                                'cscid'    => 'certif_coursesets_course.id'
                        ),
                        'nosort' => true, // You can't sort concatenated columns.
                        'displayfunc' => 'certification_course_status_list',
                        'style' => array('white-space' => 'pre'),
                )

        );

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
                'certif',
                'id',
                get_string('certifnameselect', "rbsource_certificationoverview"),
                'select',
                array(
                        'selectfunc' => 'certification_list',
                        'attributes' => rb_filter_option::select_width_limiter(),
                        'simplemode' => true,
                        'noanychoice' => true,
                )
        );

        $filteroptions[] = new rb_filter_option(
                'certif_completion',
                'duedate',
                get_string('duedate', 'rbsource_certificationoverview'),
                'date'
        );

        $this->add_certification_fields_to_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
                'certif_completion',
                'status',
                get_string('status', 'rbsource_certificationoverview'),
                'select',
                array(
                        'selectfunc' => 'certif_completion_status',
                        'attributes' => rb_filter_option::select_width_limiter(),
                )
        );

        $filteroptions[] = new rb_filter_option(
                'certif_completion',
                'timecompleted',
                get_string('timecompleted', 'rbsource_certificationoverview'),
                'date'
        );


        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('completeddate', 'rbsource_certificationcompletion'),
                'base.timecompleted'
        );

        return $contentoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array();
        $defaultcolumns[] = array('type' => 'certif', 'value' => 'shortname');
        $defaultcolumns[] = array('type' => 'user', 'value' => 'namelink');
        $defaultcolumns[] = array('type' => 'certif_completion', 'value' => 'duedate');

        $defaultcolumns[] = array('type' => 'course', 'value' => 'shortname');
        $defaultcolumns[] = array('type' => 'course', 'value' => 'status');
        $defaultcolumns[] = array('type' => 'course', 'value' => 'finalgrade');

        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'certif',
                        'value' => 'id',
                        'advanced' => 0,
                ),
        );
        return $defaultfilters;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();
        $requiredcolumns[] = new rb_column(
                'certif',
                'groupbycol',
                '',
                "certif.id",
                array(
                        'joins' => 'certif',
                        'hidden' => true,
                )
        );
        return $requiredcolumns;
    }

    /**
     * Display the program completion status
     *
     * @deprecated Since Totara 10.14
     * @param $status
     * @param $row
     * @return string
     */
    function rb_display_certif_completion_status($status, $row) {
        if (is_null($status)) {
            return '';
        }
        if ($status == 1) {
            return get_string('coursecompletion_1', 'rbsource_certificationoverview');
        } else if ($status == -1) {
            return get_string('coursecompletion_-1', 'rbsource_certificationoverview');
        } else {
            return get_string('coursecompletion_0', 'rbsource_certificationoverview');
        }
    }

    /**
     * Displays categories as html links.
     *
     * @deprecated Since Totara 10.14
     * @param array $data
     * @param object Report row $row
     * @return string html link
     */
    public function rb_display_category_link_list($data, $row) {
        $output = array();
        if (empty($data)) {
            return '';
        }
        $items = explode(self::$uniquedelimiter, $data);
        foreach ($items as $item) {
            list($catid, $visible, $catname) = explode('|', $item);
            if ($visible) {
                $url = new moodle_url('/course/index.php', array('categoryid' => $catid));
                $output[] = html_writer::link($url, format_string($catname));
            } else {
                $output[] = format_string($catname);
            }
        }

        return implode($output, "\n");
    }


    /**
     * Displays course names as html links.
     *
     * @deprecated Since Totara 10.14
     * @param array $data
     * @param object Report row $row
     * @return string html link
     */
    public function rb_display_list($data, $row) {
        if (empty($data)) {
            return '';
        }
        $items = explode(self::$uniquedelimiter, $data);
        return implode($items, "\n");
    }

    // Source specific filter display methods.
    function rb_filter_certification_list() {
        global $CFG, $DB;

        $list = [];

        $progs = $DB->get_records_menu('certif', [], 'fullname', 'id, fullname');

        foreach ($progs as $id => $name) {
            $list[$id] = format_string($name);
        }

        return ($list);
    }

    public function rb_filter_program_status() {
        global $CFG;

        require_once($CFG->dirroot . '/totara/program/program.class.php');

        $list = array();

        $list[0] = get_string('incomplete', 'rbsource_certificationoverview');
        $list[1] = get_string('complete', 'rbsource_certificationoverview');;

        return $list;
    }

    /**
     * Certification completion status filter options
     */
    function rb_filter_certif_completion_status() {
        return [
                0 => get_string('complete', 'rbsource_certificationoverview'),
                1 => get_string('incomplete', 'rbsource_certificationoverview')
        ];
    }
}
