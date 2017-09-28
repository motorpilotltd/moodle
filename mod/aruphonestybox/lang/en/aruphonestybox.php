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
 * English strings for aruphonestybox
 *
 * @package    mod_newmodule
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Arup Honesty Box';
$string['modulenameplural'] = 'Arup Honesty Boxes';
$string['modulename_help'] = 'Arup Honesty Box allows self completion';
$string['aruphonestybox:addinstance'] = 'Add a new Arup Honesty Box';
$string['pluginadministration'] = 'Arup honesty box administration';
$string['pluginname'] = 'Arup honesty box';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['cpdformheader'] = 'CPD settings';
$string['cpdrequestsent'] = 'CPD request sent';

$string['ihavecompleted'] = 'I confirm that I have fully participated in this module and that I would like to add this learning burst to my Personal Learning History';

$string['pleaseconfirm'] = 'Please confirm you would like to add this module to your my Learning History';
$string['modal_title']   = 'Confirm';

$string['viewnotimplemented'] = 'The Arup Honesty box does not implement a view';

$string['msgincomplete:viewlink'] = 'In order to complete this module and add the learning burst to your Personal Learning History you must submit completion information.<br />When ready please <a href="{$a}">complete the form</a>.';

$string['msgincomplete'] = 'In order to complete this module and add the learning burst to your Personal Learning History you must read and confirm the statement below. <br />Please note that completion can only be added once; any further changes should be made in the CPD record.';
$string['msgpending:editlink'] = 'Your completion information has been submitted for <i>approval</i>.<br> While in <i>pending</i> status you can still modify your completion information <a href="{$a}">here</a>.';

$string['msgsuccess'] = 'Congratulations, you have confirmed that you fully participated in this module; it has been added to your Learning History';
$string['msgerror'] = 'We were unable to add the learning burst to your personal history. Please contact the Univeristy team for assistance.';
$string['tapserror'] = 'We were unable to set attendance for all participants. Please contact the Univeristy team for assistance.';

$string['msgauto'] = 'Your CPD record will be updated once you complete this module.';
$string['msgautocomplete'] = 'Your learning history has been updated';

$string['eventcpdrequestsent'] = 'CPD Request sent';

$string['showcompletiondate'] = 'Show completion date field';
$string['showcertificateupload'] = 'Show completion certificate upload';
$string['setcompletiondate'] = 'Date completed';
$string['completiondate']    = 'Completion Date';
$string['viewcertificatefile'] = 'View Certificate File';
$string['uploadcertificate'] = 'Upload certificate of completion';
$string['approvalrequired'] = 'Approval Required';
$string['approveahbcompletion'] = 'Approve Arup Honesty Box Completion';
$string['approvallink'] = 'You have completion requests pending approval. Please visit <a href="{$a}">the approval page</a> to action these requests.';

$string['reviewchanges'] = 'Review Changes';
$string['datamodified'] = 'Another user has updated the completion information since this page was loaded.<br>Please review the changes before editing.';

$string['approve:name'] = 'Name';
$string['approve:email'] = 'Email';
$string['approve:datecompleted'] = 'Date Completed';
$string['approve:certificatelink'] = 'Certificate Link';
$string['approve:actions'] = 'Actions';
$string['approve:dateapproved'] = 'Date Approved';
$string['approve:approvedby'] = 'Approved By';
$string['approve:approved'] = 'Approved';
$string['approve:approve'] = 'Approve';
$string['approve:edit'] = 'Edit';
$string['approve:alreadyapproved'] = 'Already approved.';
$string['approve:requestapproved'] = 'This request has already been approved and completion information can no longer be updated.';
$string['approve:successapproved'] = 'Successfully approved!';
$string['approve:nocompletions'] = 'No applicable completion requests found';
$string['approve:cannotapproveown'] = 'You cannot approve your own request.';

$string['error:required'] = 'Must not be empty when <i>Approval Required</i> is on';

$string['email:subject'] = 'New Completion Request for Approval';
$string['email:body'] = '<p>Dear {$a->approverfirstname},</p>
<p>I have requested approval of my Honesty Box submission.</p>
<p>Please visit your <a href="{$a->approvalurl}">approvals page</a> to review my request.</p>
<p>Kind regards,<br>{$a->userfirstname} {$a->userlastname}</p>';