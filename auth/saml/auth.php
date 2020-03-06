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
 * Authentication Plugin: SAML based SSO Authentication
 *
 * Authentication using SAML2 with SimpleSAMLphp.
 *
 * Based on plugins made by Sergio Gómez (moodle_ssp), Martin Dougiamas (Shibboleth) and Erlend Strømsvik.
 *
 * @package auth_saml
 * @copyright 2016 Motorpilot Ltd.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/auth/ldap/auth.php');

/**
 * SimpleSAML authentication plugin class.
 *
 * @copyright 2016 Motorpilot Ltd.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class auth_plugin_saml extends auth_plugin_ldap {

    /**
     * @var array user fields to map from IdP
     */
    public $userfields = array(
        'firstname',
        'lastname',
        'email',
        'idnumber',
    );

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        $this->authtype = 'saml';
        $this->roleauth = 'auth_saml'; // Needed in auth_plugin_ldap.
        $this->errorlogtag = '[AUTH SAML] '; // Needed in auth_plugin_ldap.
        $this->init_plugin($this->authtype); // From auth_plugin_ldap.
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB, $SESSION;

        $redirect = false;
        // If true, user_login was initiated by saml/index.php.
        if (!empty($GLOBALS['saml_login'])) {
            unset($GLOBALS['saml_login']);
            return true;
        } else if (stristr($username, '@arup.com')) {
            $redirect = true;
        } else {
            $select = 'auth = :auth AND mnethostid = :mnethostid AND deleted = 0 AND (username = :username';
            $params = array('auth' => 'saml', 'mnethostid' => $CFG->mnet_localhost_id, 'username' => $username);
            $email = clean_param($username, PARAM_EMAIL);
            if ($email) {
                $select .= $email ? ' OR LOWER(email) = LOWER(:email)' : '';
                $params['email'] = $email;
            }
            $select .= ')';
            $users = $DB->get_records_select('user', $select, $params, 'id', 'id', 0, 2);
            if (count($users) === 1) {
                $redirect = true;
            }
        }

        if ($redirect) {
            // This user is SAML auth.
            $wantsurl = !empty($SESSION->wantsurl) ? $SESSION->wantsurl : '';
            $redirectto = new moodle_url('/auth/saml/index.php', array('wantsurl' => $wantsurl));
            redirect($redirectto);
        }

        return false;
    }


    /**
     * Returns the user information for 'external' users. In this case the
     * attributes provided by Identity Provider
     *
     * @param string $username
     * @return array $result Associative array of user data.
     */
    public function get_userinfo($username) {
        if (!empty($GLOBALS['samlloginattributes'])) {
            $loginattributes = $GLOBALS['samlloginattributes'];
            $attributemap = $this->get_attributes();
            $result = array();

            foreach ($attributemap as $key => $value) {
                if (!empty($loginattributes[$value][0])) {
                    $result[$key] = $loginattributes[$value][0];
                } else {
                    $result[$key] = '';
                }
            }
            unset($GLOBALS['samlloginattributes']);

            $result['username'] = $username;
            return $result;
        } else {
            return parent::get_userinfo($username);
        }
    }

    /**
     * Returns user attribute mappings between moodle and LDAP.
     *
     * @return array
     */
    public function ldap_attributes () {
        $moodleattributes = array();
        foreach ($this->userfields as $field) {
            if (!empty($this->config->{"ldap_map_$field"})) {
                $moodleattributes[$field] = core_text::strtolower(trim($this->config->{"ldap_map_$field"}));
                if (preg_match('/,/', $moodleattributes[$field])) {
                    $moodleattributes[$field] = explode(',', $moodleattributes[$field]); // Split?
                }
            }
        }
        $moodleattributes['username'] = core_text::strtolower(trim($this->config->user_attribute));
        return $moodleattributes;
    }

    /**
     * Returns array containing attribute mappings between Moodle and Identity Provider.
     *
     * @return array
     */
    public function get_attributes() {
        $configarray = (array) $this->config;

        $fields = $this->userfields;

        $moodleattributes = array();
        foreach ($fields as $field) {
            if (isset($configarray["field_map_$field"])) {
                $moodleattributes[$field] = $configarray["field_map_$field"];
            }
        }

        return $moodleattributes;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     *
     * @return bool true means flag 'not_cached' stored instead of password hash
     */
    public function prevent_local_passwords() {
        return true;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's password, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of password from moodle.
     *
     * @return bool
     */
    public function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin allows signup and user creation.
     *
     * @return bool
     */
    public function can_signup() {
        return false;
    }

    /**
     * Login page hook.
     *
     * @return void
     */
    public function loginpage_hook() {
        global $CFG;

        if (empty($CFG->alternateloginurl)) {
            $saml = optional_param('saml', true, PARAM_BOOL);
            $url = new moodle_url('/auth/saml/login.php', array('saml' => $saml));
            $CFG->alternateloginurl = $url->out(false);
        }

        // Prevent username from being shown on login page after logout.
        $CFG->nolastloggedin = true;
    }

    /**
     * Logout page hook.
     *
     * @return void
     */
    public function logoutpage_hook() {
        global $redirect, $CFG;

        if ($CFG->forcelogin && !empty($this->config->autologin)) {
            $redirect = $CFG->httpswwwroot.'/login/index.php?saml=false';
        }

        if (isset($this->config->dosinglelogout) && $this->config->dosinglelogout) {
            set_moodle_cookie('nobody');
            require_logout();
            redirect($CFG->wwwroot.'/auth/saml/index.php?logout=1');
        }
    }

    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     * @return void
     */
    public function user_authenticated_hook(&$user, $username, $password) {
        global $CFG, $DB;

        // This will get called even if not actually using saml auth so return if not.
        if ($user->auth != 'saml') {
            return;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();
        $ldapuserdn = $this->ldap_find_userdn($ldapconnection, $extusername);

        // AD data mapping (requires special treatment OR fields not in config).
        $userdata = ldap_read(
                $ldapconnection, $ldapuserdn, '(objectClass=*)',
                array('c',
                      'extensionAttribute1',
                      'extensionAttribute3',
                      'extensionAttribute5',
                      'extensionAttribute7',
                      'extensionAttribute8',
                      'mobile',
                      'telephoneNumber',
                      'thumbnailPhoto',
                    )
                );

        $userentries = ldap_get_entries($ldapconnection, $userdata);

        $userupdated = new stdClass();

        if (empty($user->policyagreed)) {
            $userupdated->policyagreed = 1;
        }

        if (empty($user->email) && validate_email($username)) {
            $userupdated->email = $username;
        }

        if (isset($userentries[0]['c'][0])) {
            $userupdated->country = $userentries[0]['c'][0];
        } else {
            $userupdated->country = '';
        }

        if (isset($userentries[0]['extensionattribute1'][0])) {
            $userupdated->aim = $userentries[0]['extensionattribute1'][0];
        } else {
            $userupdated->aim = '';
        }

        if (isset($userentries[0]['extensionattribute3'][0])) {
            $userupdated->msn = $userentries[0]['extensionattribute3'][0];
        } else {
            $userupdated->msn = '';
        }

        if (isset($userentries[0]['extensionattribute5'][0])) {
            $userupdated->city = str_replace([' Region', '-'], '', $userentries[0]['extensionattribute5'][0]);
        } else {
            $userupdated->city = '';
        }

        if (isset($userentries[0]['extensionattribute7'][0])) {
            $userupdated->institution = $userentries[0]['extensionattribute7'][0];
        } else {
            $userupdated->institution = '';
        }

        if (isset($userentries[0]['extensionattribute8'][0])) {
            $userupdated->address = $userentries[0]['extensionattribute8'][0];
        } else {
            $userupdated->address = '';
        }

        if (isset($userentries[0]['mobile'][0])) {
            $userupdated->phone2 = $userentries[0]['mobile'][0];
        } else {
            $userupdated->phone2 = '';
        }

        if (isset($userentries[0]['telephonenumber'][0])) {
            $userupdated->phone1 = $userentries[0]['telephonenumber'][0];
        } else {
            $userupdated->phone1 = '';
        }

        $imagedata = ldap_read($ldapconnection, $ldapuserdn, '(objectClass=*)', array('thumbnailPhoto'));
        $imageentries = ldap_get_entries($ldapconnection, $imagedata);

        if (isset($imageentries[0]['thumbnailphoto'][0])) {
            try {
                require_once("$CFG->libdir/gdlib.php");

                $context = context_user::instance($user->id);

                $tempfile = tempnam(sys_get_temp_dir(), 'tp');
                file_put_contents($tempfile, $imageentries[0]['thumbnailphoto'][0]);

                if ($tempfile) {
                        if (process_new_icon($context, 'user', 'icon', 0, $tempfile)) {
                            $userupdated->picture = 1;
                        }
                    @unlink($tempfile);
                }
            } catch (Exception $e) {
                if ($tempfile) {
                    @unlink($tempfile);
                }
            }
        }

        // Get icq (costcentre code) and department (costcentre name) from hub.
        $query = "
            SELECT
                COMPANY_CODE as companycode, CENTRE_CODE as centrecode, CENTRE_NAME as centrename
            FROM
                SQLHUB.ARUP_ALL_STAFF_V
            WHERE
                EMPLOYEE_NUMBER = :idnumber
        ";
        $params= ['idnumber' => (int) $user->idnumber];

        $hubrecord = $DB->get_record_sql($query, $params);
        if ($hubrecord) {
            $userupdated->department = $hubrecord->centrename;
            $userupdated->icq = "{$hubrecord->companycode}-{$hubrecord->centrecode}";
        } else {
            $userupdated->department = '';
            $userupdated->icq = '';
        }

        if (!empty($userupdated)) {
            $userupdated = (object) truncate_userinfo((array) $userupdated);
            $userupdated->id = $user->id;
            $userupdated->timemodified = time();
            $DB->update_record('user', $userupdated);
            // Trigger event.
            \core\event\user_updated::create_from_userid($user->id)->trigger();
            $user = get_complete_user_data('id', $user->id);
        }

        // Check region/subregion is set (if local_regions plugin installed).
        if (get_config('local_regions', 'version')) {
            require_once($CFG->dirroot.'/local/regions/lib.php');
            // May redirect to profile via a notification.
            local_regions_user_check($user);
        }
    }

    /**
     * User check.
     * Checks user to see if username is being re-used for a new employeeid.
     * Maps user to existing if username has changed (looks at employeeid).
     * This method is called from authenticate_user_login().
     *
     * @param object $user user object
     * @param string $username
     * @return void
     */
    public function user_check(&$user, $username) {
        global $CFG, $DB;

        $userinfo = $this->get_userinfo($username);

        if (empty($userinfo['idnumber'])) {
            // No Staff ID.
            saml_error(get_string('error:employeeid_none', 'auth_saml'), '?logout');
        }

        $query = "SELECT EMPLOYEE_NUMBER
                    FROM SQLHUB.ARUP_ALL_STAFF_V
                   WHERE EMPLOYEE_NUMBER = :idnumber";
        if (!$DB->get_record_sql($query, ['idnumber' => (int) $userinfo['idnumber']])) {
            // No HUB record.
            saml_error(get_string('error:employeeid_nohub', 'auth_saml'), '?logout');
        }

        if ($user->id) {
            // Moodle user loaded.
            if ($user->idnumber != $userinfo['idnumber']) {
                // ID mismatch between Moodle and SAML/AD.
                saml_error(get_string('error:employeeid_mismatch', 'auth_saml'), '?logout');
            }
        }

        // Load all existing users with this employeeid.
        $existingusers = $DB->get_records(
            'user',
            array(
                'idnumber' => $userinfo['idnumber'],
                'mnethostid' => $CFG->mnet_localhost_id
            ),
            'lastaccess DESC'
        );

        if (count($existingusers) > 1) {
            // More than one found - this shouldn't happen but let's stop if it does!
            saml_error(get_string('error:employeeid_duplicate', 'auth_saml'), '?logout');
        }

        // Get user (or NULL if empty).
        $existinguser = array_shift($existingusers);

        if (!$user->id && $existinguser) {
            // Moodle user not found based on username but account for employeeid exists.
            $existinguser->username = trim(core_text::strtolower($username));
            $DB->update_record('user', $existinguser);
            $user = get_complete_user_data('id', $existinguser->id);
        }

        // Final check that user is not excluded from Moodle.
        // Store config to reset after.
        $contexts = $this->config->contexts;
        // Tweak config.
        $this->config->contexts = 'OU=Extranet,DC=global,DC=arup,DC=com';
        // Check for employee id.
        $excluded = $this->ldap_get_userlist("(employeeid={$userinfo['idnumber']})");
        // Reset config.
        $this->config->contexts = $contexts;
        if (!empty($excluded)) {
            // User is excluded.
            if ($user->id) {
                // User exists so needs to be suspended.
                $user->suspended = 1;
                $user->timemodified = time();
                $DB->update_record('user', $user);
                // Trigger event.
                \core\event\user_updated::create_from_userid($user->id)->trigger();
            }
            saml_error(get_string('error:employeeid_excluded', 'auth_saml'), '?logout');
        }
    }
}

function auth_saml_updatedcallback() {
    global $CFG;
    $samlsettings = new stdClass();

    if ($value = get_config('auth_saml', 'samllib')) {
        $samlsettings->samllib = $value;
    }

    if ($value = get_config('auth_saml', 'sp_source')) {
        $samlsettings->sp_source = $value;
    }

    $samlsettings->dosinglelogout = get_config('auth_saml', 'dosinglelogout');

    $samlsettingsencoded = json_encode($samlsettings);
    file_put_contents($CFG->dataroot . '/saml_config.json', $samlsettingsencoded);
}