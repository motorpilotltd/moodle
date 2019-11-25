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
 * The auth_saml index (login processing) page.
 *
 * @package    auth_saml
 * @copyright  2016 Motorpilot Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('SAML_INTERNAL', 1);

$requestwantsurl = !empty($_REQUEST['wantsurl']) ? filter_var(urldecode($_REQUEST['wantsurl']), FILTER_VALIDATE_URL) : null;
if (!empty($requestwantsurl)) {
    $wantsurl = $requestwantsurl;
} else {
    $wantsurl = null;
}

// In order to avoid session problems we first do the SAML issues and then...
// we log in and register the attributes of user.

try {
    // We read saml parameters from a config file instead from the database...
    // due we can not operate with the moodle database without load all...
    // moodle session issue.

    // We need to get dataroot and wwwroot from CFG without loading moodle database.
    $CFG = new stdClass;
    $config = file_get_contents('../../config.php');
    $config = preg_replace('/\s+/', '', $config);
    if (preg_match('/^.+?\$CFG\-\>dataroot=[\'"](.+?)[\'"];/', $config, $match)) {
        $CFG->dataroot = $match[1];
    } else {
        throw new Exception('Moodle dataroot not found');
    }
    if (preg_match('/^.+?\$CFG\-\>wwwroot=[\'"](.+?)[\'"];/', $config, $match)) {
        $CFG->wwwroot = $match[1];
    } else {
        throw new Exception('Moodle wwwroot not found');
    }

    if (file_exists($CFG->dataroot.'/saml_config.json')) {
        $contentfile = file_get_contents($CFG->dataroot.'/saml_config.json');
    } else {
        throw new Exception('SAML config params are not set.');
    }

    $samlparams = json_decode($contentfile);

    if (!file_exists($samlparams->samllib.'/_autoload.php')) {
        throw new Exception('simpleSAMLphp lib loader file does not exist: '.$samlparams->samllib.'/_autoload.php');
    }
    include_once($samlparams->samllib.'/_autoload.php');
    $as = new SimpleSAML_Auth_Simple($samlparams->sp_source);

    if (isset($_GET["logout"])) {
        if (isset($_SERVER['SCRIPT_URI'])) {
            $urltogo = $_SERVER['SCRIPT_URI'];
            $urltogo = str_replace('auth/saml/index.php', '', $urltogo);
        } else if (isset($_SERVER['HTTP_REFERER'])) {
            $urltogo = $_SERVER['HTTP_REFERER'];
        } else {
            $urltogo = '/';
        }

        if ($samlparams->dosinglelogout) {
            $as->logout($urltogo);
            exit(); // The previous line issues a redirect.
        } else {
            header('Location: '.$urltogo);
            exit();
        }
    }

    $requestparams = array();
    if (!empty($wantsurl)) {
        // Keep wantsurl available but still return to correct place to process.
        $requestparams['ReturnTo'] = $CFG->wwwroot . '/auth/saml/index.php?wantsurl=' . urlencode($wantsurl);
    }
    $as->requireAuth($requestparams);
    $validsamlsession = $as->isAuthenticated();
    $samlattributes = $as->getAttributes();
} catch (Exception $e) {
    session_destroy();
    require_once('../../config.php');
    require_once('error.php');

    global $CFG, $err, $PAGE, $OUTPUT;
    $PAGE->set_url('/auth/saml/index.php');
    $PAGE->set_context(context_system::instance());

    $pluginconfig = get_config('auth_saml');
    $urltogo = $CFG->wwwroot . '/';

    $err['login'] = $e->getMessage();
    log_saml_error('Moodle SAML module:'. $err['login'], $pluginconfig->samllogfile);;
    saml_error($err['login'], $urltogo, $pluginconfig->samllogfile);
}

// Now we close simpleSAMLphp session.
session_destroy();

// We load all moodle config and libs.
require_once('../../config.php');

// Check if this user should be sent to manual login page.
if (isset($samlattributes['http://schemas.microsoft.com/ws/2008/06/identity/claims/groups'])) {
    $bypassgroupsconfig = get_config('auth_saml', 'bypass_groups_azureappproxy');
    if ($bypassgroupsconfig) {
        $bypassgroups = explode("\n", $bypassgroupsconfig);
        if (!empty(array_intersect($bypassgroups, $samlattributes['http://schemas.microsoft.com/ws/2008/06/identity/claims/groups']))) {
            header('Location: ' . $CFG->wwwroot . '/auth/saml/login.php?saml=0');
            exit();
        }
    }
}

