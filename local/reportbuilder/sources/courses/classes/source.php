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

namespace rbsource_courses;
use rb_base_source;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_param_option;
use rb_content_option;
use rb_column;
use core_collator;
use reportbuilder;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    function __construct() {
        $this->base = '{course}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->defaulttoolbarsearchcolumns = $this->define_defaultsearchcolumns();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_courses');

        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    protected function define_joinlist() {
        global $DB;

        $list = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('m.name'), '|');
        $joinlist = array(
                new rb_join(
                        'mods',
                        'LEFT',
                        "(SELECT cm.course, {$list} AS list
                    FROM {course_modules} cm
               LEFT JOIN {modules} m ON m.id = cm.module
                GROUP BY cm.course)",
                        'mods.course = base.id',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'course_completions_courses_started',
                        'LEFT',
                        "(SELECT course, COUNT(id) as number
                    FROM {course_completions}
                    WHERE timestarted > 0 AND (timecompleted = 0 OR timecompleted IS NULL)
                    GROUP BY course)",
                        'base.id = course_completions_courses_started.course',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'totara_stats_courses_completed',
                        'LEFT',
                        "(SELECT course, count(id) AS number
                    FROM {course_completions}
                    WHERE timecompleted > 0
                    GROUP BY course)",
                        'base.id = totara_stats_courses_completed.course',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'totara_stats_courses_enrolled',
                        'LEFT',
                        "(SELECT courseid as course, count(distinct userid) AS number
                    FROM {user_enrolments} ue
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    GROUP BY courseid)",
                        'base.id = totara_stats_courses_enrolled.course',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'totara_stats_courses_enrolled_completion',
                        'LEFT',
                        "(SELECT courseid, count(distinct userid) AS number
                    FROM {user_enrolments} ue
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    INNER JOIN {course} c ON c.id = e.courseid
                    WHERE c.enablecompletion = 1
                    GROUP BY courseid)",
                        'base.id = totara_stats_courses_enrolled_completion.courseid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
        );

        // Include some standard joins.
        $this->add_context_table_to_joinlist($joinlist, 'base', 'id', CONTEXT_COURSE, 'INNER');
        $this->add_course_category_table_to_joinlist($joinlist,
                'base', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'base', 'id');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'base', 'id');

        return $joinlist;
    }

    protected function define_columnoptions() {
        $columnoptions = array(
                new rb_column_option(
                        'course',
                        'mods',
                        get_string('content', 'rbsource_courses'),
                        "mods.list",
                        array('joins' => 'mods', 'displayfunc' => 'modicons')
                ),
        );

        // A column to display the number of started courses for a user
        // We need a COALESCE on the field for 0 to replace nulls, which ensures correct sorting order.
        $columnoptions[] = new rb_column_option(
                'statistics',
                'coursesstarted',
                get_string('userscoursestartedcount', 'rbsource_courses'),
                'COALESCE(course_completions_courses_started.number,0)',
                array(
                        'displayfunc' => 'count',
                        'joins' => 'course_completions_courses_started',
                        'dbdatatype' => 'integer',
                )
        );

        // A column to display the number of completed courses for a user
        // We need a COALESCE on the field for 0 to replace nulls, which ensures correct sorting order.
        $columnoptions[] = new rb_column_option(
                'statistics',
                'coursescompleted',
                get_string('userscoursescompletedcount', 'rbsource_courses'),
                'COALESCE(totara_stats_courses_completed.number,0)',
                array(
                        'displayfunc' => 'count',
                        'joins' => 'totara_stats_courses_completed',
                        'dbdatatype' => 'integer',
                )
        );
        $columnoptions[] = new rb_column_option(
                'statistics',
                'coursesenrolled',
                get_string('userscoursesenrolledcount', 'rbsource_courses'),
                'COALESCE(totara_stats_courses_enrolled.number,0)',
                array(
                        'displayfunc' => 'count',
                        'joins' => 'totara_stats_courses_enrolled',
                        'dbdatatype' => 'integer',
                )
        );
        $columnoptions[] = new rb_column_option(
                'statistics',
                'progressthroughenrolled',
                get_string('progressthroughenrolled', 'rbsource_courses'),
                'COALESCE(totara_stats_courses_enrolled_completion.number,0)',
                array(
                        'extrafields' => ['todo' => 'totara_stats_courses_enrolled_completion.number', 'green' => 'totara_stats_courses_completed.number', 'amber' => 'course_completions_courses_started.number'],
                        'displayfunc' => 'progressbar',
                        'joins' => ['totara_stats_courses_enrolled_completion', 'totara_stats_courses_completed', 'course_completions_courses_started'],
                        'dbdatatype' => 'integer',
                )
        );

        // Include some standard columns.
        $this->add_course_fields_to_columns($columnoptions, 'base');
        $this->add_course_category_fields_to_columns($columnoptions, 'course_category');
        $this->add_core_tag_fields_to_columns('core', 'course', $columnoptions);
        $this->add_cohort_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
                new rb_filter_option(
                        'course',         // type
                        'mods',           // value
                        get_string('coursecontent', 'rbsource_courses'), // label
                        'multicheck',     // filtertype
                        array(            // options
                                'selectfunc' => 'modules_list',
                                'concat' => true, // Multicheck filter need to know that we work with concatenated values
                                'simplemode' => true,
                                'showcounts' => array(
                                        'joins' => array("LEFT JOIN (SELECT course, name FROM {course_modules} cm " .
                                                "LEFT JOIN {modules} m ON m.id = cm.module) course_mods_filter ".
                                                "ON base.id = course_mods_filter.course"),
                                        'dataalias' => 'course_mods_filter',
                                        'datafield' => 'name')
                        )
                )
        );

        // Include some standard filters.
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'course', $filteroptions);
        $this->add_cohort_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array(

                new rb_content_option(
                        'date',
                        get_string('startdate', 'rbsource_courses'),
                        'base.startdate'
                ),
        );

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'base.id'
        );

        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'courseid',
                        'base.id'
                ),
                new rb_param_option(
                        'category',
                        'base.category'
                ),
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'course',
                        'value' => 'courselink',
                ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'course',
                        'value' => 'fullname',
                        'advanced' => 0,
                ),
                array(
                        'type' => 'course_category',
                        'value' => 'path',
                        'advanced' => 0,
                )
        );

        return $defaultfilters;
    }

    protected function define_defaultsearchcolumns() {
        $defaultsearchcolumns = array(
                array(
                        'type' => 'course',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'course',
                        'value' => 'summary',
                ),
        );

        return $defaultsearchcolumns;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();
        $requiredcolumns[] = new rb_column(
                'ctx',
                'id',
                '',
                "ctx.id",
                array('joins' => 'ctx')
        );
        $requiredcolumns[] = new rb_column(
                'base',
                'category',
                '',
                "base.category"
        );
        $requiredcolumns[] = new rb_column(
                'visibility',
                'id',
                '',
                "base.id"
        );
        $requiredcolumns[] = new rb_column(
                'visibility',
                'visible',
                '',
                "base.visible"
        );
        return $requiredcolumns;
    }


    //
    //
    // Source specific column display methods
    //
    //

    function rb_display_modicons($mods, $row, $isexport = false) {
        global $OUTPUT, $CFG;
        $modules = explode('|', $mods);
        $mods = array();

        // Sort module list before displaying to make
        // cells all consistent
        foreach ($modules as $mod) {
            if (empty($mod)) {
                continue;
            }
            $module = new \stdClass();
            $module->name = $mod;
            if (get_string_manager()->string_exists('pluginname', $mod)) {
                $module->localname = get_string('pluginname', $mod);
            } else {
                $module->localname = ucfirst($mod);
            }
            $mods[] = $module;
        }
        core_collator::asort_objects_by_property($mods, 'localname');

        $out = array();
        $glue = '';

        foreach ($mods as $module) {
            if ($isexport) {
                $out[] = $module->localname;
                $glue = ', ';
            } else {
                $glue = '';
                if (file_exists($CFG->dirroot . '/mod/' . $module->name . '/pix/icon.gif') ||
                        file_exists($CFG->dirroot . '/mod/' . $module->name . '/pix/icon.png')) {
                    $out[] = $OUTPUT->pix_icon('icon', $module->localname, $module->name);
                } else {
                    $out[] = $module->name;
                }
            }
        }

        return implode($glue, $out);
    }


    public function post_config(reportbuilder $report) {
        // Don't include the front page (site-level course).
        $categorysql = $report->get_field('base', 'category', 'base.category') . " <> :sitelevelcategory";
        $categoryparams = array('sitelevelcategory' => 0);

        $reportfor = $report->reportfor; // ID of the user the report is for.
        list($visiblesql, $visibleparams) = $report->post_config_visibility_where('course', 'base', $reportfor);

        // Combine the results.
        $report->set_post_config_restrictions(array($categorysql . " AND " . $visiblesql,
                array_merge($categoryparams, $visibleparams)));
    }

} // End of rb_source_courses class.
