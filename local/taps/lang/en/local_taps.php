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
 * The local_taps English language file.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Linked Courses';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

// Settings page strings.
$string['configuration'] = 'Configuration';
$string['taps_add_course_heading'] = 'Linked course settings';

// Capabilities.
$string['taps:addcourse'] = 'Add linked course';
$string['taps:listcourses'] = 'List linked courses';

// Page strings.
$string['activities'] = 'Link to course';
$string['addcourse'] = 'Add course';
$string['addcourse:submit'] = 'Add course';
$string['addcourse:step1'] = 'Choose category';
$string['addcourse:step1:submit'] = 'Continue to add course';
$string['all'] = 'All';
$string['arupadvert'] = $string['at'] = 'Advert';

$string['catalogue_region_mapping'] = 'Catalogue region override';
$string['chooseregion'] = 'Choose region: ';
$string['coursedetails'] = '{$a} details';

$string['datefrom'] = 'Date from';
$string['dateto'] = 'Date to';
$string['datesinvalid'] = 'The to date cannot be bofore the from date!';

$string['enrolment'] = 'Enrolment details';
$string['enrolment_region_mapping'] = 'Enrolment region override';
$string['enrolmentrole'] = 'Default role for enrolments';
$string['enrolmentrole_help'] = 'Select role used for automatic enrolment.<br />'
    . 'This enables the auto enrolment plugin to enrol the user in the module once the tapsenrol activity is complete.<br />'
    . 'Default is Participant or Student.';
$string['error:maxcharlength'] = 'Must be {$a->maxlength} characters or less in length.<br />Current length: {$a->actuallength}.<br />NB. Some special characters may actually count as multiple characters.';
$string['error:p_course_cost'] = '{$a} is longer than the maximum allowed 12 characters.';

$string['filter'] = 'Filter results';

$string['id'] = 'ID';
$string['invalidtapscourse'] = 'Invalid course selected.';
$string['issues'] = 'Issues';

$string['linkedcoursemismatch'] = 'Linked courses do not match.';
$string['listcourses'] = 'List linked courses';

$string['modsnotinstalled'] = 'The following activities need to be installed:
    <br />mod/arupadvert (with arupadvertdatatype_taps sub-plugin)
    <br />mod/tapsenrol
    <br />mod/tapscompletion
    <br /><br />The following local plugin needs be installed:
    <br />local/coursemetadata
    <br />local/regions';
$string['missing'] = 'missing.';

$string['norecords'] = 'No records';
$string['norecords:filter'] = 'No records match the selected filters';
$string['notfound'] = 'Not found';
$string['notlinked'] = 'Not linked to a course';

$string['overrideregions'] = 'Only add region mappings above if you wish to override those set at course level.<br />'
    . 'CTRL-click to select multiple options or de-select selected options.';

$string['shortnamecourse'] = 'Module short name';
$string['shortnamecourse_help'] = 'The short name of the module is displayed in the navigation and is used in the subject line of module email messages. If left blank the linked course ID will be used as the module shortname.';
$string['showissues'] = 'Filter only issues';
$string['showissues:reset'] = 'Remove issue filter';
$string['statuses'] = 'Status';

$string['tapscompletion'] = $string['tc'] = 'Completion';
$string['tapscourse'] = 'Linked course';
$string['tapscoursedetails'] = 'Linked course details<br />ID | Code | Name';
$string['tapsenrol'] = $string['te'] = 'Enrolment';
$string['tapsenrol:workflow'] = $string['teiw'] = 'Workflow';
