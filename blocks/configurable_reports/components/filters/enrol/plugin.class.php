<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

require_once($CFG->dirroot.'/blocks/configurable_reports/plugin.class.php');

class plugin_enrol extends plugin_base{

    public function init(){
        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('filterenrol', 'block_configurable_reports');
        $this->reporttypes = array('enrol');
    }

    public function summary($data){
        return get_string('filterenrol_summary', 'block_configurable_reports');
    }

    public function execute($rows, $data){
        $filter_enrol_type = optional_param('filter_enrol_type', 0, PARAM_INT);
        if ($filter_enrol_type) {
            $filter_enrol = optional_param_array('filter_enrol', array(), PARAM_INT);

            switch ($filter_enrol_type) {
                case 1 :
                    foreach ($rows as $rowindex => $row) {
                        $removerow = true;
                        foreach ($filter_enrol as $enrol => $checked) {
                            if (array_key_exists($enrol, $row->enrolments)) {
                                $removerow = false;
                                break;
                            }
                        }
                        if ($removerow) {
                            unset($rows[$rowindex]);
                        }
                    }
                    break;
                case 2 :
                    foreach ($rows as $rowindex => $row) {
                        foreach ($filter_enrol as $enrol => $checked) {
                            if (!array_key_exists($enrol, $row->enrolments)) {
                                unset($rows[$rowindex]);
                                break;
                            }
                        }
                    }
                    break;
            }
        }
        return $rows;
    }

    public function print_filter(&$mform){
        $filter_enrol_type_options = array(
            0 => get_string('filter_enrol_type_0', 'block_configurable_reports'),
            1 => get_string('filter_enrol_type_1', 'block_configurable_reports'),
            2 => get_string('filter_enrol_type_2', 'block_configurable_reports')
        );

        $enrollist = get_plugin_list('enrol');

        if($enrollist){
            $mform->addElement('select', 'filter_enrol_type', get_string('filter_enrol_type', 'block_configurable_reports'), $filter_enrol_type_options);
            $enrolgroup = array();
            foreach ($enrollist as $enrol => $dir) {
                // No icons for enrol methods.
                $name = get_string('pluginname', 'enrol_'.$enrol);
                $text = $name;
                $enrolgroup[] =& $mform->createElement('checkbox', 'filter_enrol['.$enrol.']', null, $text);
            }
            $mform->addGroup($enrolgroup, 'enrolgroup', 'Choose Enrolment Methods:', '&nbsp;&nbsp;', false);
            $mform->disabledIf('enrolgroup', 'filter_enrol_type', 'eq', 0);
        }
    }
}
