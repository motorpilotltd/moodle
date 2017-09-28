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

class plugin_userregionfield extends plugin_base {

    protected $_regionsinstalled = false;

    function init() {
        if (get_config('local_regions', 'version')) {
            $this->_regionsinstalled = true;
        }
        $this->fullname = get_string('userregionfield', 'block_configurable_reports');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = $this->_regionsinstalled ? array('completion', 'tapscompletion', 'halogencompletion', 'users') : array();
    }

    function summary($data) {
        return format_string($data->columname);
    }

    function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    // Data -> Plugin configuration data.
    // Row -> Complet course row c->id, c->fullname, etc...
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB;

        if (!$this->_regionsinstalled) {
            return '';
        }

        $regions = $DB->get_records('local_regions_reg');

        switch ($this->report->type) {
            case 'completion' :
            case 'tapscompletion' :
            case 'halogencompletion' :
                $idfield = $row->compuserid;
                break;
            case 'users' :
            default :
                $idfield = $row->id;
                break;
        }

        $userregionfields = $DB->get_record('local_regions_use', array('userid' => $idfield));

        if ($userregionfields) {
            foreach ($userregionfields as $fieldname => $fielddata) {
                switch($fieldname){
                    case 'acttapsregionid' :
                    case 'geotapsregionid' :
                    case 'regionid' :
                        $row->{'lru_'.$fieldname} = isset($regions[$fielddata]) ? $regions[$fielddata]->name : '';
                        break;
                    case 'statusflag' :
                        $row->{'lru_'.$fieldname} = $fielddata ? $fielddata : get_string('statusflag:null', 'block_configurable_reports');
                        break;
                    default :
                        $row->{'lru_'.$fieldname} = $fielddata ? $fielddata : '';
                        break;
                }
            }
        }

        return (isset($row->{$data->column})) ? $row->{$data->column} : '';
    }
}
