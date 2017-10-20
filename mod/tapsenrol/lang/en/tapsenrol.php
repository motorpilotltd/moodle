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
 * English language strings for mod_tapsenrol.
 *
 * @package   mod_tapsenrol
 * @copyright 2016 Motorpilot
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['actions'] = 'Actions';
$string['admin:blockediting'] = 'This page is currently in admin mode, for block editing.';
$string['admin:blocks:cancel'] = 'Edit blocks on cancellation page';
$string['admin:blocks:enrol'] = 'Edit blocks on enrol page';
$string['admin:dropdown'] = 'Admin tools';
$string['alert:classroom:requested'] = 'Your place on this module is awaiting review by your approver, once approved you will be granted access to any restricted materials below.';
$string['alert:elearning:requested'] = 'Your place on this module is awaiting review by your approver, once approved you will be granted access to any restricted materials below.';
$string['alert:iw:classroom:requested'] = 'Your place on this module is awaiting review by {$a->sponsorname} ({$a->sponsoremail}), once approved you will be granted access to any restricted materials below.'
    . '<br />'
    . 'Your approval request was submitted on {$a->requestdate} and will be automatically cancelled if not approved within {$a->cancelafter} or by {$a->cancelbefore} before the start of the class.';
$string['alert:iw:classroom:requested:apply'] = 'Your place on this module is awaiting review by {$a->sponsorname} ({$a->sponsoremail}), once approved you will be added to the waiting list.'
    . '<br />'
    . 'Your approval request was submitted on {$a->requestdate} and will be automatically cancelled if not approved within {$a->cancelafter} or by {$a->cancelbefore} before the start of the class.';
$string['alert:iw:elearning:requested'] = 'Your place on this module is awaiting review by {$a->sponsorname} ({$a->sponsoremail}), once approved you will be granted access to any restricted materials below.'
    . '<br />'
    . 'Your approval request was submitted on {$a->requestdate} and will be automatically cancelled if not approved within {$a->cancelafter}.';
$string['alert:iw:elearning:requested:apply'] = 'Your place on this module is awaiting review by {$a->sponsorname} ({$a->sponsoremail}), once approved you will be added to the waiting list.'
    . '<br />'
    . 'Your approval request was submitted on {$a->requestdate} and will be automatically cancelled if not approved within {$a->cancelafter}.';
$string['allclasses'] = 'All classes';
$string['alreadyexists:add'] = 'An Arup linked course enrolment activity already exists in this {$a}.<br />Multiple instances are not allowed.';
$string['alreadyexists:edit'] = 'Multiple Arup linked course enrolment activities already exist in this {$a}.<br />No instance of this activity will function whilst there are multiple instances in the {$a}.';
$string['applicant'] = 'Applicant';
$string['approve:actions'] = 'Actions';
$string['approve:alreadydone'] = 'This request has already been {$a}.';
$string['approve:approvaldate'] = 'Approval date';
$string['approve:approve'] = 'Approve';
$string['approve:approved'] = 'approved';
$string['approve:bookingstatus'] = 'Enrolment status';
$string['approve:class'] = 'Class';
$string['approve:classstartdate'] = 'Class date';
$string['approve:comments'] = 'Why are you rejecting this request?';
$string['approve:comments_help'] = "Make any comments regarding your reasons for rejecting this request. Comments will be forwarded to the applicant.";
$string['approve:course'] = 'Course';
$string['approve:error:rejectioncomments'] = 'Comments are required when rejecting a request.';
$string['approve:history'] = 'Approval history';
$string['approve:info:either'] = 'Please confirm whether you are approving or rejecting this request.';
$string['approve:info:approve'] = 'Please confirm that you approve the following request.';
$string['approve:info:reject'] = 'Please confirm that you are rejecting the following request.';
$string['approve:name'] = 'Name';
$string['approve:no'] = 'The requested enrolment does not require approval.';
$string['approve:nohistory'] = 'No approval history';
$string['approve:nooutstanding'] = 'No outstanding approvals';
$string['approve:norecord'] = 'No request with the specified ID was found.';
$string['approve:notsponsor'] = 'You are not the assigned approver for the specified request.';
$string['approve:outstandingapprovals'] = 'Outstanding approvals';
$string['approve:reject'] = 'Reject';
$string['approve:rejected'] = 'rejected';
$string['approve:requestcomments'] = 'Applicant comments';
$string['approve:requested'] = 'Approval requested';
$string['approve:sponsor'] = 'Approver';
$string['approve:sponsoremail'] = 'Approver email';
$string['approve:status'] = 'Approval status';
$string['approve:thankyou'] = 'Thank you, the request has been {$a}.';
$string['approve:title'] = 'Enrolment approval';
$string['approve:title:approve'] = 'Approve request';
$string['approve:title:either'] = 'Approve or reject request';
$string['approve:title:reject'] = 'Reject request';
$string['arupadvertmissing'] = 'The Arup advert activity is not installed.<br />Please install and then add an Arup advert activity to this {$a} before proceeding.';
$string['arupadvertnotinstalled'] = 'No Arup advert activity was found in this {$a}.<br />Please add an Arup advert activity before proceeding.';
$string['arupadvertnottaps'] = 'The Arup advert activity in this {$a} is not using the linked course datatype.<br />Please update the Arup advert activity before proceeding.';
$string['arupadverttoomany'] = 'There is more than one Arup advert activity in this {$a}.<br />Please ensure only one Arup advert activity is present before proceeding.';

