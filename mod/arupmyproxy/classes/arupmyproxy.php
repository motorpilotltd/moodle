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

/**
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class arupmyproxy {

    protected $_instance;
    protected $_coursecontext;

    protected $_allvalidusers = array();
    protected $_loginasusers = null;
    protected $_requestusers = null;
    protected $_pendingusers = null;
    protected $_refusedusers = null;

    /**
     * Constructor
     *
     * @param object|int $instanceorid
     */
    public function __construct($instanceorid) {
        global $DB;

        if (is_object($instanceorid)) {
            $this->_instance = $instanceorid;
        } else {
            $this->_instance = $DB->get_record('arupmyproxy', array('id' => $instanceorid), '*', MUST_EXIST);
        }

        $this->_coursecontext = context_course::instance($this->_instance->course);

        // Check required capability is set (safety net in case may have been removed elsewhere).
        assign_capability('moodle/course:view', CAP_ALLOW, $this->_instance->roleid, $this->_coursecontext->id);
        $this->_coursecontext->mark_dirty();

        $this->_get_all_valid_users();
    }

    /**
     * Returns all valid users for login as
     */
    protected function _get_all_valid_users() {
        global $DB;

        list($esql, $params) = get_enrolled_sql(context_course::instance($this->_instance->course));
        $usernamefields = get_all_user_name_fields(true, 'u');
        $sql = <<<EOS
SELECT
    u.id, {$usernamefields}, u.email
FROM
    {user} u
JOIN
    ($esql) je
    ON je.id = u.id
WHERE
    u.deleted = 0 AND u.suspended = 0
ORDER BY
    u.lastname ASC
EOS;
        $this->_allvalidusers = $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns users passed user can login as
     *
     * @return array
     */
    public function get_loginas_users() {
        global $DB, $USER;

        if (is_null($this->_loginasusers)) {
            if (has_capability('moodle/user:loginas', $this->_coursecontext)) {
                $this->_loginasusers = $this->_allvalidusers;
                unset($this->_loginasusers[$USER->id]);
            } else {
                $confirmedproxies = $DB->get_records(
                    'arupmyproxy_proxies',
                    array(
                        'arupmyproxyid' => $this->_instance->id,
                        'userid' => $USER->id,
                        'response' => 1
                    ),
                    '',
                    'proxyuserid'
                );
                $this->_loginasusers = array_intersect_key($this->_allvalidusers, $confirmedproxies);
            }
        }

        return $this->_loginasusers;
    }

    /**
     * Returns users current user can request to proxy for
     *
     * @return array
     */
    public function get_request_users() {
        global $DB, $USER;

        if (is_null($this->_requestusers)) {
            if (has_capability('moodle/user:loginas', $this->_coursecontext)) {
                $this->_requestusers = array();
            } else {
                $existingproxies = $DB->get_records(
                    'arupmyproxy_proxies',
                    array(
                        'arupmyproxyid' => $this->_instance->id,
                        'userid' => $USER->id
                    ),
                    '',
                    'proxyuserid'
                );
                $this->_requestusers = array_diff_key($this->_allvalidusers, $existingproxies);
                unset($this->_requestusers[$USER->id]);
            }
        }

        return $this->_requestusers;
    }

    /**
     * Returns users current user has requested to proxy for, but are not yet confirmed
     *
     * @return array
     */
    public function get_pending_users() {
        global $DB, $USER;

        if (is_null($this->_pendingusers)) {
            if (has_capability('moodle/user:loginas', $this->_coursecontext)) {
                $this->_pendingusers = array();
            } else {
                $pendingproxies = $DB->get_records(
                    'arupmyproxy_proxies',
                    array(
                        'arupmyproxyid' => $this->_instance->id,
                        'userid' => $USER->id,
                        'response' => null
                    ),
                    '',
                    'proxyuserid'
                );
                $this->_pendingusers = array_intersect_key($this->_allvalidusers, $pendingproxies);
                unset($this->_pendingusers[$USER->id]);
            }
        }

        return $this->_pendingusers;
    }

    /**
     * Returns users current user has had their requests refused by
     *
     * @return array
     */
    public function get_refused_users() {
        global $DB, $USER;

        if (is_null($this->_refusedusers)) {
            if (has_capability('moodle/user:loginas', $this->_coursecontext)) {
                $this->_refusedusers = array();
            } else {
                $refusedproxies = $DB->get_records(
                    'arupmyproxy_proxies',
                    array(
                        'arupmyproxyid' => $this->_instance->id,
                        'userid' => $USER->id,
                        'response' => 0
                    ),
                    '',
                    'proxyuserid'
                );
                $this->_refusedusers = array_intersect_key($this->_allvalidusers, $refusedproxies);
                unset($this->_refusedusers[$USER->id]);
            }
        }

        return $this->_refusedusers;
    }

    /**
     * Returns a uniquehash
     *
     * @param string $proxyemail
     * @return string
     */
    public function generate_uniquehash($proxyemail) {
        global $DB;

        $timestamp = time();
        $salt = mt_rand();
        $uniquehash = sha1("$salt $proxyemail $timestamp");
        while ($DB->get_record('arupmyproxy_proxies', array('uniquehash' => $uniquehash), 'id')) {
            // Re hash!
            $timestamp = time();
            $salt = mt_rand();
            $uniquehash = generate_uniquehash($proxyemail);
        }
        return $uniquehash;
    }
}