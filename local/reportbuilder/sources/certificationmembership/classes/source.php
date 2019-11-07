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

namespace rbsource_certificationmembership;
use rb_base_source;
use rb_content_option;
use rb_join;
use rb_column_option;
use rb_filter_option;
use html_writer;
use moodle_url;
use rb_param_option;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/custom_certification/lib.php');

class source extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        $this->base = $this->define_base();
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->usedcomponents[] = 'local_certification';
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_certificationmembership');

        parent::__construct();
    }

    private function define_base() {
        global $DB;

        $uniqueid = $DB->sql_concat_join("','", array('ccall.userid', 'certif.id'));

        return "(SELECT " . $uniqueid . " AS id, ccall.userid, certif.id AS certifid
                   FROM (SELECT cc.userid, cc.certifid
                           FROM {certif_completions} cc
                          UNION
                         SELECT cch.userid, cch.certifid
                           FROM {certif_completions_archive} cch) ccall
                   JOIN {certif} certif ON ccall.certifid = certif.id)";
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
        return $contentoptions;
    }

    protected function define_joinlist() {
        $joinlist = array();

        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_certification_table_to_joinlist($joinlist, 'base', 'certifid');

        $joinlist[] = new rb_join(
                'certif_completion',
                'LEFT',
                '{certif_completions}',
                "certif_completion.userid = base.userid AND certif_completion.certifid = base.certifid",
                REPORT_BUILDER_RELATION_ONE_TO_ONE
        );

        return $joinlist;
    }

    protected function define_columnoptions() {
        $columnoptions = array();

        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_certification_fields_to_columns($columnoptions, 'certif');

        $columnoptions[] = new rb_column_option(
                'certmembership',
                'status',
                get_string('status', 'rbsource_certificationmembership'),
                'certif_completion.status',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'certif_status',
                )
        );
        $columnoptions[] = new rb_column_option(
                'certmembership',
                'iscertified',
                get_string('iscertified', 'rbsource_certificationmembership'),
                'CASE WHEN certif_completion.certifpath = 1 THEN 1 ELSE 0 END',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'yes_or_no',
                        'dbdatatype' => 'boolean',
                        'defaultheading' => get_string('iscertified', 'rbsource_certificationmembership'),
                )
        );
        $columnoptions[] = new rb_column_option(
                'certmembership',
                'isassigned',
                get_string('isassigned', 'rbsource_certificationmembership'),
                'CASE WHEN certif_completion.id IS NOT NULL THEN 1 ELSE 0 END',
                array(
                        'joins' => 'certif_completion',
                        'displayfunc' => 'yes_or_no',
                        'dbdatatype' => 'boolean',
                )
        );
        $columnoptions[] = new rb_column_option(
                'certmembership',
                'editcompletion',
                get_string('editcompletion', 'rbsource_certificationmembership'),
                'base.id',
                array(
                        'joins' => array('certif_completion'),
                        'displayfunc' => 'edit_completion',
                        'extrafields' => array(
                                'userid' => 'base.userid',
                                'certif' => 'base.certif',
                        ),
                )
        );

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_certification_fields_to_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
                'certmembership',
                'status',
                get_string('status', 'rbsource_certificationmembership'),
                'select',
                array(
                        'selectfunc' => 'status',
                        'attributes' => rb_filter_option::select_width_limiter(),
                )
        );
        $filteroptions[] = new rb_filter_option(
                'certmembership',
                'isassigned',
                get_string('isassigned', 'rbsource_certificationmembership'),
                'select',
                array(
                        'selectfunc' => 'yesno_list',
                        'simplemode' => true,
                )
        );
        $filteroptions[] = new rb_filter_option(
                'certmembership',
                'iscertified',
                get_string('iscertified', 'rbsource_certificationmembership'),
                'select',
                array(
                        'selectfunc' => 'yesno_list',
                        'simplemode' => true,
                )
        );

        return $filteroptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'certif',
                        'base.certif',
                        'base'
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
                        'type' => 'certmembership',
                        'value' => 'status',
                ),
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
                array(
                        'type' => 'certmembership',
                        'value' => 'status',
                        'advanced' => 0,
                ),
        );
        return $defaultfilters;
    }

    public function rb_filter_status() {
        return [
                1 =>  get_string('complete', 'local_custom_certification'),
                0 =>  get_string('incomplete', 'local_custom_certification')
        ];
    }

    public function rb_display_edit_completion($id, $row, $isexport) {
        // Ignores $id == certif_completion id, because the user might have been unassigned and only history records exist.
        if ($isexport) {
            return get_string('editcompletion', 'rbsource_certificationmembership');
        }

        $url = new moodle_url('/totara/certification/edit_completion.php',
                array('id' => $row->progid, 'userid' => $row->userid));
        return html_writer::link($url, get_string('editcompletion', 'rbsource_certificationmembership'));
    }
}
