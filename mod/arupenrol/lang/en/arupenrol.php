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
 * English language strings for mod_arupenrol.
 *
 * @package   mod_arupenrol
 * @copyright 2016 Motorpilot
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['alreadyexists:add'] = 'An Arup enrolment activity already exists in this {$a}.<br />Multiple instances are not allowed.';
$string['alreadyexists:edit'] = 'Multiple Arup enrolment activities already exist in this {$a}.<br />No instance of this activity will function whilst there are multiple instances in the {$a}.';
$string['arupenrol:addinstance'] = 'Add a new Arup enrol';

$string['completionenrol'] = 'Show as complete when user is enrolled';
$string['completionnotenabled'] = 'Completion is not enabled in this {$a}.<br />Please enable completion for this activity to function.';

$string['enrolplugins:countmismatch'] = 'The number of active enrolment plugins differs from the requirement.';
$string['enrolplugins:edit'] = 'Edit enrolment settings';
$string['enrolplugins:incorrectorder'] = 'The active enrolment plugins do not match the requirement.';
$string['enrolplugins:incorrectsettings'] = 'The active enrolment plugin settings do not match the requirement.';
$string['enrolplugins:requirements'] = "Active enrolment plugins, in this order, should be 'manual', 'self' (with auto enrolment off) and 'guest'.";

$string['functionality:action:1'] = 'Actvity completes on user enrolment';
$string['functionality:action:2'] = 'Activity completes on successful key entry';
$string['functionality:action:3'] = 'Activity completes when user clicks button';
$string['functionality:buttontext'] = 'Button text';
$string['functionality:buttontype'] = 'Button type';
$string['functionality:enroluser'] = 'Enrol user';
$string['functionality:enroluser_help'] = 'Will also enrol the user as well as triggering activity completion.';
$string['functionality:header'] = 'Functionality settings';
$string['functionality:keyvalue'] = 'Key to unlock';
$string['functionality:keylabel'] = 'Label for key entry';
$string['functionality:keytransform'] = 'Transform key';
$string['functionality:keytransform_help'] = 'Will transform the required key by adding the user\'s staff ID';
$string['functionality:outroeditor'] = 'Post completion content';
$string['functionality:outroeditor_help'] = 'If this field contains content then it will be displayed n the course page when the activity has been marked as complete.';
$string['functionality:showdescriptionafter'] = 'Show description after completion';
$string['functionality:showdescriptionbefore'] = 'Show description before completion';
$string['functionality:shownameafter'] = 'Show name after completion';
$string['functionality:shownamebefore'] = 'Show name before completion';
$string['functionality:successmessage'] = 'Alert message on success';
$string['functionality:successmessage_help'] = 'If empty then no alert box will be displayed.';
$string['functionality:unenrolbuttontext'] = 'Unenrol button text';
$string['functionality:unenrolbuttontype'] = 'Unenrol button type';
$string['functionality:unenroluser'] = 'Allow user to unenrol';
$string['functionality:unenroluser_help'] = 'Will display a button which enables the user to unenrol themselves.';
$string['functionality:usegroupkeys'] = 'Use group keys';
$string['functionality:usegroupkeys_help'] = 'Will check entered key against group keys and add the user to groups accordingly.';


$string['introeditor'] = 'Description';

$string['modulename'] = 'Arup enrol';
$string['modulename_help'] = 'Add an Arup enrol activity to the course.';
$string['modulename_link'] = 'mod/arupenrol/view';
$string['modulenameplural'] = 'Arup enrols';

$string['name'] = 'Name';

$string['pluginname'] = 'Arup enrol';
$string['pluginadministration'] = 'Arup enrol administration';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';
$string['process:badkey'] = 'Sorry, the key you entered was incorrect, please try again.';
$string['process:couldnotenrol'] = 'Sorry, it was not possible to enrol you in the {$a}, please try again.';

$string['uservisible:availableinfo'] = '<br /><br />{$a}';
$string['uservisible:not'] = 'You do not have appropriate privileges to enrol on this module. If you think that you should then please '
    . 'contact <a href="mailto:moodle.support@arup.com">moodle.support@arup.com</a> for assistance.'
    . '{$a}';

$string['viewnotimplemented'] = 'This activity does not utilise a view page, you will be returned to the {$a} you were viewing.';
