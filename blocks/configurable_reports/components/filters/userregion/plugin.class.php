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

class plugin_userregion extends plugin_base {

    protected $_regionsinstalled = false;

    public function init(){
        if (get_config('local_regions', 'version')) {
            $this->_regionsinstalled = true;
        }
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('filteruserregion', 'block_configurable_reports');
        $this->reporttypes = $this->_regionsinstalled ? array('completion', 'tapscompletion', 'halogencompletion', 'users') : array();
    }

    public function summary($data){
        return get_string($data->field, 'block_configurable_reports');
    }

    public function execute($finalelements, $data){
        global $DB;

        if (!$this->_regionsinstalled) {
            return $finalelements;
        }

        $filter_userregion = optional_param('filter_userregion_'.$data->field, 0, PARAM_RAW);

        if(!$filter_userregion) {
            return $finalelements;
        }

        list($usql, $uparams) = $DB->get_in_or_equal($finalelements);
        $select = "{$data->field} = ? AND userid $usql";
        $params = array_merge(array($filter_userregion), $uparams);

        $filterusers = $DB->get_records_select_menu('local_regions_use', $select, $params, '', 'id, userid');

        return array_intersect($finalelements, $filterusers);
    }

    public function print_filter(&$mform, $data){
        global $remotedb;

        if (!$this->_regionsinstalled) {
            $mform->addElement('html', get_string('userregionunavailable'));
            return;
        }

        $columns = array(
            'regionid',
            'actregionid',
            'georegionid',
            'statusflag'
        );

        if(!in_array($data->field, $columns)) {
            print_error('nosuchcolumn');
        }

        $filteroptions = array();
        $filteroptions[''] = get_string('filter_all', 'block_configurable_reports');
        switch ($data->field) {
            case 'regionid' :
            case 'georegionid' :
                $filteroptions = $filteroptions + $remotedb->get_records_menu('local_regions_reg', array('userselectable' => 1), 'name ASC', 'id, name');
                break;
            case 'actregionid' :
                $filteroptions = $filteroptions + $remotedb->get_records_menu('local_regions_reg', array(), 'name ASC', 'id, name');
                break;
            case 'statusflag' :
                $sql = "SELECT DISTINCT(statusflag) FROM {local_regions_use} WHERE statusflag != '' ORDER BY statusflag ASC";
                $rs = $remotedb->get_recordset_sql($sql);
                if ($rs){
                    foreach($rs as $record) {
                        $filteroptions[base64_encode($record->statusflag)] = $record->statusflag;
                    }
                    $rs->close();
                }
                break;
        }

        $selectname = get_string($data->field, 'block_configurable_reports');
        $mform->addElement('select', 'filter_userregion_'.$data->field, $selectname, $filteroptions);
    }

}

