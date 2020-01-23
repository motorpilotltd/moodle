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

namespace rbsource_cohort;
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

    /**
     * Constructor
     * @global object $CFG
     */
    public function __construct() {
        // Global restrictions are applied in define_joinlist() method.

        $this->base = '{cohort}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = array();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_cohort');

        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    /**
     * Creates the array of rb_join objects required for this->joinlist
     *
     * @global object $CFG
     * @return array
     */
    protected function define_joinlist() {
        $joinlist = array(
                new rb_join(
                        'members', // Table alias?
                        'LEFT', // Type of join.
                        "{cohort_members}",
                        'base.id = members.cohortid', // How it is joined.
                        REPORT_BUILDER_RELATION_ONE_TO_MANY
                ),
                new rb_join(
                        'membercount',
                        'LEFT', // Type of join.
                        "(SELECT cohortid, count(cm2.id) AS count FROM {cohort_members} cm2 GROUP BY cohortid)",
                        'base.id = membercount.cohortid', // How it is joined.
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'context',
                        'INNER',
                        '{context}',
                        "context.id = base.contextid",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE
                ),
                new rb_join(
                        'course_category',
                        'LEFT',
                        '{course_categories}',
                        "(course_category.id = context.instanceid AND context.contextlevel = ". CONTEXT_COURSECAT . ")",
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'context'
                )
        );

        $this->add_user_table_to_joinlist($joinlist, 'members', 'userid');
        $this->add_core_tag_tables_to_joinlist('core', 'cohort', $joinlist, 'base', 'id');

        return $joinlist;
    }

    /**
     * Creates the array of rb_column_option objects required for
     * $this->columnoptions
     *
     * @return array
     */
    protected function define_columnoptions() {
        $columnoptions = array();

        $columnoptions[] = new rb_column_option(
                'cohort',  // Which table? Type.
                'name', // Alias for the field.
                get_string('name', 'cohort'), // Name for the column.
                'base.name', // Table alias and field name.
                array('dbdatatype' => 'char',
                      'outputformat' => 'text') // Options.
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'namelink',
                get_string('namelink', 'rbsource_cohort'),
                'base.name',
                array(
                        'displayfunc' => 'cohort_name_link',
                        'extrafields' => array(
                                'cohort_id' => 'base.id'
                        )
                )
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'idnumber',
                get_string('idnumber', 'cohort'),
                'base.idnumber',
                array('dbdatatype' => 'char',
                      'displayfunc' => 'plaintext',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'numofmembers',
                get_string('numofmembers', 'rbsource_cohort'),
                'CASE WHEN membercount.count IS NULL THEN 0 ELSE membercount.count END',
                array(
                        'joins' => array('membercount'),
                        'dbdatatype' => 'integer'
                )
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'actions',
                get_string('actions', 'rbsource_cohort'),
                'base.id',
                array(
                        'displayfunc' => 'cohort_actions',
                        'extrafields' => array('contextid' => 'base.contextid', 'component' => 'base.component'),
                        'nosort' => true,
                        'noexport' => true
                )
        );
        $columnoptions[] = new rb_column_option(
                'course_category',
                'name',
                get_string('coursecategory', 'local_reportbuilder'),
                "course_category.name",
                array('joins' => 'course_category',
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'course_category',
                'namelink',
                get_string('coursecategorylinked', 'local_reportbuilder'),
                "course_category.name",
                array(
                        'joins' => 'course_category',
                        'displayfunc' => 'link_cohort_category',
                        'defaultheading' => get_string('category', 'local_reportbuilder'),
                        'extrafields' => array('context_id' => 'base.contextid')
                )
        );
        $columnoptions[] = new rb_column_option(
                'course_category',
                'id',
                get_string('coursecategoryid', 'local_reportbuilder'),
                "course_category.id",
                array('joins' => 'course_category')
        );

        $this->add_user_fields_to_columns($columnoptions);
        $this->add_core_tag_fields_to_columns('core', 'cohort', $columnoptions);

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions
     * @return array
     */
    protected function define_filteroptions() {
        // No filter options!
        $filteroptions = array();
        $filteroptions[] = new rb_filter_option(
                'cohort',
                'name',
                get_string('name', 'cohort'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
                'cohort',
                'idnumber',
                get_string('idnumber', 'cohort'),
                'text'
        );
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'cohort', $filteroptions);

        return $filteroptions;
    }


    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                )
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                        'advanced' => 0,
                ),
        );

        return $defaultfilters;
    }
    /**
     * Creates the array of rb_content_option object required for $this->contentoptions
     * @return array
     */
    protected function define_contentoptions() {
        $contentoptions = array();

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
                        'cohortid', // Parameter name.
                        'base.id'  // Field.
                ),
                new rb_param_option(
                        'contextid', // Parameter name.
                        'base.contextid'  // Field.
                ),
        );
        return $paramoptions;
    }

    function rb_display_link_cohort_category($categoryname, $row, $isexport = false) {

        $categoryname = format_string($categoryname);

        $contextid = $row->context_id;
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);

        if (!$context) {
            return $categoryname;
        }

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $categoryname = \context_system::get_level_name();
        }

        if ($isexport) {
            return $categoryname;
        }

        if (!has_any_capability(array('moodle/cohort:manage', 'moodle/cohort:view'), $context)) {
            return $categoryname;
        }

        $url = new \moodle_url('/cohort/index.php', array('contextid' => $context->id));
        return \html_writer::link($url, $categoryname);
    }

    /**
     * RB helper function to show the name of the cohort with a link to the cohort's details page
     * @param int $cohortid
     * @param object $row
     */
    public function rb_display_cohort_name_link($cohortname, $row ) {
        if (empty($cohortname)) {
            return '';
        }
        return \html_writer::link(new \moodle_url('/cohort/view.php', array('id' => $row->cohort_id)), format_string($cohortname));
    }

    /**
     * RB helper function to show the "action" links for a cohort -- edit/clone/delete
     * @param int $cohortid
     * @param stdClass $row
     * @return string
     */
    public function rb_display_cohort_actions($cohortid, $row) {
        global $OUTPUT;

        $contextid = $row->contextid;
        if ($contextid) {
            $context = context::instance_by_id($contextid);
        } else {
            $context = context_system::instance();
        }

        if (!has_capability('moodle/cohort:manage', $context)) {
            return '';
        }

        $str = '';
        if (empty($row->component)) {
            $editurl = new \moodle_url('/cohort/edit.php', array('id' => $cohortid));
            $str .= \html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')));
        }
        $cloneurl = new \moodle_url('/cohort/view.php', array('id' => $cohortid, 'clone' => 1, 'cancelurl' => qualified_me()));
        $str .= \html_writer::link($cloneurl, $OUTPUT->pix_icon('t/copy', get_string('copy', 'cohort')));
        $delurl = new \moodle_url('/cohort/view.php', array('id' => $cohortid, 'delete' => 1, 'cancelurl' => qualified_me()));
        $str .= \html_writer::link($delurl, $OUTPUT->pix_icon('t/delete', get_string('delete')));
        return $str;
    }
}

// End of rb_source_user class
