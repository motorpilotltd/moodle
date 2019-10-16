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

class plugin_pcostcentres extends plugin_base{

    function init(){
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('pcostcentres', 'block_configurable_reports');
        $this->reporttypes = array('sql');
    }

    function summary($data){
        return get_string('pcostcentres_summary', 'block_configurable_reports');
    }

    function execute($userid, $context, $data){
        if(isset($data->ccroles) && count($data->ccroles) > 0){
            foreach ($data->ccroles as $role) {
                if(\local_costcentre\costcentre::is_user($userid, $role)) {
                    return true;
                }
            }
            return false;
        } else {
            return \local_costcentre\costcentre::is_user(
                $userid,
                array(
                    \local_costcentre\costcentre::LEARNING_REPORTER,
                    \local_costcentre\costcentre::GROUP_LEADER,
                    \local_costcentre\costcentre::HR_ADMIN,
                    \local_costcentre\costcentre::HR_LEADER
                )
            );
        }
    }
}