$string['backtocoursemanager'] = 'Back to course manager';
$string['backtomanageenrolments'] = 'Back to manage enrolments';
$string['backtomodule'] = 'Back to module';
$string['backtoprevious'] = 'Back to previous page';
$string['bookingstatus'] = 'Status';
$string['button:approve'] = 'Approve';
$string['button:approve:confirm'] = 'Confirm approval';
$string['button:cancel'] = 'Cancel';
$string['button:reject'] = 'Reject';
$string['button:reject:confirm'] = 'Confirm rejection';

$string['cancel:alert:cancelled'] = 'Cancellation process halted, your enrolment has not been cancelled.';
$string['cancel:alert:error'] = 'Sorry, there was a problem processing the cancellation of your enrolment on the class {$a->classname} for {$a->coursename}.<br />Error message: {$a->message}';
$string['cancel:alert:success'] = 'Your enrolment on the class {$a->classname} for {$a->coursename} has successfully been cancelled.';
$string['cancel:comments'] = 'Reason for cancelling';
$string['cancel:comments_help'] = 'Please provide the reason you are cancelling this enrolment.';
$string['cancel:error:enrolmentdoesnotexist'] = 'Sorry, the enrolment does not exist, please try again.';
$string['cancel:error:failedtoconnect'] = 'Failed to connect to cancellation server';
$string['cancelenrolment'] = 'Cancel Enrolment';
$string['cancelenrolment:areyousure'] = 'Are you sure you want to cancel your enrolment on:';
$string['cannotenrol'] = 'Sorry, you are not able to enrol on classes in this {$a->course}.{$a->reason}';
$string['cannotenrol:regions'] = '<br />Enrolment is only allowed from the following regions: {$a}.';
$string['classes:classroom'] = 'To enrol onto an available class or express your interest please select from the table below:';
$string['classes:elearning'] = 'To enrol onto the e-learning module please select from the table below:';
$string['classes:mixed'] = 'To enrol please select from the table below:';
$string['classfull'] = 'Full';
$string['classname'] = 'Class Code';
$string['close'] = 'Close';
$string['completionenrolment'] = 'Show as complete when user has had their request approved';
$string['cost'] = 'Cost';
$string['couldnotloadcourse'] = 'Sorry, the {$a} details could not be loaded.';

$string['date'] = 'Date';
$string['duration'] = 'Duration';

