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

class navbarmenu {

    /**
     * Appraisal user types.
     * @var array $types
     */
    private static $types = array ('appraisee', 'appraiser', 'signoff', 'groupleader', 'hrleader', 'guest', 'businessadmin', 'costcentreadmin', 'feedback');

    public function __construct() {

    }

    /**
     * Find info on the users roles on the appraisals found in the appraisal DB
     * @return array $appraisal records with added fields
     */
    private function all_my_appraisalroles($user) {
        global $DB;

        $baseapproles = new stdClass();
        $baseapproles->is = false;
        $baseapproles->active = 0;
        $baseapproles->new = 0; // Only used for appraisee.

        $myapproles = new stdClass();

        foreach (self::$types as $type) {
            // Shallow clone OK here.
            $myapproles->$type = clone($baseapproles);
        }

        // Find Appraisal instance roles.
        $sql = "SELECT aa.*, u.icq as costcentre FROM {local_appraisal_appraisal} aa
                JOIN {user} u ON u.id = aa.appraisee_userid
                WHERE aa.deleted = 0
                    AND (
                        aa.appraisee_userid = ?
                        OR aa.appraiser_userid = ?
                        OR aa.signoff_userid = ?
                        OR aa.groupleader_userid = ?
                    )";

        $params = array($user->id, $user->id, $user->id, $user->id);
    
        if ($appraisals = $DB->get_records_sql($sql, $params)) {
            foreach ($appraisals as $appraisal) {
                if ($appraisal->appraisee_userid == $user->id) {
                    $myapproles->appraisee->is = true;
                    // Active if not archived (Should only ever be one).
                    $myapproles->appraisee->active += (int) !$appraisal->archived;
                    // Is it new.
                    if (!$appraisal->archived && $appraisal->statusid == 1) { // Use int here not constant as locallib.php may not be loaded.
                        $myapproles->appraisee->new++;
                    }
                }
                if ($appraisal->appraiser_userid == $user->id) {
                    $myapproles->appraiser->is = true;
                    // Active if not archived and status 3 or 5.
                    $myapproles->appraiser->active += (int) (!$appraisal->archived && in_array($appraisal->statusid, array(3, 5)));
                }
                if ($appraisal->signoff_userid == $user->id) {
                    $myapproles->signoff->is = true;
                    // Active if not archived and status 6.
                    $myapproles->signoff->active += (int) (!$appraisal->archived && $appraisal->statusid == 6);
                }
                if ($appraisal->groupleader_userid == $user->id && costcentre::get_cost_centre_groupleaderactive($appraisal->costcentre)) {
                    $myapproles->groupleader->is = true;
                    // Active if not archived and status 7.
                    $myapproles->groupleader->active += (int) (!$appraisal->archived && $appraisal->statusid == 7);
                }
            }
        }

        if (has_capability('local/onlineappraisal:itadmin', \context_system::instance())) {
            $myapproles->itadmin = true;
        } else {
            $myapproles->itadmin = false;
        }

        // If the appraisal is not active for this costcentre the
        // user is not an appraisee.
        if ($myapproles->appraisee->is) {
            $costcentreactive = $DB->get_field('local_costcentre', 'enableappraisal', array('costcentre' => $user->icq));
            if (!$costcentreactive) {
                // Reset all numbers.
                $myapproles->appraisee = clone($baseapproles);

            } 
        }

        // Find costcentre roles.
        $myapproles->groupleader->is = $myapproles->groupleader->is || costcentre::is_user($user->id, costcentre::GROUP_LEADER);
        $myapproles->hrleader->is = costcentre::is_user($user->id, array(costcentre::HR_LEADER, costcentre::HR_ADMIN));
        $myapproles->businessadmin->is = $this->is_business_administrator($user->id);
        $myapproles->costcentreadmin->is = has_capability('local/costcentre:administer', \context_system::instance()) || costcentre::is_user($user->id, costcentre::BUSINESS_ADMINISTRATOR);

        $sort = 'received_date DESC, lastname ASC, firstname ASC';
        $like = $DB->sql_like('email', ':email', false);
        $feedbacks = $DB->get_records_select('local_appraisal_feedback', $like, array('email' => $user->email), $sort);
        foreach ($feedbacks as $feedback) {
            $appraisal = $DB->get_record('local_appraisal_appraisal', array('id' => $feedback->appraisalid, 'deleted' => 0));
            if (!$appraisal) {
                // Appraisal doesn't exist or has been deleted.
                continue;
            }
            if ($feedback->received_date) {
                // Will appear in completed feedback requests table so need menu link.
                $myapproles->feedback->is = true;
                continue;
            }
            $permission = 'feedback:submit';
            $stage = $appraisal->permissionsid;
            $usertype = 'guest';
            // Active if can submit.
            if (\local_onlineappraisal\permissions::is_allowed($permission, $stage, $usertype, $appraisal->archived, $appraisal->legacy)) {
                $myapproles->feedback->is = true;
                $myapproles->feedback->active++;
            }
        }

        return $myapproles;
    }

