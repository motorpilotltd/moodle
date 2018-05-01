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
 * English strings for mod_arupevidence
 *
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Arup Evidence Upload';
$string['modulenameplural'] = 'Arup Evidence Uploads';
$string['modulename_help'] = 'Arup Evidence Upload allow users to upload evidence of completion for approval.';
$string['arupevidence:addinstance'] = 'Add a new Arup Evidence Upload';
$string['pluginadministration'] = 'Arup Evidence Upload administration';
$string['pluginname'] = 'Arup Evidence Upload';

$string['cpdformheader'] = 'CPD settings';
$string['cpdrequestsent'] = 'CPD request sent';

$string['ihavecompleted'] = 'I confirm that I have fully participated in this module and that I would like to add this learning burst to my Personal Learning History';

$string['pleaseconfirm'] = 'Please confirm you would like to add this module to your my Learning History';
$string['modal_title']   = 'Confirm';

$string['viewnotimplemented'] = 'The Arup Evidence Upload does not implement a view';

$string['msgincomplete:viewlink'] = 'In order to complete this module and add the learning burst to your Personal Learning History you must submit completion information.<br />When ready please <a href="{$a}">complete the form</a>.';

$string['msgincomplete'] = 'In order to complete this module and add the learning burst to your Personal Learning History you must read and confirm the statement below. <br />Please note that completion can only be added once; any further changes should be made in the CPD record.';
$string['msgpending:editlink'] = 'Your completion information has been submitted for <i>approval</i>.<br> While in <i>pending</i> status you can still modify your completion information <a href="{$a}">here</a>.';

$string['msgsuccess'] = 'Congratulations, you have confirmed that you fully participated in this module; it has been added to your Learning History';
$string['msgerror'] = 'We were unable to add the learning burst to your personal history. Please contact the Univeristy team for assistance.';
$string['tapserror'] = 'We were unable to set attendance for all participants. Please contact the Univeristy team for assistance.';

$string['msgauto'] = 'Your CPD record will be updated once you complete this module.';
$string['msgautocomplete'] = 'Your learning history has been updated';

$string['eventcpdrequestsent'] = 'CPD Request sent';
$string['eventcoursecompletionupdated'] = 'Course completion updated';

$string['selectunit'] = 'Select unit';
$string['selectvalidityperiod'] = 'Select validity period';

$string['requireexpirydate'] = 'Require expiry date';
$string['mustendmonth'] = 'Must be end of month';
$string['requirevalidityperiod'] = 'Require validity period';
$string['expectedvalidityperiod'] = 'Expected validity period';
$string['validityperiod'] = 'Validity Period';
$string['approvalroles'] = 'Approval roles';
$string['approvalusers'] = 'Approval users';
$string['cpdorlms'] = 'CPD or LMS';
$string['arupevidence_cpd'] = 'CPD';
$string['arupevidence_lms'] = 'LMS';

$string['setcoursecompletion'] = 'Overwrite course completion';
$string['setcoursecompletion_help'] = 'Setting this will mean that when the course is completed the completion date will be overwritten with that from this activity.';
$string['setcertificationcompletion'] = 'Overwrite certification completion';
$string['setcertificationcompletion_help'] = 'Setting this will mean that when a certification containing this course is completed the completion date and expiry date will be updated with those from this activity.';

$string['showcompletiondate'] = 'Show completion date field';
$string['showcertificateupload'] = 'Show completion certificate upload';
$string['setcompletiondate'] = 'Date completed';
$string['completiondate']    = 'Completion Date';
$string['viewcertificatefile'] = 'View Certificate File';
$string['uploadcertificate'] = 'Upload certificate of completion';
$string['approvalrequired'] = 'Approval Required';
$string['approveahbcompletion'] = 'Approve Arup Evidence Upload Completion';
$string['approvallink'] = 'You have completion requests pending approval. Please visit <a href="{$a}">the approval page</a> to action these requests.';

$string['reviewchanges'] = 'Review Changes';
$string['datamodified'] = 'Another user has updated the completion information since this page was loaded.<br>Please review the changes before editing.';

$string['pending:submittedforvalidation'] = 'Thank you for uploading your training completion evidence this has now be submitted for validation. (Please allow 7 working days)';
$string['pending:completionrequests'] = 'You have completion requests pending validation. Please visit the validation page to action these requests.';

$string['completionevidence'] = 'Thank you for uploading your training completion evidence this has now been accepted and your training record updated.';
$string['provideevidence'] = 'Once you have competed your training you will need to provide evidence of completion using the Upload Evidence button.';
$string['allrequestapproved'] = "All the completion requests has been approved. You may visit the page to see the approved requests.";

$string['status:evidencesubmitteed'] = 'Evidence submitted pending validation';
$string['status:pendingvalidation'] = '{$a->numberofpending} pending validation';
$string['status:evidencerejected'] = 'Your evidence has been <i>rejected</i>, please validate you submission';
$string['status:approvedevidence'] = '{$a->numberofapproved} approved evidence';
$string['status:uploadcomplete'] = 'Upload Complete';
$string['status:awaiting'] = 'Awaiting evidence to be uploaded';

$string['label:expirydate'] = 'Expiry Date';
$string['label:enrolment'] = 'Select Enrolment';
$string['instructions'] = 'Instructions';

