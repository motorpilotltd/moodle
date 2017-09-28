<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\admin;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;
use local_costcentre\costcentre as costcentre;

class allstaff extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        // HR_LEADER view restrictions
        $data->ishrleader = has_capability('local/costcentre:administer', \context_system::instance())
                || costcentre::is_user($this->admin->user->id, costcentre::HR_LEADER, $this->admin->groupid);

        $users = $this->admin->get_group_users_allstaff();

        // Default template - possibly overridden later.
        $data->template = 'closed';
        
        $data->cohort = $this->admin->get_cohort_name();
        $data->groupcohort = $this->admin->groupcohort;
        $data->assignedusers = array_values($users->assigned);
        $data->assignedusercount = count($data->assignedusers);
        $data->notassignedusers = array_values($users->notassigned);
        $data->notassignedusercount = count($data->notassignedusers);

        if (!$data->groupcohort->closed) {
            $data->form = new stdClass();

            if ($data->groupcohort->duedate > time()) {
                $data->form->duedate = [
                    'y' => (int) userdate($data->groupcohort->duedate, '%Y', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                    'm' => (int) userdate($data->groupcohort->duedate, '%m', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                    'd' => (int) userdate($data->groupcohort->duedate, '%d', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                ];
            }

            $data->form->hiddeninputs = [
                ['name' => 'page', 'value' => 'allstaff'],
                ['name' => 'groupid', 'value' => $this->admin->groupid],
                ['name' => 'cohortid', 'value' => $this->admin->cohortid],
                ['name' => 'sesskey', 'value' => sesskey()],
            ];
        }
        
        if (!$data->groupcohort->started) {
            $data->template = 'start';
            $data->form->id = 'oa-form-appraisalcycle-start';
            $data->form->confirmtext = get_string('admin:confirm:start', 'local_onlineappraisal');
            $data->form->buttontext = get_string('admin:allstaff:button:start', 'local_onlineappraisal');
            $data->form->buttonprocessing = get_string('admin:startingdots', 'local_onlineappraisal');
            $data->form->hiddeninputs[] = ['name' => 'action', 'value' => 'start'];
        } else if (!$data->groupcohort->locked) {
            $data->template = 'lock';
            $data->form->buttontext = get_string('admin:allstaff:button:lock', 'local_onlineappraisal');
            $data->form->confirmtext = get_string('admin:confirm:lock', 'local_onlineappraisal');
            $data->form->buttonprocessing = get_string('admin:lockingdots', 'local_onlineappraisal');
            $data->form->id = 'oa-form-appraisalcycle-lock';
            $data->form->hiddeninputs[] = ['name' => 'action', 'value' => 'lock'];
        } else if (!$data->groupcohort->closed) {
            $data->template = 'update';
            $data->form->id = 'oa-form-appraisalcycle-update';
            $data->form->buttontext = get_string('admin:allstaff:button:update', 'local_onlineappraisal');
            $data->form->buttonprocessing = get_string('admin:updatingdots', 'local_onlineappraisal');
            $data->form->hiddeninputs[] = ['name' => 'action', 'value' => 'update'];
        }

        return $data;
    }
}
