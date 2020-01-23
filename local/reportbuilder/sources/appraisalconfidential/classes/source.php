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

namespace rbsource_appraisalconfidential;

use rb_base_source;
use rb_content_option;
use rb_join;
use rb_column_option;
use rb_filter_option;
use rb_column;

defined('MOODLE_INTERNAL') || die();

class source extends \rbsource_appraisal\source {

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $defaultcolumns, $defaultfilters, $requiredcolumns;
    public $sourcetitle;
    public $contentoptions;

    public function __construct() {
        parent::__construct();

        $this->sourcetitle = get_string('sourcetitle', 'rbsource_appraisalconfidential');
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = parent::define_requiredcolumns();

        $requiredcolumns[] = new rb_column(
                'appraisal',
                'vip',
                '',
                "vips.value",
                array(
                        'joins'    => 'vips',
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

        $requiredroles = [
                \local_costcentre\costcentre::HR_LEADER,
                \local_costcentre\costcentre::HR_ADMIN,
                \local_costcentre\costcentre::GROUP_LEADER,
                \local_costcentre\costcentre::SIGNATORY
        ];

        $bitand = [];
        foreach ($requiredroles as $role) {
            $bitand[] = $DB->sql_bitand('permissions',  $role) . ' = ' .$role;
        }
        $bitand = '(' . implode(' OR ', $bitand) . ')';


        $results = $DB->get_records_sql(
                "select costcentre from {local_costcentre_user} where userid = :userid AND $bitand group by costcentre",
                ['userid' => $report->reportfor]
        );

        if (empty($results)) {
            $report->set_post_config_restrictions(array('1=0', []));
            return;
        }

        list($sql, $nonvipparams) = $DB->get_in_or_equal(array_keys($results), SQL_PARAMS_NAMED);
        $nonvipssql = " (auser.icq $sql AND (vips.value is null or vips.value = 0))";

        $bitand = $DB->sql_bitand('permissions',  \local_costcentre\costcentre::HR_LEADER) . ' = ' . \local_costcentre\costcentre::HR_LEADER;
        $groupleaderresults = $DB->get_records_sql(
                "select costcentre from {local_costcentre_user} where userid = :userid AND $bitand group by costcentre",
                ['userid' => $report->reportfor]
        );

        if (!empty($groupleaderresults)) {
            list($sql, $vipparams) = $DB->get_in_or_equal(array_keys($groupleaderresults), SQL_PARAMS_NAMED);
            $vipssql = " (auser.icq $sql AND (vips.value = 1))";
            $sql = "($nonvipssql OR $vipssql)";
            $params = $nonvipparams + $vipparams;
        } else {
            $sql = $nonvipssql;
            $params = $nonvipparams;
        }

        // Combine the results.
        $report->set_post_config_restrictions(array("$sql", $params));
    }

    /**
     * Define join list
     *
     * @return array
     */
    protected function define_joinlist() {

        $joinlist = parent::define_joinlist();

        $joinlist[] = new rb_join(
                'checkins',
                'LEFT',
                '{local_appraisal_checkins}',
                'checkins.appraisalid = base.id',
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        $joinlist[] = new rb_join(
                'comments',
                'LEFT',
                '{local_appraisal_comment}',
                'comments.appraisalid = base.id',
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        $joinlist[] = new rb_join(
                'vips',
                'INNER',
                '{local_appraisal_users}',
                "setting = 'appraisalvip' and userid = base.appraisee_userid",
                REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        $addedforms = [];
        foreach ($this->get_form_fields() as $field) {
            $formjoinname = 'local_appraisal_forms_' . $field->form_name;
            if (!in_array($field->form_name, $addedforms)) {
                $joinlist[] = new rb_join(
                        $formjoinname,
                        'LEFT',
                        '{local_appraisal_forms}',
                        "$formjoinname.appraisalid = base.id and $formjoinname.form_name = '$field->form_name'",
                        REPORT_BUILDER_RELATION_ONE_TO_MANY
                );
                $addedforms[] = $field->form_name;
            }

            $name = "local_appraisal_data_{$field->form_name}_{$field->field_name}";
            $joinlist[] = new rb_join(
                    $name,
                    'LEFT',
                    '{local_appraisal_data}',
                    "$name.form_id = $formjoinname.id and $name.name = '$field->field_name'",
                    REPORT_BUILDER_RELATION_ONE_TO_MANY,
                    $formjoinname
            );
        }

        return $joinlist;
    }

    private function get_form_fields() {
        static $fields;

        if (!isset($fields)) {
            global $DB;

            $concat = $DB->sql_concat('form_name', 'name');

            $sql = "SELECT $concat, form_name, name as field_name
                FROM {local_appraisal_forms} f 
                inner join {local_appraisal_data} d on f.id = d.form_id
                group by form_name, name
                order by form_name, name";

            $fields = $DB->get_records_sql($sql);
        }

        return $fields;
    }

    /**
     * define column options
     *
     * @return array
     */
    protected function define_columnoptions() {
        $columnoptions = parent::define_columnoptions();

        $fields = $this->get_form_fields();

        foreach ($fields as $key => $field) {
            $sm = get_string_manager();
            if ($sm->string_exists("pdf:form:$field->form_name:$field->field_name", 'local_onlineappraisal')) {
                $label = get_string("pdf:form:$field->form_name:$field->field_name", 'local_onlineappraisal');
            } else if ($sm->string_exists("form:$field->form_name:$field->field_name", 'local_onlineappraisal')) {
                $label = get_string("form:$field->form_name:$field->field_name", 'local_onlineappraisal');
            } else {
                $label = $field->field_name;
            }

            $header =
                    get_string($field->form_name, 'local_onlineappraisal')
                    . ' - '
                    . $label;

            $joinname = "local_appraisal_data_{$field->form_name}_{$field->field_name}";
            $columnoptions[] = new rb_column_option(
                    'appraisalcontents',
                    $key,
                    $header,
                    $joinname . '.data',
                    array(
                            'displayfunc' => 'appraisaldata',
                            'joins'       => $joinname,
                            'extrafields' => ['type' => $joinname . '.type']
                    )
            );
        }

        $columnoptions[] =
                new rb_column_option(
                        'comments',
                        'created_date',
                        get_string('comment_created_date', 'rbsource_appraisalconfidential'),
                        "comments.created_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp',
                                'joins'       => 'comments'
                        )
                );

        $columnoptions[] =
                new rb_column_option(
                        'comments',
                        'comment',
                        get_string('comment', 'rbsource_appraisalconfidential'),
                        "comments.comment",
                        array(
                                'dbdatatype'   => 'char',
                                'outputformat' => 'text',
                                'joins'        => 'comments'
                        )
                );

        $columnoptions[] =
                new rb_column_option(
                        'checkins',
                        'created_date',
                        get_string('checkin_created_date', 'rbsource_appraisalconfidential'),
                        "checkins.created_date",
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp',
                                'joins'       => 'checkins'
                        )
                );

        $columnoptions[] =
                new rb_column_option(
                        'checkins',
                        'checkin_user_type',
                        get_string('checkin_user_type', 'rbsource_appraisalconfidential'),
                        "checkins.user_type",
                        array(
                                'dbdatatype'   => 'char',
                                'outputformat' => 'text',
                                'joins'        => 'checkins'
                        )
                );

        $columnoptions[] =
                new rb_column_option(
                        'appraisal',
                        'vip',
                        get_string('vip', 'rbsource_appraisalconfidential'),
                        "vips.value",
                        array(
                                'displayfunc' => 'yes_or_no',
                                'dbdatatype'  => 'boolean',
                                'joins'        => 'vips'
                        )
                );
        return $columnoptions;
    }

    /**
     * define filter options
     *
     * @return array
     */
    protected function define_filteroptions() {

        $filteroptions = parent::define_filteroptions();

        return $filteroptions;
    }

    public function rb_display_appraisaldata($data, $row, $isexport) {
        if ($row->type == 'normal') {
            return $data;
        } else if ($row->type == 'array') {
            $decoded = unserialize($data);
            if (!$decoded) {
                return $data;
            }
            return implode(', ', $decoded);
        }
    }
}
