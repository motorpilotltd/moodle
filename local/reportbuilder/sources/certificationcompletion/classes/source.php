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

namespace rbsource_certificationcompletion;
use rb_base_source;
use rb_content_option;
use rb_join;
use rb_column_option;
use rb_filter_option;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/custom_certification/lib.php');

class source extends rb_base_source {

    /**
     * Overwrite instance type value of totara_visibility_where() in rb_source_certification->post_config().
     */
    protected $instancetype = 'certification';

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
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_certificationcompletion');
        $this->sourcejoins = $this->get_source_joins();

        parent::__construct();
    }

    protected function define_joinlist() {
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_certification_table_to_joinlist($joinlist, 'base', 'certifid');
        $this->add_course_category_table_to_joinlist($joinlist, 'certif', 'category');

        $this->add_cohort_certification_tables_to_joinlist($joinlist, 'base', 'certifid');

        $joinlist[] = new rb_join(
                'certif_completion',
                'INNER',
                '{certif_completions}',
                "certif_completion.userid = base.userid AND certif_completion.certifid = base.certifid",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                array('base')
        );

        return $joinlist;
    }

    protected function get_source_joins() {
        return ['certif_completion', 'certif'];
    }

    protected function define_contentoptions() {
        $contentoptions = array();
        // Add the time created content option.
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

    protected function define_columnoptions() {
        $columnoptions = array();

        // Add back the columns that were just removed, but suitable for certifications.
        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'status',
                get_string('status', 'rbsource_certificationcompletion'),
                'certif_completion.status',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'certif_status',
                )
        );
        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'iscertified',
                get_string('iscertified', 'rbsource_certificationcompletion'),
                'CASE WHEN certif_completion.certifpath = ' . \local_custom_certification\certification::CERTIFICATIONPATH_RECERTIFICATION . ' THEN 1 ELSE 0 END',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'yes_or_no',
                        'dbdatatype' => 'boolean',
                        'defaultheading' => get_string('iscertified', 'rbsource_certificationcompletion'),
                )
        );
        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'isnotcertified',
                get_string('isnotcertified', 'rbsource_certificationcompletion'),
                'CASE WHEN certif_completion.certifpath <> ' . \local_custom_certification\certification::CERTIFICATIONPATH_RECERTIFICATION . ' THEN 1 ELSE 0 END',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'yes_or_no',
                        'dbdatatype' => 'boolean',
                        'defaultheading' => get_string('isnotcertified', 'rbsource_certificationcompletion'),
                )
        );

        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'assigneddate',
                get_string('dateassigned', 'rbsource_certificationcompletion'),
                'base.timeassigned',
                array('displayfunc' => 'nice_date', 'dbdatatype' => 'timestamp')
        );

        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'timecompleted',
                get_string('completeddate', 'rbsource_certificationcompletion'),
                'base.timecompleted',
                array('displayfunc' => 'nice_date', 'dbdatatype' => 'timestamp')
        );

        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'duedate',
                get_string('duedate', 'rbsource_certificationcompletion'),
                'base.duedate',
                array('displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp')
        );

        $columnoptions[] = new rb_column_option(
                'certcompletion',
                'isassigned',
                get_string('isuserassigned', 'rbsource_certificationcompletion'),
                '(SELECT CASE WHEN COUNT(pua.id) >= 1 THEN 1 ELSE 0 END
                FROM {certif_user_assignment} pua
               WHERE pua.certifid = base.certifid AND pua.userid = base.userid)',
                array(
                        'displayfunc' => 'yes_or_no',
                        'dbdatatype' => 'boolean',
                        'issubquery' => true,
                        'defaultheading' => get_string('isuserassigned', 'rbsource_certificationcompletion')
                )
        );

        // Include some standard columns.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);

        $this->add_certification_fields_to_columns($columnoptions, 'certif');

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'timecompleted',
                get_string('completeddate', 'rbsource_certificationcompletion'),
                'date'
        );

        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'duedate',
                get_string('duedate', 'rbsource_certificationcompletion'),
                'date'
        );

        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'isassigned',
                get_string('isuserassigned', 'rbsource_certificationcompletion'),
                'select',
                array(
                        'selectfunc' => 'yesno_list',
                        'simplemode' => 'true'
                )
        );

        // Include some standard filters.
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_certification_fields_to_filters($filteroptions);

        // Add back the filters that were just removed, but suitable for certifications.
        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'status',
                get_string('status', 'rbsource_certificationcompletion'),
                'select',
                array(
                        'selectfunc' => 'status',
                        'attributes' => rb_filter_option::select_width_limiter(),
                )
        );
        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'iscertified',
                get_string('iscertified', 'rbsource_certificationcompletion'),
                'select',
                array(
                        'selectfunc' => 'yesno_list',
                        'simplemode' => true,
                )
        );
        $filteroptions[] = new rb_filter_option(
                'certcompletion',
                'isnotcertified',
                get_string('isnotcertified', 'rbsource_certificationcompletion'),
                'select',
                array(
                        'selectfunc' => 'yesno_list',
                        'simplemode' => true,
                )
        );

        return $filteroptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelink',
                ),
                array(
                        'type' => 'certcompletion',
                        'value' => 'duedate',
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
                        'type' => 'user',
                        'value' => 'fullname',
                        'advanced' => 0,
                ),
        );
        return $defaultfilters;
    }
}
