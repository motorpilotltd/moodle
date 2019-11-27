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

class plugin_costcentres2 extends plugin_base {

    public function init(){
        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('filtercostcentres2', 'block_configurable_reports');
        $this->reporttypes = array('sql');
    }

    public function summary($data){
        return get_string('filtercostcentres2:summary', 'block_configurable_reports');
    }

    public function execute($finalelements,$data){
        return $this->execute_sql($finalelements, $data);
    }

    private function list_allowed_costcentres(){
        global $USER;
        $allowedcostcentres = array();
        if (get_config('local_costcentre', 'version')) {
            $components = cr_unserialize($this->report->components);
            $permissions = (isset($components['permissions'])) ? $components['permissions'] : [];
            foreach($permissions['elements'] as $pvalue){
                if($pvalue['pluginname'] == "pcostcentres2" && isset($pvalue['formdata']->ccroles)){
                    $selectedroles = $pvalue['formdata']->ccroles;
                }
            }
            if(empty($selectedroles)){
                $selectedroles = optional_param_array(
                        'ccroles',
                        array(
                            \local_costcentre\costcentre::LEARNING_REPORTER
                            ),
                         PARAM_INT);
            }
            $allowedcostcentres = \local_costcentre\costcentre::get_user_cost_centres(
                    $USER->id,
                    $selectedroles
            );
        }
        return $allowedcostcentres;
    }

    private function execute_sql($finalelements, $data) {

        $allowedcostcentres = $this->list_allowed_costcentres();

        $filtercostcentre = optional_param('filter_costcentre2', '', PARAM_BASE64);
        $filter = clean_param(base64_decode($filtercostcentre), PARAM_RAW);

        $filterarray = array();
        if (array_key_exists($filter, $allowedcostcentres)) {
            $filterarray[] = $filter;
        } elseif (!empty($allowedcostcentres) && empty($filter)) {
            $filterarray = array_keys($allowedcostcentres);
        } else {
            $filterarray[] = 'XX-XXX'; // Force a non-match.
        }

        if(preg_match("/%%FILTER_COSTCENTRES2%%/i", $finalelements)){
            $in = "('".implode("', '", $filterarray)."')";
            $replace = " AND u.icq IN $in ";
            return str_replace('%%FILTER_COSTCENTRES2%%', $replace, $finalelements);
        }

        return $finalelements;
    }

    public function print_filter(&$mform, $data){

        $allowedcostcentres = $this->list_allowed_costcentres();

        if (empty($allowedcostcentres)) {
            $mform->addElement(
                'html',
                \html_writer::div(
                    get_string('filtercostcentres2:nocostcentres', 'block_configurable_reports'),
                    'alert alert-danger fade in'
                    )
                );
            return;
        }

        $filteroptions = array();
        $filteroptions[''] = get_string('filter_all', 'block_configurable_reports');

        foreach ($allowedcostcentres as $code => $name) {
            $filteroptions[base64_encode($code)] = $name;
        }

        $mform->addElement('select', 'filter_costcentre2', get_string('filtercostcentres2:costcentre', 'block_configurable_reports'), $filteroptions);
        $mform->setType('filter_costcentre2', PARAM_BASE64);
    }
}