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

class inprogress extends base {

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
        $this->users = $this->admin->get_group_appraisals('inprogress');
        $this->appraiserissupervisor = costcentre::get_setting($this->admin->groupid, 'appraiserissupervisor');
        if ($this->appraiserissupervisor) {
            $this->check_supervisors();
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
        $data->users = array_values($this->users);
        $data->usercount = count($data->users);
        $appraisers = $this->get_users_select('appraiser');
        $signoffs = $this->get_users_select('signoff');
        $data->groupleaderactive = $this->admin->groupleaderactive;
        if ($data->groupleaderactive) {
            $groupleaders = $this->get_users_select('groupleader', 'notrequired');
        }
        foreach ($data->users as $user) {
            $user->progress = $this->get_progress($user->statusid);
            if (!empty($user->held_date)) {
                $user->held_date_array = array(
                    'y' => (int) userdate($user->held_date, '%Y', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                    'm' => (int) userdate($user->held_date, '%m', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                    'd' => (int) userdate($user->held_date, '%d', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                );
                $user->held_date = userdate($user->held_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
            } else {
                $user->held_date = '-';
            }
            // Deep clone.
            $user->appraisers = unserialize(serialize($appraisers));
            // Remove appraisee.
            unset($user->appraisers->options[$user->uid]);
            // Mark selected.
            if (isset($user->appraisers->options[$user->auid])) {
                $user->appraisers->options[$user->auid]->selected = true;
                $user->appraisers->options[0]->selected = false;
            }
            // Re-index for mustache.
            $user->appraisers->options = array_values($user->appraisers->options);
            // Mark as hidden.
            $user->appraisers->hidden = true;

            // Deep clone.
            $user->signoffs = unserialize(serialize($signoffs));
            // Remove appraisee.
            unset($user->signoffs->options[$user->uid]);
            // Mark selected.
            if (isset($user->signoffs->options[$user->suid])) {
                $user->signoffs->options[$user->suid]->selected = true;
                $user->signoffs->options[0]->selected = false;
            }
            // Re-index for mustache.
            $user->signoffs->options = array_values($user->signoffs->options);
            // Mark as hidden.
            $user->signoffs->hidden = true;

            if ($data->groupleaderactive) {
                // Deep clone.
                $user->groupleaders = unserialize(serialize($groupleaders));
                // Remove appraisee.
                unset($user->groupleaders->options[$user->uid]);
                // Mark selected.
                if (isset($user->groupleaders->options[$user->glid])) {
                    $user->groupleaders->options[$user->glid]->selected = true;
                    $user->groupleaders->options[0]->selected = false;
                }
                // Re-index for mustache.
                $user->groupleaders->options = array_values($user->groupleaders->options);
                // Mark as hidden.
                $user->groupleaders->hidden = true;
            }
        }
        return $data;
    }

    /**
     * If 'appraiserissupervisor' this ensures supervisors have appraiser permissions for the cost centre.
     * It also tidies up those that are no longer required.
     *
     * @global \moodle_database $DB
     */
    private function check_supervisors() {
        global $DB;

        $appraisers = array_keys($this->admin->get_selectable_users('appraiser'));

        foreach ($this->users as $user) {
            $staffid = (int) $user->idnumber;
$supsql = <<<EOS
SELECT
    h.SUP_EMPLOYEE_NUMBER
FROM
    SQLHUB.ARUP_ALL_STAFF_V h
WHERE
    h.EMPLOYEE_NUMBER =  :staffid
EOS;
            $supidnumber = $DB->get_field_sql($supsql, ['staffid' => $staffid]);
            $supid = $DB->get_field('user', 'id', ['idnumber' => str_pad($supidnumber, 6, '0', STR_PAD_LEFT), 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0]);
            if ($supid && !in_array($supid, $appraisers)) {
                // Need to update permissions (add).
                costcentre::update_user_permissions($supid, $this->admin->groupid, [costcentre::APPRAISER]);
            }
        }
    }
}
