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

$string['pluginname'] = 'Arup admin';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['action:employeeid'] = 'Update employee IDs from AD';
$string['admin:accesscoursecompletionsettings'] = 'Access course completion settings';
$string['admin:accesscoursegroupsettings'] = 'Access course group settings';
$string['admin:enrolmentcheck'] = 'Run enrolment checks for selected courses/users';
$string['admin:registeradusers'] = 'Register AD users';
$string['admin:testemails'] = 'Test emails';
$string['adminfunctions'] = 'Admin functions';

$string['enrolmentcheck'] = 'Enrolment check';
$string['enrolmentcheck:course'] = 'Course';
$string['enrolmentcheck:error:staffid'] = 'Staff ID "{$a}" is invalid<br>';
$string['enrolmentcheck:process'] = 'Process Enrolment Checks';
$string['enrolmentcheck:processed'] = 'Processed enrolment check for staff id: {$a}.';
$string['enrolmentcheck:staffids'] = 'Staff IDs (one per line)';
$string['enrolmentcheck:stats:enrolled'] = '{$a->stat} user{$a->s} enrolled or already enrolled.';
$string['enrolmentcheck:stats:nonmoodle'] = '{$a->stat} non-Moodle user{$a->s}.';
$string['enrolmentcheck:stats:notenrolled'] = '{$a->stat} user{$a->s} un-enrolled or already not enrolled.';

$string['taskupdateusers'] = 'Update users';
$string['testemail'] = 'Test email';
$string['testemails'] = 'Test emails';
$string['testemails:cc'] = 'CC';
$string['testemails:close'] = 'Close';
$string['testemails:body'] = 'Body';
$string['testemails:preview'] = 'Preview email';
$string['testemails:send'] = 'Send email';
$string['testemails:sendinvite'] = 'Send as invite';
$string['testemails:subject'] = 'Subject';
$string['testemails:subject:default'] = 'Moodle Test Email';
$string['testemails:to'] = 'To';
$string['testemails:usehtml'] = 'Use HTML';

$string['userreport'] = 'User update report';

// Language strings for core modifications.
$string['activitydeletehelp'] = 'This activity cannot be deleted due to the being a part of the current {$a} completion settings, please contact an administrator if you require help.';
$string['adaccount'] = 'Active Directory account information';
$string['adaccountinfo'] = 'This account is synchronised with Active Directory, any fields that cannot be edited below are automatically updated at each login.';
$string['adaccountimage'] = 'To change your picture please update it via Arup People. It will then be updated here on your next login.';

$string['completionhdrstatichelp'] = 'You do not have permission to edit this value, please contact an administrator if you require help.';
$string['completionunlockhelp'] = 'You do not have permission to unlock completion, please contact an administrator if you require help.';
$string['completionunlockhelp:coursecriteria'] = 'Completion is locked as this activity is part of the {$a} completion criteria, please contact an administrator if you require help.';

$string['groupsstatichelp'] = 'You do not have permission to edit these values, please contact an administrator if you require help.';

$string['urgloginnotallowed'] = 'We believe that you have accessed Moodle from a digital workspace test account and have blocked '
    . 'your access to stop test accounts being created in Moodle.<br />If you need to login then please use your normal Arup '
    . 'username.<br />If you believe this not to be true then please contact moodle@arup.com for assistance.';