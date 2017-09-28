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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use Exception;
use stdClass;

class permissions {
    const PERMISSION_NOT_ALLOWED = 0;
    const PERMISSION_ALLOWED = 1;
    const PERMISSION_ONLY = 2;

    /**
     * Associated DB table.
     * @var string $table
     */
    private static $table = 'local_appraisal_permissions';

    /**
     * Definition of 'all', necessary to protect against 'guest' access and maintain appraisee only access in permission status 2.
     * @var array $all
     */
    private static $all = array(
        'appraisee' => array(2,3,4,5,6,7),
        'appraiser' => array(3,4,5,6,7),
        'signoff' => array(3,4,5,6,7),
        'groupleader' => array(3,4,5,6,7),
        'hrleader' => array(3,4,5,6,7),
    );

    /**
     * Checks cache for permission and tries to load if not present.
     * Will insert null in cache if not found in DB.
     * 
     * @param string $permission
     * @return null|array
     */
    private static function cache_check($permission) {
        $cache = \cache::make('local_onlineappraisal', 'permissions');
        $allowed = $cache->get($permission);
        if ($allowed === false) { // Permission not in cache.
            $allowed = self::load_permission($permission);
            // Update cache.
            $cache->set($permission, $allowed);
        }
        // Will be null if not found, array otherwise.
        return $allowed;
    }

    /**
     * Checks is a certain permission exists.
     * 
     * @param string $permission
     * @return boolean
     */
    public static function exists($permission) {
        // If permission exists result will be an array, otherwise null.
        $allowed = self::cache_check($permission);
        return is_array($allowed) ? true : false;
    }

    /**
     * Check if a certain permission is allowed for tcurrent user/status.
     *
     * @param string $permission the full permission name.
     * @param int $permissionsid the permission status id for the appraisal.
     * @param int $viewingas who the user is viewing the appraisal as.
     * @param int $archived is appraisal archived.
     * @param int $legacy is appraisal legacy.
     * @return boolean
     */
    public static function is_allowed($permission, $permissionsid, $viewingas, $archived, $legacy) {
        if ($permission === 'all') {
            // Not a valid check.
            return false;
        }
        
        $allowed = self::cache_check($permission);

        if (!empty($allowed[$viewingas][$permissionsid])) {
            $userallowed = $allowed[$viewingas][$permissionsid];
            
            // First check for archived/legacy ONLY permissions.
            if (!$archived && $userallowed->archived == self::PERMISSION_ONLY) {
                // Only allowed if archived.
                return false;
            }
            if (!$legacy && $userallowed->legacy == self::PERMISSION_ONLY) {
                // Only allowed if legacy.
                return false;
            }

            // General checks.
            $isallowed = $userallowed->allowed;
            if ($archived) {
                $isallowed = $isallowed && $userallowed->archived;
            }
            if ($legacy) {
                $isallowed = $isallowed && $userallowed->legacy;
            }

            return $isallowed;
        }

        return false;
    }

    /**
     * Rebuilds permissions table and cache.
     * 
     * @global moodle_database $DB
     */
    public static function rebuild_permissions() {
        global $DB;
        // Delete records.
        $DB->delete_records('local_appraisal_permissions');

        // Purge cache.
        \cache::make('local_onlineappraisal', 'permissions')->purge();

        // Add records to DB table.
        self::setup_permissions();

        $allallowed = self::load_permission('all');
        // Rebuild cache.
        $cache = \cache::make('local_onlineappraisal', 'permissions');
        $cache->set_many($allallowed);
    }

