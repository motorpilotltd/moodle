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

$string['pluginname'] = 'Lunch & Learn';
$string['pluginnameplural'] = 'Learning Events';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['lunchandlearn:global'] = 'View global';
$string['lunchandlearn:edit'] = 'Edit Learning Event session';
$string['lunchandlearn:mark'] = 'Mark attendance';
$string['lunchandlearn:delete'] = 'Delete Learning Event';
$string['moredetails'] = 'Further details';

$string['lunchandlearnadmin'] = 'Event administration';
$string['lunchandlearnevent'] = 'Learning event';
$string['newlunchlearn'] = 'Add learning event';
$string['newlunchlearntitle'] = 'New Learning Event';
$string['editlunchlearntitle'] = 'Edit Learning Event';
$string['cancelevent'] = 'Cancel Event';
$string['cancelledattendees'] = 'Cancelled Attendees';
$string['nocancelledattendees'] = 'There are no cancelled attendees';
$string['confirmeventdelete'] = 'Are you sure you want to delete this Learning Event?';
$string['confirmeventcancelled'] = 'Are you sure you want to cancel this Learning Event?';
$string['deletehasattendees'] = 'This event has attendees registered. If you cancel the event they will be removed and sent a cancellation email.';
$string['confirmsignup'] = 'Register to attend Learning Event: {$a}';
$string['confirmmsgsignup'] = 'Please fill out the details below and click on confirm to register.';
$string['confirmcancel'] = 'Cancel my registration to Learning Event: {$a}';
$string['adminconfirmcancel'] = 'Cancel sign-up for {$a->fullname} to Learning Event: {$a->eventname}';
$string['signupedit'] = 'Editing sign-up for {$a->fullname} to Learning Event: {$a->eventname}';
$string['confirmmsgcancel'] = 'Are you sure that you would like to cancel your registration?';
$string['confirmeventsignup'] = 'Confirm';
$string['confirmeventcancel'] = 'Yes, cancel';
$string['confirmeventedit'] = 'Update Attendance';
$string['cancelsession'] = 'Cancel Session';
$string['donotconfirmeventsignup'] = 'Cancel';
$string['donotconfirmeventedit'] = 'Cancel';
$string['donotconfirmeventcancel'] = 'No thanks';
$string['currentlyattending'] = '{$a} user(s) are currently registered to attend this session.';
$string['noattendees'] = 'There are currently no users signed up to attend.';
$string['nocategory'] = '- No Category -';
$string['sessionlocked'] = 'Attendance has already been taken for this session, you can only update users not already marked as attended.';
$string['attending'] = 'Registered';
$string['attended'] = 'Attended';
$string['notattended'] = 'Did not attend';
$string['notfound'] = 'We\'re sorry, but we don\'t seem to be able to find the Learning Event you are looking for. It\'s possible that the event has been deleted, or the link you followed has been truncated. Click continue to search through upcoming events.';
$string['overbookingpermitted'] = 'overbooking is permitted';
$string['overbookingnotpermitted'] = 'overbooking not permitted';

$string['schedulegroup'] = 'Schedule';
$string['onlinegroup'] = 'Online details';

$string['selectoneonlineinperson'] = 'The Learning Event cannot be unavailable online and in person. Please select at least one option.';

$string['inviteto'] = 'Send to';
$string['invitebody'] = 'Body';
$string['inviteheader'] = 'Send invite to Learning Event';
$string['invitepreview'] = 'Preview Invite';
$string['invitesubject'] = 'Learning Event - {$a}';
$string['invitesignoff'] = 'To register for the event please click on the following URL / Link: {$a}';
$string['send'] = 'Send';
$string['invite'] = 'Invite to event';
$string['messagessent'] = 'The invites have been sent';
$string['noresults'] = '<div class="alert alert-info" role="alert">No results found that match your search</div>';