$string['editclass'] = 'Edit Linked Course Classes';
$string['editcourse'] = 'Edit Linked Course';
$string['enrol'] = 'Enrol Here';
$string['enrol:alert:alreadyattended'] = 'Sorry, you cannot currently enrol on another class as you have an active completed enrolment.';
$string['enrol:alert:alreadyattended:certification'] = 'Your window for retaking this course opens on {$a}, please come back to re-enrol after this date.';
$string['enrol:alert:alreadyattended:help'] = 'Should you have any further questions or require further assistance please contact moodle.support@arup.com.';
$string['enrol:alert:cancelled'] = 'Enrolment process cancelled, you have not been enrolled on a class.';
$string['enrol:alert:enrolmentclosed'] = 'Sorry, you can not enrol on a class in the {$a} hours prior to it starting.';
$string['enrol:alert:error'] = 'Sorry, there was a problem processing your enrolment on the class {$a->classname} for {$a->coursename}.<br />Error message: {$a->message}';
$string['enrol:alert:success'] = 'You have successfully been enrolled on the class {$a->classname} for {$a->coursename}.<br />Booking Status: {$a->message}';
$string['enrol:alert:success:alreadyenrolled'] = 'You are have already been enrolled on this module, your enrolment details should now have been updated.';
$string['enrol:closed'] = 'Enrolment Closed';
$string['enrol:closed_help'] = 'Enrolment for this class has now closed.<br /><br />Please contact your L&D administrator directly to enquire about enrolling.';
$string['enrol:comments'] = 'Comments to approver:';
$string['enrol:comments_help'] = "Make any comments regarding your enrolment below in the 'Comments to approver' box. Then click on 'Submit for Approval' to forward your enrolment to your Group Training Contact for approval.";
$string['enrol:declaration:required'] = 'All declarations must be confirmed';
$string['enrol:enrolmentkey'] = 'Enrolment key';
$string['enrol:enrolmentkey:error'] = 'The enrolment key you entered was incorrect.';
$string['enrol:sponsoremail'] = 'Approver\'s email address';
$string['enrol:sponsoremail_help'] = 'This is the email of the person responsible for approving your requests.';
$string['enrol:sponsoremail:error:invalid'] = 'Email address is not valid';
$string['enrol:sponsoremail:error:noldap'] = 'Cannot connect to validate the email address';
$string['enrol:sponsoremail:error:notfound'] = 'Email address not found';
$string['enrol:sponsoremail:error:notself'] = 'You are not able to choose yourself as your approver';
$string['enrol:sponsoremail:error:toomany'] = 'Multiple users found that match this address, please contact support for help.';
$string['enrol:error:classdoesnotexist'] = 'Sorry, the class does not exist, please try again.';
$string['enrol:error:failedtoconnect'] = 'Failed to connect to enrolment server';
$string['enrol:error:unavailable'] = 'Currently unavailable';
$string['enrol:planned'] = 'Register Interest';
$string['enrol:submit'] = 'Submit for Approval';
$string['enrol:submit:noapproval'] = 'Enrol';
$string['enrol:waitinglist'] = 'Join Waiting List';
$string['enrolledmessage'] = 'The table below shows any classes you are currently enrolled in, any previous or cancelled classes and any upcoming classes.';
$string['enrolment_region_mapping'] = 'Enrolment region override';
$string['enrolments'] = 'Enrolments';
$string['enrolments:placed'] = 'Enrolments (Placed)';
$string['enrolplugins:countmismatch'] = 'The number of active enrolment plugins differs from the requirement.';
$string['enrolplugins:incorrectorder'] = 'The active enrolment plugins do not match the requirement.';
$string['enrolplugins:incorrectsettings'] = 'The active enrolment plugin settings do not match the requirement.';
$string['enrolplugins:requirements'] = "Active enrolment plugins, in this order, should be 'manual', 'self' (with auto enrolment off) and 'guest'.";
$string['error:invalidclass'] = 'Invalid class selected, please try again.';
$string['error:invalidenrolment'] = 'Invalid enrolment selected, please try again.';
$string['eventenrolmentcancelled'] = 'Enrolment cancelled.';
$string['eventenrolmentcreated'] = 'Enrolment created.';
$string['eventenrolmentrequestapproved'] = 'Enrolment request approved.';
$string['eventenrolmentrequestrejected'] = 'Enrolment request rejected.';
$string['eventenrolmentrequestviewed'] = 'Enrolment request viewed.';
$string['exit:locked'] = 'Exit (Will not save)';
$string['exit:viewonly'] = 'Exit (Will not save)';

$string['groups:requirements'] = 'Groups need to be enabled within the {$a} to use this activity. The following settings are required.<br />Group mode: Visible groups<br />Default grouping: None';

$string['installationissue'] = 'There are issues with the installation settings for this activity and it is not currently accessible.<br />
    Please inform the site administrator if this problem persists.';
$string['internalworkflow'] = 'Use internal workflow';
$string['internalworkflow_configure'] = 'Internal workflow settings';
$string['internalworkflow_help'] = 'Only applies to classroom courses, as approval is automatic for elearning courses.';
$string['internalworkflow_heading'] = 'Linked course enrolment: Internal workflow settings';
$string['internalworkflow_settings'] = 'Update internal workflow settings';
$string['intro'] = 'Intro';
$string['invalidworkflow'] = 'Invalid workflow';
$string['iw:actions'] = 'Actions';
$string['iw:add'] = 'Add internal workflow';
$string['iw:approvalinfo'] = 'Approval page information';
$string['iw:approvalinfohint'] = 'Information added here will be displayed to the approver when presented with the form for approving/rejecting a request.';
$string['iw:approveinfo'] = 'When approving';
$string['iw:approveinfo_help'] = 'This information will be displayed to the approver when they are presented with the form specifically for approving a request.';
$string['iw:approvalreminder'] = 'Approval reminder after (days)';
$string['iw:approvalreminder_help'] = 'If the approver hasn\'t responded within x days of the enrolment request being made a reminder will be sent to the approver.<br />'
        . 'Set to 0 to stop the sending of approval reminders.';
$string['iw:approvalrequired'] = 'Approval required';
$string['iw:autocancellation_classstarted'] = 'AUTOMATICALLY CANCELLED: Request not approved by {$a} before the start of the class.';
$string['iw:autocancellation_notapproved'] = 'AUTOMATICALLY CANCELLED: Request not approved within {$a}.';
$string['iw:cancelafter'] = 'Cancel after enrolment (days)';
$string['iw:cancelafter_help'] = 'If the approver hasn\'t responded within x days of the enrolment request being made the enrolment request will automatically be cancelled.<br />'
        . 'Set to 0 to stop this type of automatic cancellation.';
$string['iw:cancelbefore'] = 'Cancel before class (hours)';
$string['iw:cancelbefore_help'] = 'If the approver hasn\'t responded to the enrolment request by x hours before the class is due to start the enrolment request will automatically be cancelled.<br />'
        . 'Set to 0 to stop this type of automatic cancellation.';
