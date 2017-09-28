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

require_once($CFG->dirroot.'/blocks/configurable_reports/plugin.class.php');

class plugin_cuserregionfield extends plugin_base{
	
	function init(){
		$this->fullname = get_string('cuserregionfield', 'block_configurable_reports');
		$this->reporttypes = array('users');
		$this->form = true;
	}
		
	function summary($data){
		return get_string($data->field, 'block_configurable_reports').' '.$data->operator.' '.$data->value;
	}
	
	// data -> Plugin configuration data
	function execute($data, $user, $courseid){
		global $DB;

        switch($data->operator){
            case 'IS NULL' :
            case 'IS NOT NULL' :
                $sql = "$data->field $data->operator";
                $params = array();
                break;
            case 'LIKE':
                $sql = $DB->sql_like($data->field, '?');
                $params = array($data->value);
                break;
            case 'NOT LIKE':
                $sql = $DB->sql_like($data->field, '?', true, true, true);
                $params = array($data->value);
                break;
            case 'LIKE % %':
                $sql = $DB->sql_like($data->field, '?');
                $params = array("%{$data->value}%");
                break;
            default:
                $sql = "$data->field $data->operator ?";
                $params = array($data->value);
                break;
        }

        $users = $DB->get_records_select_menu('local_regions_use', $sql, $params, '', 'userid as id, userid');
				
		return $users;
	}
}