$string['eventname'] = 'Event name';
$string['eventregion'] = 'Region';
$string['office'] = 'Office';
$string['meetingroom'] = 'Meeting room';
$string['eventcategory'] = 'Module category';
$string['supplier'] = 'Supplier';
$string['eventsummary'] = 'Summary';
$string['sessioninfo'] = 'Special requirements text box instructions';
$string['nosessioninfo'] = 'Please use this box to let us know any dietary or other requirements';
$string['eventdescription'] = 'Details/agenda';
$string['eventjoindetail'] = '<em class="fa fa-headphones">​</em> Online Instructions';
$string['durationminutes'] = 'Event duration in minutes';
$string['sessionmaterials'] = 'Related material';
$string['recordedsession'] = 'Session recording';
$string['eventrequirements'] = 'Special requirements';
$string['eventnotescancel'] = 'Cancellation reason';
$string['eventinperson'] = 'Attending in person?';
$string['takeattendance'] = 'Take Attendance';
$string['addattendee'] = 'Add Attendee(s)';
$string['submiterror'] = 'Please check attendance in the form above. You can not submit without at least checking one box.';
$string['addadditionalattendees'] = 'Add Additional Attendees';
$string['addadditionalattendeesprint'] = 'Additional Attendees';
$string['addadditionalprintdescription'] = 'If you haven\'t already signed up for this event, please add your name below as proof of attendance and for recording in your learning history.';
$string['selectregion'] = 'Region Filter';
$string['date'] = 'When';

$string['submitattendancetitle'] = 'Learning History Submission';
$string['pleasecheck'] = 'You are submitting {$a} attendance record(s). Please check the following details before you submit:';
$string['lockmessage'] = 'Once you have taken attendance the screen will be locked and you will not be allowed to resubmit a modified attendance for the session. If you need to edit any other details please return to the settings page and then resubmit attendance.';
$string['classname'] = 'Class Name:';
$string['provider'] = 'Provider:';
$string['completiondate'] = 'Completion Date:';
$string['duration'] = 'Duration:';
$string['location'] = 'Location:';

$string['p_learning_desc'] = 'Learning Description:';
$string['p_learning_method'] = 'Learning Method:';

$string['resendinvitestitle'] = 'Resend Invites';
$string['resendinvitesbody'] = 'You have made changes to the learning event. Would you like to send amended invites out to attendees?';

$string['heading:summary'] = 'About this event';
$string['heading:agenda'] = 'Session details';
$string['heading:joindetail'] = 'Join online instructions';
$string['heading:related'] = 'Resources';
$string['heading:recording'] = 'Session recording';

$string['suretakeattendance'] = '<p>Once you have taken attendance the screen will be locked and you will not be allowed to resubmit a modified attendance for the session.</p>'
        . '<p><strong>Please confirm that you are sure you wish to continue.</strong></p>';

$string['yestakeattendance'] = 'Yes, commit to learning history';
$string['emailattendees'] = 'Email all Attendees';
$string['emailattendeessubject'] = 'Learning Event: {$a}';

$string['thead:user'] = 'User';
$string['thead:user:first'] = 'Firstname';
$string['thead:user:last'] = 'Lastname';
$string['thead:user:sep'] = '/';
$string['thead:office'] = 'Office';
$string['thead:inperson'] = 'In Person?';
$string['thead:requirements'] = 'Special Requirements';
$string['thead:attended'] = 'Attended';
$string['thead:signature'] = 'Signature';
$string['thead:searchusers'] = 'Search Name';
$string['admincancellationreason'] = 'Please enter the reason for the cancellation below:';

$string['error:summarylen'] = 'Summary length should be no more than {$a} characters';

$string['lunchandlearnsignupemailsubject'] = 'Learning Event - {$a}';
$string['settings'] = 'Learning event settings';
$string['setting:rootcategory'] = 'Root Category';
$string['setting:rootcategorylong'] = 'Which category to treat as the root (top) category when selecting a Learning Event category';
$string['setting:timezones'] = 'Timezones';
$string['setting:timezoneslong'] = 'Choose which timezones to display from the full list provided';
$string['setting:notifications'] = 'Notifications';
$string['setting:notificationslong'] = 'Settings for email notifications sent by the Learning Event sessions.<br />'
        . 'Note that you can use the following replacement variables in <em>all</em> Learning Event emails:<ul>'
        . '<li>fullname - The fullname of the user attending</li>'
        . '<li>sessionname - The name of the session</li>'
        . '<li>date - The date that the session will take place</li>'
        . '<li>cancelurl - The URL to cancel a sign-up</li>'
        . '</ul><p>You are also able to access properties and methods on the user and lunchandlearn objects using the following syntax: {{lunchandlearn.joindetail}}. This gives you access to the following: <ul>'
        . '<li>user - firstname, lastname, email, idnumber, city, etc.</li>'
        . '<li>lunchandlearn - capacity, date, date_string, timezone, description (agenda), duration, eventid, joindetail, office, room, name, regionid, regionname, summary, recording, attendee_count</li>'
        . '</ul></p>';
