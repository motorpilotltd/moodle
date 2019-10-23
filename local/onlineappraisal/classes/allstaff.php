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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use local_costcentre\costcentre as costcentre;

class allstaff {

    private $admin;

    private $permittedactions = ['start', 'lock', 'update'];

    /**
     * Constructor.
     *
     * @param object $admin the full admin object
     */
    public function __construct(\local_onlineappraisal\admin $admin) {
        $this->admin = $admin;
    }

    /**
     * Hook
     *
     * This function is called from the main admin controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_onlineappraisal\admin->add_page();
     *
     * @return void
     */
    public function hook() {
        $action = optional_param('action', null, PARAM_ALPHA);

        if (!in_array($action, $this->permittedactions)) {
            return;
        }

        try {
            require_sesskey();

            $groupid = required_param('groupid', PARAM_ALPHANUMEXT);
            $cohortid = required_param('cohortid', PARAM_INT);
            $duedate = required_param('duedate', PARAM_INT);

            $result = $this->{$action}($groupid, $cohortid, $duedate);
            appraisal::set_alert($result->message, 'success');

            redirect($result->url);
        } catch (Exception $e) {
            appraisal::set_alert($e->getMessage(), 'danger');
        }
    }

    /**
     * Start appraisal cycle.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @param int $groupid
     * @param int $cohortid
     * @param int $duedate
     * @return string
     * @throws moodle_exception
     */
    private function start($groupid, $cohortid, $duedate) {
        global $DB, $USER;

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $groupid)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisalcycle:start', 'local_onlineappraisal');
        }

        $cohortinfo = $DB->get_record('local_appraisal_cohort_ccs', ['costcentre' => $groupid, 'cohortid' => $cohortid]);

        if (!$cohortinfo) {
            // Invalid group/cohort.
            throw new moodle_exception('error:appraisalcycle:groupcohort', 'local_onlineappraisal');
        }

        if ($cohortinfo->started) {
            // Already started.
            throw new moodle_exception('error:appraisalcycle:alreadystarted', 'local_onlineappraisal');
        }

        // Close previous cycle.
        $cohorts = $DB->get_records_select(
                'local_appraisal_cohort_ccs',
                'costcentre = :costcentre AND cohortid != :cohortid AND closed IS NULL',
                ['costcentre' => $groupid, 'cohortid' => $cohortid]);
        $now = time();
        foreach ($cohorts as $cohort) {
            if (!$cohort->locked) {
                $cohort->locked = $now;
            }
            $cohort->closed = $now;
            $DB->update_record('local_appraisal_cohort_ccs', $cohort);
        }

        // Archive all existing records for this cost centre where not already assigned to new cycle (i.e. if moved from a diff cost centre).
        $usersubquery = "SELECT id FROM {user} WHERE icq = :groupid";
        $appsubquery = "SELECT appraisalid FROM {local_appraisal_cohort_apps} WHERE cohortid = :cohortid";
        $updatesql = "UPDATE {local_appraisal_appraisal} SET archived = 1 WHERE appraisee_userid IN ($usersubquery) AND id NOT IN ($appsubquery) AND archived = 0";
        $DB->execute($updatesql, ['groupid' => $groupid, 'cohortid' => $cohortid]);

        // Are any (not suspended) users already assigned (from cost centre moves, etc.)?
        $users = $this->admin->get_group_users_allstaff(false);

        // Update record to show started.
        $cohortinfo->started = time();
        if (!empty($users->assigned)) {
            // Lock cycle as users already assigned.
            $cohortinfo->locked = time();
        }
        $cohortinfo->duedate = $duedate;
        $DB->update_record('local_appraisal_cohort_ccs', $cohortinfo);

        $return = new stdClass();
        $url = new moodle_url('/local/onlineappraisal/admin.php', ['page' => 'allstaff', 'groupid' => $groupid, 'cohortid' => $cohortid]);
        $return->url = $url->out(false);
        $return->message = get_string('success:appraisalcycle:start', 'local_onlineappraisal');
        return $return;
    }

    /**
     * Lock users to appraisal cycle.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @param int $groupid
     * @param int $cohortid
     * @param int $duedate
     * @return string
     * @throws moodle_exception
     */
    private function lock($groupid, $cohortid, $duedate) {
        global $DB, $USER;

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $groupid)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisalcycle:lock', 'local_onlineappraisal');
        }

        $cohortinfo = $DB->get_record('local_appraisal_cohort_ccs', ['costcentre' => $groupid, 'cohortid' => $cohortid]);

        if (!$cohortinfo) {
            // Invalid group/cohort.
            throw new moodle_exception('error:appraisalcycle:groupcohort', 'local_onlineappraisal');
        }

        if ($cohortinfo->locked) {
            // Already locked.
            throw new moodle_exception('error:appraisalcycle:alreadylocked', 'local_onlineappraisal');
        }

        // Assign all users who are currently marked as requiring an appraisal.

        $users = $this->admin->get_group_users_allstaff();
        $cohortuser = new stdClass();
        $cohortuser->cohortid = $cohortid;
        foreach ($users->assigned as $user) {
            if ($user->appraisalnotrequired || $user->suspended) {
                $DB->delete_records('local_appraisal_cohort_users', ['cohortid' => $cohortid, 'userid' => $user->id]);
            }
        }
        foreach ($users->notassigned as $user) {
            if (!$user->appraisalnotrequired) {
                $cohortuser->userid = $user->id;
                $DB->insert_record('local_appraisal_cohort_users', $cohortuser);
            }
        }

        // Update record to show locked.
        $cohortinfo->locked = time();
        $cohortinfo->duedate = $duedate;
        $DB->update_record('local_appraisal_cohort_ccs', $cohortinfo);

        $return = new stdClass();
        $url = new moodle_url('/local/onlineappraisal/admin.php', ['page' => 'initialise', 'groupid' => $groupid, 'cohortid' => $cohortid, 'clean' => 1]);
        $return->url = $url->out(false);
        $return->message = get_string('success:appraisalcycle:lock', 'local_onlineappraisal');
        return $return;

    }

    /**
     * Update appraisal cycle.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @param int $groupid
     * @param int $cohortid
     * @param int $duedate
     * @return string
     * @throws moodle_exception
     */
    private function update($groupid, $cohortid, $duedate) {
        global $DB, $USER;

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $groupid)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisalcycle:lock', 'local_onlineappraisal');
        }

        $cohortinfo = $DB->get_record('local_appraisal_cohort_ccs', ['costcentre' => $groupid, 'cohortid' => $cohortid]);

        if (!$cohortinfo) {
            // Invalid group/cohort.
            throw new moodle_exception('error:appraisalcycle:groupcohort', 'local_onlineappraisal');
        }

        if ($cohortinfo->closed) {
            // Closed.
            throw new moodle_exception('error:appraisalcycle:closed', 'local_onlineappraisal');
        }

        // Update record with new due date.
        $cohortinfo->duedate = $duedate;
        $DB->update_record('local_appraisal_cohort_ccs', $cohortinfo);

        $return = new stdClass();
        $url = new moodle_url('/local/onlineappraisal/admin.php', ['page' => 'allstaff', 'groupid' => $groupid, 'cohortid' => $cohortid]);
        $return->url = $url->out(false);
        $return->message = get_string('success:appraisalcycle:update', 'local_onlineappraisal');
        return $return;
    }
}
