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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');

class tapsenrol_enrol_user_selector extends user_selector_base {

    private $excludeusersql = '';
    private $excludeuserparams = [];

    public function __construct($name, $options = []) {
        parent::__construct($name);

        if (isset($options['excludeusersql'])) {
            $this->excludeusersql = $options['excludeusersql'];
            // Params only needed/valid if exclude sql is set.
            if (isset($options['excludeuserparams'])) {
                $this->excludeuserparams = $options['excludeuserparams'];
            }
        }

        // Override options.
        $this->preserveselected = true;
        $this->autoselectunique = false;
        $this->searchanywhere = true;

        $this->extrafields = array('idnumber', 'email');
    }

    public function find_users($search) {
        global $DB;

        $whereconditions = array("u.idnumber != ''");
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        if ($wherecondition) {
            $whereconditions[] = $wherecondition;
        }

        if ($this->excludeusersql) {
            $whereconditions[] = "u.id NOT IN ({$this->excludeusersql})";
        }

        if ($whereconditions) {
            $wherecondition = ' WHERE ' . implode(' AND ', $whereconditions);
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(u.id)';

        $sql = " FROM {user} u
                 $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search);
        $order = ' ORDER BY ' . $sort;

        $params = array_merge($params, $this->excludeuserparams);

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        return array(get_string('potentialusers', 'tapsenrol') => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = '/mod/tapsenrol/classes/user_selectors.php';
        return $options;
    }
}