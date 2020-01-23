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

namespace rbsource_cohortmembers;
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
     */
    public function  __construct() {
        $this->base = '{cohort_members}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = array();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_cohortmembers');

        parent::__construct();
    }

    /**
     * Creates the array of rb_join objects required for this->joinlist
     *
     * @global object $CFG
     * @return array
     */
    private function define_joinlist() {
        global $CFG;

        $joinlist = array(
                new rb_join(
                        'cohort',
                        'INNER',
                        '{cohort}',
                        'base.cohortid = cohort.id',
                        REPORT_BUILDER_RELATION_MANY_TO_MANY
                ),
        );

        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');

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
                'cohort', // Which table? Type.
                'name', // Alias for the field.
                get_string('name', 'cohort'), // Name for the column.
                'cohort.name', // Table alias and field name.
                array('joins'=>array('cohort'),
                      'dbdatatype' => 'char',
                      'outputformat' => 'text') // Options.
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'namelink',
                get_string('namelink', 'rbsource_cohortmembers'),
                'cohort.name',
                array(
                        'displayfunc' => 'cohort_name_link',
                        'extrafields' => array(
                                'cohort_id' => 'cohort.id'
                        ),
                        'joins' => array('cohort')
                )
        );
        $columnoptions[] = new rb_column_option(
                'cohort',
                'idnumber',
                get_string('idnumber', 'cohort'),
                'cohort.idnumber',
                array('joins'=>array('cohort'),
                      'displayfunc' => 'plaintext',
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );

        $this->add_user_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions
     * @return array
     */
    protected function define_filteroptions() {
        global $CFG;
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

        return $filteroptions;
    }


    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'cohort',
                        'value' => 'name',
                ),
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
        $paramoptions = array(new rb_param_option('cohortid', 'base.cohortid'));

        return $paramoptions;
    }

    /**
     * RB helper function to show the name of the cohort with a link to the cohort's details page
     * @param int $cohortid
     * @param object $row
     */
    public function rb_display_cohort_name_link($cohortname, $row) {
        if (empty($cohortname)) {
            return '';
        }
        return \html_writer::link(new moodle_url('/cohort/view.php',
                array('id' => $row->cohort_id)), format_string($cohortname));
    }

    /**
     * RB helper function to show the "action" links for a cohort -- edit/clone/delete
     * @param int $cohortid
     * @param object $row
     * @return string|string
     */
    public function rb_display_cohort_actions($cohortid, $row ) {
        global $OUTPUT;

        static $canedit = null;
        if ($canedit === null) {
            $canedit = has_capability('moodle/cohort:manage', context_system::instance());
        }

        if ($canedit) {
            $editurl = new \moodle_url('/cohort/edit.php', array('id' => $cohortid));
            $str = \html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')));
            $cloneurl = new \moodle_url('/cohort/view.php', array('id' => $cohortid, 'clone' => 1, 'cancelurl' => qualified_me()));
            $str .= \html_writer::link($cloneurl, $OUTPUT->pix_icon('t/copy', get_string('copy', 'cohort')));
            $delurl = new \moodle_url('/cohort/view.php', array('id'=>$cohortid, 'delete' => 1, 'cancelurl' => qualified_me()));
            $str .= \html_writer::link($delurl, $OUTPUT->pix_icon('t/delete', get_string('delete')));

            return $str;
        }
        return '';
    }
}

