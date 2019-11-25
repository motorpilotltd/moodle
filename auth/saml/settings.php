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
 * Admin settings and defaults.
 *
 * @package auth_saml
 * @copyright  2017 Xantico
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once($CFG->dirroot.'/auth/saml/auth.php');

    $saml = new auth_plugin_saml();

    // Get saml parameters stored in the saml_config.json.
    if (file_exists($CFG->dataroot.'/saml_config.json')) {
        $contentfile = file_get_contents($CFG->dataroot.'/saml_config.json');
        $samlsettings = json_decode($contentfile);
    } else {
        $samlsettings = new stdClass();
    }

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_saml/pluginname', '',
        new lang_string('auth_samldescription', 'auth_saml')));

    // Start TLS.
    $yesno = array(
        new lang_string('no'),
        new lang_string('yes'),
    );

    // SAML Library Path
    $samllib = isset($samlsettings->samllib) ? $samlsettings->samllib : 'C:\inetpub\simplesaml\lib';
    $libsetting = new admin_setting_configfile('auth_saml/samllib',
        get_string('samllib', 'auth_saml'),
        get_string('samllib_description', 'auth_saml'), $samllib);
    $libsetting->set_updatedcallback('auth_saml_updatedcallback');
    $settings->add($libsetting);

    // SimpleSAMLPHP SP source
    $sp_source = isset($samlsettings->sp_source) ? $samlsettings->sp_source : 'arup-sp';
    $sp_sourcesetting = new admin_setting_configtext('auth_saml/sp_source',
        get_string('sp_source', 'auth_saml'),
        get_string('sp_source_description', 'auth_saml'), $sp_source, PARAM_RAW);
    $sp_sourcesetting->set_updatedcallback('auth_saml_updatedcallback');
    $settings->add($sp_sourcesetting);

    // Single Log out
    //pe(var_dump($samlsettings->dosinglelogout));
    $dosinglelogout = isset($samlsettings->dosinglelogout) ? (int)$samlsettings->dosinglelogout : 0;
    $dosinglelogoutsetting = new admin_setting_configselect('auth_saml/dosinglelogout',
        new lang_string('dosinglelogout', 'auth_saml'),
        new lang_string('dosinglelogout_description', 'auth_saml'), $dosinglelogout , $yesno);
    $dosinglelogoutsetting->set_updatedcallback('auth_saml_updatedcallback');
    $settings->add($dosinglelogoutsetting);

    // SAML username mapping
    $settings->add(new admin_setting_configtext('auth_saml/username',
        get_string('username', 'auth_saml'),
        get_string('username_description', 'auth_saml'), 'http://schemas.microsoft.com/ws/2008/06/identity/claims/upn', PARAM_RAW));

    // Auto Login
    $settings->add(new admin_setting_configselect('auth_saml/autologin',
        new lang_string('autologin', 'auth_saml'),
        new lang_string('autologin_description', 'auth_saml'), 0 , $yesno));

    // SAML automatic login subnet
    $settings->add(new admin_setting_configtext('auth_saml/autologin_subnet',
        get_string('autologin_subnet', 'auth_saml'),
        get_string('autologin_subnet_description', 'auth_saml'), '', PARAM_RAW));

    // SAML automatic login Azure Application Proxy
    $settings->add(new admin_setting_configselect('auth_saml/autologin_azureappproxy',
        get_string('autologin_azureappproxy', 'auth_saml'),
        get_string('autologin_azureappproxy_description', 'auth_saml'), 0 , $yesno));

    // SAML groups to bypass automatic login Azure Application Proxy
    $settings->add(new admin_setting_configtext('auth_saml/bypass_groups_azureappproxy',
        get_string('bypass_groups_azureappproxy', 'auth_saml'),
        get_string('bypass_groups_azureappproxy_description', 'auth_saml'), '' , PARAM_ALPHANUMEXT));

    // Log file path
    $settings->add(new admin_setting_configfile('auth_saml/samllogfile',
        get_string('logfile', 'auth_saml'),
        get_string('logfile_description', 'auth_saml'), ''));

    // Set to defaults if undefined (LDAP).
    // Host.
    $settings->add(new admin_setting_configtext('auth_saml/host_url',
        get_string('auth_ldap_host_url_key', 'auth_ldap'),
        get_string('auth_ldap_host_url', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

    // Use TLS
    $settings->add(new admin_setting_configselect('auth_saml/start_tls',
        new lang_string('start_tls_key', 'auth_ldap'),
        new lang_string('start_tls', 'auth_ldap'), 0 , $yesno));

    // Encoding.
    $settings->add(new admin_setting_configtext('auth_saml/ldapencoding',
        get_string('auth_ldap_ldap_encoding_key', 'auth_ldap'),
        get_string('auth_ldap_ldap_encoding', 'auth_ldap'), 'utf-8', PARAM_RAW_TRIMMED));

    // Page Size. (Hide if not available).
    $settings->add(new admin_setting_configtext('auth_saml/pagesize',
        get_string('pagesize_key', 'auth_ldap'),
        get_string('pagesize', 'auth_ldap'), '250', PARAM_INT));

    // Contexts.
    $settings->add(new auth_ldap_admin_setting_special_contexts_configtext('auth_saml/contexts',
        get_string('auth_ldap_contexts_key', 'auth_ldap'),
        get_string('auth_ldap_contexts', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

    // User Type.
    $settings->add(new admin_setting_configselect('auth_saml/user_type',
        new lang_string('auth_ldap_user_type_key', 'auth_ldap'),
        new lang_string('auth_ldap_user_type', 'auth_ldap'), 'default', ldap_supported_usertypes()));

    // User attribute.
    $settings->add(new auth_ldap_admin_setting_special_lowercase_configtext('auth_saml/user_attribute',
        get_string('auth_ldap_user_attribute_key', 'auth_ldap'),
        get_string('auth_ldap_user_attribute', 'auth_ldap'), '', PARAM_RAW));

    // Search subcontexts.
    $settings->add(new admin_setting_configselect('auth_saml/search_sub',
        new lang_string('auth_ldap_search_sub_key', 'auth_ldap'),
        new lang_string('auth_ldap_search_sub', 'auth_ldap'), 0 , $yesno));

    // Dereference aliases.
    $optderef = array();
    $optderef[LDAP_DEREF_NEVER] = get_string('no');
    $optderef[LDAP_DEREF_ALWAYS] = get_string('yes');
    $settings->add(new admin_setting_configselect('auth_saml/opt_deref',
        new lang_string('auth_ldap_opt_deref_key', 'auth_ldap'),
        new lang_string('auth_ldap_opt_deref', 'auth_ldap'), LDAP_DEREF_NEVER , $optderef));

    // User ID.
    $settings->add(new admin_setting_configtext('auth_saml/bind_dn',
        get_string('auth_ldap_bind_dn_key', 'auth_ldap'),
        get_string('auth_ldap_bind_dn', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

    // Password.
    $settings->add(new admin_setting_configpasswordunmask('auth_saml/bind_pw',
        get_string('auth_ldap_bind_pw_key', 'auth_ldap'),
        get_string('auth_ldap_bind_pw', 'auth_ldap'), ''));

    // Version.
    $versions = array();
    $versions[2] = '2';
    $versions[3] = '3';
    $settings->add(new admin_setting_configselect('auth_saml/ldap_version',
        new lang_string('auth_ldap_version_key', 'auth_ldap'),
        new lang_string('auth_ldap_version', 'auth_ldap'), 3, $versions));

    // Object class.
    $settings->add(new admin_setting_configtext('auth_saml/objectclass',
        get_string('auth_ldap_objectclass_key', 'auth_ldap'),
        get_string('auth_ldap_objectclass', 'auth_ldap'), '', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_heading('auth_saml/data_mapping',
        new lang_string('auth_data_mapping', 'auth'), ''));

    foreach ($saml->userfields as $field) {
        $settings->add(new admin_setting_configtext('auth_saml/ldap_map_' . $field,
            get_string($field),
            '', '', PARAM_RAW_TRIMMED));
    }
}