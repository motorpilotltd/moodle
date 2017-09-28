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

class plugin_mods extends plugin_base{

    public function init(){
        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('filtermods', 'block_configurable_reports');
        $this->reporttypes = array('mods');
    }

    public function summary($data){
        return get_string('filtermods_summary', 'block_configurable_reports');
    }

    public function execute($rows, $data){
        $filter_mod_type = optional_param('filter_mod_type', 0, PARAM_INT);
        if ($filter_mod_type) {
            $filter_mods = optional_param_array('filter_mods', array(), PARAM_INT);

            switch ($filter_mod_type) {
                case 1 :
                    foreach ($rows as $rowindex => $row) {
                        $removerow = true;
                        foreach ($filter_mods as $filter_mod => $checked) {
                            if (array_key_exists($filter_mod, $row->activities)) {
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
                        foreach ($filter_mods as $filter_mod => $checked) {
                            if (!array_key_exists($filter_mod, $row->activities)) {
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
        global $remotedb, $OUTPUT;

        $filter_mod_type_options = array(
            0 => get_string('filter_mod_type_0', 'block_configurable_reports'),
            1 => get_string('filter_mod_type_1', 'block_configurable_reports'),
            2 => get_string('filter_mod_type_2', 'block_configurable_reports')
        );

        $modlist = $remotedb->get_records('modules');

        if($modlist){
            $mform->addElement('select', 'filter_mod_type', get_string('filter_mod_type', 'block_configurable_reports'), $filter_mod_type_options);
            $modgroup = array();
            foreach($modlist as $mod){
                $name = get_string('modulename', $mod->name);
                $text = '<img title="'
                        . $name
                        . '" alt="'
                        . $name
                        . '" class="activityicon" src="'
                        . $OUTPUT->pix_url('icon', $mod->name)
                        . '"> '
                        . $name;
                $modgroup[] =& $mform->createElement('checkbox', 'filter_mods['.$mod->name.']', null, $text);
            }
            $mform->addGroup($modgroup, 'modgroup', 'Choose activities:', '&nbsp;&nbsp;', false);
            $mform->disabledIf('modgroup', 'filter_mod_type', 'eq', 0);
        }
    }

}