$string['iw:cancelcomments'] = 'Require comments on cancellation';
$string['iw:cancelinfo'] = 'When cancelling';
$string['iw:cancelinfo_help'] = 'This information will appear between the class details and the cancellation form.';
$string['iw:cancellationinfo'] = 'Cancellation page information';
$string['iw:cancellationinfohint'] = 'This information will be displayed to the applicant when they are presented with the form to confirm they wish to cancel their place on a class.';
$string['iw:closeenrolment'] = 'Close enrolment (hours)';
$string['iw:closeenrolment_help'] = 'The submission of enrolment requests will not be allowed within x hours of the start time of the class.<br />'
        . 'Set to 0 to stop this occurring.';
$string['iw:current'] = 'Current internal workflows';
$string['iw:customapprovalinfo'] = 'Custom approval information';
$string['iw:customenrolmentinfo'] = 'Custom enrolment information';
$string['iw:customemails'] = 'Custom emails';
$string['iw:declaration'] = 'Declaration';
$string['iw:declaration:add'] = 'Add another declaration';
$string['iw:declarations'] = 'Declarations';
$string['iw:declarationshint'] = 'Declarations will be displayed to the applicant as part of the form to confirm they wish to enrol on a class.'
    . 'The applicant must check the boxes to confirm the declarations to be allowed to enrol.';
$string['iw:delete'] = 'Delete';
$string['iw:delete:redirect:confirm'] = 'Are you sure you want to delete the internal workflow \'{$a}\'?';
$string['iw:delete:redirect:inuse'] = 'Deletion request cancelled as internal workflow is currently in use.';
$string['iw:delete:redirect:saved'] = 'Deletion request successful.';
$string['iw:duplicate'] = 'Duplicate';
$string['iw:duplicate:redirect:confirm'] = 'Are you sure you want to duplicate the internal workflow \'{$a}\'?';
$string['iw:duplicate:redirect:saved'] = 'Duplication request successful.';
$string['iw:editing:locked'] = 'You are not able to edit the settings on this page.<br />Any changes made will not be saved.';
$string['iw:editing:redirect:cancelled'] = 'Update cancelled, your changes have not been saved.';
$string['iw:editing:redirect:saved'] = 'Update successful, your changes have been saved.';
$string['iw:editing:redirect:viewonly'] = 'You are only able to view these details, your changes have not been saved.';
$string['iw:eitherinfo'] = 'When either approving or rejecting';
$string['iw:eitherinfo_help'] = 'This information will be displayed to the approver when they are presented with the generic form for either approving or rejecting a request.';
$string['iw:emails'] = 'Emails';
$string['iw:emailsoff'] = 'Turn emails off for this workflow';
$string['iw:emails:body'] = 'Body';
$string['iw:emails:current'] = '<p>Current email: {$a->currentemail} [{$a->previewlink}]</p>';
$string['iw:emails:error:emailnotfound'] = 'The requested email could not be found';
$string['iw:emails:error:emptysubjectbody'] = 'Both subject and body must be provided (or left empty)';
$string['iw:emails:error:missingplaceholder'] = 'The body of the email must contain the {$a} placeholder';
$string['iw:emails:htmlversion'] = 'HTML version';
$string['iw:emails:preview'] = 'Preview';
$string['iw:emails:redirect:cancelled'] = 'Update cancelled, your changes have not been saved.';
$string['iw:emails:redirect:locked'] = 'This internal workflow is locked, your changes have not been saved.';
$string['iw:emails:redirect:saved'] = 'Update successful, your changes have been saved.';

$string['iw:emails:replacements:general'] =
    '<p>Generally allowed text replacements:<br />'
    . '[[applicant:firstname]]<br />[[applicant:lastname]]<br />[[applicant:email]]<br />'
    . '[[coursename]]<br />[[courseurl]]<br />'
    . '[[classname]]<br />[[classlocation]]<br />[[classtrainingcenter]]<br />[[classdate]]<br />[[classduration]]<br />[[classcost]]</p>'
    . '<p>NB. In any email you can enter sections of content between {{approver}} and {{/approver}} to hide the contained information when approval is not required.';

$string['iw:emails:replacements:approval_request'] =
$string['iw:emails:replacements:approval_request_reminder'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[comments:applicant]]<br />'
    . '[[approveurl]]<br />[[rejecturl]]<br />[[directapproveurl]]<br />[[directrejecturl]]<br />'
    . '[[approvebydate]]</p>';

$string['iw:emails:replacements:awaiting_approval'] =
$string['iw:emails:replacements:approved'] =
$string['iw:emails:replacements:class_full'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[cancelurl]]</p>';

$string['iw:emails:replacements:approved_invite'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[cancelurl]]<br />'
    . '[[update:extrainfo]] (mandatory)</p>';

$string['iw:emails:replacements:reminder_first'] =
$string['iw:emails:replacements:reminder_second'] =
    '<p>Email specific text replacements:<br />'
    . '[[cancelurl]]</p>';

$string['iw:emails:replacements:rejected'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[comments:approver]]</p>';

$string['iw:emails:replacements:cancellation'] =
$string['iw:emails:replacements:cancellation_invite'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[comments:cancellation]]</p>';

