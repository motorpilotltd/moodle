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

namespace local_admin\form;

defined('MOODLE_INTERNAL') || die();

class user_report_filter extends \moodleform {

    public function definition() {
        global $DB;

        $mform =& $this->_form;

        // Choose action.
        $actions = $DB->get_records_menu('local_admin_user_update_log', null, 'action ASC', 'DISTINCT action, action as action2');
        $actionselement = $mform->addElement(
                'select',
                'actions',
                get_string('userreport:actions', 'local_admin'),
                array('' => '') + $actions,
                array('class' => 'select2', 'data-placeholder' => get_string('userreport:actions:choose', 'local_admin')));
        $actionselement->setMultiple(true);

        // Choose status.
        $statuses = $DB->get_records_menu('local_admin_user_update_log', null, 'status ASC', 'DISTINCT status, status as status2');
        $statuseselement = $mform->addElement(
                'select',
                'statuses',
                get_string('userreport:statuses', 'local_admin'),
                array('' => '') + $statuses,
                array('class' => 'select2', 'data-placeholder' => get_string('userreport:statuses:choose', 'local_admin')));
        $statuseselement->setMultiple(true);

        // Date range.
        $earliestdate = $DB->get_field('local_admin_user_update_log', 'MIN(timecreated)', []);
        $latestdate = $DB->get_field('local_admin_user_update_log', 'MAX(timecreated)', []);
        $options = [
            'optional' => true,
            'startyear' => gmdate('Y', $earliestdate),
            'stopyear' => gmdate('Y', $latestdate),
            'timezone' => 0,
        ];
        $mform->addElement('date_selector', 'from', get_string('userreport:from', 'local_admin'), $options);
        $mform->addElement('date_selector', 'to', get_string('userreport:to', 'local_admin'), $options);

        // Submit buttons.
        $this->add_action_buttons(false, get_string('userreport:apply_filters', 'local_admin'));
    }
}
