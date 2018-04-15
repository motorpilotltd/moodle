<?php
// This file is part of the Arup cost centre system
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

$string['pluginname'] = 'Cost centres';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

// Settings.
$string['setting:help_courseid'] = 'Help {$a} ID';
$string['setting:help_courseid_desc'] = 'The "Appraisal Admin Learning Burst" {$a} ID.';
$string['settings'] = 'Configuration';
$string['tasktidyusers'] = 'Tidy users';

// Capabilities.
$string['costcentre:administer'] = 'Administer cost centres';

// Menu items.
$string['menu:index'] = 'Cost centre setup';

// Error strings.
$string['error:invalid:action'] = 'Invalid action specified.';
$string['error:invalid:page'] = 'Invalid page requested.';

// General strings.
$string['choosecostcentre'] = 'Choose cost centre';

$string['edit:cancelled'] = 'Editing cancelled, your changes were not saved.';
$string['edit:success'] = 'Editing successful, your changes have been saved.';

$string['header:adminonly'] = 'Leader / Appraisal Administrator Users';
$string['header:groupleaderappraisal'] = 'Appraisal Specific Leaders';
$string['header:hrusers'] = 'HR Users';
$string['header:learning'] = 'Learning Specific Roles';
$string['html:adminonly'] = '<p>This provides Leader access to view content of all appraisal for this cost centre except where designated VIP by an HR Leader. Appraisal Administrators can setup, track and make changes (i.e. appraiser name) to an appraisal in this cost centre.</p>';
$string['html:groupleaderappraisal'] = '<p>This provides an additional Leader sign off (i.e. summary box 5.5). Leaders designated below can only view appraisals specified on the initialise page.</p>';
$string['html:hrusers'] = '<p>This provides HR Leaders access to view content of all appraisal for this cost centre and the ability to mark any appraisal as VIP. The HR Admin can view the content of any appraisal in this cost centre not marked VIP.</p>';
$string['html:learning'] = '<p>This provides the ability to set roles for access to learning information/reports.</p>';

$string['label:appraiser'] = 'Additional appraisers (outside cost centre)';
$string['label:appraiserissupervisor'] = 'Appraiser default is Supervisor';
$string['label:businessadmin'] = 'Appraisal administrator(s)';
$string['label:groupleaderactive'] = 'Enable additional Leader sign off (i.e. summary box 5.5)';
$string['label:costcentre'] = 'Cost centre';
$string['label:enableappraisal'] = 'Enable appraisal';
$string['label:groupleader'] = 'Leader / Primary Sign Off(s)';
$string['label:groupleaderappraisal'] = 'Additional Leader(s) for specific appraisal(s) only';
$string['label:hradmin'] = 'HR Admin(s)';
$string['label:hrleader'] = 'HR Leader(s)';
$string['label:learningreporter'] = 'Can run learning reports';
$string['label:reporter'] = 'Can run reports';
$string['label:signatory'] = 'Can sign off appraisal (in addition to leader / primary sign off)';
$string['loadcostcentre'] = 'Load cost centre';

$string['selectuser'] = 'Select user';
$string['selectusers'] = 'Select users';

$string['title:index'] = 'Cost centre setup';

// Other alerts.
$string['alert:restrictedaccess'] = 'Having trouble with this page? Complete the <a href="{$a}">Appraisal Admin Learning Burst</a> if you haven\'t already.';
$string['alert:restrictedaccess:tooltip'] = 'Only the Moodle Team can edit this field.';