$string['setting:signupemail'] = 'Sign-up email';
$string['setting:signupemaillong'] = 'The email sent to an attendee when they have been signed up.';
$string['setting:signupemaildefault'] = '<p><strong>Learning Event Registration Confirmation - {{sessionname}}</strong><br /><br /> Dear {{user.firstname}},<br /><br /> Thank you for registering for the {{sessionname}} learning event. Here are the event details:<br /><br /> <strong>Date:</strong> {{date}}<br /> <strong>Office:</strong> {{scheduler.office}}<br /> <strong>Room:</strong> {{scheduler.room}}<br /><br /> <strong>Summary</strong> {{lunchandlearn.summary}}<br/><br/>
<strong>Agenda</strong>
{{lunchandlearn.description}}
<strong>Online joining instructions (if applicable)</strong>{{lunchandlearn.joindetail}} <br/><br/><strong>IMPORTANT:</strong><br /> If you need to cancel your place, please click <a href="{{cancelurl}}">here</a>. Please note that declining or deleting the calendar invite alone will not inform the organiser that you will not be attending.<br /><br />This email was sent to {{fullname}} from Arup University Moodle. To view the event information online you can copy and paste this URL {{lunchandlearn.get_cal_url|full}} or to cancel you can copy and paste this URL {{cancelurl}} directly into your browser.</p>';
$string['lunchandlearncancelemailsubject'] = 'Learning Event cancellation - {$a}';
$string['setting:cancelemail'] = 'Cancellation email';
$string['setting:cancelemaillong'] = 'The email sent to an attendee when they have cancelled their attendance';
$string['setting:cancelemaildefault'] = '<p><strong>Learning Event Cancellation Confirmation</strong> - <strong>{{sessionname}}</strong><br /><br />Dear {{user.firstname}},<br /><br />Thank you for informing us that you will not be attending the {{sessionname}} learning event.<br /><br /><strong>Date:</strong> {{date}}<br /><strong>Office:</strong> {{scheduler.office}}<br /><strong>Room :</strong> {{scheduler.room}}<br /><br /><strong>Summary</strong> {{lunchandlearn.summary}}<br/><br/><strong>IMPORTANT:</strong> <br />If you need to re-book your place then please click <a href="{{lunchandlearn.get_cal_url|full}}">here</a>.<br /><br />This email was sent to {{fullname}} from Arup University Moodle.<br />To view the event information online or to rebook you can copy and paste this URL {{lunchandlearn.get_cal_url|full}} directly into your browser.</p>';
$string['lunchandlearnadmincancelemailsubject'] = 'Learning Event User cancellation - {$a}';
$string['setting:admincancelemail'] = 'Admin Cancellation email';
$string['setting:admincancelemaillong'] = 'The email sent to an attendee when the session admin removes them individually';
$string['setting:admincancelemaildefault'] = '<p><strong>Learning Event User Cancellation - {{sessionname}}</strong><br /><br />Dear {{user.firstname}},<br /><br />Please note that your attendance at the {{sessionname}} learning event has been cancelled by the organiser. For further information please check the cancellation reason below or contact the organiser. <br /><br /><strong>Date:</strong> {{date}}<br /><strong>Office:</strong> {{scheduler.office}}<br /><strong>Room:</strong> {{scheduler.room}}<br /><strong>Summary:</strong> {{lunchandlearn.summary}} <br /><br />
<strong>Cancellation Reason:</strong> {{cancellationnote}}
<br /><br />

