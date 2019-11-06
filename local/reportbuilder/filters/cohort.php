<?php //$Id$
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
 * @package t0tara
 * @subpackage reportbuilder
 */

require_once($CFG->dirroot.'/local/reportbuilder/filters/lib.php');

/**
 * Filter based on selecting multiple cohorts via a dialog
 */
class rb_filter_cohort extends rb_filter_type {

    /**
     * Returns an array of comparison operators
     * @return array of comparison operators
     */
    function get_operators() {
        return array(0 => get_string('isanyvalue', 'local_reportbuilder'),
                     1 => get_string('matchesanyselected', 'local_reportbuilder'),
                     2 => get_string('matchesallselected', 'local_reportbuilder'));
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setupForm(&$mform) {
        global $SESSION, $CFG, $SITE, $DB;

        require_once("$CFG->dirroot/cohort/lib.php");

        $label = format_string($this->label);
        $advanced = $this->advanced;

        $cohorts = cohort_get_all_cohorts(0, 1000);

        $cohortsbycat = [];
        foreach ($cohorts['cohorts'] as $c) {
            if (!isset($cohortsbycat[$c->contextid])) {
                $cohortsbycat[$c->contextid] = [];
            }
            $cohortsbycat[$c->contextid][] = $c;
        }
        $list = \core_course_category::make_categories_list();

        $catcontmap = $DB->get_records_menu('context', ['contextlevel' => CONTEXT_COURSECAT], 'instanceid', 'instanceid, id');

        $select = [];

        foreach ($cohortsbycat[1] as $c) {
            $select[$c->id] = $c->name;
        }
        foreach ($list as $catid => $category) {
            if (empty($cohortsbycat[$catcontmap[$catid]])) {
                continue;
            }

            foreach ($cohortsbycat[$catcontmap[$catid]] as $c) {
                if ($c->id == $SITE->id) {
                    continue;
                }
                $select[$c->id] = $category . ' / ' .
                        format_string($c->name, true, array('context' => \context_course::instance($c->id)));
            }
        }

        $mform->addElement('autocomplete',  $this->name, $label, $select, ['multiple' => true]);

        if ($advanced) {
            $mform->setAdvanced($this->name);
        }

        // set default values
        if (isset($SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name])) {
            $defaults = $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name];
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

        if (isset($formdata->$field) && !empty($formdata->$field) ) {
            return array('value'    => implode(',', $formdata->$field));
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    function get_sql_filter($data) {
        global $DB;

        $items    = explode(',', $data['value']);
        $query    = $this->get_field();

        // don't filter if none selected
        if (empty($items)) {
            // return 1=1 instead of TRUE for MSSQL support
            return array(' 1=1 ', array());
        }

        // split by comma and look for any items
        // within list
        $res = array();
        $params = array();
        if (is_array($items)) {
            $count = 1;
            foreach ($items as $id) {

                $uniqueparam = rb_unique_param("fcohequal_{$count}_");
                $equals = "{$query} = :{$uniqueparam}";
                $params[$uniqueparam] = $id;

                $uniqueparam = rb_unique_param("fcohendswith_{$count}_");
                $endswithlike = $DB->sql_like($query, ":{$uniqueparam}");
                $params[$uniqueparam] = '%|' . $DB->sql_like_escape($id);

                $uniqueparam = rb_unique_param("fcohstartswith_{$count}_");
                $startswithlike = $DB->sql_like($query, ":{$uniqueparam}");
                $params[$uniqueparam] = $DB->sql_like_escape($id) . '|%';

                $uniqueparam = rb_unique_param("fcohcontains{$count}_");
                $containslike = $DB->sql_like($query, ":{$uniqueparam}");
                $params[$uniqueparam] = '%|' . $DB->sql_like_escape($id) . '|%';

                $res[] = "( {$equals} OR \n" .
                    "    {$endswithlike} OR \n" .
                    "    {$startswithlike} OR \n" .
                    "    {$containslike} )\n";

                $count++;

            }
        }

        // none selected - match everything
        if (count($res) == 0) {
            // using 1=1 instead of TRUE for MSSQL support
            return array(' 1=1 ', array());;
        }

        // combine with OR logic (match any cohort)
        return array('(' . implode(' OR ', $res) . ')', $params);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;
        $value     = $data['value'];
        $values = explode(',', $value);
        $label = $this->label;

        if (empty($values)) {
            return '';
        }

        $a = new stdClass();
        $a->label    = $label;

        $selected = array();
        list($insql, $inparams) = $DB->get_in_or_equal($values);
        if ($cohorts = $DB->get_records_select('cohort', "id {$insql}", $inparams)) {
            foreach ($cohorts as $cohort) {
                $selected[] = '"' . format_string($cohort->name) . '"';
            }
        }

        $orstring = get_string('or', 'local_reportbuilder');
        $a->value    = implode($orstring, $selected);

        return get_string('selectlabelnoop', 'local_reportbuilder', $a);
    }
}
