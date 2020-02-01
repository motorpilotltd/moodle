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
 * @package mod_appraisal
 */

namespace rbsource_appraisal;

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
    public $sourcetitle;
    public $contentoptions;

    public function __construct() {
        $this->base = '{local_appraisal_appraisal}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->contentoptions = $this->define_contentoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_appraisal');

        parent::__construct();
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();

        $requiredcolumns[] = new rb_column(
                'user',
                'icq',
                '',
                "auser.icq",
                array(
                        'joins'    => 'auser',
                        'required' => 'true',
                        'hidden'   => 'true'
                )
        );

        return $requiredcolumns;
    }

    public function post_config(\reportbuilder $report) {
        global $DB;

        if (is_siteadmin($report->reportfor)) {
           return;
        }

        $results = $DB->get_records_sql(
                'select costcentre from {local_costcentre_user} where userid = :userid group by costcentre',
                ['userid' => $report->reportfor]
        );

        if (empty($results)) {
            return $report->set_post_config_restrictions(array('1=0', []));
        }

        list($sql, $params) = $DB->get_in_or_equal(array_keys($results), SQL_PARAMS_NAMED);

        // Combine the results.
        $report->set_post_config_restrictions(array("auser.icq $sql", $params));
    }

    /**
     * Define join list
     *
     * @return array
     */
    protected function define_joinlist() {

        $joinlist = array(
                new rb_join(
                        'checkinsstats',
                        'LEFT',
                        '(select appraisalid, count(id) as count from {local_appraisal_checkins} group by appraisalid)',
                        'checkinsstats.appraisalid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'feedback',
                        'LEFT',
                        '{local_appraisal_feedback}',
                        'feedback.appraisalid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'feedbackstats',
                        'LEFT',
                        '(
                            select appraisalid, count(id) as count, SUM(CASE WHEN received_date is not null THEN 1 ELSE 0 END ) as receivedcount
                            from {local_appraisal_feedback} 
                            group by appraisalid
                            )',
                        'feedbackstats.appraisalid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'forms',
                        'LEFT',
                        '{local_appraisal_forms}',
                        'forms.appraisalid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'formstats',
                        'LEFT',
                        '(select appraisalid, count(form_name) fieldcount from {local_appraisal_forms} group by appraisalid)',
                        'formstats.appraisalid = base.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'data',
                        'LEFT',
                        '{local_appraisal_data}',
                        'data.form_id = forms.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'forms'
                )
        );

        // join users, courses and categories
        $this->add_user_table_to_joinlist($joinlist, 'base', 'appraisee_userid', 'auser', true);
        $this->add_user_table_to_joinlist($joinlist, 'base', 'appraiser_userid', 'appraiser');
        $this->add_user_table_to_joinlist($joinlist, 'base', 'signoff_userid', 'signoff');
        $this->add_user_table_to_joinlist($joinlist, 'base', 'groupleader_userid', 'groupleader');

        return $joinlist;
    }

    /**
     * define column options
     *
     * @return array
     */
    protected function define_columnoptions() {
        global $CFG;

        $columnoptions = [
                new rb_column_option(
                        'appraisal',
                        'created_date',
                        get_string('created_date', 'rbsource_appraisal'),
                        "base.created_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'held_date',
                        get_string('held_date', 'rbsource_appraisal'),
                        "base.held_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'completed_date',
                        get_string('completed_date', 'rbsource_appraisal'),
                        "base.completed_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'modified_date',
                        get_string('modified_date', 'rbsource_appraisal'),
                        "base.modified_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'due_date',
                        get_string('due_date', 'rbsource_appraisal'),
                        "base.due_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'face_to_face_held',
                        get_string('face_to_face_held', 'rbsource_appraisal'),
                        "base.face_to_face_held",
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype'  => 'boolean',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'statusid',
                        get_string('statusid', 'rbsource_appraisal'),
                        "base.statusid",
                        array(
                                'displayfunc' => 'appraisalstatus',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'fieldcount',
                        get_string('fieldcount', 'rbsource_appraisal'),
                        "formstats.fieldcount",
                        array(
                                'dbdatatype' => 'integer',
                                'joins'       => 'formstats'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'checkins',
                        get_string('checkins', 'rbsource_appraisal'),
                        "checkinsstats.count",
                        array(
                                'dbdatatype' => 'integer',
                                'joins'       => 'checkinsstats'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'feedback',
                        get_string('feedback', 'rbsource_appraisal'),
                        "feedbackstats.count",
                        array(
                                'dbdatatype' => 'integer',
                                'joins'       => 'feedbackstats'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'feedbackreceived',
                        get_string('feedbackreceived', 'rbsource_appraisal'),
                        "feedbackstats.receivedcount",
                        array(
                                'dbdatatype' => 'integer',
                                'joins'       => 'feedbackstats'
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'archived',
                        get_string('archived', 'rbsource_appraisal'),
                        "base.archived",
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype'  => 'boolean',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'job_title',
                        get_string('job_title', 'rbsource_appraisal'),
                        "base.job_title",
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'operational_job_title',
                        get_string('operational_job_title', 'rbsource_appraisal'),
                        "base.operational_job_title",
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'grade',
                        get_string('grade', 'rbsource_appraisal'),
                        "base.grade",
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'status_history',
                        get_string('status_history', 'rbsource_appraisal'),
                        "base.status_history",
                        array(
                                'dbdatatype' => 'char',
                                'outputformat' => 'text',
                                'displayfunc' => 'appraisalstatushistory',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'successionplan',
                        get_string('successionplan', 'rbsource_appraisal'),
                        "base.successionplan",
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype'  => 'boolean',
                        )
                ),
                new rb_column_option(
                        'appraisal',
                        'leaderplan',
                        get_string('leaderplan', 'rbsource_appraisal'),
                        "base.leaderplan",
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype'  => 'boolean',
                        )
                ),
        ];

        // User, course and category fields.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);

        $this->add_user_fields_to_columns($columnoptions, 'appraiser', 'appraiser', true);
        $this->add_staff_details_to_columns($columnoptions, 'appraiserstaff', 'appraiser', true);

        $this->add_user_fields_to_columns($columnoptions, 'signoff', 'signoff', true);
        $this->add_staff_details_to_columns($columnoptions, 'signoffstaff', 'signoff', true);

        $this->add_user_fields_to_columns($columnoptions, 'groupleader', 'groupleader', true);
        $this->add_staff_details_to_columns($columnoptions, 'groupleaderstaff', 'groupleader', true);

        return $columnoptions;
    }

    /**
     * define filter options
     *
     * @return array
     */
    protected function define_filteroptions() {

        $filteroptions = array(
                new rb_filter_option(
                        'appraisal',
                        'created_date',
                        get_string('created_date', 'rbsource_appraisal'),
                        'date'
                ),
                new rb_filter_option(
                        'appraisal',
                        'held_date',
                        get_string('held_date', 'rbsource_appraisal'),
                        'date'
                ),
                new rb_filter_option(
                        'appraisal',
                        'completed_date',
                        get_string('completed_date', 'rbsource_appraisal'),
                        'date'
                ),
                new rb_filter_option(
                        'appraisal',
                        'modified_date',
                        get_string('modified_date', 'rbsource_appraisal'),
                        'date'
                ),
                new rb_filter_option(
                        'appraisal',
                        'due_date',
                        get_string('due_date', 'rbsource_appraisal'),
                        'date'
                ),
        );

        $allstatusoptions = [];
        for ($i = 1; $i < 10; $i++) {
            $allstatusoptions[$i] = get_string('status:' . $i, 'local_onlineappraisal');
        }

        $filteroptions[] = new rb_filter_option(
                'appraisal',
                'statusid',
                get_string('statusid', 'rbsource_appraisal'),
                'select',
                array(
                        'selectchoices' => $allstatusoptions
                )
        );

        $filteroptions[] = new rb_filter_option(
                'appraisal',
                'archived',
                get_string('archived', 'rbsource_appraisal'),
                'select',
                array(
                        'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                        'simplemode' => true
                )
        );

        // user, course and category filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);

        $this->add_user_fields_to_filters($filteroptions, 'appraiser', true);
        $this->add_staff_fields_to_filters($filteroptions, 'appraiser', true);

        $this->add_user_fields_to_filters($filteroptions, 'signoff', true);
        $this->add_staff_fields_to_filters($filteroptions, 'signoff', true);

        $this->add_user_fields_to_filters($filteroptions, 'groupleader', true);
        $this->add_staff_fields_to_filters($filteroptions, 'groupleader', true);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'base.userid']
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
     * display the appraisalment type
     *
     * @param string $field
     * @param object $record
     * @param boolean $isexport
     */
    public function rb_display_appraisalstatus($id, $record, $isexport) {
        return get_string("status:$id", 'local_onlineappraisal');
    }

    public function rb_display_appraisalstatushistory($ids, $record, $isexport) {
        $ids = explode('|', $ids);

        $retval = [];
        foreach ($ids as $id) {
            $retval[] = get_string("status:$id", 'local_onlineappraisal');
        }
        return implode(', ', $retval);
    }
}