<strong>IMPORTANT:</strong><br />If you would like to view other learning events coming up in your Region, please click <a href="https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming">here</a>.<br /><br />This email was sent to {{fullname}} from Arup University Moodle.<br />To view other learning events in your Region you can copy and paste this URL <a href="https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming">https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming</a> directly into your browser.</p>';
$string['lunchandlearnadminbulkcancelemailsubject'] = 'Learning Event cancellation - {$a}';
$string['setting:adminbulkcancelemail'] = 'Admin Session Cancellation email';
$string['setting:adminbulkcancelemaillong'] = 'The email sent to an attendee when the session admin deletes an event';
$string['setting:adminbulkcancelemaildefault'] = '<p><strong>Learning Event Cancellation - {{sessionname}}</strong><br /><br />Dear {{user.firstname}},<br /><br />Please note that the {{sessionname}} learning event has been cancelled by the organiser. <br /><br /><strong>Date:</strong> {{date}}<br /><strong>Office:</strong> {{scheduler.office}}<br /><strong>Room:</strong> {{scheduler.room}}<br /><strong>Summary:</strong> {{lunchandlearn.summary}} <br /><br />
<strong>Cancellation Reason:</strong> {{cancellationnote}}
<br /><br />

<strong>IMPORTANT:</strong><br />If you would like to view other learning events coming up in your Region, please click <a href="https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming">here</a>.<br /><br />This email was sent to {{fullname}} from Arup University Moodle.<br />To view other learning events in your Region you can copy and paste this URL <a href="https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming">https://moodle.arup.com/calendar/view.php?course=0&amp;view=upcoming</a> directly into your browser.</p>';
$string['setting:reminderemail'] = 'Reminder email';
$string['setting:cancellationreason'] = 'No reason given';
$string['setting:reminderemaillong'] = 'The email sent to an attendee as reminder. You can set when this is sent below.';
$string['setting:reminderemaildefault'] = '<p><strong>Learning Event Reminder - {{sessionname}}</strong><br /><br />Dear {{user.firstname}},<br /><br />You have registered to attend the {{sessionname}} learning event. Here are the event details:<br /><br /><strong>Date:</strong> {{date}}<br /><strong>Office:</strong> {{scheduler.office}}<br /><strong>Room:</strong> {{scheduler.room}}<br /><br /><strong>Summary</strong>{{lunchandlearn.summary}}<br/><br/>
<strong>Agenda</strong>
{{lunchandlearn.description}}
<strong>Online joining instructions (if applicable)</strong>{{lunchandlearn.joindetail}}<br/><br/><strong>IMPORTANT:</strong><br />If you need to cancel your place then please click <a href="{{cancelurl}}">here</a>. Please note that declining or deleting the calendar appointment alone will not inform the organiser that you will not be attending.<br /><br />This email was sent to {{fullname}} from Arup University Moodle.<br />To view the event information online you can copy and paste this URL {{lunchandlearn.get_cal_url|full}} or to cancel you can copy and paste this URL {{cancelurl}} directly into your browser.</p>';
$string['setting:reminderdays'] = 'Reminder period';
$string['setting:reminderdayslong'] = 'Number of days before the session to send a reminder email';

$string['closed'] = 'Complete';
$string['open']  = 'Open';
$string['cancelled']  = 'Cancelled';
$string['outof'] = '/';

$string['label:attendance'] = 'Attendance: ';
$string['label:attending'] = 'Attending: ';
$string['label:location'] = 'Location: ';
$string['label:youattended'] = 'You attended';
$string['label:availableinperson'] = 'Available in-person: ';
$string['label:capacity'] = 'Places: ';
$string['label:overbookinperson'] = 'Allow overbooking: ';
$string['label:availableonline'] = 'Available online: ';
$string['label:onlinecapacity'] = 'Online Places: ';
$string['label:overbookonline'] = 'Allow overbooking online: ';
$string['label:availableplaces'] = 'Available places: ';
$string['button:nocapacity'] = 'Full';


$string['label:onlinesession'] = 'Online: ';

$string['notice:overcapacity'] = 'This session is currently at, or over, indicated capacity. You may still register your intent to attend but please note that availablity may be limited to those that signed up first.';