$string['button:uploadevidence'] = 'Upload Evidence';
$string['button:amendsubmission'] = 'Amend Submission';
$string['button:validate'] = 'Validate';
$string['button:viewsubmission'] = 'View Submission';
$string['button:check'] = 'Check';

$string['approve:name'] = 'Name';
$string['approve:email'] = 'Email';
$string['approve:datecompleted'] = 'Date Completed';
$string['approve:certificatelink'] = 'Certificate Link';
$string['approve:actions'] = 'Actions';
$string['approve:dateapproved'] = 'Date Approved';
$string['approve:approvedby'] = 'Approved By';
$string['approve:approved'] = 'Approved';
$string['approve:approve'] = 'Approve';
$string['approve:reject'] = 'Reject';
$string['approve:edit'] = 'Edit';
$string['approve:alreadyapproved'] = 'Already approved the by the other approver.';
$string['approve:alreadyrejected'] = 'Evidence was already rejected';
$string['approve:requestapproved'] = 'This request has already been approved and completion information can no longer be updated.';
$string['approve:successapproved'] = 'Successfully approved!';
$string['approve:nocompletions'] = 'No applicable completion requests found';
$string['approve:cannotapproveown'] = 'You cannot approve your own request.';
$string['approve:cannotrejectown'] = 'You cannot reject your own request.';

$string['reject:daterejected'] = 'Date Rejected';
$string['reject:rejectedby'] = 'Rejected By';
$string['reject:evidencerejected'] = 'Evidence has been rejected and an email was sent to the user.';
$string['reject:evidencerejectedalready'] = 'Evidence has already been rejected by the other approver.';

$string['error:required'] = 'Must not be empty when <i>Approval Required</i> is on';
$string['error:emptyyear'] = 'Year must not be empty';
$string['error:emptymonth'] = 'Month must not be empty';
$string['error:mustlinkedcourse'] = 'Must be Linked Course';
$string['error:cpdrequired'] = 'Must not be empty when <i>CPD</i> is selected';
$string['error:expirydate'] = 'Invalid <i>Expiry Date</i>, should be ahead of <i>Completion Date</i>.';
$string['error:noevidenceupload'] = 'Failed to approve as evidence file upload not found.';

$string['alert:restrictedaccess:tooltip'] = 'Only the Moodle Team can edit this field.';
$string['alert:approveronly'] = 'You are not allowed to make this action. You are not an approver.';

$string['selectusers'] = 'Select users';
$string['selectyear'] = 'Select a year';
$string['selectmonth'] = 'Select a month';
$string['chooseusers'] = 'Choose one or more users';
$string['validityperiod:m'] = 'month(s)';
$string['validityperiod:y'] = 'year(s)';
$string['chooseclass'] = 'Choose a class';

$string['returntocourse'] = 'Return to {$a}';

$string['noenrolments'] = 'You do not have any current enrolments on this course, please ensure you are enrolled and try again.';

$string['form:modal:validityconfirm:dismiss'] = 'Dismiss';
$string['form:modal:validityconfirm:title'] = 'Check your evidence validity period';
$string['form:modal:validityconfirm:content'] = 'Your evidence expiry date didn\'t met the exepcted validity period of {$a}. Please try again or save your evidence.';
$string['modal:validityconfirm:cancel'] = 'Cancel';
$string['modal:validityconfirm:confirm'] = 'Continue';

$string['modal:evidence:approve'] = 'approve-evidence';
$string['modal:evidence:reject'] = 'reject-evidence';
$string['modal:approveevidence:title'] = 'Approve evidence';
$string['modal:rejectevidence:title'] = 'Rejecting the submitted evidence';
$string['modal:typemessage'] = 'Type your message:';
$string['modal:rejectevidence:content'] = 'Are you sure you want to reject this evidence?';
$string['modal:approveevidence:content'] = 'Approving the evidence means the student has completed the course and this will update his/her <i>Learning History</i>.';
$string['label:processing'] = 'Processing...';
$string['modal:no'] = 'No';
$string['modal:yes'] = 'Yes';
$string['modal:rejectevidence:forminfo'] = 'The message will be sent to the user via email';

$string['email:approve:content'] = 'Your evidence has been approved';
$string['email:approve:subject'] = '<p>Hi {$a->firstname},</p>
<p>Your submitted evidence has been <b>approved</b>.</p>';

$string['email:subject'] = 'New Completion Request for Approval';
$string['email:reject:subject'] = 'Submitted evidence has been rejected';
$string['email:reject:content'] = '<p>Hi {$a->firstname},</p>
<p>Your submitted evidence has been <b>rejected</b>.<br />
Kindly review and update your evidence again.<br /></p>
<p><i>Your evidence link:</i> {$a->evidenceeditlink}</p>
<p><i>Approver comment:</i><br />
{$a->approvercomment}
</p>';

$string['email:body'] = '<p>Dear {$a->approverfirstname},</p>
<p>I have requested approval of my Evidence Upload.</p>
<p>Please visit your <a href="{$a->approvalurl}">approvals page</a> to review my request.</p>
<p>Kind regards,<br>{$a->userfirstname} {$a->userlastname}</p>';