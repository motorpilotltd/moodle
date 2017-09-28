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
 * English strings for arupmyproxy
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['alert:confirmproxy:no'] = 'Thank you for confirming that you DO NOT wish {$a->name} ({$a->email}) to act as your proxy on {$a->coursename}.<br />You may now close this window.';
$string['alert:confirmproxy:norecord'] = 'Sorry, we could not process your request as it has been withdrawn.<br />You may now close this window.';
$string['alert:confirmproxy:yes'] = 'Thank you for confirming that you DO wish {$a->name} ({$a->email}) to act as your proxy on {$a->coursename}.<br />You may now close this window.';
$string['alert:delete:success'] = 'You have deleted your request to act as proxy for {$a}.';
$string['alert:reminder:success'] = 'A reminder of your pending request to act as proxy for {$a} has been sent to them.';
$string['alert:request:success'] = 'Your request to act as proxy for {$a} has been sent to them.';
$string['arupmyproxy:addinstance'] = 'Add a new Arup my proxy';

$string['backtomodule'] = 'Back to module';
$string['button:delete'] = 'Delete Request';
$string['button:loginas'] = 'Login As User';
$string['button:remind'] = 'Send Reminder';
$string['button:request'] = 'Request Proxy Permissions';
$string['button:wrapper:hide'] = 'Hide proxy';
$string['button:wrapper:hide:post'] = '';
$string['button:wrapper:hide:pre'] = '<i class="fa fa-minus" style="color: #3399FF;" ></i>&nbsp;';
$string['button:wrapper:show'] = 'Start proxy';
$string['button:wrapper:show:post'] = '';
$string['button:wrapper:show:pre'] = '<i class="fa fa-plus" style="color: #3399FF;" ></i>&nbsp;';

$string['confirmproxy:question'] = 'Are you happy for {$a->name} ({$a->email}) to act as your proxy on {$a->coursename}?';
$string['currentlyloggedinas'] = '{$a->realfullname}, you are currently logged in as {$a->fullname}.';

$string['email:reminder:body'] = '<p>Dear {$a->proxyname},</p>
<p>This is a reminder that I have requested to act as your proxy on the module {$a->coursename}.</p>
<p>Please confirm or deny this request using the links below.</p>
<p><a href="{$a->allowurl}">Allow</a> | <a href="{$a->disallowurl}">Disallow</a></p>
<p>Yours sincerely,<br />
   {$a->requestername}</p>';
$string['email:reminder:subject'] = 'Reminder: Proxy Request';
$string['email:request:body'] = '<p>Dear {$a->proxyname},</p>
<p>I have requested to act as your proxy on the module {$a->coursename}.</p>
<p>Please confirm or deny this request using the links below.</p>
<p><a href="{$a->allowurl}">Allow</a> | <a href="{$a->disallowurl}">Disallow</a></p>
<p>Yours sincerely,<br />
   {$a->requestername}</p>';
$string['email:request:subject'] = 'Proxy Request';
$string['error:nouser'] = 'Please select a user';
$string['eventproxyloginasattempted'] = 'Proxy log in as attempt';
$string['eventproxyrequestcreated'] = 'Proxy request created';
$string['eventproxyrequestdeleted'] = 'Proxy request deleted';
$string['eventproxyrequestremindersent'] = 'Proxy request reminder sent';
$string['eventproxyrequestresponsecompleted'] = 'Proxy request response completed';
$string['eventproxyrequestresponsefailed'] = 'Proxy request response failed';

$string['introeditor'] = 'Description';

$string['label:loginas'] = 'Proxy for';
$string['label:pending'] = 'Pending requests';
$string['label:refused'] = 'Refused requests';
$string['label:request'] = 'Request to be proxy for';
$string['logout'] = 'Logout';
$string['logouturl'] = 'Logout URL';
$string['logouturl_help'] = 'This URL is specific to this instance of the activity and can be used anywhere within the module.<br />'
    . 'If the current user is logged in as another user following this link will log them out and redirect them to the module page '
    . '(and on to the login page, where SSO will be attempted) so they can easily login as another user again.<br />'
    . 'If the current user is logged in as themselves following this link will simply exit the module and return them to the home page.';

$string['modulename'] = 'Arup my proxy';
$string['modulename_help'] = 'The arupmyproxy activity module allows users to request, and receive, proxy access to a module on '
    . 'behalf of another user.';
$string['modulenameplural'] = 'Arup my proxy';

$string['name'] = 'Name';
$string['noarupmyproxys'] = '';

$string['pluginname'] = 'Arup my proxy';
$string['pluginadministration'] = 'Arup my proxy administration';

$string['roleid'] = 'Role to give Proxy';
$string['roleid_help'] = 'Please select the role (normally the student, or equivalent, role) that will be given to the proxy. '
    . 'This is necessary as a role assignment and capability adjustment is required to allow non-enrolled users to act as proxies. '
    . 'Once proxy requests have been initiated, this setting will be locked.';

$string['viewnotimplemented'] = 'This activity does not utilise a view page, you will be returned to the {$a} you were viewing.';
