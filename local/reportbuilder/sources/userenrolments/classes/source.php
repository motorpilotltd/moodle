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

namespace rbsource_userenrolments;
use core\plugininfo\enrol;
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
        $this->base = '(SELECT ue.userid as id, e.courseid as course, min(timestart) as timestart, max(timeend) as timeend, min(ue.timecreated) as timecreated, max(ue.timemodified) as timemodified
                    FROM {user_enrolments} ue
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    GROUP BY userid)';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_userenrolments');

        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    protected function define_joinlist() {
        global $CFG;

        // to get access to constants
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');

        $joinlist = array(
                new rb_join(
                        'coursecompletions',
                        'LEFT',
                        '{course_completions}',
                        '(coursecompletions.course = base.course and coursecompletions.userid = base.id)',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'enrol',
                        'LEFT',
                        '{enrol}',
                        'enrol.id = base.enrolid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'criteria',
                        'LEFT',
                        '{course_completion_criteria}',
                        '(criteria.course = base.course AND ' .
                        'criteria.criteriatype = ' .
                        COMPLETION_CRITERIA_TYPE_GRADE . ')',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'critcompl',
                        'LEFT',
                        '{course_completion_crit_compl}',
                        '(critcompl.userid = base.id AND ' .
                        'critcompl.criteriaid = criteria.id AND ' .
                        '(critcompl.deleted IS NULL OR critcompl.deleted = 0)',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'criteria'
                ),
                new rb_join(
                        'grade_items',
                        'LEFT',
                        '{grade_items}',
                        '(grade_items.courseid = base.course AND ' .
                        'grade_items.itemtype = \'course\')',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'grade_grades',
                        'LEFT',
                        '{grade_grades}',
                        '(grade_grades.itemid = grade_items.id AND ' .
                        'grade_grades.userid = base.id)',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'grade_items'
                ),
        );

        // include some standard joins
        $this->add_user_table_to_joinlist($joinlist, 'base', 'id');
        $this->add_course_table_to_joinlist($joinlist, 'base', 'course', 'INNER');
        // requires the course join
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'base', 'course');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'base', 'course');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;
        $columnoptions = array(
                new rb_column_option(
                        'enrol',
                        'name',
                        get_string('enrolname', 'rbsource_userenrolments'),
                        'enrol.name',
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                                'joins' => 'enrol')
                ),
                new rb_column_option(
                        'enrol',
                        'plugin',
                        get_string('enrolplugin', 'rbsource_userenrolments'),
                        'enrol.enrol',
                        array(
                                'displayfunc' => 'enrolpluginname',
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                                'joins' => 'enrol')
                ),
                new rb_column_option(
                        'course_completion',
                        'status',
                        get_string('completionstatus', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted is null then -1 WHEN coursecompletions.timecompleted = 0 then 0 WHEN coursecompletions.timecompleted > 0 then 1 END ',
                        array('displayfunc' => 'completion_status', 'joins' => 'coursecompletions')
                ),
                new rb_column_option(
                        'course_completion',
                        'iscomplete',
                        get_string('iscompleteany', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted > 0 THEN 1 ELSE 0 END',
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype' => 'boolean',
                                'defaultheading' => get_string('iscomplete', 'rbsource_userenrolments'),
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'isnotcomplete',
                        get_string('isnotcomplete', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted > 0 then 0 ELSE 1 END ',
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype' => 'boolean',
                                'defaultheading' => get_string('isnotcomplete', 'rbsource_userenrolments'),
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'isinprogress',
                        get_string('isinprogress', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted = 0 then 1 ELSE 0 END ',
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype' => 'boolean',
                                'defaultheading' => get_string('isinprogress', 'rbsource_userenrolments'),
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'isnotyetstarted',
                        get_string('isnotyetstarted', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted IS NULL THEN 1 ELSE 0 END',
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype' => 'boolean',
                                'defaultheading' => get_string('isnotyetstarted', 'rbsource_userenrolments'),
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'completeddate',
                        get_string('completiondate', 'rbsource_userenrolments'),
                        'coursecompletions.timecompleted',
                        array('displayfunc' => 'nice_date', 'dbdatatype' => 'timestamp', 'joins' => 'coursecompletions')
                ),
                new rb_column_option(
                        'course_completion',
                        'starteddate',
                        get_string('datestarted', 'rbsource_userenrolments'),
                        'coursecompletions.timestarted',
                        array('displayfunc' => 'nice_date', 'dbdatatype' => 'timestamp', 'joins' => 'coursecompletions')
                ),
                new rb_column_option(
                        'course_completion',
                        'enrolleddate',
                        get_string('dateenrolled', 'rbsource_userenrolments'),
                        'base.timestart',
                        array('displayfunc' => 'nice_date', 'dbdatatype' => 'timestamp', 'joins' => 'coursecompletions')
                ),
                new rb_column_option(
                        'course_completion',
                        'timecompletedsincestart',
                        get_string('timetocompletesincestart', 'rbsource_userenrolments'),
                        "CASE WHEN coursecompletions.timecompleted IS NULL OR coursecompletions.timecompleted = 0 THEN null
                      ELSE coursecompletions.timecompleted - coursecompletions.timestarted END",
                        array(
                                'displayfunc' => 'duration',
                                'dbdatatype' => 'integer',
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'timecompletedsinceenrol',
                        get_string('timetocompletesinceenrol', 'rbsource_userenrolments'),
                        "CASE WHEN coursecompletions.timecompleted IS NULL OR coursecompletions.timecompleted = 0 THEN null
                      ELSE coursecompletions.timecompleted - coursecompletions.timestart END",
                        array(
                                'displayfunc' => 'duration',
                                'dbdatatype' => 'integer',
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'grade',
                        get_string('grade', 'rbsource_userenrolments'),
                        'grade_grades.finalgrade',
                        array(
                                'joins' => 'grade_grades',
                                'extrafields' => array(
                                        'maxgrade' => 'grade_grades.rawgrademax',
                                        'mingrade' => 'grade_grades.rawgrademin',
                                        'status' => 'CASE WHEN coursecompletions.timecompleted is null then -1 WHEN coursecompletions.timecompleted = 0 then 0 WHEN coursecompletions.timecompleted > 0 then 1 END'
                                ),
                                'displayfunc' => 'course_grade_percent',
                                'joins' => 'coursecompletions'
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'passgrade',
                        get_string('passgrade', 'rbsource_userenrolments'),
                        '(((criteria.gradepass - grade_items.grademin) / (grade_items.grademax - grade_items.grademin)) * 100)',
                        array(
                                'joins' => ['criteria', 'grade_items'],
                                'displayfunc' => 'percent',
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'gradestring',
                        get_string('requiredgrade', 'rbsource_userenrolments'),
                        'grade_grades.finalgrade',
                        array(
                                'joins' => array('criteria', 'grade_grades'),
                                'displayfunc' => 'grade_string',
                                'extrafields' => array(
                                        'gradepass' => 'criteria.gradepass',
                                        'grademax' => 'grade_items.grademax',
                                        'grademin' => 'grade_items.grademin',
                                ),
                                'defaultheading' => get_string('grade', 'rbsource_userenrolments'),
                        )
                ),
                new rb_column_option(
                        'course_completion',
                        'done',
                        get_string('progresspercent', 'rbsource_userenrolments'),
                        'CASE WHEN coursecompletions.timecompleted is null then 0 WHEN coursecompletions.timecompleted = 0 then 50 WHEN coursecompletions.timecompleted > 0 then 100 END',
                        array(
                                'displayfunc' => 'course_progress',
                                'extrafields' => array('todo' => 100),
                                'defaultheading' => get_string('progress', 'rbsource_userenrolments'),
                        )
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
                        'course_completion',
                        'completeddate',
                        get_string('datecompleted', 'rbsource_userenrolments'),
                        'date'
                ),
                new rb_filter_option(
                        'course_completion',
                        'starteddate',
                        get_string('datestarted', 'rbsource_userenrolments'),
                        'date'
                ),
                new rb_filter_option(
                        'course_completion',
                        'enrolleddate',
                        get_string('dateenrolled', 'rbsource_userenrolments'),
                        'date'
                ),
                new rb_filter_option(
                        'course_completion',
                        'status',
                        get_string('completionstatus', 'rbsource_userenrolments'),
                        'multicheck',
                        array(
                                'selectfunc' => 'completion_status_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                                'showcounts' => array(
                                        'joins' => array("LEFT JOIN {course_completions} ccs_filter ON coursecompletions.id = ccs_filter.id"),
                                        'dataalias' => 'ccs_filter',
                                        'datafield' => 'CASE WHEN coursecompletions.timecompleted is null then -1 WHEN coursecompletions.timecompleted = 0 then 0 WHEN coursecompletions.timecompleted > 0 then 1 END')
                        )
                ),
                new rb_filter_option(
                        'course_completion',
                        'iscomplete',
                        get_string('iscompleteany', 'rbsource_userenrolments'),
                        'select',
                        array(
                                'selectfunc' => 'yesno_list',
                                'simplemode' => true,
                        )
                ),
                new rb_filter_option(
                        'course_completion',
                        'isnotcomplete',
                        get_string('isnotcomplete', 'rbsource_userenrolments'),
                        'select',
                        array(
                                'selectfunc' => 'yesno_list',
                                'simplemode' => true,
                        )
                ),
                new rb_filter_option(
                        'course_completion',
                        'isinprogress',
                        get_string('isinprogress', 'rbsource_userenrolments'),
                        'select',
                        array(
                                'selectfunc' => 'yesno_list',
                                'simplemode' => true,
                        )
                ),
                new rb_filter_option(
                        'course_completion',
                        'isnotyetstarted',
                        get_string('isnotyetstarted', 'rbsource_userenrolments'),
                        'select',
                        array(
                                'selectfunc' => 'yesno_list',
                                'simplemode' => true,
                        )
                ),
                new rb_filter_option(
                        'course_completion',
                        'grade',
                        get_string('grade', 'rbsource_userenrolments'),
                        'number'
                ),
                new rb_filter_option(
                        'course_completion',
                        'passgrade',
                        'Required Grade',
                        'number'
                ),
                new rb_filter_option(
                        'course_completion',
                        'enrolled',
                        get_string('isenrolled', 'rbsource_userenrolments'),
                        'enrol',
                        array(),
                        // special enrol filter requires a composite field
                        array('course' => 'coursecompletions.course', 'user' => 'coursecompletions.userid')
                ),
                new rb_filter_option(
                        'course_completion',
                        'enrolltype',
                        get_string('courseenroltypes', 'local_reportbuilder'),
                        'text',
                        array(
                                'cachingcompatible' => false, // Current filter code is not compatible with aggregated columns.
                        )
                ),
                new rb_filter_option(
                        'enrol',
                        'name',
                        get_string('enrolname', 'rbsource_userenrolments'),
                        'text'
                ),
                new rb_filter_option(
                        'enrol',
                        'plugin',
                        get_string('enrolplugin', 'rbsource_userenrolments'),
                        'select',
                        array(
                                'selectfunc' => 'enrolplugins',
                                'simplemode' => true,
                        )
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
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('completiondate', 'rbsource_userenrolments'),
                'coursecompletions.timecompleted'
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
                'base.course'
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
                        'coursecompletions.userid',  // field
                        null            // joins
                ),
                new rb_param_option(
                        'courseid',
                        'coursecompletions.course'
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
                        'type' => 'course',
                        'value' => 'courselink',
                ),
                array(
                        'type' => 'course_completion',
                        'value' => 'status',
                ),
                array(
                        'type' => 'course_completion',
                        'value' => 'completeddate',
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
                        'type' => 'course',
                        'value' => 'fullname',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'course_category',
                        'value' => 'path',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'course_completion',
                        'value' => 'completeddate',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'course_completion',
                        'value' => 'status',
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
                array()     // options
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

    function rb_display_completion_status($status, $row, $isexport) {
        if ($status == 1) {
            return get_string('complete', 'rbsource_coursecompletion');
        } else if ($status == 0) {
            return get_string('incomplete', 'rbsource_coursecompletion');
        } else {
            return get_string('notstarted', 'rbsource_coursecompletion');
        }
    }

    function rb_display_course_progress($status, $row, $isexport) {
        if ($isexport) {
            global $PAGE;

            $renderer = $PAGE->get_renderer('local_reportbuilder');
            $content = (array)$renderer->export_course_progress_for_template($row->userid, $row->courseid, $status);

            $percent = '';
            if (isset($content['percent'])){
                $percent = $content['percent'];
            } else if (isset($content['statustext'])) {
                $percent = $content['statustext'];
            }

            if ($row->numericonly || !is_numeric($percent)) {
                return $percent;
            }

            return get_string('xpercentcomplete', 'local_reportbuilder', $percent);
        }

        return totara_display_course_progress_bar($row->userid, $row->courseid, $status);
    }

    function rb_display_enrolpluginname($name, $row, $isexport) {
        return get_string('pluginname', 'enrol_' . $name);
    }

    function rb_filter_enrolplugins() {
        global $CFG;

        $enabled = explode(',', $CFG->enrol_plugins_enabled);

        $yn = array();
        foreach($enabled as $plugin) {
            $yn[$plugin] = get_string('pluginname', 'enrol_' . $plugin);
        }
        return $yn;
    }

    //
    //
    // Source specific filter display methods
    //
    //

    function rb_filter_completion_status_list() {
        return [
                1 =>  get_string('complete', 'rbsource_coursecompletion'),
                0 =>  get_string('incomplete', 'rbsource_coursecompletion')
        ];
    }
} // end of rb_source_course_completion class

