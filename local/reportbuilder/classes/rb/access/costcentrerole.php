<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package local_reportbuilder
 */

namespace local_reportbuilder\rb\access;
use reportbuilder;


/**
 * Role based access restriction
 *
 * Limit access to reports by user role (either in system context or any context)
 */
class costcentrerole extends base {
    /**
     * Get list of reports this user is allowed to access by this restriction class
     * @param int $userid reports for this user
     * @return array of permitted report ids
     */
    public function get_accessible_reports($userid) {
        global $DB;

        $type = $this->get_type();
        $allowedreports = array();

        $sql =  "SELECT rb.id AS reportid, rbs.value AS costcentreroles, rbs2.value as enabled
                   FROM {report_builder} rb
        INNER JOIN {report_builder_settings} rbs
                     ON rb.id = rbs.reportid
                    AND rbs.type = ?
                    AND rbs.name = ?
        INNER JOIN {report_builder_settings} rbs2
                     ON (rbs.reportid = rbs2.reportid
                    AND rbs2.type = ?
                    AND rbs2.name = ?)
                  WHERE rb.embedded = ?";

        $reports = $DB->get_records_sql($sql, array($type, 'costcentreroles', $type, 'enable', 0));


        $usercostcentreroles = [];
        $usercostcentreallocations = $DB->get_records('local_costcentre_user', ['userid' => $userid]);
        if (!empty($usercostcentreallocations)) {
            foreach ($usercostcentreallocations as $allocation) {
                $usercostcentreroles[] = $allocation->permissions;
            }
        }

        if (count($reports) > 0) {
            // Now loop through our reports again checking role permissions.
            foreach ($reports as $rpt) {
                if (empty($rpt->enabled)) {
                    $allowedreports[] = $rpt->reportid;
                    continue;
                }

                $costcentreroles = explode('|', $rpt->costcentreroles);

                foreach ($costcentreroles as $costcentreroleid) {
                    if (in_array($costcentreroleid, $usercostcentreroles)) {
                        $allowedreports[] = $rpt->reportid;
                        break;
                    }
                }
            }
        }
        return $allowedreports;
    }

    /**
     * Adds form elements required for this access restriction's settings page
     *
     * @param \MoodleQuickForm $mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     */
    public function form_template($mform, $reportid) {
        global $DB;

        $type = $this->get_type();

        $costcentreroles = explode('|', reportbuilder::get_setting($reportid, $type, 'costcentreroles'));
        $enabled =reportbuilder::get_setting($reportid, $type, 'enable');

        if (!isset($enabled)) {
            $enabled = false;
        }

        // generate the check boxes for the access form
        $mform->addElement('header', 'accessbycostcentrerole', get_string('accessbycostcentrerole', 'local_reportbuilder'));

        $mform->addElement('checkbox', 'accessbycostcentrerole_enablecondition', get_string('enablecondition', 'local_reportbuilder'));
        $mform->setType('accessbycostcentrerole_enablecondition', PARAM_INT);
        $mform->setDefault('accessbycostcentrerole_enablecondition', $enabled);

        $options = \local_costcentre\costcentre::get_permission_list();
        $settings = array('multiple' => 'multiple', 'size' => 20, 'style' => 'width:600px');
        $mform->addElement('select', 'costcentreroles', get_string('costcentreroles', 'local_reportbuilder'), $options,
                $settings);

        $mform->setDefault('costcentreroles', $costcentreroles);
    }

    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param \MoodleQuickForm $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $type = $this->get_type();
        if (isset($fromform->costcentreroles)) {
            reportbuilder::update_setting($reportid, $type, 'costcentreroles', implode('|', $fromform->costcentreroles));
        }
        reportbuilder::update_setting($reportid, $type, 'enable', !empty($fromform->accessbycostcentrerole_enablecondition));
        return true;
    }
}
