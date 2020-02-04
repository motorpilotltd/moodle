<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Generic filter based on a list of values.
 */
class rb_filter_selectbookingstatus extends rb_filter_select {

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array containing filtering condition SQL clause and params
     */
    function get_sql_filter($data) {
        global $DB;

        $value = explode(',', $data['value']);
        $query = $this->get_field();
        $simplemode = $this->options['simplemode'];

        if ($simplemode) {
            if (count($value) == 1 && current($value) == '') {
                // return 1=1 instead of TRUE for MSSQL support
                return array(' 1=1 ', array());
            } else {
                // use "equal to" operator for simple select
                $operator = 1;
            }
        } else {
            $operator = $data['operator'];
        }

        if ($operator == 0) {
            // return 1=1 instead of TRUE for MSSQL support
            return array(' 1=1 ', array());
        } else if ($operator == 1) {
            // equal
            list($insql, $inparams) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED,
                rb_unique_param('fsequal_'));
            $sql = "{$query} {$insql}";

            if (in_array('Full Attendance', $value)) {
                $sql = "(($sql) OR cpdid IS NOT NULL)";
            }

            return array($sql, $inparams);
        } else {
            // not equal
            list($insql, $inparams) = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED,
                rb_unique_param('fsequal_'), false);
            // check for null case for is not operator
            $sql = "({$query} {$insql} OR ({$query}) IS NULL)";

            if (in_array('Full Attendance', $value)) {
                $sql = "(($sql) OR cpdid IS NULL)";
            }

            return array("{$sql}", $inparams);
        }
    }
}
