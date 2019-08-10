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

defined('MOODLE_INTERNAL') || die;

$string['allclasses'] = 'All classes';
$string['alreadyexists:add'] = 'An Arup linked course completion activity already exists in this {$a}.<br />Multiple instances are not allowed.';
$string['alreadyexists:edit'] = 'Multiple Arup linked course completion activities already exist in this {$a}.<br />No instance of this activity will function whilst there are multiple instances in the {$a}.';
$string['arupadvertmissing'] = 'The Arup advert activity is not installed.<br />Please install and then add an Arup advert activity to this {$a} before proceeding.';
$string['arupadvertnotinstalled'] = 'No Arup advert activity was found in this {$a}.<br />Please add an Arup advert activity before proceeding.';
$string['arupadvertnottaps'] = 'The Arup advert activity in this {$a} is not using the linked course datatype.<br />Please update the Arup advert activity before proceeding.';
$string['arupadverttoomany'] = 'There is more than one Arup advert activity in this {$a}.<br />Please ensure only one Arup advert activity is present before proceeding.';
$string['attended'] = 'You have successfully completed this module and your learning history has been updated.';
$string['autocompletion'] = 'Automatically update learning history on course completion';
$string['autocompletionhint'] = 'This setting can only be changed by administrators.';

$string['backtomodule'] = 'Back to module';
$string['bookingstatus'] = 'Status';

$string['chooseclass'] = 'Choose class';
$string['classwithname'] = 'Class : {$a}';
$string['completionattended'] = 'Mark as complete when user has been marked as attended';
$string['completionfailed'] = '{$a->user} : Failed to set as \'{$a->status}\' on {$a->classname} [{$a->errormessage}]';
$string['completionfailed:invalidclass'] = 'Invalid class';
$string['completionfailed:invalidenrolment'] = 'Invalid enrolment';
$string['completionfailed:invalidstatus'] = 'Invalid status';
$string['completionsucceeded'] = '{$a->user} : Successfully set as \'{$a->status}\' on {$a->classname}';
$string['completiontimetype'] = 'Completion time to use';
$string['completiontimetype:classendtime'] = 'Class end time';
$string['completiontimetype:currenttime'] = 'Current time at moment of completion';

$string['eventstatusesupdated'] = 'Statuses updated';

$string['filter'] = 'Filter';

$string['inprogress'] = 'You are currently in progress.<br />If you have recently completed all the necessary requirements your overall result should be updated shortly.';
$string['installationissue'] = 'There are issues with the installation settings for this activity and it is not currently accessible.<br />
    Please inform the site administrator if this problem persists.';
$string['intro'] = 'Intro';

$string['markattendance'] = 'Mark attendance';
$string['modulename'] = 'Arup linked course completion';
$string['modulename_help'] = 'Adds an linked course completion activity to enable the reporting of completion status back to users\' learning history, either automatically or manually.';
$string['modulename_link'] = 'mod/tapscompletion/view';
$string['modulenameplural'] = 'Arup linked course completions';

$string['na'] = 'N/A';
$string['name'] = 'Name';
$string['noapplicablecourses'] = 'No applicable linked courses available';
$string['nousers'] = 'No applicable users';

$string['pluginname'] = 'Arup linked course completion';
$string['pluginadministration'] = 'Arup linked course completion administration';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['selectallforclass'] = 'All for class';
$string['staffid'] = 'Staff ID';

$string['tapscompletion:addinstance'] = 'Add a new linked course completion';
$string['tapscompletion:updatecompletion'] = 'Update linked course completion';
$string['tapscompletion:setautocompletion'] = 'Set automatic linked course completion';
$string['tapscompletion'] = 'Arup linked course completion ';
$string['tapscourse'] = 'Linked course';
$string['tapscourse_help'] = 'Linked course selection is determined by the Arup advert activity.';
$string['tapsenrolmissing'] = 'The linked course enrolment activity is not installed.<br />Please install and then add a linked course enrolment activity to this {$a} before proceeding.';
$string['tapsenroltoomany'] = 'There is more than one linked course enrolment activity in this {$a}.<br />Please ensure only one linked course enrolment activity is present before proceeding.';
$string['tapsenrolnotinstalled'] = 'No linked course enrolment activity was found in this {$a}.<br />Please add a linked course enrolment activity before proceeding.';
$string['taskactivitycleanup'] = 'Activity cleanup.';
$string['taskrecalccompletion'] = 'Recalculate completion.';

$string['updateusers'] = 'Update users';
$string['user'] = 'User';
$string['userstocomplete'] = 'Users to complete';