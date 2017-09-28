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
 * Strings for component 'block_arup_mylearning', language 'en'
 *
 * @package block_arup_mylearning
 */

$string['arup_mylearning:addinstance'] = 'Add a my learning block';
$string['arup_mylearning:myaddinstance'] = 'Add a my learning block to My home';

// Name.
$string['pluginname'] = 'Arup my learning';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

// Capabilities.
$string['arup_mylearning:addinstance'] = 'Add the My learning block';
$string['arup_mylearning:addcpd'] = 'Add CPD';
$string['arup_mylearning:editcpd'] = 'Edit CPD';
$string['arup_mylearning:deletecpd'] = 'Delete CPD';
$string['arup_mylearning:myaddinstance'] = 'Add the My learning block to My home';

// Tabs.
$string['me'] = 'Me';
$string['overview'] = 'My modules';
$string['myteaching'] = 'My teaching';
$string['myhistory'] = 'My history';
$string['bookmarked'] = 'My wishlist';

// Content.
$string['actions'] = 'Actions';
$string['alert:cancelled:add'] = 'Add CPD process cancelled, CPD has not been added.';
$string['alert:cancelled:delete'] = 'Delete CPD process cancelled, CPD has not been deleted.';
$string['alert:cancelled:edit'] = 'Edit CPD process cancelled, CPD has not been edited.';
$string['alert:cannot:add'] = 'It is currently not possible to add a CPD.';
$string['alert:cannot:delete'] = 'It is currently not possible to delete a CPD.';
$string['alert:cannot:edit'] = 'It is currently not possible to edit a CPD.';
$string['alert:edit:nocpd'] = 'Could not retrieve selected CPD to edit.';
$string['alert:edit:notown'] = 'You can only edit your own CPDs.';
$string['alert:error:add'] = 'Sorry, there was a problem adding your CPD.<br />Error message: {$a}';
$string['alert:error:delete'] = 'Sorry, there was a problem deleting your CPD.';
$string['alert:error:edit'] = 'Sorry, there was a problem editing your CPD.<br />Error message: {$a}';
$string['alert:error:failedtoconnect'] = 'Failed to connect to CPD server.';
$string['alert:success:add'] = 'You have successfully added your CPD.';
$string['alert:success:delete'] = 'You have successfully deleted your CPD.';
$string['alert:success:edit'] = 'You have successfully edited your CPD.';
$string['addcpd'] = 'Add CPD';
$string['addcpd:save'] = 'Add CPD';
$string['addcpd_help'] = 'This is used to upload items to your learning history.';

$string['certificateno'] = 'Certificate number';
$string['classroom'] = 'Classroom';
$string['close'] = 'Close';
$string['confirmdeletecpd'] = 'Are you sure you want to delete the CPD \'{$a}\'?';
$string['couldnotloadenrolment'] = 'Sorry, it was not possible to load further information for this history entry.';
$string['cpd'] = 'Learning Burst';
$string['cpd:certificateno'] = 'Certificate number';
$string['cpd:classcategory'] = 'Subject category';
$string['cpd:classcompletiondate'] = 'Completion date';
$string['cpd:classcost'] = 'Cost';
$string['cpd:classcostcurrency'] = 'Currency';
$string['cpd:classname'] = 'Class name';
$string['cpd:classstartdate'] = 'Start date';
$string['cpd:classtype'] = 'Learning method';
$string['cpd:duration'] = 'Duration';
$string['cpd:durationunitscode'] = 'Duration units';
$string['cpd:expirydate'] = 'Expiry date';
$string['cpd:header'] = 'Details';
$string['cpd:healthandsafetycategory'] = 'Health and safety category';
$string['cpd:learningdesc'] = 'Learning description';
$string['cpd:location'] = 'Location';
$string['cpd:provider'] = 'Provider';

$string['date'] = 'Date';
$string['date:completion'] = 'Completion date';
$string['date:expiry'] = 'Expiry date';
$string['date:start'] = 'Start date';
$string['deletecpd'] = 'Delete CPD';
$string['deletecpd_help'] = 'This is used to delete items from your learning history.';
$string['deletecpd:save'] = 'Delete';
$string['duration'] = 'Duration';
$string['durationunits'] = 'Duration units';

$string['editcpd'] = 'Edit CPD';
$string['editcpd_help'] = 'This is used to edit items in your learning history.';
$string['editcpd:save'] = 'Save';
$string['elearning'] = 'E-learning';
$string['enrolments'] = 'Enrolments';
$string['export:excel'] = 'Export to Excel';

$string['halogen:back'] = 'Back to My history';
$string['halogen:completed'] = 'Completed Date';
$string['halogen:historyavailable'] = 'You have learning history from Halogen available to view.';
$string['halogen:intro'] = 'Here is a summary of your Halogen learning history.';
$string['halogen:nohistory'] = 'You have no Halogen learning history';
$string['halogen:passfail'] = 'Pass/Fail';
$string['halogen:score'] = 'Score';
$string['halogen:title'] = 'Learning Activity';
$string['halogen:viewhistory'] = 'View your Halogen learning history';

$string['learningdescription'] = 'Learning description';
$string['location'] = 'Location';

$string['methodology'] = 'Type';
$string['modal:certificateno'] = 'Certificate number';
$string['modal:classcategory'] = 'Category';
$string['modal:classstartdate'] = 'Start date';
$string['modal:classtype'] = 'Method';
$string['modal:classcompletiondate'] = 'Completion date';
$string['modal:courseobjectives'] = 'Course objectives';
$string['modal:duration'] = 'Duration';
$string['modal:expirydate'] = 'Expiry date';
$string['modal:learningdesc'] = 'Learning description';
$string['modal:location'] = 'Location';
$string['modal:provider'] = 'Provider';
$string['more'] = 'More';
$string['myhistory:intro'] = 'Below is a list of modules that you are have completed. '
    . 'To add additional CPD entries please click on add CPD at the bottom of the page. '
    . 'You can also export your CPD learning history by click on export to Excel at the bottom of the page.';
$string['myteaching:intro'] = 'Below is a list of courses that you are a tutor for.';

$string['nohistory'] = 'You have no learning history.';

$string['open'] = 'Open';
$string['overview:history'] = 'To view completed modules please visit the \'{$a}\' tab above.';
$string['overview:intro'] = 'Below is a list of courses that you are participating in or have recently completed.';

$string['provider'] = 'Provider';

$string['status'] = 'Status';
$string['status:attended'] = 'Completed';
$string['status:cancelled'] = 'Cancelled';
$string['status:placed'] = 'In progress';
$string['status:requested'] = 'Pending approval';
$string['status:unknown'] = 'Unknown';
$string['status:waitlisted'] = 'Waiting list';

$string['worksheet:history'] = 'Learning history';

// Errors.
$string['exportfailed'] = 'Sorry, the export failed.';

// Settings.
$string['methodologyfield'] = 'Coursemetadatafield for methodology';