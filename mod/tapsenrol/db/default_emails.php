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

defined('MOODLE_INTERNAL') || die();

class tapsenrol_default_email {
    public $sortorder;
    public $sendtype;
    public $email;
    public $title;
    public $description;
    public $subject;
    public $body;
    public $html;

    public function __construct($sortorder, $sendtype, $email, $title, $description, $subject, $body, $html) {
        $this->sortorder = $sortorder;
        $this->sendtype = $sendtype;
        $this->email = $email;
        $this->title = $title;
        $this->description = $description;
        $this->subject = $subject;
        $this->body = $body;
        $this->html = $html;
    }
}


$defaultemails = array();

$sortorder = 1;

$awaiting_approval = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your request to go on the following class is now awaiting approval.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a><br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    $sortorder,
    'instant',
    'awaiting_approval',
    'Awaiting approval',
    'Sent to applicant when they enrol.',
    'Awaiting approval',
    $awaiting_approval,
    1
);

$approval_request = <<<EOS
<p>Dear [[approver:firstname]],</p>
<p>[[applicant:firstname]] [[applicant:lastname]] has requested your approval to go on the following class.
    If you do not approve or reject the request by [[approvebydate]], it will be automatically cancelled.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p><b>Comments from applicant:</b><br />
    [[comments:applicant]]</p>

<p>Please approve or reject their request:<br />
    <a href="[[approveurl]]">Approve</a><br />
    <a href="[[rejecturl]]">Reject</a></p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approval_request',
    'Approval request',
    'Sent to approver when applicant enrols.',
    'Approval request',
    $approval_request,
    1
);

$approval_request_reminder = <<<EOS
<p>Dear [[approver:firstname]],</p>
<p>This is a reminder that [[applicant:firstname]] [[applicant:lastname]] has requested your approval to go on the following class.
    If you do not approve or reject the request by [[approvebydate]], it will be automatically cancelled.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p><b>Comments from applicant:</b><br />
    [[comments:applicant]]</p>

<p>Please approve or reject their request:<br />
    <a href="[[approveurl]]">Approve</a><br />
    <a href="[[rejecturl]]">Reject</a></p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approval_request_reminder',
    'Approval request reminder',
    'Sent to approver if they haven\'t approved/rejected the request after x days.',
    'Reminder: Approval request',
    $approval_request_reminder,
    1
);


$approved = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your request to attend the following class has been approved.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approved',
    'Request approved',
    'Sent to applicant following approval on a scheduled class.',
    'Request approved',
    $approved,
    1
);


$approvedinvite = <<<EOS
<p>[[update:extrainfo]]</p>
<p>Dear [[applicant:firstname]],</p>
<p>Your request to attend the following class has been approved.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approved_invite',
    'Request approved (Invite)',
    'Invite sent to applicant following approval on a scheduled class.',
    'Request approved',
    $approvedinvite,
    1
);


$approved_waitlist_planned = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your request to be added to the waiting list for the following class has been approved.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approved_waitlist_planned',
    'Request approved - Planned class',
    'Sent to applicant following approval on a planned class.',
    'Request approved - Planned class',
    $approved_waitlist_planned,
    1
);


$approved_waitlist_apply = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your application to attend the following class has been approved and you have been added to the waiting list.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'approved_waitlist_apply',
    'Application approved - Waiting list',
    'Sent to applicant following approval of an application to attend a scheduled class.',
    'Application approved - Waiting list',
    $approved_waitlist_apply,
    1
);


$rejected = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your request to attend the following class has been rejected.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p><b>Comments from your approver:</b><br />
    [[comments:approver]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'rejected',
    'Request rejected',
    'Sent to applicant following rejection on a scheduled class.',
    'Request rejected',
    $rejected,
    1
);


$class_full = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Sorry, the following class is full and we were unable to offer you a place,
    you have been added to the waiting list.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]]),
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'class_full',
    'Class full',
    'Sent to applicant, on approval, if class is full.',
    'Class full',
    $class_full,
    1
);


$cancellation = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>You have successfully cancelled your place on the following class.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p><b>Your comments:</b><br />
    [[comments:cancellation]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancellation',
    'Applicant cancels',
    'Sent to the applicant when they cancel their place on a class.',
    'Cancellation notification',
    $cancellation,
    1
);


$cancellation_invite = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>You have successfully cancelled your place on the following class.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancellation_invite',
    'Applicant cancels (Invite)',
    'Invite sent to the applicant when they cancel their place on a class.',
    'Cancellation notification',
    $cancellation_invite,
    1
);


$cancellation_admin = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your place on the following class has been cancelled by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancellation_admin',
    'Admin cancels',
    'Sent to the applicant when an admin cancels their place on a class.',
    'Cancellation notification',
    $cancellation_admin,
    1
);


$cancellation_admin_invite = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your place on the following class has been cancelled by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancellation_admin_invite',
    'Admin cancels (Invite)',
    'Invite sent to the applicant when an admin cancels their place on a class.',
    'Cancellation notification',
    $cancellation_admin_invite,
    1
);


