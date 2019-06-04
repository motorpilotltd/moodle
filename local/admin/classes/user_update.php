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

namespace local_admin;

defined('MOODLE_INTERNAL') || die();

use Exception;
use stdClass;

class user_update {
    const TABLES = [
        'hub' => 'SQLHUB.ARUP_ALL_STAFF_V',
        'user' => '{user}',
    ];

    private $samlauth;

    private $adexclusions;

    private $deletedusers = [];

    public function __construct() {
        $this->samlauth = get_auth_plugin('saml');
        $this->load_deleted_users();
        $this->load_ad_exclusions();
    }

    public function add_users() {
        global $DB;

        $successcount = 0;
        $errorcount = 0;

        $adds = $this->get_users_to_add();

        foreach($adds as $add) {
            try {
                $add->staffid = str_pad($add->staffid, 6, '0', STR_PAD_LEFT);
                if ($this->is_deleted($add->staffid)) {
                    $errorcount++;
                    // Log error.
                    $this->log($add->staffid, null, 'ADD', 'ERROR_DELETED_USER');
                    continue;
                }

                $adusers = $this->check_ad($add->staffid);
                if (empty($adusers)) {
                    $errorcount++;
                    // Log error.
                    $this->log($add->staffid, null, 'ADD', 'ERROR_NOT_AD');
                    continue;
                }

                // Grab username (UPN).
                $username = array_pop($adusers);

                if ($DB->record_exists('user', ['username' => $username])) {
                    $errorcount++;
                    // Log error.
                    $this->log($add->staffid, null, 'ADD', 'ERROR_USERNAME_EXISTS');
                    continue;
                }

                $user = create_user_record($username, '', 'saml');
                if (!$user) {
                    $errorcount++;
                    // Log error.
                    $this->log($add->staffid, null, 'ADD', 'ERROR_CREATE_FAILED');
                    continue;
                }

                $this->samlauth->user_authenticated_hook($user, $username, time());
            } catch (Exception $e) {
                $errorcount++;
                // Log error.
                $extrainfo = ['exception' => $e->getMessage()];
                if (isset($e->debuginfo)) {
                    $extrainfo['debuginfo'] = $e->debuginfo;
                }
                $this->log($add->staffid, null, 'ADD', 'ERROR_EXCEPTION', $extrainfo);
                continue;
            }

            $successcount++;
            // Log success.
            $this->log($add->staffid, $user->id, 'ADD', 'SUCCESS');
        }

        return['success' => $successcount, 'error' => $errorcount];
    }

    public function unsuspend_users() {
        global $DB;

        $successcount = 0;
        $errorcount = 0;

        $unsuspends = $this->get_users_to_unsuspend();

        foreach ($unsuspends as $unsuspend) {
            $unsuspend->staffid = str_pad($unsuspend->staffid, 6, '0', STR_PAD_LEFT);
            if (empty($this->check_ad($unsuspend->staffid))) {
                $errorcount++;
                // Log error.
                $this->log($unsuspend->staffid, $unsuspend->uid, 'UNSUSPEND', 'ERROR_NOT_AD');
                continue;
            }
            try {
                $DB->execute(
                        'UPDATE {user} SET suspended = 0, timemodified = :time WHERE id = :id',
                        ['time' => time(), 'id' => $unsuspend->uid]
                        );
                \core\event\user_updated::create_from_userid($unsuspend->uid)->trigger();
            } catch (Exception $e) {
                $errorcount++;
                // Log error.
                $extrainfo = ['exception' => $e->getMessage()];
                if (isset($e->debuginfo)) {
                    $extrainfo['debuginfo'] = $e->debuginfo;
                }
                $this->log($unsuspend->staffid, $unsuspend->uid, 'UNSUSPEND', 'ERROR_EXCEPTION', $extrainfo);
                continue;
            }

            $successcount++;
            // Log success.
            $this->log($unsuspend->staffid, $unsuspend->uid, 'UNSUSPEND', 'SUCCESS');
        }

        return['success' => $successcount, 'error' => $errorcount];
    }

