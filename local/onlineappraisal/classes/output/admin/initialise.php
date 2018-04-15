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

class initialise extends base {

    private $users;
    private $appraiserissupervisor;

    /**
     * Constructor.
     * 
     * @param \local_onlineappraisal\admin $admin
     */
    public function __construct(\local_onlineappraisal\admin $admin) {
        parent::__construct($admin);
        if ($this->admin->groupcohort->closed) {
            // Next bits not needed.
            return;
        }
        $this->users = $this->admin->get_group_users_initialise();
        $this->appraiserissupervisor = costcentre::get_setting($this->admin->groupid, 'appraiserissupervisor');
        if ($this->appraiserissupervisor) {
            $this->check_supervisors(optional_param('clean', false, PARAM_BOOL));
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->groupcohort = $this->admin->groupcohort;
        if ($data->groupcohort->closed) {
            // No need to do any more work.
            return $data;
        }
        if ($data->groupcohort->duedate > time()) {
            $data->duedate = [
                'y' => (int) userdate($data->groupcohort->duedate, '%Y', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                'm' => (int) userdate($data->groupcohort->duedate, '%m', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                'd' => (int) userdate($data->groupcohort->duedate, '%d', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
            ];
        }
        $data->users = array_values($this->users);
        $data->usercount = count($data->users);
        $appraisers = $this->get_users_select('appraiser');
        $signoffs = $this->get_users_select('signoff');
        $data->groupleaderactive = $this->admin->groupleaderactive;
        if ($data->groupleaderactive) {
            $groupleaders = $this->get_users_select('groupleader', 'notrequired');
        }
        if ($this->appraiserissupervisor) {
            $primaryappraiser = 'auid2';
            $fallbackappraiser = 'auid';
        } else {
            $primaryappraiser = 'auid';
            $fallbackappraiser = 'auid2';
        }
        foreach ($data->users as $user) {
            // Deep clone.
            $user->appraisers = unserialize(serialize($appraisers));
            // Remove appraisee.
            unset($user->appraisers->options[$user->id]);
            // Mark previous.
            if (isset($user->appraisers->options[$user->{$primaryappraiser}])) {
                $user->appraisers->options[$user->{$primaryappraiser}]->selected = true;
                $user->appraisers->options[0]->selected = false;
            } else if (isset($user->appraisers->options[$user->{$fallbackappraiser}])) {
                $user->appraisers->options[$user->{$fallbackappraiser}]->selected = true;
                $user->appraisers->options[0]->selected = false;
            }
            // Re-index for mustache.
            $user->appraisers->options = array_values($user->appraisers->options);

            // Deep clone.
            $user->signoffs = unserialize(serialize($signoffs));
            // Remove appraisee.
            unset($user->signoffs->options[$user->id]);
            // Mark selected.
            if (isset($user->signoffs->options[$user->suid])) {
                $user->signoffs->options[$user->suid]->selected = true;
                $user->signoffs->options[0]->selected = false;
            }
            // Re-index for mustache.
            $user->signoffs->options = array_values($user->signoffs->options);

            if ($data->groupleaderactive) {
                // Deep clone.
                $user->groupleaders = unserialize(serialize($groupleaders));
                // Remove appraisee.
                unset($user->groupleaders->options[$user->id]);
                // Mark selected.
                if (isset($user->groupleaders->options[$user->glid])) {
                    $user->groupleaders->options[$user->glid]->selected = true;
                    $user->groupleaders->options[0]->selected = false;
                } else if (count($user->groupleaders->options) === 2) { // Only one actual option.
                    reset($user->groupleaders->options);
                    next($user->groupleaders->options)->selected = true;
                }
                // Re-index for mustache.
                $user->groupleaders->options = array_values($user->groupleaders->options);
            }
        }
        return $data;
    }

    /**
     * If 'appraiserissupervisor' this ensures supervisors have appraiser permissions for the cost centre.
     * It also tidies up those that are no longer required.
     *
     * @param bool $remove
     * @global \moodle_database $DB
     */
    private function check_supervisors($remove = false) {
        global $DB;

        // Used to check for actively in use appraisers from outside cost centre.
        $requiredsql = <<<EOS
SELECT
    DISTINCT aa.appraiser_userid as id, aa.appraiser_userid as auid
FROM
    {local_appraisal_appraisal} aa
JOIN
    {local_appraisal_cohort_apps} aca
    ON aca.appraisalid = aa.id
LEFT JOIN
    {user} u
    ON u.id = aa.appraisee_userid
WHERE
    u.icq = :ccid
    AND aa.deleted = 0
    AND aca.cohortid = :cohortid

EOS;
        $params = [
            'ccid' => $this->admin->groupid,
            'cohortid' => $this->admin->cohortid,
        ];
        $required = $DB->get_records_sql_menu($requiredsql, $params);

        $appraisers = array_keys($this->admin->get_selectable_users('appraiser'));

        foreach ($this->users as $user) {
            if (!$user->auid2) {
                // Could be NULL.
                continue;
            }
            $required[$user->auid2] = $user->auid2;
        }

        foreach ($required as $appraiserid) {
            if (!in_array($appraiserid, $appraisers)) {
                // Need to update permissions (add).
                costcentre::update_user_permissions($appraiserid, $this->admin->groupid, [costcentre::APPRAISER]);
            }
        }

        if ($remove) {
            $ccappraisers = costcentre::get_cost_centre_users($this->admin->groupid, costcentre::APPRAISER);
            foreach (array_keys($ccappraisers) as $ccappraiserid) {
                if (!in_array($ccappraiserid, $required)) {
                    costcentre::update_user_permissions($ccappraiserid, $this->admin->groupid, [], [costcentre::APPRAISER]);
                }
            }
        }
    }
}