    /**
     * Create the permission table if it has not been created yet.
     */
    private static function setup_permissions() {

        //appraisal:view
        self::add_permissions('appraisal', 'view', 'appraisee', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('appraisal', 'view', 'appraiser', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('appraisal', 'view', 'signoff', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('appraisal', 'view', 'groupleader', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('appraisal', 'view', 'hrleader', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        //appraisal:print
        self::add_permissions('appraisal', 'print', 'all', 'all', self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        //appraisal:update
        // Ability to move to different stage (Need to be checked against status id NOT permissions id).
        self::add_permissions('appraisal', 'update', 'appraisee', array(1,2,4));
        self::add_permissions('appraisal', 'update', 'appraiser', array(3,5));
        self::add_permissions('appraisal', 'update', 'signoff', array(6));
        self::add_permissions('appraisal', 'update', 'groupleader', array(7));

        //introduction:view
        self::add_permissions('introduction', 'view', 'appraisee', array(1,2,3,4,5,6,7));

        //comments:add
        self::add_permissions('comments', 'add', 'appraisee', array(2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('comments', 'add', 'appraiser', array(2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('comments', 'add', 'signoff', array(2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('comments', 'add', 'groupleader', array(2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ALLOWED);
        //comments:view
        self::add_permissions('comments', 'view', 'all', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        //f2f:add
        self::add_permissions('f2f', 'add', 'appraisee', array(2,3));
        self::add_permissions('f2f', 'add', 'appraiser', array(2,3));
        //f2f:complete
        self::add_permissions('f2f', 'complete', 'appraisee', array(2,3));
        self::add_permissions('f2f', 'complete', 'appraiser', array(2,3));
        //userinfo:view
        self::add_permissions('userinfo', 'view', 'appraisee', 'all');
        self::add_permissions('userinfo', 'view', 'appraiser', array(2,3,4,5,6,7)); // Modified for appraiser to access F2F.
        self::add_permissions('userinfo', 'view', 'signoff', 'all');
        self::add_permissions('userinfo', 'view', 'groupleader', 'all');
        self::add_permissions('userinfo', 'view', 'hrleader', 'all');
        //userinfo:add
        self::add_permissions('userinfo', 'add', 'appraisee', array(2,3,4));
        //lastyear:view
        self::add_permissions('lastyear', 'view', 'all', 'all');
        //lastyear:add
        self::add_permissions('lastyear', 'add', 'appraisee', array(2,3,4)); // Fields restricted.
        self::add_permissions('lastyear', 'add', 'appraiser', array(3,4)); // Fields restricted.
        //feedback:add
        self::add_permissions('feedback', 'add', 'appraisee', array(2,3,4));
        self::add_permissions('feedback', 'add', 'appraiser', array(2,3,4));
        //feedback:view
        // Change Upgrade Log Appraisal V3 id 5
        self::add_permissions('feedbackown', 'view', 'appraisee', array(2,3,4,5,6,7)); // Non-confidential only.
        self::add_permissions('feedback', 'view', 'appraisee', array(4,5,6,7)); // Non-confidential only.
        self::add_permissions('feedback', 'view', 'appraiser', array(2,3,4,5,6,7));
        self::add_permissions('feedback', 'view', 'signoff', array(2,3,4,5,6,7));
        self::add_permissions('feedback', 'view', 'groupleader', array(2,3,4,5,6,7));
        self::add_permissions('feedback', 'view', 'hrleader', array(2,3,4,5,6,7));
        self::add_permissions('feedback', 'view', 'guest', array(2,3,4,5)); // Own ONLY (if Moodle user).
        //feedback:submit
        self::add_permissions('feedback', 'submit', 'guest', array(2,3,4));
        //addfeedback:view
        self::add_permissions('addfeedback', 'view', 'guest', array(2,3,4));
        //feedback:print
        self::add_permissions('feedbackown', 'print', 'appraisee', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_NOT_ALLOWED);
        self::add_permissions('feedback', 'print', 'appraisee', array(4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('feedback', 'print', 'appraiser', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('feedback', 'print', 'signoff', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('feedback', 'print', 'groupleader', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        self::add_permissions('feedback', 'print', 'hrleader', array(2,3,4,5,6,7), self::PERMISSION_ALLOWED, self::PERMISSION_ALLOWED);
        //careerdirection:view
        self::add_permissions('careerdirection', 'view', 'all', 'all');
        //careerdirection:add
        self::add_permissions('careerdirection', 'add', 'appraisee', array(2,3,4)); // Fields restricted.
        self::add_permissions('careerdirection', 'add', 'appraiser', array(3,4)); // Fields restricted.
        //impactplan:view
        self::add_permissions('impactplan', 'view', 'all', 'all');
        //impactplan:add
        self::add_permissions('impactplan', 'add', 'appraisee', array(2,3,4)); // Fields restricted.
        self::add_permissions('impactplan', 'add', 'appraiser', array(3,4)); // Fields restricted.
        //development:view
        self::add_permissions('development', 'view', 'all', 'all');
        //development:add
        self::add_permissions('development', 'add', 'appraisee', array(2,3,4)); // Fields restricted.
        self::add_permissions('development', 'add', 'appraiser', array(3,4)); // Fields restricted.
        //summaries:view
        self::add_permissions('summaries', 'view', 'all', array(3,4,5,6,7));
        //summaries:add
        self::add_permissions('summaries', 'add', 'appraisee', array(3,4)); // Fields restricted.
        self::add_permissions('summaries', 'add', 'appraiser', array(3,4)); // Fields restricted.
        self::add_permissions('summaries', 'add', 'signoff', array(6)); // Fields restricted.
        self::add_permissions('summaries', 'add', 'groupleader', array(7));
        //checkin:view
        self::add_permissions('checkin', 'view', 'all', array(3,4,5,6,7));
        //checkin:add
        self::add_permissions('checkin', 'add', 'appraisee', array(3,4,5,6,7));
        self::add_permissions('checkin', 'add', 'appraiser', array(3,4,5,6,7));
        self::add_permissions('checkin', 'add', 'signoff', array(3,4,5,6,7));
        self::add_permissions('checkin', 'add', 'groupleader', array(3,4,5,6,7));

        // Special legacy permission.
        //sixmonth:view
        self::add_permissions('sixmonth', 'view', 'appraisee', array(1,2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ONLY);
        self::add_permissions('sixmonth', 'view', 'appraiser', array(1,2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ONLY);
        //sixmonth:add
        self::add_permissions('sixmonth', 'add', 'appraisee', array(1,2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ONLY);
        self::add_permissions('sixmonth', 'add', 'appraiser', array(1,2,3,4,5,6,7), self::PERMISSION_NOT_ALLOWED, self::PERMISSION_ONLY);
    }

    /**
     * Add a permission to the local_appraisal_permissions table
     *
     * @param string $page the page or entity to set permissions to
     * @param string $action the user action for this page. F.I. add, view
     * @param string $usertype the user type for the appraisal. F.I. appraisee, appraiser etc.
     * or the keyword all.
     * @param array|string $data an array of permission ids or the keyword 'all'.
     * @param boolean $archived is permission valid when archived
     * @param boolean $legacy is permission valid when legacy
     */
    private static function add_permissions($page, $action, $usertype, $data, $archived = self::PERMISSION_NOT_ALLOWED, $legacy = self::PERMISSION_NOT_ALLOWED) {
        global $DB;

        if ($data !== 'all') {
            $data = json_encode($data);
        }

        $pagetype = "$page:$action";
        if ($permission = $DB->get_record('local_appraisal_permissions', array('permission' => $pagetype, 'usertype' => $usertype))) {
            $permission->data = $data;
            $permission->archived = $archived;
            $permission->legacy = $legacy;
            $DB->update_record('local_appraisal_permissions', $permission);
        } else {
            $permission = new stdClass;
            $permission->permission = $pagetype;
            $permission->usertype = $usertype;
            $permission->data = $data;
            $permission->archived = $archived;
            $permission->legacy = $legacy;
            $DB->insert_record('local_appraisal_permissions', $permission);
        }
    }

    /**
     * Loads multi-dimensional array of users and allowed permissions statuses for a given permission.
     * Or loads array of 'all' permissions each pointing to a sub-array as above.
     * 
     * @global moodle_database $DB
     * @param string $permission Required permission or 'all'
     * @return array
     * @throws Exception
     */
    private static function load_permission($permission) {
        global $DB;

        $params = array();
        if ($permission !== 'all') {
            $params['permission'] = $permission;
        }
        
        $perms = $DB->get_records(self::$table, $params);

        if (empty($perms)) {
            // Specifically return null to show permission not found.
            return null;
        }

        $allowed = array();
        
        foreach ($perms as $perm) {
            if ($perm->usertype == 'all') {
                $types = array_keys(self::$all);
            } else {
                $types = array($perm->usertype);
            }
            foreach ($types as $type) {
                if ($perm->data === 'all') {
                    $statuses = isset(self::$all[$type]) ? self::$all[$type] : array();
                } else {
                    $statuses = json_decode($perm->data);
                }
                foreach ($statuses as $status) {
                    $allowed[$perm->permission][$type][$status] = new stdClass();
                    $allowed[$perm->permission][$type][$status]->allowed = true;
                    $allowed[$perm->permission][$type][$status]->archived = $perm->archived;
                    $allowed[$perm->permission][$type][$status]->legacy = $perm->legacy;
                }
            }
        }

        if ($permission === 'all') {
            return $allowed;
        }

        return $allowed[$permission];
    }
}