$string['iw:emails:replacements:cancellation_admin'] =
$string['iw:emails:replacements:cancellation_invite_admin'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[admin:firstname]]<br />[[admin:lastname]]<br />[[admin:email]]</p>';

$string['iw:emails:replacements:cancelled'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[time:cancelledafter]] (Time after enrolment unapproved requests are cancelled, inc. units (days))</p>';
$string['iw:emails:replacements:cancelled_classstart'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[time:cancelledbefore]] (Time before class unapproved requests are cancelled, inc. units (hours))</p>';

$string['iw:emails:replacements:moved'] =
$string['iw:emails:replacements:moved_cancel_invite'] =
$string['iw:emails:replacements:moved_new_invite'] =
    '<p>Email specific text replacements:<br />'
    . '[[approver:firstname]]<br />[[approver:lastname]]<br />[[approver:email]]<br />'
    . '[[coursename:old]]<br />[[courseurl:old]]<br />'
    . '[[classname:old]]<br />[[classlocation:old]]<br />[[classtrainingcenter:old]]<br />[[classdate:old]]]<br />[[classduration:old]]<br />[[classcost:old]]'
    . '[[admin:firstname]]<br />[[admin:lastname]]<br />[[admin:email]]</p>';

$string['iw:emails:subject'] = 'Subject';
$string['iw:emails:textonly'] = ' [No HTML version]';
$string['iw:emails:textversion'] = 'Text version';
$string['iw:emails:title:global'] = 'Edit global internal workflow emails'; // No view only version.
$string['iw:emails:title:cm:edit'] = 'Edit activity internal workflow emails';
$string['iw:emails:title:iw:edit'] = 'Edit internal workflow emails';
$string['iw:emails:title:cm:view'] = 'View activity internal workflow emails';
$string['iw:emails:title:iw:view'] = 'View internal workflow emails';
$string['iw:emails:type:cm'] = 'Customised at activity level';
$string['iw:emails:type:default'] = 'Default email';
$string['iw:emails:type:global'] = 'Customised at global level';
$string['iw:emails:type:iw'] = 'Customised at workflow level';
$string['iw:emails:usehtml'] = 'Use HTML';
$string['iw:emails:viewonly'] = 'You are not able to edit the emails on this page.<br />Any changes made will not be saved.';
$string['iw:enrolmentinfo'] = 'Enrolment page information';
$string['iw:enrolmentinfohint'] = 'This information will be displayed to the applicant when they are presented with the form to confirm they wish to enrol on a class.';
$string['iw:enrolinfo'] = 'When enrolling';
$string['iw:enrolinfo_help'] = 'This information will appear between the class details and the enrolment form.';
$string['iw:enroltype'] = 'Enrolment type';
$string['iw:enroltype_help'] = 'Choose the enrolment type for this workflow.<br /><br />'
        . 'Enrol is the default whereby once approved users are placed on the class, space permitting.<br />'
        . 'Apply is the alternative whereby users are given a waitlisted place on approval and adminsistrators are tasked with confirming places.';
$string['iw:enroltype:apply'] = 'Apply';
$string['iw:enroltype:enrol'] = 'Enrol';
$string['iw:firstreminder'] = 'Send first reminder (days)';
$string['iw:firstreminder_help'] = 'Send a (first) reminder, to users enrolled on the class, x days before the class is due to start.<br />'
        . 'Set to 0 to not send this reminder.';
$string['iw:form:error:greaterthan'] = 'Must be greater than "{$a}"';
$string['iw:form:error:intorzero'] = 'Must be a positive integer or zero';
$string['iw:form:error:lessthan'] = 'Must be less than "{$a}"';
$string['iw:from'] = 'Alternate \'From\' email details';
$string['iw:fromemail'] = '\'From\' email address';
$string['iw:fromfirstname'] = '\'From\' firstname';
$string['iw:fromhint'] = 'Entering details below will set who the emails sent by the workflow system appear to come from,'
    . 'if empty they will come from the Moodle administrator account {$a->fullname} ({$a->email})';
$string['iw:fromlastname'] = '\'From\' lastname';
$string['iw:id'] = 'ID';
$string['iw:lock:redirect:locked'] = 'Lock request successful.';
$string['iw:lock:redirect:unlocked'] = 'Unlock request successful.';
$string['iw:locked'] = 'Locked';
$string['iw:name'] = 'Name';
$string['iw:nocurrent'] = 'No current internal workflows';
$string['iw:noreminder'] = 'No reminder for (hours)';
$string['iw:noreminder_help'] = 'Do not send reminders, if they would normally be due, within x hours of the enrolment being approved.<br />'
        . 'Set to 0 to send reminders regardless of when the enrolment was approved.';
