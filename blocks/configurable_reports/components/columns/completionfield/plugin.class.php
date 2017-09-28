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

class plugin_completionfield extends plugin_base{

    public function init() {
        $this->fullname = get_string('completionfield', 'block_configurable_reports');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('completion', 'tapscompletion', 'halogencompletion');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    // Data -> Plugin configuration data.
    // Row -> Complet course row c->id, c->fullname, etc...
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        $datacolumn = str_ireplace('.', '', $data->column);

        if (property_exists($row, $datacolumn)) {
            switch($datacolumn){
                case 'comptimenotified' :
                case 'comptimeenrolled' :
                case 'comptimestarted' :
                case 'comptimecompleted' :
                case 'ufirstaccess' :
                case 'ulastaccess' :
                case 'ulastlogin' :
                case 'ucurrentlogin' :
                case 'utimecreated' :
                case 'utimemodified' :
                case 'cstartdate' :
                case 'ctimecreated' :
                case 'ctimemodified' :
                case 'cctimemodified' :
                    switch ($this->report->type) {
                        case 'tapscompletion' :
                        case 'halogencompletion' :
                            $row->{$datacolumn} = ($row->{$datacolumn}) ? userdate($row->{$datacolumn}, '%d-%b-%Y', 99, false) : '';
                            break;
                        default :
                            $row->{$datacolumn} = ($row->{$datacolumn}) ? userdate($row->{$datacolumn}, '%d %b %Y, %H:%M') : '--';
                            break;
                    }
                    break;
                case 'uidnumber' :
                    switch ($this->report->type) {
                        case 'tapscompletion' :
                            $row->{$datacolumn} = ltrim($row->{$datacolumn}, '0');
                            break;
                    }
                    break;
                case 'compcompletionstatus' :
                    $row->{$datacolumn} = is_null($row->{$datacolumn}) ? 'Not complete' : 'Complete';
                    break;
            }
        }
        return (isset($row->{$datacolumn})) ? $row->{$datacolumn} : '';
    }

}

