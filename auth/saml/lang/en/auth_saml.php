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
 * English language file for auth_saml.
 *
 * @package    auth_saml
 * @copyright  2016 Motorpilot Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_samldescription'] = 'SSO Authentication using SimpleSAML';

$string['autologin'] = 'SAML automatic login';
$string['autologin_description'] = 'Automatically redirect to SAML idP without showing a login form';
$string['autologin_subnet'] = 'SAML automatic login subnet';
$string['autologin_subnet_description'] = 'If set, it will only attempt auto login with clients in this subnet. Format: xxx.xxx.xxx.xxx/bitmask. Separate multiple subnets with \',\' (comma).';

$string['db_reset_button'] = 'Reset values to factory settings';
$string['db_reset_error'] = 'Error reseting the saml plugin values';
$string['dosinglelogout'] = 'Single Log out';
$string['dosinglelogout_description'] = 'Check it to enable the single logout. This will log out you from moodle, identity provider and all conected service providers';

$string['error:authentication_process'] = 'Error in authenticating {$a}';
$string['error:employeeid_duplicate'] = 'There has been a issue with the details for your account discovered.<br />'
        . 'Please contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> with your username and staff id for investigation.';
$string['error:employeeid_mismatch'] = 'There has been a mismatch in details for your account discovered.<br />'
        . 'Please contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> with your username and staff id for investigation.';
$string['error:employeeid_nohub'] = 'There has been a issue with a missing linked HR record for your account discovered.<br />'
        . 'Please contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> with your username and staff id for investigation.';
$string['error:employeeid_none'] = 'There has been a issue with missing details for your account discovered.<br />'
        . 'Please contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> with your username and staff id for investigation.';
$string['error:suspended_account'] = 'Your account ({$a}) is currently suspended.<br />'
        . 'Please contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> to re-activate your account.';

$string['form:username'] = 'Email address';
$string['form:username_help'] = 'Internal users: Please use your @arup.com ID'
        . '<br /><br />External users: Please use either the username given to you or the email associated with your account.';
$string['form_error'] = 'There are errors in the form, please check and update.';

$string['failedtocopyconfig'] = 'Failed to copy the configuration file from {\$a->source} to {\$a->destination}';

$string['invalidlogin'] = 'Invalid login, please try again.';
$string['invalidlogin:email'] = 'Invalid login, please enter an email address.';

$string['logfile'] = 'Log file path';
$string['logfile_description'] = 'Set a filename if you want log the SAML plugin errors in a different file that the syslog (Use an absolute path or Moodle will save this file in the moodledata folder)';
$string['login:aruplink'] = 'Log in with your Arup Account';
$string['login:ormanual'] = 'Or enter your Moodle account details';

$string['not_authorised'] = '{$a} could not be authorised.';

$string['pluginname'] = 'SAML Authentication';
$string['pluginnotenabled'] = 'Plugin not enabled!';

$string['redirect_message'] = '<i class="fa fa-spinner fa-pulse"></i> Redirecting to Arup SSO Server...';

$string['samllib'] = 'SimpleSAMLphp Library path';
$string['samllib_description'] = 'Library path for the SimpleSAMLphp environment you want to eg: C:\inetpub\simplesamlphp';
$string['samllib_error'] = "SimpleSAMLphp lib directory {\$a} is not correct.";
$string['sp_source'] = 'SimpleSAMLPHP SP source';
$string['sp_source_description'] = 'Select the SP source you want to connect to moodle. (Sources are in /config/authsources.php).';

$string['username'] = 'SAML username mapping';
$string['username_description'] = 'SAML attribute that is mapped to Moodle username';
$string['username_not_found'] = 'IdP returned a set of data that did not contain the SAML username mapping field ({$a}). This field is required to login.';