$string['iw:regionemail'] = 'Email to notify of user region mismatch';
$string['iw:rejectioncomments'] = 'Require comments on rejection';
$string['iw:rejectinfo'] = 'When rejecting';
$string['iw:rejectinfo_help'] = 'This information will be displayed to the approver when they are presented with the form specifically for rejecting a request.';
$string['iw:return:cm'] = $string['iw:return:view:cm'] = 'Back to editing activity'; // Can only be viewing activity emails if able to edit activity.
$string['iw:return:global'] = 'Back to internal worfklow overview';
$string['iw:return:iw'] = 'Back to editing internal worfklow';
$string['iw:return:view:iw'] = 'Back to viewing internal worfklow';
$string['iw:secondreminder'] = 'Send second reminder (days)';
$string['iw:secondreminder_help'] = 'Send a (second) reminder, to users enrolled on the class, x days before the class is due to start.<br />'
        . 'Set to 0 to not send this reminder.';
$string['iw:sponsorentry'] = 'Approver entry';
$string['iw:sponsors'] = 'Possible approvers<br />(emails, one per line)';
$string['iw:sponsors:error:invalid'] = 'Email address ({$a}) is not valid<br />';
$string['iw:sponsors:error:noldap'] = 'Cannot connect to validate the email addresses';
$string['iw:sponsors:error:notfound'] = 'Email address ({$a}) not found<br />';
$string['iw:sponsors_help'] = 'Enter a list of possible approver options (email addresses, one per line).<br />'
    . 'Leave blank to allow the user to freely enter their approver\'s email address, which will be validated on submisssion fo the enrolment form.';

$string['location'] = 'Location';

$string['manageenrolments'] = 'Manage enrolments';
$string['manageenrolments:button:cancel'] = 'Proceed to cancel enrolments';
$string['manageenrolments:button:delete'] = 'Proceed to delete enrolments';
$string['manageenrolments:button:future'] = 'Proceed to enrol users';
$string['manageenrolments:button:move'] = 'Proceed to move users to another class';
$string['manageenrolments:button:past'] = 'Proceed to add attendees';
$string['manageenrolments:button:waitlist'] = 'Proceed to approve wait listed applicants';
$string['manageenrolments:button:update'] = 'Proceed to update enrolment statuses';
$string['manageenrolments:cancel:button'] = 'Proceed';
$string['manageenrolments:cancel:header'] = 'Cancel enrolments';
$string['manageenrolments:cancel:results'] = 'Enrolment cancellation results:{$a}';
$string['manageenrolments:cancel:status'] = 'Cancellation status';
$string['manageenrolments:cancel:users'] = 'Enrolments to cancel';
$string['manageenrolments:cannot'] = 'Managing enrolments of users is not possible for this activity.';
$string['manageenrolments:class'] = 'Class';
$string['manageenrolments:classdetails'] = 'Class details';
$string['manageenrolments:currentenrolments'] = 'Current active enrolments (Moodle users)';
$string['manageenrolments:delete:button'] = 'Proceed';
$string['manageenrolments:delete:header'] = 'Delete enrolments';
$string['manageenrolments:delete:results'] = 'Enrolment deletion results:{$a}';
$string['manageenrolments:delete:sendemails'] = 'Send emails';
$string['manageenrolments:cancel:users'] = 'Enrolments to delete';
$string['manageenrolments:enrol:button'] = 'Proceed';
$string['manageenrolments:enrol:header'] = 'Enrol users';
$string['manageenrolments:enrol:results'] = 'User enrolment results:{$a}';
$string['manageenrolments:enrol:users'] = 'User Staff IDs (one per line)';
$string['manageenrolments:error:notypeset'] = 'Management task was not set or could not be found, please try again.';
$string['manageenrolments:error:cancel:nousers'] = 'There are no users with enrolments that can be cancelled on the chosen class.';
$string['manageenrolments:footeralert:cancel'] = 'When you cancel a user\'s enrolment, if they enrolled via Moodle, they will receive a cancellation '
    . 'email and if they previously received an invite it will be cancelled. Their approver will also receive notification of the cancellation.'
    . '<ul><li>No emails will be sent if the class start time is in the past.</li>'
    . '<li>\'No Show\' status is only available if the class start time is in the past.</li></ul>';
$string['manageenrolments:footeralert:delete'] = 'When deleting an enrolment users with an approved place will automatically be notified and their invite will be cancelled.<br />'
    . 'For all other enrolments you can choose whether the user, and if applicable their approver (if enrolment was waiting approval), are sent an email notification.';
$string['manageenrolments:footeralert:future'] = 'When you enrol a user on an upcoming class they will be enrolled with a status of '
    . '\'Approved Place\' or \'W:Wait Listed\', as appropriate, and you will be recorded as being their approver. The user will receive '
    . 'an approval email and, if they receive a status of \'Approved Place\', an invite. Any future cancellation will result in the '
    . 'user receiving a cancellation email and if they received an invite that will be cancelled. You, as their approver, will also receive '
    . 'notification of the cancellation.<br><br>If applicable, any certifications will be reset prior to enrolling the user.';