    public function suspend_users() {
        global $DB;

        $successcount = 0;
        $errorcount = 0;

        $suspends = $this->get_users_to_suspend();

        foreach ($suspends as $suspend) {
            try {
                $suspend->staffid = str_pad($suspend->staffid, 6, '0', STR_PAD_LEFT);
                $DB->execute(
                        'UPDATE {user} SET suspended = 1, timemodified = :time WHERE id = :id',
                        ['time' => time(), 'id' => $suspend->uid]
                        );
                \core\event\user_updated::create_from_userid($suspend->uid)->trigger();
            } catch (Exception $e) {
                $errorcount++;
                // Log error.
                $extrainfo = ['exception' => $e->getMessage()];
                if (isset($e->debuginfo)) {
                    $extrainfo['debuginfo'] = $e->debuginfo;
                }
                $this->log($suspend->staffid, $suspend->uid, 'SUSPEND', 'ERROR_EXCEPTION', $extrainfo);
                continue;
            }

            $successcount++;
            // Log success.
            $extrainfo = null;
            if (in_array((int) $suspend->staffid, $this->adexclusions)) {
                $extrainfo = 'EXCLUDED';
            }
            $this->log($suspend->staffid, $suspend->uid, 'SUSPEND', 'SUCCESS', $extrainfo);
        }

        return['success' => $successcount, 'error' => $errorcount];
    }

    public function update_users() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/regions/lib.php');

        $successcount = 0;
        $errorcount = 0;

        $ccupdates = $this->get_users_to_update_cost_centre();

        foreach ($ccupdates as $ccupdate) {
            try {
                $ccupdate->staffid = str_pad($ccupdate->staffid, 6, '0', STR_PAD_LEFT);
                $params = [
                    'icq' => "{$ccupdate->companycode}-{$ccupdate->centrecode}",
                    'department' => $ccupdate->centrename,
                    'time' => time(),
                    'id' => $ccupdate->uid,
                ];
                $DB->execute(
                        'UPDATE {user} SET icq = :icq, department = :department, timemodified = :time WHERE id = :id',
                        $params
                        );
                \core\event\user_updated::create_from_userid($ccupdate->uid)->trigger();
            } catch (Exception $e) {
                $errorcount++;
                // Log error.
                $extrainfo = ['exception' => $e->getMessage()];
                if (isset($e->debuginfo)) {
                    $extrainfo['debuginfo'] = $e->debuginfo;
                }
                $this->log($ccupdate->staffid, $ccupdate->uid, 'UPDATE|COSTCENTRE', 'ERROR_EXCEPTION', $extrainfo);
                continue;
            }

            $successcount++;
            // Log success.
            $this->log($ccupdate->staffid, $ccupdate->uid, 'UPDATE|COSTCENTRE', 'SUCCESS');
        }

        $regupdates = $this->get_users_to_update_region();

        foreach ($regupdates as $regupdate) {
            try {
                local_regions_user_check($regupdate, false);
            } catch (Exception $e) {
                $errorcount++;
                // Log error.
                $extrainfo = ['exception' => $e->getMessage()];
                if (isset($e->debuginfo)) {
                    $extrainfo['debuginfo'] = $e->debuginfo;
                }
                $this->log((int) $regupdate->idnumber, $regupdate->id, 'UPDATE|REGION', 'ERROR_EXCEPTION', $extrainfo);
                continue;
            }

            $successcount++;
            // Log success.
            $this->log((int) $regupdate->idnumber, $regupdate->id, 'UPDATE|REGION', 'SUCCESS');
        }

