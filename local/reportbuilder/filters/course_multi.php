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
 * @author Brendan Cox <brendan.cox@.t0taralms.com>
 * @package local_reportbuilder
 */

/**
 * Class that allows for filtering of by multiple courses at once.
 */
class rb_filter_course_multi extends rb_filter_type {

    // Constants relating to comparison operators for this filter.
    const COURSE_MULTI_ANYVALUE   = 0;
    const COURSE_MULTI_EQUALTO    = 1;
    const COURSE_MULTI_NOTEQUALTO = 2;

    /**
     * Returns an array of comparison operators.
     */
    public function get_operators() {
        return array(self::COURSE_MULTI_ANYVALUE   => get_string('isanyvalue', 'local_reportbuilder'),
                     self::COURSE_MULTI_EQUALTO    => get_string('isequalto', 'local_reportbuilder'),
                     self::COURSE_MULTI_NOTEQUALTO => get_string('isnotequalto', 'local_reportbuilder'));
    }

    public function __construct($type, $value, $advanced, $region, $report) {
        global $SESSION;
        parent::__construct($type, $value, $advanced, $region, $report);

        // We need to check the user has permission to view the courses in the saved
        // search as these may be a search created by someone else who can view
        // a different selection of courses.
        if (isset($SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name])) {
            $defaults = $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name];

            if (isset($defaults['value'])) {
                $courseids = array_filter(explode(',', $defaults['value']));

                $defaults['value'] = implode(',', $courseids);

                // Even if operator is set, if there are no more course ids after checking what the user
                // can view, we set the operator to 'Is any value'.
                if (!isset($defaults['operator']) || empty($courseids)) {
                    $defaults['operator'] = self::COURSE_MULTI_ANYVALUE;
                }

            } else {
                $defaults['value'] = '';
                $defaults['operator'] = self::COURSE_MULTI_ANYVALUE;
            }

            $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name] = $defaults;
        }

    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setupForm(&$mform) {
        global $SESSION, $DB, $SITE, $CFG;
        require_once("$CFG->dirroot/lib/coursecatlib.php");

        $label = format_string($this->label);
        $advanced = $this->advanced;

        $objs = array();
        $objs[] =& $mform->createElement('select', $this->name.'_op', $label, $this->get_operators());
        $objs[] =& $mform->createElement('static', 'title'.$this->name, '',
            html_writer::tag('span', '', array('id' => $this->name . 'title', 'class' => 'dialog-result-title')));
        $mform->setType($this->name.'_op', PARAM_TEXT);

        $courses = $DB->get_records('course', ['visible' => true], 'category, fullname');

        $coursesbycat = [];
        foreach ($courses as $c) {
            if (!isset($coursesbycat[$c->category])) {
                $coursesbycat[$c->category] = [];
            }
            $coursesbycat[$c->category][] = $c;
        }
        $list = \coursecat::make_categories_list();

        $select = [];
        foreach ($list as $catid => $category) {
            foreach ($coursesbycat[$catid] as $c) {
                if ($c->id == $SITE->id) {
                    continue;
                }
                $select[$c->id] = $category . ' / ' .
                        format_string($c->fullname, true, array('context' => \context_course::instance($c->id)));
            }
        }

        $objs[] = $mform->createElement('autocomplete',  $this->name, $label, $select, ['multiple' => true]);

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
    }

    /**
     * Retrieves data from the form data.
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        $field = $this->name;
        $operator = $field . '_op';

        if (isset($formdata->$field) && $formdata->$field != '') {
            $data = array('operator' => (int)$formdata->$operator,
                'value'    => implode(',', $formdata->$field));

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

        $courseids = explode(',', $data['value']);
        $query = $this->get_field();
        $operator  = $data['operator'];

        switch($operator) {
            case self::COURSE_MULTI_EQUALTO:
                $equal = true;
                break;
            case self::COURSE_MULTI_NOTEQUALTO:
                $equal = false;
                break;
            default:
                // Return 1=1 instead of TRUE for MSSQL support.
                return array(' 1=1 ', array());
        }

        // None selected - match everything.
        if (empty($courseids)) {
            // Using 1=1 instead of TRUE for MSSQL support.
            return array(' 1=1 ', array());
        }

        list($insql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid', $equal);
        $sql = ' ('.$query.') '.$insql;

        return array($sql, $params);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    public function get_label($data) {
        global $DB;

        $operator  = $data['operator'];
        $values = explode(',', $data['value']);

        if (empty($operator) || empty($values)) {
            return '';
        }

        $a = new stdClass();
        $a->label = $this->label;

        $selected = array();
        list($insql, $inparams) = $DB->get_in_or_equal($values);
        if ($courses = $DB->get_records_select('course', "id ".$insql, $inparams)) {
            foreach ($courses as $course) {
                $selected[] = '"' . format_string($course->fullname) . '"';
            }
        }

        $orandstr = ($operator == self::COURSE_MULTI_EQUALTO) ? 'or': 'and';
        $a->value = implode(get_string($orandstr, 'local_reportbuilder'), $selected);
        $operators = $this->get_operators();
        $a->operator = $operators[$operator];

        return get_string('selectlabel', 'local_reportbuilder', $a);
    }
}