$string['manageenrolments:footeralert:move'] = 'When you move users between classes their current status will be retained, unless moving between a \'Planned class\' and a \'Normal\' class. '
    . 'If the are waitlisted on a planned class they will be approved on a normal class and vice versa.<br /><br />'
    . 'You can choose whether to re-send applicable emails (i.e. approval request, invite) which will then be automatically be sent based on the status:<br />'
    . '<ul><li>Requested: Approval request will be sent with new class details.</li>'
    . '<li>Placed: Original invite will be cancelled and a new invite sent.</li>'
    . '<li>If approval is/was required the approver will be notified of the class change.</li></ul><br />'
    . 'We would advise that you always send emails when moving users with requested or approved places as it will ensure approval requests are resent or current invites are cancelled and new invites sent.';
$string['manageenrolments:footeralert:past'] = 'When you enrol a user on a past class they will be enrolled with a status of '
    . '\'Full Attendance\'. No emails will be sent.<br><br>If applicable, any certifications will be reset prior to enrolling the user.';
$string['manageenrolments:footeralert:update'] = 'Coming soon...';
$string['manageenrolments:footeralert:waitlist'] = 'When you approve a waitlisted application the enrolment will be updated with a status of '
    . '\'Approved Place\'. A notification email and invitation will be sent.';
$string['manageenrolments:generic:button:cancel'] = 'Back';
$string['manageenrolments:header:cancel'] = 'Cancel enrolments';
$string['manageenrolments:header:class'] = 'Class - {$a}';
$string['manageenrolments:header:delete'] = 'Delete enrolments';
$string['manageenrolments:header:future'] = 'Enrol users on upcoming classes';
$string['manageenrolments:header:move'] = 'Move users to a different class';
$string['manageenrolments:header:past'] = 'Add attendees to past classes';
$string['manageenrolments:header:update'] = 'Update enrolment statuses';
$string['manageenrolments:header:waitlist'] = 'Approve wait listed applicants';
$string['manageenrolments:heading'] = 'Manage enrolments - {$a}';
$string['manageenrolments:help'] = 'If you cannot see the class you wish to manage enrolments on it is most likely due to the enrolment end date having past.<br />'
    . 'Please try editing the enrolment end date of the class in the linked course and then refresh this page.';
$string['manageenrolments:markattendance'] = 'To mark attendance, click here.';
$string['manageenrolments:move:button'] = 'Proceed';
$string['manageenrolments:move:header'] = 'Move users';
$string['manageenrolments:move:resendemails'] = 'Resend emails';
$string['manageenrolments:move:results'] = 'Move users results:{$a}';
$string['manageenrolments:move:toclass'] = 'Class to move to';
$string['manageenrolments:move:users'] = 'Users to move';
$string['manageenrolments:noclasses'] = 'There are currently no applicable classes.';
$string['manageenrolments:noenrolments'] = 'No applicable enrolments have been retrieved for this class';
$string['manageenrolments:registeradusers'] = 'Can\'t find the user you are looking for? Then try {$a} to '
    . 'register users on the Moodle system, before enrolling them below.';
$string['manageenrolments:registeraduserslink'] = 'searching Active Directory';
$string['manageenrolments:table:address'] = 'Office';
$string['manageenrolments:table:bookingstatus'] = 'Enrolment status';
$string['manageenrolments:table:cancel'] = 'Cancel';
$string['manageenrolments:table:costcentre'] = 'Cost centre';
$string['manageenrolments:table:delete'] = 'Delete';
$string['manageenrolments:table:move'] = 'Move';
$string['manageenrolments:table:phone1'] = 'Contact number';
$string['manageenrolments:table:sponsor'] = 'Approver';
$string['manageenrolments:table:staffid'] = 'Staff ID';
$string['manageenrolments:table:timeenrolled'] = 'Enrolment date';
$string['manageenrolments:table:waitlist'] = 'Approve';
$string['manageenrolments:unavailable'] = 'Unavailable';
$string['manageenrolments:waitlist:button'] = 'Proceed';
$string['manageenrolments:waitlist:header'] = 'Approve applications';
$string['manageenrolments:waitlist:results'] = 'Application approval results:{$a}';
$string['manageenrolments:waitlist:seatsremaining'] = 'Seats currently remaining: <span id="tapsenrol-waitlist-seatsremaining" data-seatsremaining="{$a->value}">{$a->text}</span> (Including those selected below)';
$string['manageenrolments:waitlist:users'] = 'Applications to approve';
$string['modulename'] = 'Arup linked course enrolment';
$string['modulename_help'] = 'Adds an Arup linked course enrolment activity';
$string['modulename_link'] = 'mod/tapsenrol/view';
$string['modulenameplural'] = 'Arup linked course enrolments';

$string['name'] = 'Name';
$string['newwindow'] = '[Opens in new window]';
$string['noapplicablecourses'] = 'No applicable linked courses available';
$string['noclasses'] = 'Unfortunately there are no upcoming classes nor the option to express your interest in this module at this time.';
$string['nopermission'] = 'Sorry, you do not have permission to perform the requested action.';

$string['online'] = 'Online';

