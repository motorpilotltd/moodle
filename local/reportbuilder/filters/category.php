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
 * @author Maria Torres <maria.torres@.t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Generic filter based on a hierarchy.
 */
class rb_filter_category extends rb_filter_type {

    /**
     * Returns an array of comparison operators.
     * @return array of comparison operators
     */
    public function get_operators() {
        return array(0 => get_string('isanyvalue', 'local_reportbuilder'),
                     1 => get_string('isequalto', 'local_reportbuilder'),
                     2 => get_string('isnotequalto', 'local_reportbuilder'));
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setupForm(&$mform) {
        global $SESSION, $CFG;
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        $label = format_string($this->label);
        $advanced = $this->advanced;

        $objs = array();
        $objs[] =& $mform->createElement('select', $this->name.'_op', $label, $this->get_operators());
        $objs[] =& $mform->createElement('static', 'title'.$this->name, '',
            html_writer::tag('span', '', array('id' => $this->name . 'title', 'class' => 'dialog-result-title')));
        $mform->setType($this->name.'_op', PARAM_TEXT);

        $cats = \coursecat::make_categories_list();
        $objs[] = $mform->createElement('autocomplete',  $this->name, $label, $cats, ['multiple' => true]);

        $objs[] =& $mform->createElement('checkbox', $this->name . '_rec', '', get_string('includesubcategories', 'local_reportbuilder'));
        $mform->setType($this->name . '_rec', PARAM_TEXT);

        // Create a group for the elements.
        $grp =& $mform->addElement('group', $this->name.'_grp', $label, $objs, '', false);
        $mform->addHelpButton($grp->_name, 'reportbuilderdialogfilter', 'local_reportbuilder');

        if ($advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        // Set default values.
        if (isset($SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name])) {
            $defaults = $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name];
        }
        if (isset($defaults['operator'])) {
            $mform->setDefault($this->name . '_op', $defaults['operator']);
        }
        if (isset($defaults['value'])) {
            $mform->setDefault($this->name, $defaults['value']);
        }
        if (isset($defaults['recursive'])) {
            $mform->setDefault($this->name . '_rec', $defaults['recursive']);
        }
    }


    /**
     * Retrieves data from the form data.
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        $field = $this->name;
        $operator = $field . '_op';
        $recursive = $field . '_rec';

        if (isset($formdata->$field) && $formdata->$field != '') {
            $data = array('operator' => (int)$formdata->$operator,
                          'value'    => implode(',', $formdata->$field));
            if (isset($formdata->$recursive)) {
                $data['recursive'] = (int)$formdata->$recursive;
            } else {
                $data['recursive'] = 0;
            }

            return $data;
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where.
     * @param array $data filter settings
     * @return array containing filtering condition SQL clause and params
     */
    public function get_sql_filter($data) {
        global $DB;

        $items = explode(',', $data['value']);
        $query = $this->get_field();
        $operator  = $data['operator'];
        $recursive = (isset($data['recursive']) && $data['recursive']) ? '/%' : '';

        // Operators: 1 => Is equal to, 2 => Is not equal to.
        switch($operator) {
            case 1:
                $notlike = false;
                $equal = '=';
                $logicaloperator = 'OR';
                break;
            case 2:
                $notlike = true;
                $equal = '<>';
                $logicaloperator = 'AND';
                break;
            default:
                // Return 1=1 instead of TRUE for MSSQL support.
                return array(' 1=1 ', array());
        }

        // None selected - match everything.
        if (empty($items)) {
            // Using 1=1 instead of TRUE for MSSQL support.
            return array(' 1=1 ', array());
        }

        $count = 1;
        $sql = '';
        $params = array();
        foreach ($items as $itemid) {
            if ($count > 1) { // Don't add on first iteration.
                $sql .= ($notlike) ? ' AND ' : ' OR ';
            }
            $path = $DB->get_field('course_categories', 'path', array('id' => $itemid));
            $uniqueparam  = rb_unique_param("ccp_{$count}");
            $uniqueparam2 = rb_unique_param("ccp2_{$count}");
            $sqlquery = "({$query} {$equal} :{$uniqueparam})";
            $params[$uniqueparam] = $path;
            if (!empty($recursive)) {
                $sqlquery = "({$sqlquery} {$logicaloperator} (" . $DB->sql_like($query, ":{$uniqueparam2}", true, true, $notlike) . '))';
                $params[$uniqueparam2] = $DB->sql_like_escape($path) . $recursive;
            }
            $sql .= $sqlquery;
            $count++;
        }

        return array(' ( ' .$sql . ')', $params);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    public function get_label($data) {
        global $DB;

        $operators = $this->get_operators();
        $operator  = $data['operator'];
        $recursive = $data['recursive'];
        $value  = $data['value'];
        $values = explode(',', $value);
        $label  = $this->label;

        if (empty($operator) || empty($value)) {
            return '';
        }

        $a = new stdClass();
        $a->label = $label;

        $selected = array();
        list($insql, $inparams) = $DB->get_in_or_equal($values);
        if ($categories = $DB->get_records_select('course_categories', "id {$insql}", $inparams)) {
            foreach ($categories as $category) {
                $selected[] = '"' . format_string($category->name) . '"';
            }
        }

        $orandstr = ($operator == 1) ? 'or': 'and';
        $a->value = implode(get_string($orandstr, 'local_reportbuilder'), $selected);
        if ($recursive) {
            $a->value .= get_string('andchildren', 'local_reportbuilder');
        }
        $a->operator = $operators[$operator];

        return get_string('selectlabel', 'local_reportbuilder', $a);
    }
}