$string['tab:summary'] = '<em class="fa fa-file-text-o"></em> Summary';
$string['tab:description'] = '<em class="fa fa-th-list"></em> Agenda';
$string['tab:joindetail'] = '<em class="fa fa-headphones">​</em> Online Instructions';
$string['tab:recording'] = 'Session Recording';
$string['tab:related'] = '<em class="fa fa-book"></em> Related Material';

$string['eventsummary_help'] = 'Add a brief summary of the session. This will be used as the description of the event in an attendee\'s CPD record / learning history.';
$string['eventjoindetail_help'] = 'Add details for how attendees can join the online meeting e.g. Lync instructions.';
$string['eventdescription_help'] = 'Add an agenda, or any other information about the Learning Event';
$string['sessioninfo_help'] = 'Further info for display on sign-up form';
$string['sessionmaterials_help'] = 'Add any related files to this area, and they will displayed in the session. You can use this to provide any materials attendees may need prior to the session';
$string['label:capacity_help'] = 'If this event has limited seating then you can specify the number of places available.';
$string['label:onlinecapacity_help'] = 'If this online session has limited seating then you can specify the number of places here';
$string['eventcategory_help'] = 'If you specify a module category then the session will appear under that context when browsing the catalogue.';
$string['eventrequirements_help'] = 'Please use this box to let us know any dietary or other requirements';
$string['label:overbookinperson_help'] = 'If checked, apply a soft limit, which displays a capacity but allows sign-ups even if they exceed capacity. If not checked, sign-ups will not be allowed once capacity has been reached.';
$string['label:overbookonline_help'] = 'If checked, apply a soft limit, which displays a capacity but allows sign-ups even if they exceed capacity. If not checked, sign-ups will not be allowed once capacity has been reached.';
$string['eventnotescancel_help'] = 'Add a note to explain the reason for cancellation. This will be displayed within the cancellation email sent to the user';
$string['thead:searchusers_help'] = 'Search only contains Moodle users, if missing please ask user to login to create an account before trying again';

$string['signup'] = 'Register';
$string['cancelsignup'] = 'Go to cancellation page';
$string['signedup'] = 'Registered';
$string['sessionpassed'] = 'Unfortunately, this session has already been taken, but you can:';
$string['viewrecording'] = 'view a recording';
$string['more'] = 'View calendar';
$string['nocapacity'] = '(full)';

$string['label:overbooking'] = 'Overbooking';
$string['label:inperson'] = 'In-person: ';
$string['label:online'] = 'Online: ';

$string['thead:signupcreated'] = 'Created';
$string['thead:signupdate'] = 'Updated';

$string['lunchandlearnviewsessions'] = 'View learning events';
$string['lunchandlearnmarkattendance'] = 'Mark Attendance';

$string['timezone'] = 'Timezone';
$string['lunchandlearnlist'] = 'List Learning Event Sessions';
$string['lunchandlearnattendeelist'] = 'Session Attendees';

$string['attendees'] = 'Show Attendees';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';

$string['blocknosessions'] = 'No sessions to display, you can add one below';

$string['tapserror'] = 'We were unable to set attendance for all participants. Please contact the Univeristy team for assistance.';
$string['signuptogetinstructions'] = '<div class="alert alert-warning" role="alert">Sign-up to the session in order to view the online joining instructions</div>';

$string['popover:online'] = 'Online';
$string['popover:onlinedata'] = 'Places available online';
$string['popover:inperson'] = 'In person';
$string['popover:inpersondata'] = 'Places available to attend in person';
$string['warn:onlineonly']   = 'Places only available online';
$string['warn:inpersononly'] = 'Places only available in person';

$string['error:missinguser'] = 'Please select at least one user that will attend';
$string['error:missinguser:id'] = 'Could not load the user with ID: {$a}';
$string['error:selectattendancetype'] = 'Please select whether they will attend in person, or online';

// Events.
$string['eventattendancetaken'] = 'Attendance taken';
$string['eventeventviewed'] = 'Event viewed';
$string['eventusersignupcancelled'] = 'User signup cancelled';
$string['eventusersignupcompleted'] = 'User signup completed';

// Search
$string['loadmore'] = 'Load more learning events&hellip;';
$string['loadfiltered'] = 'Filtering learning events&hellip;';

$string['lunchandlearn:view'] = 'Allow user to view learning events';