$string['pluginname'] = 'Arup linked course enrolment';
$string['pluginadministration'] = 'Arup linked course enrolment administration';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';
$string['potentialusers'] = 'Potential users';

$string['redirectmessage:onupdate'] = 'Your status has changed, reloading {$a->course} to process updates.<br />If the {$a->course} does not reload, please {$a->link}.';
$string['redirectmessage:onupdatelink'] = 'reload the {$a->course} manually';
$string['regions:enrolment'] = 'Enrolment regions';
$string['resendinvites'] = 'Resend invites';
$string['resendinvites:cannot'] = 'Resending of invites is not possible for this activity.';
$string['resendinvites:extrainfo'] = 'Extra information to add to invite';
$string['resendinvites:extrainfo_help'] = 'This information will be inserted into the [[update:extrainfo]] placeholder in the invite email.';
$string['resendinvites:heading'] = 'Resend invites - {$a}';
$string['resendinvites:invitesresent'] = '{$a} invite(s) successfully re-sent.';
$string['resendinvites:noclasses'] = 'No applicable classes to resend invites for.';
$string['resendinvites:resendingcancelled'] = 'Resending cancelled, no invites have been sent.';
$string['resendinvites:selectallnone'] = 'Select all/no applicants';
$string['reviewenrolment'] = 'Review Enrolment';
$string['reviewenrolment:areyousure'] = 'Are you sure you want to enrol on:';
$string['reviewenrolment:pre'] = '';
$string['reviewenrolment:pre:iw'] = '';

$string['seatsremaining'] = 'Seats Remaining';
$string['separator'] = ' - ';
$string['settings:forceemailsending'] = 'Override $CFG email settings';
$string['settings:forceemailsending_desc'] = 'Will ignore $CFG->noemailever, $CFG->divertallemailsto and $CFG->divertccemailsto email settings and attempt to send emails.';
$string['status:elearning:attended'] = 'Completed';
$string['status:elearning:cancelled'] = 'Cancelled';
$string['status:elearning:placed'] = 'Enrolled, in progress';
$string['status:elearning:requested'] = 'Pending Approval';
$string['status:elearning:waitlisted'] = 'Waiting List';
$string['status:classroom:attended'] = 'Completed';
$string['status:classroom:cancelled'] = 'Cancelled';
$string['status:classroom:placed'] = 'Approved';
$string['status:classroom:requested'] = 'Pending Approval';
$string['status:classroom:waitlisted'] = 'Waiting List';
$string['status:dropdown:cancel'] = 'Go to cancellation page';
$string['status:dropdown:cancel:waitlisted'] = 'Remove me from waiting list';
$string['status:requested:fullclass'] = 'You are pending approval on a full class,
    upon approval you will be placed in a waiting list for this class,
    you may wish to consider cancelling your enrolment and re-booking on another class.';
$string['status:waitlisted:fullclass'] = 'You are on the waiting list for a full class,
    you may wish to consider cancelling your enrolment and re-booking on another class';
$string['status:waitlisted:plannedclass'] = 'You are on the waiting list for a class which now has seats available,
    please contact your regional administrator to be assigned one of the remaining seats.';

$string['tapscourse'] = 'Linked course';
$string['tapscourse_help'] = 'Linked course selection is determined by the Arup advert activity.';
$string['tapsenrol:addinstance'] = 'Add a new Arup linked course enrolment';
$string['tapsenrol:canapproveanyone'] = 'Can approve anyone';
$string['tapsenrol:deleteattendedenrolments'] = 'Can delete attended enrolments';
$string['tapsenrol:internalworkflow'] = 'Manage internal workflows';
$string['tapsenrol:internalworkflow_change'] = 'Change activity\'s internal workflow';
$string['tapsenrol:internalworkflow_edit'] = 'Add/Edit unlocked internal workflows';
$string['tapsenrol:internalworkflow_edit_activity'] = 'Edit internal workflow emails for activity instance';
$string['tapsenrol:internalworkflow_lock'] = 'Lock/unlock internal workflows';
$string['tapsenrol:manageenrolments'] = 'Manage linked course enrolments';
$string['tapsenrol:resendinvites'] = 'Resend invites';
$string['tapsenrol:viewallapprovals'] = 'View all approvals';
$string['tapsenrolment'] = 'Arup linked course enrolment';
$string['taskactivitycleanup'] = 'Activity cleanup.';
$string['taskautomaticcancellation'] = 'Automatic cancellation.';
$string['taskinternalworkflowcleanup'] = 'Internal workflow cleanup.';
$string['tasksendreminders'] = 'Send reminders.';
$string['tbc'] = 'TBC';
$string['time'] = 'Time';
$string['to'] = 'to';
$string['trainingcenter'] = 'Room';

$string['unlimited'] = 'Unlimited';

$string['waitinglist:classroom'] = 'Waiting List';
$string['waitinglist:elearning'] = 'Waiting List';
$string['whenandwhere:classroom'] = 'When and Where';
$string['whenandwhere:elearning'] = 'Enrolment Information';
$string['whenandwhere:mixed'] = 'Enrolment Information';