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
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param object $config
     * @param object $err
     * @param array $userfields
     * @return void
     */
    public function config_form($config, $err, $userfields) {
        global $CFG, $OUTPUT; // Global $OUTPUT used in included file.

        if (!function_exists('ldap_connect')) { // Is php-ldap really there?
            echo $OUTPUT->notification(get_string('auth_ldap_noextension', 'auth_ldap'));
            return;
        }

        include($CFG->dirroot.'/auth/saml/config.html');
    }

    /**
     * A chance to validate form data, and last chance to
     * do stuff before it is inserted in config_plugin.
     *
     * @param object $form object with submitted configuration settings (without system magic quotes)
     * @param array $err array of error messages
     * @return void
     */
    public function validate_form($form, &$err) {

        if (!isset($form->db_reset)) {
            if (!isset($form->samllib) || !file_exists($form->samllib.'/_autoload.php')) {
                $err['samllib'] = get_string('samllib_error', 'auth_saml', $form->samllib);
            }
        }
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param object $config Configuration object
     * @return bool
     */
    public function process_config($config) {
        global $err, $DB, $CFG;

        if (isset($config->db_reset)) {
            try {
                $DB->delete_records('config_plugins', array('plugin' => 'auth/saml'));
            } catch (Exception $e) {
                $err['reset'] = get_string('db_reset_error', 'auth_saml');
                return false;
            }
            redirect(new moodle_url('/admin/auth_config.php', array('auth' => 'saml')));
        }

        // Class for settings to be saved to file.
        $samlsettings = new stdClass();

        // SAML settings.
        if (!isset($config->samllib)) {
            $samlsettings->samllib = '';
        } else {
            $samlsettings->samllib = $config->samllib;
        }
        if (!isset($config->sp_source)) {
            $samlsettings->sp_source = 'saml';
        } else {
            $samlsettings->sp_source = $config->sp_source;
        }
        if (!isset($config->dosinglelogout)) {
            $samlsettings->dosinglelogout = false;
        } else {
            $samlsettings->dosinglelogout = $config->dosinglelogout;
        }
        if (!isset($config->username)) {
            $config->username = 'userPrincipalName';
        }
        if (!isset($config->autologin)) {
            $config->autologin = false;
        }
        if (!isset($config->autologin_subnet)) {
            $config->autologin_subnet = '';
        }
        if (!isset($config->samllogfile)) {
            $config->samllogfile = '';
        }

        // LDAP settings.
        if (!isset($config->host_url)) {
            $config->host_url = '';
        }
        if (!isset($config->start_tls)) {
             $config->start_tls = false;
        }
        if (empty($config->ldapencoding)) {
            $config->ldapencoding = 'utf-8';
        }
        if (!isset($config->pagesize)) {
            $config->pagesize = LDAP_DEFAULT_PAGESIZE;
        }
        if (!isset($config->contexts)) {
            $config->contexts = '';
        }
        if (!isset($config->user_type)) {
            $config->user_type = 'default';
        }
        if (!isset($config->user_attribute)) {
            $config->user_attribute = '';
        }
        if (!isset($config->search_sub)) {
            $config->search_sub = '';
        }
        if (!isset($config->opt_deref)) {
            $config->opt_deref = LDAP_DEREF_NEVER;
        }
        if (!isset($config->bind_dn)) {
            $config->bind_dn = '';
        }
        if (!isset($config->bind_pw)) {
            $config->bind_pw = '';
        }
        if (!isset($config->ldap_version)) {
            $config->ldap_version = '3';
        }
        if (!isset($config->objectclass)) {
            $config->objectclass = '';
        }

        // LDAP mappings.
        foreach ($this->userfields as $field) {
            if (!isset($config->{"ldap_map_$field"})) {
                $config->{"ldap_map_$field"} = '';
            }
        }

        // Save required SAML settings to file.
        $samlsettingsencoded = json_encode($samlsettings);
        file_put_contents($CFG->dataroot . '/saml_config.json', $samlsettingsencoded);

        // Save SAML settings.
        set_config('samllib', $samlsettings->samllib, $this->pluginconfig);
        set_config('sp_source', $samlsettings->sp_source, $this->pluginconfig);
        set_config('dosinglelogout', $samlsettings->dosinglelogout, $this->pluginconfig);
        set_config('username', $config->username, $this->pluginconfig);
        set_config('autologin', $config->autologin, $this->pluginconfig);
        set_config('autologin_subnet', $config->autologin_subnet, $this->pluginconfig);
        set_config('samllogfile', $config->samllogfile, $this->pluginconfig);

        // Save LDAP settings.
        set_config('host_url', trim($config->host_url), $this->pluginconfig);
        set_config('start_tls', $config->start_tls, $this->pluginconfig);
        set_config('ldapencoding', trim($config->ldapencoding), $this->pluginconfig);
        set_config('pagesize', (int)trim($config->pagesize), $this->pluginconfig);
        set_config('contexts', trim($config->contexts), $this->pluginconfig);
        set_config('user_type', core_text::strtolower(trim($config->user_type)), $this->pluginconfig);
        set_config('user_attribute', core_text::strtolower(trim($config->user_attribute)), $this->pluginconfig);
        set_config('search_sub', $config->search_sub, $this->pluginconfig);
        set_config('opt_deref', $config->opt_deref, $this->pluginconfig);
        set_config('bind_dn', trim($config->bind_dn), $this->pluginconfig);
        set_config('bind_pw', $config->bind_pw, $this->pluginconfig);
        set_config('ldap_version', $config->ldap_version, $this->pluginconfig);
        set_config('objectclass', trim($config->objectclass), $this->pluginconfig);

        // Save LDAP mappings.
        foreach ($this->userfields as $field) {
            set_config("ldap_map_$field", $config->{"ldap_map_$field"}, $this->pluginconfig);
        }

        return true;
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

        if (!$user->id) {
            // Moodle user not found based on username, see if account for employeeid exists.
            $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

            $ldapconnection = $this->ldap_connect();
            $ldapuserdn = $this->ldap_find_userdn($ldapconnection, $extusername);

            $employeeiddata = ldap_read($ldapconnection, $ldapuserdn, '(objectClass=*)', array('employeeid'));
            $employeeidentries = ldap_get_entries($ldapconnection, $employeeiddata);

            if (isset($employeeidentries[0]['employeeid'][0]) && strlen($employeeidentries[0]['employeeid'][0])) {
                $existingusers = $DB->get_records(
                    'user',
                    array(
                        'idnumber' => $employeeidentries[0]['employeeid'][0],
                        'mnethostid' => $CFG->mnet_localhost_id
                    ),
                    'lastaccess DESC'
                );
                if ($existingusers) {
                    $existinguser = array_shift($existingusers);
                    $existinguser->username = trim(core_text::strtolower($username));
                    $DB->update_record('user', $existinguser);
                    $user = get_complete_user_data('id', $existinguser->id);
                }
            }
        } else if (!empty($user->idnumber)) {
            // Let's double check employeeids match between Moodle and SAML/AD.
            $userinfo = $this->get_userinfo($username);
            if ($user->idnumber != $userinfo['idnumber']) {
                // ID mismatch!
                saml_error(get_string('error:employeeid_mismatch', 'auth_saml'), '?logout');
            }
        }
    }
}
