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

class plugin_completiondate extends plugin_base{

    protected $_now;
    protected $_start;

    public function init() {
        global $remotedb;

        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('filtercompletiondate', 'block_configurable_reports');
        $this->reporttypes = array('tapscompletion', 'halogencompletion');

        $monthsago3 = strtotime('midnight 3 months ago');

        if ($this->report->courseid == SITEID) {
            $timecreated = $remotedb->get_field_select('course', 'MIN(timecreated)', 'id != :courseid', array('courseid' => $this->report->courseid));
        } else {
            $timecreated = $remotedb->get_field_select('course', 'timecreated', 'id = :courseid', array('courseid' => $this->report->courseid));
        }
        $this->_now = time();
        $this->_start = max($timecreated, $monthsago3);
    }

    public function summary($data){
        return get_string('filtercompletiondate_summary','block_configurable_reports');
    }

    public function execute($rows, $data){
        global $CFG;

        if ($CFG->version < 2011120100) {
            $filter_completiondatefrom = optional_param('filter_completiondatefrom', 0, PARAM_RAW);
            $filter_completiondateto = optional_param('filter_completiondateto', 0, PARAM_RAW);
        } else {
            $filter_completiondatefrom = optional_param_array('filter_completiondatefrom', 0, PARAM_RAW);
            $filter_completiondateto = optional_param_array('filter_completiondateto', 0, PARAM_RAW);
        }

        if(!$filter_completiondatefrom || !$filter_completiondateto) {
            return $rows;
        }

        $filter_completiondatefrom = max(
            make_timestamp(
                $filter_completiondatefrom['year'],
                $filter_completiondatefrom['month'],
                $filter_completiondatefrom['day'],
                0,
                0,
                0
            ),
            $this->_start
        );
        $filter_completiondateto = make_timestamp(
            $filter_completiondateto['year'],
            $filter_completiondateto['month'],
            $filter_completiondateto['day'],
            23,
            59,
            59
        );

        foreach ($rows as $rowindex => $row) {
            if ($row->comptimecompleted < $filter_completiondatefrom || $row->comptimecompleted > $filter_completiondateto) {
                unset($rows[$rowindex]);
            }
        }

        return $rows;
    }

    public function print_filter(&$mform){
        $options = array();
        $options['startyear'] = date('Y', $this->_start);
        $options['stopyear'] = date('Y', $this->_now);

        $mform->addElement('date_selector', 'filter_completiondatefrom', get_string('completiondatefrom', 'block_configurable_reports'), $options);
        $mform->setDefault('filter_completiondatefrom', $this->_start);
        $mform->addElement('date_selector', 'filter_completiondateto', get_string('completiondateto', 'block_configurable_reports'), $options);
        $mform->setDefault('filter_completiondateto', $this->_now);
    }
}
