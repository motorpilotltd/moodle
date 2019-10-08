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

namespace rbsource_feedbacksummary;
use rb_base_source;
use rb_global_restriction_set;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_param_option;
use rb_content_option;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $sourcetitle;

    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        if ($groupid instanceof rb_global_restriction_set) {
            throw new coding_exception('Wrong parameter orders detected during report source instantiation.');
        }
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        // Apply global user restrictions.
        $this->add_global_report_restriction_join('base', 'userid', 'auser');

        $this->base = '{feedback_completed}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_feedbacksummary');

        parent::__construct();
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return true;
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    protected function define_joinlist() {
        global $DB;

        // get the trainer role's id (or set a dummy value)
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        if (!$trainerroleid) {
            $trainerroleid = 0;
        }

        // joinlist for this source
        $joinlist = array(
                new rb_join(
                        'feedback',
                        'LEFT',
                        '{feedback}',
                        'feedback.id = base.feedback',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'session_value',
                        'LEFT',
                        // subquery as table
                        "(SELECT i.feedback, v.value
                    FROM {feedback_item} i
                    JOIN {feedback_value} v
                        ON v.item=i.id AND i.typ='trainer')",
                        'session_value.feedback = base.feedback',
                        // potentially could be multiple trainer questions
                        // in a feedback instance
                        REPORT_BUILDER_RELATION_ONE_TO_MANY
                ),
                new rb_join(
                        'sessiontrainer',
                        'LEFT',
                        '{facetoface_session_roles}',
                        '(sessiontrainer.userid = ' .
                        $DB->sql_cast_char2int('session_value.value', true) . ' AND ' .
                        "sessiontrainer.roleid = $trainerroleid)",
                        // potentially multiple trainers in a session
                        REPORT_BUILDER_RELATION_ONE_TO_MANY,
                        'session_value'
                ),
                new rb_join(
                        'trainer',
                        'LEFT',
                        '{user}',
                        'trainer.id = sessiontrainer.userid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'sessiontrainer'
                ),
                new rb_join(
                        'trainer_job_assignment',
                        'LEFT',
                        '{job_assignment}',
                        '(trainer_job_assignment.userid = sessiontrainer.userid AND trainer_job_assignment.sortorder = 1)',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'sessiontrainer'
                ),
                new rb_join(
                        'trainer_position',
                        'LEFT',
                        '{pos}',
                        'trainer_position.id = ' .
                        'trainer_job_assignment.positionid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'trainer_job_assignment'
                ),
                new rb_join(
                        'trainer_organisation',
                        'LEFT',
                        '{org}',
                        'trainer_organisation.id = ' .
                        'trainer_job_assignment.organisationid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'trainer_job_assignment'
                ),
        );

        // include some standard joins
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'feedback', 'course');
        // requires the course join
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'feedback', 'course');
        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array(
                new rb_column_option(
                        'responses',
                        'timecompleted',
                        get_string('timecompleted', 'rbsource_feedbacksummary'),
                        'base.timemodified',
                        array('displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp')
                ),
                new rb_column_option(
                        'feedback',
                        'name',
                        get_string('feedbackactivity', 'rbsource_feedbacksummary'),
                        'feedback.name',
                        array('joins' => 'feedback',
                              'dbdatatype' => 'char',
                              'outputformat' => 'text')
                ),
                new rb_column_option(
                        'trainer',
                        'id',
                        get_string('trainerid', 'rbsource_feedbacksummary'),
                        'sessiontrainer.userid',
                        array('joins' => 'sessiontrainer')
                ),
                new rb_column_option(
                        'trainer',
                        'fullname',
                        get_string('trainerfullname', 'rbsource_feedbacksummary'),
                        $DB->sql_fullname('trainer.firstname', 'trainer.lastname'),
                        array('joins' => 'trainer',
                              'dbdatatype' => 'char',
                              'outputformat' => 'text')
                ),
        );
        // include some standard columns
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_core_tag_fields_to_columns('core', 'course', $columnoptions);

        return $columnoptions;
    }


    protected function define_filteroptions() {
        $filteroptions = array(
                new rb_filter_option(
                        'feedback',
                        'name',
                        get_string('feedbackname', 'rbsource_feedbacksummary'),
                        'text'
                ),
                new rb_filter_option(
                        'responses',
                        'timecompleted',
                        get_string('timecompleted', 'rbsource_feedbacksummary'),
                        'date'
                ),
                new rb_filter_option(
                        'trainer',
                        'fullname',
                        get_string('trainerfullname', 'rbsource_feedbacksummary'),
                        'text'
                ),
        );

        // include some standard filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'course', $filteroptions);

        return $filteroptions;
    }


    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'tag',
                get_string('course', 'rbsource_feedbacksummary'),
                'tagids.idlist',
                'tagids'
        );

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('responsetime', 'rbsource_feedbacksummary'),
                'base.timemodified'
        );

        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'userid',         // parameter name
                        'base.userid'     // field
                ),
                new rb_param_option(
                        'courseid',
                        'feedback.course',
                        'feedback'
                ),
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelink',
                        'heading' => get_string('user', 'rbsource_feedbacksummary'),
                ),
                array(
                        'type' => 'course',
                        'value' => 'courselink',
                        'heading' => get_string('coursename', 'rbsource_feedbacksummary'),
                ),
                array(
                        'type' => 'feedback',
                        'value' => 'name',
                ),
                array(
                        'type' => 'responses',
                        'value' => 'timecompleted',
                ),
        );

        return $defaultcolumns;
    }

    protected function define_defaultfilters() {

        $defaultfilters = array(
                array(
                        'type' => 'course',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'feedback',
                        'value' => 'name',
                        'advanced' => 1,
                ),
                array(
                        'type' => 'responses',
                        'value' => 'timecompleted',
                        'advanced' => 1,
                ),
        );


        return $defaultfilters;
    }


    //
    //
    // Methods for adding commonly used data to source definitions
    //
    //

    //
    // Join data
    //

    //
    // Column data
    //

    //
    // Filter data
    //

    //
    //
    // Source specific display functions
    //
    //

    //
    //
    // Source specific filter display methods
    //
    //


} // end of rb_source_feedback_summary class