    /**
     * Generate the menu to be added to the (Bootstrap 3 based) theme
     * @return text $html. The rendered dropdown menu.
     */
    public function get_navdata() {
        global $USER;

        $return = '';

        if (empty($USER->id)) {
            return $return;
        }

        $nav = $this->all_my_appraisalroles($USER);

        // Check if this user has any permissions at all and count up notices (active flags).
        $nav->hasmenu = false;
        $nav->notices = 0;
        foreach (self::$types as $type) {
            if ($nav->$type->is) {
                $nav->hasmenu = true;
                // We don't show active count for appraisee.
                $nav->notices += ($type != 'appraisee') ? $nav->$type->active : 0;
            }
        }

        // No we know we have a certain role create the link structures
        // For the data going into our template
        $nav->dashboardlink = new moodle_url('/local/onlineappraisal/index.php');

        if ($nav->appraisee->is) {
            $nav->dashboardlink = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'appraisee'));
            $nav->appraiseelink = new moodle_url('/local/onlineappraisal/view.php', array('page' => 'overview'));
        }

        if ($nav->appraiser->is) {
            $nav->appraiserlink = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'appraiser'));
        }

        if ($nav->signoff->is) {
            $nav->signofflink = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'signoff'));
        }

        if ($nav->feedback->is) {
            $nav->feedbacklink = new moodle_url('/local/onlineappraisal/feedback_requests.php');
        }

        if ($nav->businessadmin->is) {
            $nav->businessadminlink = new moodle_url('/local/onlineappraisal/admin.php');
        }

        if ($nav->costcentreadmin->is) {
            $nav->costcentreadminlink = new moodle_url('/local/costcentre/index.php');
        }

        if ($nav->groupleader->is) {
            $nav->groupleaderlink = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'groupleader'));
        }

        if ($nav->hrleader->is) {
            $nav->hrleaderlink = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'hrleader'));
        }

        if ($nav->itadmin) {
            $nav->itadminlink = new moodle_url('/local/onlineappraisal/itadmin.php');
        }

        $dashboardlinks = ($nav->appraiser->is || $nav->signoff->is || $nav->feedback->is);
        $adminlinks = ($nav->businessadmin->is || $nav->costcentreadmin->is || $nav->groupleader->is || $nav->hrleader->is);
        $nav->hasdashboardlinks = $dashboardlinks;
        $nav->hasadminlinks = $adminlinks;
        if ($nav->appraisee->is && ($dashboardlinks || $adminlinks)) {
            $nav->firstdivider = true;
        }
        if ($dashboardlinks && $adminlinks) {
            $nav->seconddivider = true;
        }
        return $nav;
    }

    /**
     * Check if this user has rights to view admin pages.
     * permission on the costcentre plugin.
     * @param int $userid
     * @return bool true/false.
     */
    public function is_business_administrator($userid) {
        global $DB;

        // Check if user has golbal rights to administer cost centres.
        if (has_capability('local/costcentre:administer', \context_system::instance())) {
            return true;
        }

        $sql = 'SELECT lcu.id FROM {local_costcentre_user} lcu
                JOIN {local_costcentre} lc ON lc.costcentre = lcu.costcentre
                WHERE lcu.userid = ?
                AND lc.enableappraisal = 1
                AND ('.$DB->sql_bitand('lcu.permissions', costcentre::BUSINESS_ADMINISTRATOR).' = ?
                    OR '.$DB->sql_bitand('lcu.permissions', costcentre::HR_LEADER).' = ?
                    OR '.$DB->sql_bitand('lcu.permissions', costcentre::HR_ADMIN).' = ?)';
        $params = array($userid, costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN);

        if ($DB->get_records_sql($sql,$params)) {
            return true;
        } else {
            return false;
        }
    }
}