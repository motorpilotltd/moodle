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

namespace rbsource_certification;
use rb_base_source;
use rb_join;
use rb_column;
use rb_content_option;
use reportbuilder;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/custom_certification/lib.php');

class source extends rb_base_source {

    /**
     * Overwrite instance type value of totara_visibility_where() in rb_source_certification->post_config().
     */
    protected $instancetype = 'certification';

    public function __construct() {
        $this->base = '{certif}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_certification');
        list($this->sourcewhere, $this->sourceparams) = $this->define_sourcewhere();

        parent::__construct();
    }

    protected function define_columnoptions() {
        $columnoptions = [];
        // Include some standard columns, override parent so they say certification.
        $this->add_certification_fields_to_columns($columnoptions, 'base');
        $this->add_course_category_fields_to_columns($columnoptions, 'course_category');

        $columnoptions[] = new \rb_column_option(
                'certif',
                'cohortnames',
                get_string('cohortnames', 'rbsource_certification'),
                "cohorts.cohortnames",
                array('joins' => 'cohorts')
        );

        return $columnoptions;
    }

    protected function define_joinlist() {
        global $DB;

        $assignment_type_audience = \local_custom_certification\certification::ASSIGNMENT_TYPE_AUDIENCE;
        $concat = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat('ca.name', ', ', 'ca.name ASC');

        $joinlist = array(
                new rb_join(
                        'ctx',
                        'INNER',
                        '{context}',
                        'ctx.instanceid = base.category AND ctx.contextlevel = ' . CONTEXT_COURSECAT,
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'cohorts',
                        'LEFT',
                        "(SELECT certifid, $concat as cohortnames
                                FROM (
                                    select ca.certifid, c.name 
                                    from {certif_assignments} ca 
                                    INNER JOIN {cohort} c on ca.assignmenttypeid = c.id 
                                    where assignmenttype = $assignment_type_audience 
                                    group by ca.certifid, c.name 
                                ) ca
                                GROUP BY ca.certifid)",
                        'cohorts.certifid = base.id',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
        );

        $this->add_course_category_table_to_joinlist($joinlist, 'base', 'category');
        $this->add_cohort_certification_tables_to_joinlist($joinlist, 'base', 'id');

        return $joinlist;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        // Include some standard filters, override parent so they say certification.
        $this->add_certification_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'certif',
                        'value' => 'certifexpandlink',
                ),
                array(
                        'type' => 'course_category',
                        'value' => 'namelink',
                ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'certif',
                        'value' => 'fullname',
                        'advanced' => 0,
                ),
                array(
                        'type' => 'course_category',
                        'value' => 'path',
                        'advanced' => 0,
                ),
        );
        return $defaultfilters;
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

        // Visibility.
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

        /**
     * Define some extra SQL for the base to limit the data set.
     *
     * @return array The SQL and parmeters that defines the WHERE for the source.
     */
    protected function define_sourcewhere() {
        $params = array ();
        $sql = 'base.deleted = 0';

        // Ensure SQL is wrapped in brackets, otherwise our where statements will bleed into each other.
        return array("({$sql})", $params);
    }
    
    public function post_config(reportbuilder $report) {
        $reportfor = $report->reportfor; // ID of the user the report is for.
        $report->set_post_config_restrictions($report->post_config_visibility_where($this->instancetype, 'base', $reportfor));
    }
}
