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

require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Role based access restriction
 *
 * Limit access to reports by user role (either in system context or any context)
 */
class cohort extends base {
    /**
     * Get list of reports this user is allowed to access by this restriction class
     * @param int $userid reports for this user
     * @return array of permitted report ids
     */
    public function get_accessible_reports($userid) {
        global $DB;

        $type = $this->get_type();
        $allowedreports = array();

        $sql =  "SELECT rb.id AS reportid, rbs.value AS cohorts, rbs2.value as enabled
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

        $reports = $DB->get_records_sql($sql, array($type, 'cohorts', $type, 'enable', 0));


        $usercohorts = array_keys(cohort_get_user_cohorts($userid));

        if (count($reports) > 0) {
            // Now loop through our reports again checking role permissions.
            foreach ($reports as $rpt) {
                if (empty($rpt->enabled)) {
                    $allowedreports[] = $rpt->reportid;
                    continue;
                }

                $cohorts = explode('|', $rpt->cohorts);

                foreach ($cohorts as $cohortid) {
                    if (in_array($cohortid, $usercohorts)) {
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
        $type = $this->get_type();

        $cohorts = explode('|', reportbuilder::get_setting($reportid, $type, 'cohorts'));
        $enabled =reportbuilder::get_setting($reportid, $type, 'enable');

        if (!isset($enabled)) {
            $enabled = false;
        }

        // generate the check boxes for the access form
        $mform->addElement('header', 'accessbycohort', get_string('accessbycohort', 'local_reportbuilder'));

        $mform->addElement('checkbox', 'accessbycohort_enablecondition', get_string('enablecondition', 'local_reportbuilder'));
        $mform->setType('accessbycohort_enablecondition', PARAM_INT);
        $mform->setDefault('accessbycohort_enablecondition', $enabled);

        $options = [];
        $allcohorts = cohort_get_all_cohorts(0, 1000);
        foreach ($allcohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = $cohort->name;
        }
        $settings = array('multiple' => 'multiple', 'size' => 20, 'style' => 'width:600px');
        $mform->addElement('select', 'cohorts', get_string('cohorts', 'cohort'), $options,
                $settings);

        $mform->setDefault('cohorts', $cohorts);
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

        if (!empty($fromform->cohorts)) {
            reportbuilder::update_setting($reportid, $type, 'cohorts', implode('|', $fromform->cohorts));
        }
        reportbuilder::update_setting($reportid, $type, 'enable', !empty($fromform->accessbycohort_enablecondition));
        return true;
    }
}