        return['success' => $successcount, 'error' => $errorcount];
    }

    public function get_users_cannot_add() {
        global $DB;

        // Exclusions.
        list($adexsql, $adexparams) = $DB->get_in_or_equal($this->adexclusions, SQL_PARAMS_NAMED, 'adex', false, 0);

        // h.EMPLOYEE_NUMBER NOT IN ([EXCLUSIONS])
        // h.LEAVER_FLAG = 'N'
        // h.EMAIL_ADDRESS IS NULL
        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $query = "
            SELECT
              h.EMPLOYEE_NUMBER as staffid,
              h.FULL_NAME as fullname
            FROM {$hubtable} h
            LEFT JOIN {$usertable} u ON {$castidnumber} = h.EMPLOYEE_NUMBER
            WHERE
                h.EMPLOYEE_NUMBER {$adexsql}
                AND h.LEAVER_FLAG = 'N'
                AND h.EMAIL_ADDRESS IS NULL
                AND u.id IS NULL
        ";

        return $DB->get_records_sql($query, $adexparams);
    }

    private function get_users_to_add() {
        global $DB;

        // Exclusions.
        list($adexsql, $adexparams) = $DB->get_in_or_equal($this->adexclusions, SQL_PARAMS_NAMED, 'adex', false, 0);

        // h.EMPLOYEE_NUMBER NOT IN ([EXCLUSIONS])
        // h.LEAVER_FLAG = 'N'
        // h.EMAIL_ADDRESS IS NOT NULL
        // LEFT JOIN
        // u.id IS NULL

        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $query = "
            SELECT
              h.EMPLOYEE_NUMBER as staffid,
              h.FULL_NAME as fullname,
              h.EMAIL_ADDRESS as email
            FROM
                {$hubtable} h
            LEFT JOIN
                {$usertable} u
                ON {$castidnumber} = h.EMPLOYEE_NUMBER
                    AND u.deleted = 0
            WHERE
                h.EMPLOYEE_NUMBER {$adexsql}
                AND h.LEAVER_FLAG = 'N'
                AND h.EMAIL_ADDRESS IS NOT NULL
                AND u.id IS NULL
        ";

        return $DB->get_records_sql($query, $adexparams);
    }

    private function get_users_to_unsuspend() {
        global $DB;

        // Exclusions.
        list($adexsql, $adexparams) = $DB->get_in_or_equal($this->adexclusions, SQL_PARAMS_NAMED, 'adex', false, 0);

        // h.EMPLOYEE_NUMBER NOT IN ([EXCLUSIONS])
        // h.LEAVER_FLAG = 'N'
        // h.EMAIL_ADDRESS IS NOT NULL
        // JOIN
        // u.suspended = 1
        // u.deleted != 1

        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $query = "
            SELECT
              h.EMPLOYEE_NUMBER as staffid,
              h.FULL_NAME as fullname,
              h.EMAIL_ADDRESS as email,
              u.id as uid
            FROM {$hubtable} h
            JOIN {$usertable} u ON {$castidnumber} = h.EMPLOYEE_NUMBER
            WHERE
                h.EMPLOYEE_NUMBER {$adexsql}
                AND h.LEAVER_FLAG = 'N'
                AND h.EMAIL_ADDRESS IS NOT NULL
                AND u.suspended = 1
                AND u.deleted = 0
        ";

        return $DB->get_records_sql($query, $adexparams);
    }

    private function get_users_to_suspend() {
        global $DB;

        // Exclusions.
        list($adexsql, $adexparams) = $DB->get_in_or_equal($this->adexclusions, SQL_PARAMS_NAMED, 'adex', true, 0);

        // h.LEAVER_FLAG = 'Y' OR h.LEAVER_FLAG IS NULL OR h.EMPLOYEE_NUMBER IN ([EXCLUSIONS])
        // JOIN
        // u.suspended = 0
        // u.deleted != 1

        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $query = "SELECT u.id as uid, u.idnumber as staffid
                    FROM {$usertable} u
               LEFT JOIN {$hubtable} h ON {$castidnumber} = h.EMPLOYEE_NUMBER
                   WHERE u.auth = 'saml' AND u.idnumber != '' AND u.suspended = 0 AND u.deleted = 0
                         AND (h.LEAVER_FLAG = 'Y' OR h.LEAVER_FLAG IS NULL OR h.EMPLOYEE_NUMBER {$adexsql})";

        return $DB->get_records_sql($query, $adexparams);
    }

    private function get_users_to_update_cost_centre() {
        global $DB;
        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $concatcc = $DB->sql_concat('h.COMPANY_CODE', "'-'", 'h.CENTRE_CODE');
        $query = "
            SELECT
              h.EMPLOYEE_NUMBER as staffid,
              h.COMPANY_CODE as companycode,
              h.CENTRE_CODE as centrecode,
              h.CENTRE_NAME as centrename,
              u.id as uid,
              u.icq,
              u.department
            FROM {$hubtable} h
            JOIN {$usertable} u ON {$castidnumber} = h.EMPLOYEE_NUMBER
            WHERE
                u.suspended = 0
                AND u.deleted = 0
                AND (
                    {$concatcc} != u.icq
                    OR u.department != h.CENTRE_NAME
                )
        ";

        return $DB->get_records_sql($query);
    }

    private function get_users_to_update_region() {
        global $DB;
        $usertable = self::TABLES['user'];
        $hubtable = self::TABLES['hub'];
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $likes = [
            'geoname' => $DB->sql_like('h.GEO_REGION', 'lrr1.name', false, false, true),
            'geotapsname' => $DB->sql_like('h.GEO_REGION', 'lrr1.tapsname', false, false, true),
            'actname' => $DB->sql_like('h.REGION_NAME', 'lrr2.name', false, false, true),
            'acttapsname' => $DB->sql_like('h.REGION_NAME', 'lrr2.tapsname', false, false, true),
            'geoactname' => $DB->sql_like('h.GEO_REGION', 'lrr2.name', false, false, true),
            'geoacttapsname' => $DB->sql_like('h.GEO_REGION', 'lrr2.tapsname', false, false, true),
        ];
        $query = "
            SELECT
                u.id,
                u.idnumber
            FROM {$hubtable} h
            JOIN {$usertable} u ON {$castidnumber} = h.EMPLOYEE_NUMBER
            LEFT JOIN mdl_local_regions_use lru ON lru.userid = u.id
            LEFT JOIN mdl_local_regions_reg lrr1 ON lru.geotapsregionid = lrr1.id
            LEFT JOIN mdl_local_regions_reg lrr2 ON lru.acttapsregionid = lrr2.id
            WHERE
                u.suspended = 0
                AND u.deleted = 0
                AND (
                    (lrr1.id IS NULL OR ({$likes['geoname']} AND {$likes['geotapsname']}))
                    OR
                    (lrr2.id IS NULL
                        OR ({$likes['actname']} AND {$likes['acttapsname']})
                        OR (h.REGION_NAME IS NULL AND {$likes['geoactname']} AND {$likes['geoactname']})
                    )
                )
        ";

        return $DB->get_records_sql($query);
    }

    private function load_deleted_users() {
        global $DB;
        $this->deletedusers = $DB->get_records_select_menu('user', "deleted = 1 AND (idnumber IS NOT NULL OR idnumber != '')", [], 'id, idnumber');
    }

    private function is_deleted($idnumber) {
        // Search array values for any starting with idnumber.
        return !empty(preg_grep("/^{$idnumber}/", $this->deletedusers));
    }

    private function check_ad($idnumber) {
        $users = $this->samlauth->ldap_get_userlist("(&(!(useraccountcontrol:1.2.840.113556.1.4.803:=2))(employeeid={$idnumber}))");
        return $users;
    }

    private function load_ad_exclusions() {
        // Get employeeIDs of users who should not have a Moodle account but may have an active HUB record.
        // Store config to reset after.
        $userattribute = $this->samlauth->config->user_attribute;
        $contexts = $this->samlauth->config->contexts;
        // Tweak config.
        $this->samlauth->config->user_attribute = 'employeeid';
        $this->samlauth->config->contexts = 'OU=Extranet,DC=global,DC=arup,DC=com';
        // Load employee ids.
        $this->adexclusions = $this->samlauth->ldap_get_userlist("(&(!(employeeid=999999))(employeeid=*))");
        array_walk($this->adexclusions, function(&$value) {
            $value = (int) $value;
        });
        // Reset config.
        $this->samlauth->config->user_attribute = $userattribute;
        $this->samlauth->config->contexts = $contexts;
    }

    private function log($staffid, $userid, $action, $status, $extrainfo = null) {
        global $DB;
        $log = new stdClass();
        $log->staffid = $staffid;
        $log->userid = $userid;
        $log->action = $action;
        $log->status = $status;
        $log->extrainfo = $extrainfo ? json_encode($extrainfo) : null;
        $log->timecreated = time();
        $DB->insert_record('local_admin_user_update_log', $log);
    }
}