require_once($CFG->dirroot.'/login/lib.php');
require_once('error.php');

global $CFG, $USER, $SESSION, $err, $DB, $PAGE;

$PAGE->set_url('/auth/saml/index.php');
$PAGE->set_context(context_system::instance());

// Can access Moodle config/session now.
$setwantsurl = !empty($wantsurl)
               && strpos($wantsurl, $CFG->wwwroot) === 0 // Must be internal.
               && strpos($wantsurl, $CFG->httpswwwroot.'/login/?') !== 0
               && strpos($wantsurl, $CFG->httpswwwroot.'/login/index.php') !== 0
               && strpos($wantsurl, $CFG->httpswwwroot.'/auth/saml/login.php') !== 0
               && strpos($wantsurl, $CFG->httpswwwroot.'/auth/saml/index.php') !== 0;
if ($setwantsurl) {
    $SESSION->wantsurl = $wantsurl;
}

// Get the plugin config for saml.
$pluginconfig = get_config('auth_saml');

if (!$validsamlsession) {
    // Not valid session. Ship user off to Identity Provider.
    unset($USER);
    try {
        $as = new SimpleSAML_Auth_Simple($samlparams->sp_source);
        $as->requireAuth($requestparams);
    } catch (Exception $e) {
        $err['login'] = $e->getMessage();
        saml_error($err['login'], $CFG->wwwroot, $pluginconfig->samllogfile);
    }
} else {
    // Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front.
    // We require the plugin to know that we are now doing a saml login in hook user_login.
    $GLOBALS['saml_login'] = true;

    // Make variables accessible to saml->get_userinfo. Information will be...
    // ...requested from authenticate_user_login -> create_user_record / update_user_record.
    $GLOBALS['samlloginattributes'] = $samlattributes;

    if (isset($pluginconfig->username) && $pluginconfig->username != '') {
        $usernamefield = $pluginconfig->username;
    } else {
        $usernamefield = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn';
    }

    if (!isset($samlattributes[$usernamefield])) {
        $err['login'] = get_string('username_not_found', 'auth_saml', $usernamefield);
        saml_error($err['login'], '?logout', $pluginconfig->samllogfile);
    }
    $username = $samlattributes[$usernamefield][0];
    $username = trim(core_text::strtolower($username));

    // Check if the user exists.
    $userexists = $DB->get_record("user", array("username" => $username));

    $authoriseuser = true;
    $authoriseerror = '';

    // User creation not allowed.
    if (!$userexists && !empty($CFG->authpreventaccountcreation)) {
        $authoriseerror = get_string('not_authorised', 'auth_saml', $username);
        $authoriseuser = false;
    }

    if (!$authoriseuser) {
        $err['login'] = "<p>" . $authoriseerror . "</p>";
        saml_error($err, '?logout', $pluginconfig->samllogfile);
    }

    // Just passes time as a password. User will never log in directly to Moodle with this password anyway or so we hope?
    $failurereason = null;
    $user = authenticate_user_login($username, time(), false, $failurereason, false);
    if ($user === false) {
        $message =
                ($failurereason == AUTH_LOGIN_SUSPENDED)
                ? get_string('error:suspended_account', 'auth_saml', $username)
                : get_string('error:authentication_process', 'auth_saml', $username);
        $err['login'] = $message;
        saml_error($err['login'], '?logout', $pluginconfig->samllogfile);
    }

    if (!empty($user->lang)) {
        // Unset previous session language - use user preference instead.
        unset($SESSION->lang);
    }

    complete_user_login($user);

    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

    // Sets the username cookie.
    if (!empty($CFG->nolastloggedin)) {
        // Do not store last logged in user in cookie.
        // Auth plugins can temporarily override this from loginpage_hook().
        // Do not save $CFG->nolastloggedin in database!

    } else if (empty($CFG->rememberusername) or ($CFG->rememberusername == 2 and empty($frm->rememberusername))) {
        // No permanent cookies, delete old one if exists.
        set_moodle_cookie('');

    } else {
        set_moodle_cookie($USER->username);
    }

    $urltogo = core_login_get_return_url();

    if (isset($err) && !empty($err)) {
        saml_error($err, $urltogo, $pluginconfig->samllogfile);
    }

    // Test the session actually works by redirecting to self.
    $SESSION->wantsurl = $urltogo;
    redirect(new moodle_url(get_login_url(), array('testsession' => $USER->id)));
}