$cancelled = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your place on the following class has been automatically cancelled having not been approved within [[time:cancelledafter]].</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancelled',
    'Automatic cancellation - After x days',
    'Sent to applicant, if not approved/rejected for the class, x days after they have enrolled.',
    'Cancellation notification',
    $cancelled,
    1
);


$cancelled_classstart = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your place on the following class has been automatically cancelled having not been approved by [[time:cancelledbefore]] before the start of the class.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'cancelled_classstart',
    'Automatic cancellation - x hours before class',
    'Sent to applicant, if not approved/rejected for the class, x hours before the start of the class.',
    'Cancellation notification',
    $cancelled_classstart,
    1
);


$moved = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>You have had your place on the following class transferred to a different class by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Old class</b><br />
    <b>Module:</b> <a href="[[courseurl:old]]">[[coursename:old]]</a><br />
    <b>Class:</b> [[classname:old]]<br />
    <b>Location:</b> [[classlocation:old]]<br />
    <b>Room:</b> [[classtrainingcenter:old]]<br />
    <b>Date:</b> [[classdate:old]]<br />
    <b>Duration:</b> [[classduration:old]]<br />
    <b>Cost:</b> [[classcost:old]]</p>
<p><b>New class</b><br />
    <b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'moved',
    'Moved class notification',
    'Sent to applicant, if moved to a different class.',
    'Class move notification',
    $moved,
    1
);


$moved_cancel_invite = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your place on the following class has been cancelled by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    This is due to your enrolment having been moved to a new class, for which you will receive a new invitation.
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl:old]]">[[coursename:old]]</a><br />
    <b>Class:</b> [[classname:old]]<br />
    <b>Location:</b> [[classlocation:old]]<br />
    <b>Room:</b> [[classtrainingcenter:old]]<br />
    <b>Date:</b> [[classdate:old]]<br />
    <b>Duration:</b> [[classduration:old]]<br />
    <b>Cost:</b> [[classcost:old]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'moved_cancel_invite',
    'Moved class (Cancel invite)',
    'Invite sent to applicant, to cancel, when they are moved from a class.',
    'Moved class',
    $moved_cancel_invite,
    1
);


$moved_new_invite = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>You have been moved to the following class by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
<p>Should you wish to cancel you can do so via the following link:<br />
    <a href="[[cancelurl]]">Cancel</a>
    {{approver}}<br />
    Please note that your approver, [[approver:firstname]] [[approver:lastname]] ([[approver:email]])
    will be notified should you choose to cancel.{{/approver}}</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'moved_new_invite',
    'Moved class (New invite)',
    'Invites sent to applicant when they are moved to a class.',
    'Moved Class',
    $moved_new_invite,
    1
);


$deleted = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your enrolment on the following class has been deleted by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'deleted',
    'Deleted enrolment notification',
    'Sent to applicant, if enrolment is deleted.',
    'Enrolment deleted',
    $deleted,
    1
);


$deleted_cancel_invite = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>Your enrolment on the following class has been deleted by a member of the admin team ([[admin:firstname]] [[admin:lastname]]).
    If you are unsure why this has happened please contact them ([[admin:email]]).</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'deleted_cancel_invite',
    'Deleted enrolment (Cancel invite)',
    'Invite sent to applicant, to cancel, when their enrolment is deleted.',
    'Enrolment deleted',
    $deleted_cancel_invite,
    1
);


$reminder_first = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>This is to remind you that you are booked on the following class.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'timed',
    'reminder_first',
    'First reminder',
    'First reminder sent to applicant before scheduled class.',
    'Class reminder',
    $reminder_first,
    1
);


$reminder_second = <<<EOS
<p>Dear [[applicant:firstname]],</p>
<p>This is to remind you that you are booked on the following class.</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]<br />
    <b>Duration:</b> [[classduration]]<br />
    <b>Cost:</b> [[classcost]]</p>
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'timed',
    'reminder_second',
    'Second reminder',
    'Second reminder sent to applicant before scheduled class.',
    'Class reminder',
    $reminder_second,
    1
);


$region_mismatch = <<<EOS
<p>[[applicant:firstname]] [[applicant:lastname]] has requested to enrol on the following class
    but their region does not match that set for the module:</p>
<p><b>Module:</b> <a href="[[courseurl]]">[[coursename]]</a><br />
    <b>Class:</b> [[classname]]<br />
    <b>Location:</b> [[classlocation]]<br />
    <b>Room:</b> [[classtrainingcenter]]<br />
    <b>Date:</b> [[classdate]]</p>
    {{approver}}<p>Their approver is [[approver:firstname]] [[approver:lastname]] ([[approver:email]])</p>{{/approver}}
EOS;
$defaultemails[] = new tapsenrol_default_email(
    ++$sortorder,
    'instant',
    'region_mismatch',
    'Enrolment Region Mismatch',
    'Notification that a user has tried to enrol on a course in a different region.',
    'Enrolment Region Mismatch',
    $region_mismatch,
    1
);