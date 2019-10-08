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
 * Generic filter for textarea fields.
 */
class rb_filter_textarea extends rb_filter_type {

    /**
     * Returns an array of comparison operators
     * @return array of comparison operators
     */
    function getOperators() {
        return array(self::RB_FILTER_CONTAINS => get_string('contains', 'local_reportbuilder'),
                     self::RB_FILTER_DOESNOTCONTAIN => get_string('doesnotcontain', 'local_reportbuilder'),
        );
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        global $SESSION;
        $label = format_string($this->label);
        $advanced = $this->advanced;

        $objs = array();
        $objs['select'] = $mform->createElement('select', $this->name.'_op', null, $this->getOperators());
        $objs['text'] = $mform->createElement('text', $this->name, null);
        $objs['select']->setLabel(get_string('limiterfor', 'local_reportbuilder', $label));
        $objs['text']->setLabel(get_string('valuefor', 'local_reportbuilder', $label));
        $mform->setType($this->name . '_op', PARAM_INT);
        $mform->setType($this->name, PARAM_TEXT);
        $grp =& $mform->addElement('group', $this->name . '_grp', $label, $objs, '', false);
        $mform->addHelpButton($grp->_name, 'filtertext', 'local_reportbuilder');
        if ($advanced) {
            $mform->setAdvanced($this->name . '_grp');
        }

        // set default values
        if (isset($SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name])) {
            $defaults = $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name];
        }
        if (isset($defaults['operator'])) {
            $mform->setDefault($this->name . '_op', $defaults['operator']);
        }
        if (isset($defaults['value'])) {
            $mform->setDefault($this->name, $defaults['value']);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field    = $this->name;
        $operator = $field . '_op';
        $value = (isset($formdata->$field)) ? $formdata->$field : '';
        if (array_key_exists($operator, $formdata)) {
            if ($value == '') {
                // no data - no change except for empty filter
                return false;
            }
            return array('operator' => (int)$formdata->$operator, 'value' => $value);
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array containing filtering condition SQL clause and params
     */
    function get_sql_filter($data) {
        global $CFG;
        require_once($CFG->dirroot . '/local/reportbuilder/searchlib.php');

        $operator = $data['operator'];
        $value    = $data['value'];
        $query    = $this->get_field();

        if ($value === '') {
            return array('', array());
        }

        switch($operator) {
            case self::RB_FILTER_CONTAINS:
                $keywords = totara_search_parse_keywords($value);
                return search_get_keyword_where_clause($query, $keywords);
            case self::RB_FILTER_DOESNOTCONTAIN:
                $keywords = totara_search_parse_keywords($value);
                return search_get_keyword_where_clause($query, $keywords, true);
            default:
                return array('', array());
        }
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        $operator  = $data['operator'];
        $value     = $data['value'];
        $operators = $this->getOperators();
        $label = $this->label;

        $a = new stdClass();
        $a->label    = $label;
        $a->value    = '"' . s($value) . '"';
        $a->operator = $operators[$operator];


        switch ($operator) {
            case self::RB_FILTER_CONTAINS:
            case self::RB_FILTER_DOESNOTCONTAIN:
                return get_string('textlabel', 'local_reportbuilder', $a);
        }

        return '';
    }
}

