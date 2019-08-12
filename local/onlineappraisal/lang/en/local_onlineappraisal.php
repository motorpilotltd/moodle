<?php
// This file is part of the Arup online appraisal system
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
 * Language pack for local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 *
 * @package    local_onlineappraisal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_permissions'] = 'Permissions cache';

$string['onlineappraisal:deleteappraisal'] = 'Allowed to permanently delete appraisals';
$string['onlineappraisal:itadmin'] = 'Allowed to access the IT admin area';
$string['pluginname'] = 'Online Appraisal';
$string['setting:logo'] = 'Alternate logo';
$string['setting:logo_desc'] = 'An alternate logo which will be used specifically for appraisal pages.';
$string['setting:helpurl'] = 'Help Url';
$string['setting:helpurl_desc'] = 'A link to a help page for the Appraisal menu';
$string['setting:quicklinks'] = 'Quick links';
$string['setting:quicklinks_desc'] = 'Links to be added to the quick links block under the main navigation block.

Enter each quick link on a new line with format:<br>
item text, link URL, language code(s), and page(s), separated by pipe characters.
language codes and page codes are optional (empty indicates all) and should be comma separated list, can be negated with ! at beginning, for displaying the item for/on specific lanaguages/pages

For example...<br>
1. Shows for all languages except es, pl, nl, on all pages<br>
2. Shows for es, on all pages<br>
3. Shows for pl, nl, on all pages<br>
4. Shows for all languages, on all pages
5. Shows for all languages, on leaderplan page only
5. Shows for all languages, on all pages except leaderplan page.
<pre>
Contribution Guide|https://example.com/guide|!es,pl,nl
Guía de Contribución|https://example.com/guide-es|es
Guide for Poland and the Netherlands|https://example.com/guide-pl-nl|pl,nl
Guide to Appraisal|https://example.com/guide-appraisal
Leadership Development Plan|https://example.com/leader-plan||leaderplan
General Info|https://example.com/general-info||!leaderplan
</pre>';
$string['settings'] = 'Online appraisal configuration';
$string['taskarchivecycles'] = 'Archive old cycle appraisals';

$string['appraisal'] = 'Appraisal';

// User types.
$string['appraisee'] = 'Appraisee';
$string['appraiser'] = 'Appraiser';
$string['groupleader'] = 'Leader';
$string['hrleader'] = 'HR Leader';
$string['moodle'] = 'System';
$string['signoff'] = 'Sign Off';
$string['leadersignoff'] = 'Leader Sign Off';
$string['vip'] = 'VIP';

// Pages.
$string['allstaff'] = 'All Staff';
$string['archived'] = 'Archived';
$string['itadmin'] = 'IT Admin';
$string['careerdirection'] = 'Career Direction';
$string['complete'] = 'Complete';
$string['development'] = 'Development Plan';
$string['deleted'] = 'Deleted';
$string['feedback'] = 'Request Feedback';
$string['impactplan'] = 'Agreed Impact Plan';
$string['index'] = 'Dashboards';
$string['admin'] = 'Admin';
$string['index:appraisee'] = 'Appraisee Dashboard';
$string['index:appraiser'] = 'Appraiser Dashboard';
$string['index:businessadmin'] = 'Appraisal Admin Dashboard';
$string['index:contributor'] = 'Contributor Dashboard';
$string['index:costcentreadmin'] = 'Cost Centre Setup';
$string['index:moodle'] = 'Return to Moodle';
$string['index:groupleader'] = 'Leader Dashboard';
$string['index:help'] = 'Help';
$string['index:hrleader'] = 'HR Dashboard';
$string['index:signoff'] = 'Sign Off Dashboard';
$string['index:itadmin'] = 'IT Admin Dashboard';
$string['initialise'] = 'Initialise';
$string['inprogress'] = 'In Progress';
$string['introduction'] = 'Introduction';
$string['startappraisal'] = 'Start Online Appraisal';
$string['continueappraisal'] = 'Continue Online Appraisal';
$string['lastyear'] = 'Last Year Review';
$string['overview'] = 'Overview';
$string['signoff'] = 'Sign Off';
$string['sixmonth'] = 'Six Month Review';
$string['userinfo'] = 'Appraisee Info';
$string['summaries'] = 'Summaries';
$string['checkin'] = 'Check-in';
$string['help'] = 'Help';
$string['successionplan'] = 'Succession Development Plan';
$string['leaderplan'] = 'Leadership Development Plan (Beta)';

// General alerts.
$string['alert:language:notdefault'] = '<strong>Warning</strong>: You are not using the default language to view this appraisal. Please ensure you provide answers to the questions in the most appropriate language for everyone involved.';
$string['alert:language:notdefault:type'] = 'warning';

// Help Page.
$string['helppage:helpbtn'] = 'View Help Page';
$string['helppage:title'] = 'Help and Support';
$string['helppage:intro'] = 'Click the button below to access the Online Appraisal help page.';

// Request Feedback.
$string['feedback_contributor'] = 'Contributor';
$string['feedback_email_address'] = 'Email Address';
$string['feedback_sent_date'] = 'Date Email Sent';
$string['feedback_status'] = 'Status';
$string['addfeedback'] = 'Add New Contributor';
$string['addreceivedfeedback'] = 'Add Received Feedback';
$string['appraisee_feedback_delete_text'] = 'Delete';
$string['appraisee_feedback_editresend_text'] = 'Edit and Resend';
$string['appraisee_feedback_view_text'] = 'View';
$string['appraisee_feedback_viewrequest_text'] = 'View request email';
$string['feedback_setface2face'] = 'You have to set a date for the face to face appraisal meeting before you can add feedback requests. This can be found on the Appraisee Info page.';
$string['feedback_comments_none'] = '<em>No additional comments provided.</em>';

// Give Feedback.
$string['feedback_header'] = 'Giving your feedback on {$a->appraisee_fullname} (Appraiser: {$a->appraiser_fullname} - Appraisal Date: {$a->facetofacedate})';
$string['confidential_label'] = 'Confidential';
$string['confidential_label_text'] = 'Tick this box to keep your comments confidential. If you leave this box unticked, your comments will be shared with the appraisee.';
$string['feedback_send_copy'] = 'Email me a copy';
$string['feedback_intro'] = 'Please choose three or more colleagues to contribute feedback on your appraisal. In most regions this feedback can be internal or external. Please refer to your region for specific guidance.<br/><br/> For internal contributors, you should consider gathering feedback from a "360 degree" perspective, i.e. peers, those more senior and those more junior than you. You must select a mixture of people.<br/><br/><div data-visible-regions="UKIMEA, EUROPE, AUSTRALASIA">One of your contributors might be an external client or collaborator who knows you very well.</div>
<div data-visible-regions="East Asia"><br /><div class="alert alert-warning">For East Asia region, we expect feedback to be from internal source only. Comments from external client or collaborator should be understood and fed back through internal people.</div></div>
<div data-visible-regions="Americas"><br /><div class="alert alert-warning">For the Americas Region, comments from external clients or collaborators should be fed back through conversations gathered outside of this feedback tool.</div></div>
<br /><div class="alert alert-danger"> Note: Your selected contributors’ feedback will be published here after the face to face meeting, unless the feedback was requested by your appraiser. In this case, your appraiser must send the appraisal to you for your final comments (stage 3) for the feedback to appear.</div>';


// Page content.
$string['actionrequired'] = 'Action required';
$string['actions'] = 'Actions';
$string['admin:appraisalcycle:assign'] = 'Assign';
$string['admin:appraisalcycle:assign:tooltip'] = 'Assign user to appraisal cycle';
$string['admin:appraisalcycle:closed'] = 'This appraisal cycle has now closed, all appraisals from this cycle have now been archived.';
$string['admin:appraisalcycle:unassign'] = 'Unassign';
$string['admin:appraisalcycle:unassign:tooltip'] = 'Unassign user from appraisal cycle';
$string['admin:appraisalnotrequired'] = 'Appraisal not required';
$string['admin:appraisalnotrequired:noreason'] = 'No reason set';
$string['admin:appraisalnotvip'] = 'Not appraisal VIP';
$string['admin:appraisalrequired'] = 'Appraisal required';
$string['admin:allstaff:assigned'] = 'Assigned to this appraisal cycle';
$string['admin:allstaff:assigned:none'] = 'No users are assigned to this appraisal cycle.';
$string['admin:allstaff:button:lock'] = 'Assign users to appraisal cycle';
$string['admin:allstaff:button:start'] = 'Start appraisal cycle';
$string['admin:allstaff:button:update'] = 'Update default due date';
$string['admin:allstaff:notassigned'] = 'Not assigned to this appraisal cycle';
$string['admin:allstaff:notassigned:none'] = 'All users have been assigned to this appraisal cycle.';
$string['admin:allstaff:nousers'] = 'There are no active users for this group.';
$string['admin:appraisalvip'] = 'Appraisal<br>VIP';
$string['admin:bulkactions'] = 'Bulk Actions';
$string['admin:confirm:delete'] = 'Are you sure you want to delete this appraisal?';
$string['admin:confirm:lock'] = 'Are you sure you want to assign the marked users and lock the appraisal cycle user list?';
$string['admin:confirm:start'] = 'Are you sure you want to start a new appraisal cycle?';
$string['admin:deletingdots'] = 'Deleting...';
$string['admin:duedate'] = 'Due Date';
$string['admin:duedate:default'] = 'Default Due Date';
$string['admin:email'] = 'Email Appraisees';
$string['admin:employmentcategory'] = 'Employment Category';
$string['admin:grade'] = 'Grade';
$string['admin:initialise'] = 'Create Appraisal';
$string['admin:initialise:wideview'] = 'Wide view';
$string['admin:initialise:standardview'] = 'Standard view';
$string['admin:initialisingdots'] = 'Creating...';
$string['admin:leaver'] = 'User is no longer an active staff member.';
$string['admin:lockingdots'] = 'Assigning...';
$string['admin:name'] = 'Name';
$string['admin:nousers'] = 'No matching users found.';
$string['admin:processingdots'] = 'Processing...';
$string['admin:requiresappraisal'] = 'Requires<br>appraisal';
$string['admin:savingdots'] = 'Saving...';
$string['admin:staff:returnto'] = 'Return to appraisal admin';
$string['admin:staffid'] = 'Staff ID';
$string['admin:start'] = 'Start Appraisal Cycle';
$string['admin:startdate'] = 'Start Date';
$string['admin:startingdots'] = 'Starting...';
$string['admin:toptext:allstaff:closed'] = '<div class="alert alert-danger">Appraisal Cycle {$a} is Closed.</div>This appraisal cycle is now closed and no further changes can be made.';
$string['admin:toptext:allstaff:notclosed'] = '<div class="alert alert-success">Appraisal Cycle {$a} is Open</div><p>This list shows all users that appear against the above cost centre in Moodle. If there are any discrepancies in the list, please contact HR to check records in TAPS.</p><p>Use the assigned and unassigned lists below to add or remove users from the current appraisal cycle. New starters will not automatically be added and will need to be assigned if they require an appraisal. Any leavers with an active appraisal (assigned) will be shown greyed out unless you remove them from this cycle. To create appraisals please use the "Initialise" tab in the navigation box.</p>';
$string['admin:toptext:allstaff:notlocked'] = '<div class="alert alert-warning">New Appraisal Cycle {$a} Users not yet Assigned </div><p>This list shows all users that appear against the above cost centre in Moodle. If there are any discrepancies in the list, please contact HR to check records in TAPS.</p><p>Please check and mark users as either requiring or not requiring an appraisal for this new appraisal cycle before clicking on the "Assign users to appraisal cycle" button at the bottom of the page to enable you to initialise appraisals. (NB: this can be adjusted at any time on the All Staff page when selecting the current cycle).</p>';
$string['admin:toptext:allstaff:notstarted'] = '<div class="alert alert-warning">New Appraisal Cycle {$a} is not yet Started. </div>Starting the new appraisal cycle will archive all current appraisals for this group. Once archived you will be able to set up who requires an appraisal during this cycle before moving to the initialise page to start initialising  appraisals. Please add the default due date for your appraisals before clicking on the "Start appraisal cycle" button to begin.';
$string['admin:toptext:archived'] = 'Archived appraisals are a record of previous years appraisals and cannot be edited.';
$string['admin:toptext:complete'] = 'Complete appraisals will appear here once they have been signed off by the Sign Off User. Just
    prior to initialising a new set of appraisals the current appraisals will need to be archived. When an appraisal is archived, no
    further progress can be made and the appraisal will become locked in its current state. Users will be able to access the appraisal in the
    archived appraisals section of their dashboard.';
$string['admin:toptext:deleted'] = 'Deleted appraisals have been removed from the appraisal process but remain stored on the system.';
$string['admin:toptext:initialise'] = 'To set up users\' appraisals you need to add a due date, select the Appraiser and Sign Off User
    using the drop down arrows alongside the user, and then click on Create Appraisal. This will start the appraisal process and trigger
    an email to the Appraisee (cc Appraiser) to say the process has begun, giving them a link to the document.';
$string['admin:toptext:inprogress'] = 'Appraisals can be monitored below on this list. They will move to Complete once they have been
    signed off. Actions in the table allow you to change the Appraiser / Sign Off User as well as delete the appraisal (note
    this will not be recoverable). Using the select and the drop down at the bottom of the page you can email users to chase
    progress. Archive is used at the end of the year to enable you to create a new appraisal.';
$string['admin:updatingdots'] = 'Updating...';
$string['admin:usercount'] = 'Total number of staff in selected cost centre: {$a}';
$string['admin:usercount:assigned'] = '({$a} users)';
$string['admin:usercount:notassigned'] = '({$a} users)';
$string['appraisal:id'] = 'App ID';
$string['appraisal:progress'] = 'Appraisal progress';
$string['appraisal:select'] = 'Select appraisal {$a}';
$string['appraisals:archived'] = 'Archived Appraisals';
$string['appraisals:current'] = 'Current Appraisals';
$string['appraisals:noarchived'] = 'There are no archived appraisals.';
$string['appraisals:noarchived:search'] = 'There are no archived appraisals for the chosen appraisee.';
$string['appraisals:noarchived:you'] = 'You have no archived appraisals.';
$string['appraisals:noarchived:cycle'] = 'There are no archived appraisals for the chosen cycle.';
$string['appraisals:nocurrent'] = 'There are no current appraisals.';
$string['appraisals:nocurrent:search'] = 'There are no current appraisals for the chosen appraisee.';
$string['appraisals:nocurrent:you'] = 'You have no current appraisals.';
$string['appraisals:select'] = 'Select all appraisals';

$string['activitylogs'] = 'Activity Logs';
$string['checkins'] = 'Check-ins';
$string['checkins:latest'] = 'Latest check-in';
$string['cohort'] = 'Appraisal Cycle';
$string['comment'] = 'Comment';
$string['comment:adddots'] = 'Add a comment...';
$string['comment:addingdots'] = 'Adding...';
$string['comment:addnewdots'] = 'Add a new comment...';
$string['comment:ldp:locking'] = ' Leadership Development Plan has been marked as complete by {$a->relateduser}';
$string['comment:ldp:unlocking'] = 'Leadership Development Plan has been unlocked by {$a->relateduser}';
$string['comment:sdp:locking'] = ' Succession Development Plan has been locked by {$a->relateduser}';
$string['comment:sdp:unlocking'] = 'Succession Development Plan has been unlocked by {$a->relateduser}';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i> Show more';
$string['comment:status:0_to_1'] = '{$a->status} - The appraisal has been created but not started yet.';
$string['comment:status:1_to_2'] = '{$a->status} - The appraisal has been started by the appraisee.';
$string['comment:status:2_to_3'] = '{$a->status} - The appraisal has been submitted for appraiser review.';
$string['comment:status:3_to_2'] = '{$a->status} - The appraisal has been returned to the appraisee.';
$string['comment:status:3_to_4'] = '{$a->status} - The appraisal is awaiting comments from the appraisee.';
$string['comment:status:4_to_3'] = '{$a->status} - The appraisal has been returned to the appraiser.';
$string['comment:status:4_to_5'] = '{$a->status} - Awaiting appraiser to send to sign off user for sign off.';
$string['comment:status:5_to_4'] = '{$a->status} - The appraisal has been returned to the appraisee.';
$string['comment:status:5_to_6'] = '{$a->status} - Sent to sign off user for final sign off.';
$string['comment:status:6_to_7'] = '{$a->status} - Appraisal is complete.';
$string['comment:status:7_to_9'] = 'Leader comments have been added by {$a->relateduser}.';
$string['comment:system'] = 'System';
$string['comment:updated:appraiser'] = '{$a->ba} changed the appraiser from {$a->oldappraiser} to {$a->newappraiser}.';
$string['comment:updated:groupleader'] = '{$a->ba} changed the leader user from {$a->oldgroupleader} to {$a->newgroupleader}.';
$string['comment:updated:groupleader:empty'] = 'NOT SET';
$string['comment:updated:signoff'] = '{$a->ba} changed the sign off user from {$a->oldsignoff} to {$a->newsignoff}.';
$string['comment:removed:feedback'] = '{$a->itadmin} removed feedback from {$a->sender}, reason: {$a->reason}';
$string['comment:status:change'] = '{$a->itadmin} changed appraisal status to {$a->status}, reason: {$a->reason}';
$string['comment:toggleleaderplan:has'] = 'Leadership Development plan has been added to this appraisal by {$a->relateduser}.';
$string['comment:toggleleaderplan:hasnot'] = 'Leadership Development plan has been removed from this appraisal by {$a->relateduser}.';
$string['comment:togglesuccessionplan:has'] = 'Succession Development plan has been added to this appraisal by {$a->relateduser}.';
$string['comment:togglesuccessionplan:hasnot'] = 'Succession Development plan has been removed from this appraisal by {$a->relateduser}.';

$string['date:complete'] = 'Completed Date';
$string['date:due'] = 'Due Date';
$string['date:f2f'] = 'F2F Date';

$string['f2f:complete'] = 'F2F Held';
$string['f2f:notcomplete'] = 'F2F Not Held';

$string['group'] = 'Cost Centre';

$string['inactive'] = 'INACTIVE';
$string['index:awaiting'] = 'Awaiting your input';
$string['index:emptyfilter'] = 'No appraisals match your filter selection.';
$string['index:filter:label'] = 'Appraisal Cycle';
$string['index:togglef2f:complete'] = 'Mark F2F as Held';
$string['index:togglef2f:notcomplete'] = 'Mark F2F as Not Held';
$string['index:notstarted'] = 'Not Started';
$string['index:notstarted:tooltip'] = 'The appraisee has not yet started their appraisal, once they have you will be able to access it.';
$string['index:notables'] = 'Please choose a cost centre or initiate a search to see results.';
$string['index:printappraisal'] = 'Download Appraisal';
$string['index:printfeedback'] = 'Download Feedback';
$string['index:printleaderplan'] = 'Download Leadership Development Plan';
$string['index:printsuccessionplan'] = 'Download Succession Development Plan';
$string['index:search'] = 'Search by Appraisee name';
$string['index:search:hrleader'] = 'Search across cost centres by Appraisee name';
$string['index:start'] = 'Start Appraisal';
$string['index:toggleleavers:hide'] = 'Hide leavers';
$string['index:toggleleavers:show'] = 'Show leavers';
$string['index:toptext:appraisee'] = 'This dashboard shows your current and any archived appraisals. Your current appraisal can
    be accessed using the link under the Actions dropdown. Archived appraisals can be downloaded using the Download Appraisal button below.';
$string['index:toptext:appraiser'] = 'This dashboard shows any current and archived appraisals for which you are the appraiser. Any
    current appraisals can be accessed using the link under the Actions dropdown. The feedback download contains feedback that will
    not be available to the appraisee until after the face to face meeting. Any confidential feedback will remain hidden at all stages.
    Archived appraisals can be downloaded using the Download Appraisal button below.';
$string['index:toptext:groupleader'] = 'This dashboard shows any current and archived appraisals in your cost centres. Any current
    appraisals can be accessed or downloaded using the links under the Actions dropdown. Archived appraisals can be downloaded using
    the Download Appraisal button below.';
$string['index:toptext:hrleader'] = 'This dashboard shows any current and archived appraisals in your cost centres. Any current
    appraisals can be accessed or downloaded using the links under the Actions dropdown. Archived appraisals can be downloaded using
    the Download Appraisal button below.';
$string['index:toptext:signoff'] = 'This dashboard shows any current and archived appraisals for which you are the sign off. Any current
    appraisals can be accessed using the link under the Actions dropdown. Archived appraisals can be downloaded using the Download Appraisal button below.';
$string['index:toptext:itadmin'] = 'This dashboard shows tools to manage user Appraisals';
$string['index:view'] = 'View Appraisal';

$string['leaderplan:has'] = 'Has Leadership Development Plan';
$string['leaderplan:hasnot'] = 'Doesn\'t have Leadership Development Plan';
$string['leaderplan:th'] = 'LDP';

$string['progress'] = 'Progress';
$string['print:button:appraisal'] = '<i class="fa fa-download"></i> Download Appraisal';
$string['print:button:feedback'] = '<i class="fa fa-download"></i> Download Feedback';
$string['print:button:leaderplan'] = '<i class="fa fa-download"></i> Download Leadership Development Plan';
$string['print:button:successionplan'] = '<i class="fa fa-download"></i> Download Succession Development Plan';

$string['successionplan:has'] = 'Has Succession Development Plan';
$string['successionplan:hasnot'] = 'Doesn\'t have Succession Development Plan';
$string['successionplan:th'] = 'SDP';

$string['tagline'] = '{$a}\'S APPRAISAL';
$string['timediff:now'] = 'Now';
$string['timediff:second'] = '{$a} second';
$string['timediff:seconds'] = '{$a} seconds';
$string['timediff:minute'] = '{$a} minute';
$string['timediff:minutes'] = '{$a} minutes';
$string['timediff:hour'] = '{$a} hour';
$string['timediff:hours'] = '{$a} hours';
$string['timediff:day'] = '{$a} day';
$string['timediff:days'] = '{$a} days';
$string['timediff:month'] = '{$a} month';
$string['timediff:months'] = '{$a} months';
$string['timediff:year'] = '{$a} year';
$string['timediff:years'] = '{$a} years';

// Success strings.
$string['success:appraisal:create'] = 'The appraisal was successfully created.';
$string['success:appraisal:delete'] = 'The appraisal was successfully deleted.';
$string['success:appraisal:toggle'] = 'The user\'s appraisal requirements were successfully updated.';
$string['success:appraisal:togglevip'] = 'The user\'s appraisal VIP status was successfully updated.';
$string['success:appraisal:update'] = 'The appraisal was successfully updated.';
$string['success:appraisalcycle:assign'] = '{$a} has been assigned to the current appraisal cycle.';
$string['success:appraisalcycle:assign:reactivated'] = '{$a} has been assigned to the current appraisal cycle.<br />Their previously started appraisal has been reactivated.';
$string['success:appraisalcycle:lock'] = 'The marked users have been assigned to this appraisal cycle.';
$string['success:appraisalcycle:start'] = 'The appraisal cycle has been started and you can now assign users to it.';
$string['success:appraisalcycle:update'] = 'The default due date for this appraisal cycle has been updated.';
$string['success:appraisalcycle:unassign'] = '{$a} has been unassigned from the current appraisal cycle.<br />They have also been marked as not requiring an appraisal.';
$string['success:appraisalcycle:unassign:suspended'] = '{$a} has been unassigned from the current appraisal cycle.<br />They have also been marked as not requiring an appraisal.<br />The user is flagged as no longer an active member of staff so will not appear in the unassigned users list.';

$string['success:comment:add'] = 'Your comment has been added.';

$string['success:f2fdate:update'] = 'F2F date has been updated.';

$string['success:togglef2f:complete'] = 'F2F has been marked as held.';
$string['success:togglef2f:notcomplete'] = 'F2F has been marked as not held.';
$string['success:toggleleaderplan:has'] = 'Leadership Development Plan has been added to this appraisal.';
$string['success:toggleleaderplan:hasnot'] = 'Leadership Development Plan has been removed from this appraisal.';
$string['success:togglesuccessionplan:has'] = 'Succession Development Plan has been added to this appraisal.';
$string['success:togglesuccessionplan:hasnot'] = 'Succession Development Plan has been removed from this appraisal.';

$string['success:userinfo:datahub:update'] = 'The information was successfully updated from the datahub.';

// Error strings.
$string['error'] = 'Error';

$string['error:appraisal:create'] = 'Sorry, there was an error creating the appraisal.';
$string['error:appraisal:create:appraiseeemail'] = '<br /><strong>Failed to send email to Appraisee.</strong>';
$string['error:appraisal:create:appraiseremail'] = '<br /><strong>Failed to send email to Appraiser.</strong>';
$string['error:appraisal:create:comment'] = '<br /><strong>Failed to add comment.</strong>';
$string['error:appraisal:delete'] = 'Sorry, there was an error deleting the appraisal.';
$string['error:appraisal:select'] = 'Please select at least one appraisal.';
$string['error:appraisal:toggle'] = 'Sorry, there was an error updating the user\'s appraisal requirements.';
$string['error:appraisal:togglevip'] = 'Sorry, there was an error updating the user\'s appraisal VIP status.';
$string['error:appraisal:update'] = 'Sorry, there was an error updating the appraisal.';
$string['error:appraisal:update:appraiseremail'] = '<br /><strong>Failed to send email to new Appraiser.</strong>';
$string['error:appraisal:update:groupleaderemail'] = '<br /><strong>Failed to send email to new Leader.</strong>';
$string['error:appraisal:update:signoffemail'] = '<br /><strong>Failed to send email to new Sign Off.</strong>';
$string['error:appraisalcycle:alreadylocked'] = 'This appraisal cycle has already had users assigned to it.';
$string['error:appraisalcycle:alreadystarted'] = 'This appraisal cycle has already had users assigned to it.';
$string['error:appraisalcycle:closed'] = 'This appraisal cycle is closed, you can no longer make changes to it.';
$string['error:appraisalcycle:groupcohort'] = 'Invalid group or appraisal cycle information submitted.';
$string['error:appraisalexists'] = 'There is already an active appraisal for this user.';
$string['error:appraiseeassuperior'] = 'The appraisee cannot also be the appraiser, sign off or leader.';
$string['error:appraisernotvalid'] = 'The chosen appraiser is not valid for this group.';

$string['error:cohortold'] = 'The selected appraisal cycle is no longer active and was never set up for this group.<br>'
        . '<a href="{$a}">Go to current appraisal cycle</a>.';
$string['error:cohortuser'] = 'Appraisee does not require appraisal in current appraisal cycle.';
$string['error:comment:add'] = 'Sorry, there was an error adding your comment.';
$string['error:comment:validation'] = 'Please provide a comment.';

$string['error:duedate'] = 'Please enter a due date.';

$string['error:f2fdate'] = 'Please enter an F2F date.';
$string['error:f2fdate:update'] = 'Could not update the F2F date.';
$string['error:f2fdate:update:held'] = 'You cannot update the F2F date once it is marked as held.';
$string['error:formnotinit'] = 'Form not initialised.';

$string['error:groupleadernotvalid'] = 'The chosen leader user is not valid for this group.';

$string['error:invalidemail'] = 'Invalid email specified.';
$string['error:invalidemaildata'] = 'Invalid email data provided.';
$string['error:invalidfunction'] = 'The requested function does not exist.';

$string['error:loadappraisal'] = 'Could not load appraisal.';
$string['error:loadusers'] = 'Could not load appraisal users.';
$string['error:loggedinas'] = 'You cannot access the appraisal tool as you are logged in as {$a->loggedinas} for the <a href="{$a->courseurl}">{$a->coursename}</a> course.';

$string['error:toggleassign:confirm:assign'] = 'This will assign the user to the current appraisal cycle and mark them as requiring an appraisal.<br />If the user has a previously archived appraisal in this cycle it will be re-activated otherwise they will become available for initialisation on the initialise page.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:confirm:unassign:appraisalexists'] = 'Warning: There is a current appraisal initialised in the system for this user.<br />By continuing you will either archive (if any content exists) or delete (if not started) their appraisal depending on status (i.e. they will no longer be able to edit).<br />The user will be unassigned from the current appraisal cycle and marked as not requiring an appraisal, which will require a reason to be provided following confirmation.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:confirm:unassign'] = 'The user will be unassigned from the current appraisal cycle and marked as not requiring an appraisal, which will require a reason to be provided following confirmation.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:reason'] = 'Please confirm the reason this user does not require an appraisal.
    {$a->reasonfield} {$a->continue} {$a->cancel}';
$string['error:toggleassign:reason:cancel'] = 'Cancel';
$string['error:toggleassign:reason:continue'] = 'Continue';
$string['error:togglerequired:confirmnotrequired'] = 'Changing this user to not requiring an appraisal will unassign them from the current appraisal cycle if they are currently assigned to it.<br />
    This user does not currently have an active appraisal on the current appraisal cycle.<br />
    Are you sure you wish to proceed?
    <br />{$a->yes} {$a->no}';
$string['error:togglerequired:confirmnotrequired:appraisalexists'] = 'Warning: There is a current appraisal initialised in the system for this user.<br />
    By continuing you will either archive or delete their appraisal depending on status (i.e. they will no longer be able to edit).<br />
    The user will also be unassigned from the associated appraisal cycle.<br />
    Are you sure you wish to proceed?
    <br />{$a->yes} {$a->no}';
$string['error:togglerequired:confirmrequired'] = 'Changing this user to requiring an appraisal will assign them to the current appraisal cycle.<br />
    If an archived appraisal exists for this cycle it will be re-activated, otherwise an appraisal can be initialised for them on the initialise page.<br />
    Are you sure you wish to proceed?
    <br />{$a->yes} {$a->no}';
$string['error:togglerequired:reason'] = 'Please confirm the reason this user does not require an appraisal.
    {$a->reasonfield} {$a->continue} {$a->cancel}';
$string['error:togglerequired:reason:cancel'] = 'Cancel';
$string['error:togglerequired:reason:continue'] = 'Continue';
$string['error:togglef2f:complete'] = 'Could not mark F2F as held.';
$string['error:togglef2f:notcomplete'] = 'Could not mark F2F as not held.';
$string['error:toggleleaderplan:confirm:add'] = 'This will add the requirement to complete a Leadership Development Plan to this appraisal.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:toggleleaderplan:confirm:remove'] = 'This will remove the requirement to complete a Leadership Development Plan from this appraisal.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:toggleleaderplan:has'] = 'Could not add Leadership Development Plan to this appraisal.';
$string['error:toggleleaderplan:hasnot'] = 'Could not remove Leadership Development Plan from this appraisal.';
$string['error:togglesuccessionplan:confirm:add'] = 'This will add the requirement to complete a Succession Development Plan to this appraisal.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:togglesuccessionplan:confirm:remove'] = 'This will remove the requirement to complete a Succession Development Plan from this appraisal.<br />Are you sure you wish to proceed?<br />{$a->yes} {$a->no}';
$string['error:togglesuccessionplan:has'] = 'Could not add Succession Development Plan to this appraisal.';
$string['error:togglesuccessionplan:hasnot'] = 'Could not remove Succession Development Plan from this appraisal.';

$string['error:noaccess'] = 'You do not have permission to view the requested resource.';
$string['error:noappraisal'] = 'Error - You do not have an appraisal in the system. Please contact an Appraisal Administrator listed below for assistance if you require an appraisal to be set up:{$a}';
$string['error:noappraisal:ba'] = '<br>{$a}';
$string['error:noappraisal:ba:details'] = '{$a->fullname} ({$a->email})';
$string['error:noappraisal:ba:separator'] = '<br>';
$string['error:noaction'] = 'Please choose an action.';
$string['error:nochanges'] = 'You have not made any changes.';
$string['error:noselection'] = 'Please make a selection.';

$string['error:pagenotfound'] = 'Page not found: {$a}';
$string['error:pagedoesnotexist'] = 'You do not have permission to view the requested page.';
$string['error:permission:appraisal:create'] = 'You do not have permission to create this appraisal.';
$string['error:permission:appraisal:delete'] = 'You do not have permission to delete this appraisal.';
$string['error:permission:appraisal:toggle'] = 'You do not have permission to change this user\'s appraisal requirements.';
$string['error:permission:appraisal:togglevip'] = 'You do not have permission to change this user\'s appraisal VIP status.';
$string['error:permission:appraisal:update'] = 'You do not have permission to update this appraisal.';
$string['error:permission:appraisalcycle:lock'] = 'You do not have permission to assign users to an appraisal cycle.';
$string['error:permission:appraisalcycle:start'] = 'You do not have permission to start a new appraisal cycle.';
$string['error:permission:appraisalcycle:update'] = 'You do not have permission to update an appraisal cycle.';
$string['error:permission:comment:add'] = 'You do not have permission to add a comment.';
$string['error:permission:f2f:add'] = 'You do not have permission to change the F2F date.';
$string['error:permission:f2f:complete'] = 'You do not have permission to change the F2F held status.';
$string['error:permission:leaderplan:toggle'] = 'You do not have permission to add/remove a Leadership Development Plan.';
$string['error:permission:successionplan:toggle'] = 'You do not have permission to add/remove a Succession Development Plan.';
$string['error:printer:general'] = 'Error whilst generating PDF:<br />{$a}';

$string['error:request'] = 'An error occurred processing the request.';

$string['error:selectgroupleader'] = 'Please select a leader user or mark as not required.';
$string['error:selectusers'] = 'Please select an appraiser and a sign off user.';
$string['error:sessioncheck'] = 'Your Moodle session has expired or there has been an issue contacting the Moodle server.<br />
    To avoid potential data loss we suggest you take a copy of your answers before reloading the page.';
$string['error:signoffasgroupleader'] = 'The sign off user cannot also be the leader.';
$string['error:signoffnotvalid'] = 'The chosen sign off user is not valid for this group.';
$string['error:stages:comment:required'] = 'Returned appraisals require a comment to be added.';
$string['error:stages:commentfailed:auto'] = 'Failed to add the system comment.';
$string['error:stages:commentfailed:user'] = 'Failed to add your comment.';
$string['error:stages:emailnotsent'] = 'Failed to send email to: {$a}';
$string['error:stages:general'] = 'Error: {$a}';
$string['error:stages:invalidpath'] = 'Sorry, you cannot perform that update action.';
$string['error:stages:nopermission'] = 'Sorry, you do not have permission to update the status.';
$string['error:stages:updatefailed'] = 'Failed to update appraisal status.';
$string['error:stages:validation'] = 'Error validating {$a->what}:{$a->field}.';
$string['error:stages:validation:any'] = 'Please ensure you have entered some data before you \'{$a}\'.';
$string['error:stages:validation:appraisal:face_to_face_held'] = 'Please ensure you have indicated that the face to face meeting has been held.';
$string['error:stages:validation:appraisal:held_date'] = 'Please ensure you have set the date for the face to face meeting.';
$string['error:stages:validation:form'] = 'Please ensure you have provided information for the question \'{$a}\'.';
$string['error:stages:validation:groupleader'] = 'You are not the specified Leader for this appraisal.';

$string['error:userinfo:datahub:update'] = 'Sorry, there was an error updating the information from the datahub.';
$string['error:userinfo:datahub:update:status'] = 'You can no longer update this field from the datahub.';

// FFF Email templates Feedback FFF.
// Injected strings.
$string['email:body:appraiseefeedback_link_here'] = 'here';
// Subjects
// Dummy subjects (for injected message).
$string['email:subject:appraiseefeedbackmsg'] = '';
$string['email:subject:appraiserfeedbackmsg'] = '';
// Real subjects (for sent emails).
$string['email:subject:appraiseefeedback'] = 'Request for feedback for my appraisal';
$string['email:subject:appraiserfeedback'] = 'Request for feedback for {{appraisee_fullname}}\'s appraisal';
// Messages (above the 'line').
$string['email:body:appraiseefeedbackmsg'] = '<p>Dear <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>My appraisal meeting is arranged for the <span class="placeholder">{{held_date}}</span>. My appraiser is <span class="placeholder">{{appraiser_fullname}}</span>. As you and I have worked closely together over the past year, I would appreciate your feedback on areas in which you have valued my contribution, and where you feel I could have been more effective. If you are happy to contribute, please click on the link below to provide your feedback.</p> <p>I would be grateful if you could respond before my appraisal meeting.</p>
<p class="ignoreoncopy">Below are any additional comments from <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Yours sincerely,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>
';
$string['email:body:appraiserfeedbackmsg'] = '<p>Dear <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>The appraisal meeting for <span class="placeholder">{{appraisee_fullname}}</span> has been arranged for <span class="placeholder">{{held_date}}</span>. As you have worked closely together recently, I would appreciate your feedback on areas in which you have valued their contribution and where you feel they could have been more effective. If you are happy to contribute, please click on the link below to provide your feedback.</p> <p>I would be grateful if you could respond before the appraisal meeting.</p>
<p class="ignoreoncopy">Below are any additional comments from <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Yours sincerely,<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
// Body of emails that are actually sent.
$string['email:body:appraiseefeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Please click {{link}} to contribute your feedback.</p>
<p>Appraisal Name {{appraisee_fullname}}<br>
   My appraisal is on <span class="placeholder">{{held_date}}</span></p>
<p>This is an auto generated email sent by {{appraisee_fullname}} to {{firstname}} {{lastname}}.</p>
<p>If the link above does not work, please copy the following link into your browser to access the appraisal:<br />{{linkurl}}</p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Please click {{link}} to contribute your feedback.</p>
<p>Appraisal Name {{appraisee_fullname}}<br>
   Their appraisal is on <span class="placeholder">{{held_date}}</span></p>
<p>This is an auto generated email sent by {{appraiser_fullname}} to {{firstname}} {{lastname}}.</p>
<p>If the link above does not work, please copy the following link into your browser to access the appraisal:<br />{{linkurl}}</p>';

$string['appraisee_feedback_email_success'] = 'Email successfully sent';
$string['appraisee_feedback_email_error'] = 'Failed to send email';
$string['appraisee_feedback_invalid_edit_error'] = 'Invalid email address provided';
$string['appraisee_feedback_inuse_edit_error'] = 'Email address already in use';
$string['appraisee_feedback_inuse_email_error'] = 'Email address already in use';
$string['appraisee_feedback_resend_success'] = 'Successfully resent email';
$string['appraisee_feedback_resend_error'] = 'Error trying to resend email';
$string['appraisee_feedback_userfeedback_success'] = 'Successfully added your feedback';
$string['appraisee_feedback_savedraft_success'] = 'Draft feedback saved';
$string['appraisee_feedback_savedraft_error'] = 'Error trying to save your draft';
$string['appraisee_feedback_userfeedback_error'] = 'Error trying to add your feedback';

$string['feedback_requests'] = 'Feedback Requests';

// Email templates.
$string['email:body:comment:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have added a comment to your appraisal which can be accessed by clicking <a href="{{linkappraisee}}">here</a>.</p><p>My comment:<br />{{comment}}</p>
<p>Many thanks,<br />
    {{fromfirstname}} {{fromlastname}}</p>
<br />
<hr>
<p>For further assistance please either contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{fromemail}} by moodle.arup.com - Appraisal status: {{status}} - EmailCommentAppraisee</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:comment:appraisee'] = 'Your Appraisal - Comment added by {{fromtype}}';

$string['email:body:comment:appraiser'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have added a comment to {{appraiseefirstname}} {{appraiseelastname}}\'s appraisal which can be accessed by clicking <a href="{{linkappraiser}}">here</a>.</p><p> My comment:<br />{{comment}}</p>
<p>Many thanks,<br />
    {{fromfirstname}} {{fromlastname}}</p>
<br />
<hr>
<p>For further assistance please either contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{fromemail}} by moodle.arup.com - Appraisal status: {{status}} - EmailCommentAppraiser</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:subject:comment:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Comment added by {{fromtype}}';

$string['email:body:comment:signoff'] = '<p>Dear {{signofffirstname}},</p>
<p>I have added a comment to {{appraiseefirstname}} {{appraiseelastname}}\'s appraisal which can be accessed by clicking <a href="{{linksignoff}}">here</a>.</p><p>My comment:<br />{{comment}}</p>
<p>Many thanks,<br />
    {{fromfirstname}} {{fromlastname}}</p>
<br />
<hr>
<p>For further assistance please either contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{signoffemail}} from {{fromemail}} by moodle.arup.com - Appraisal status: {{status}} - EmailCommentSignOff</p>
<p>Trouble viewing? To view {{appraiseefirstname}} {{appraiseelastname}}\\\'s appraisal online please copy and paste this URL {{linksignoff}} into your browser.</p>';

$string['email:subject:comment:signoff'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Comment added by {{fromtype}}';

// * WORKFLOW Email1Appraisee *
$string['email:body:status:0_to_1:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>Your online appraisal has been created and is now ready for you to begin working on your initial draft. Your appraiser is {{appraiserfirstname}} {{appraiserlastname}}.</p>
<p>Please co-ordinate with {{appraiserfirstname}} to arrange your face to face meeting before you start completing the appraisal.</p>
<p>Your appraisal can be accessed by clicking <a href="{{linkappraisee}}">here</a>.</p>
<p>Kind regards,<br />
    {{bafirstname}} {{balastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{baemail}} by moodle.arup.com - Appraisal status: {{status}} - Email1Appraisee</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:status:0_to_1:appraisee'] = 'Your Appraisal - New appraisal is ready';

// * WORKFLOW Email1Appraiser *
$string['email:body:status:0_to_1:appraiser'] = '<p>Dear {{appraiserfirstname}},</p>
<p>An online appraisal has been created for {{appraiseefirstname}} {{appraiseelastname}}. As the appraiser, please co-ordinate with {{appraiseefirstname}} to arrange an appropriate time for your face to face meeting. You will be notified when {{appraiseefirstname}} shares the appraisal with you to review.</p>
<p>All appraisals need to be completed by {{duedate}}.</p>
<p>You can view the appraisal and progress from the <a href="{{linkappraiserdashboard}}">Appraiser Dashboard</a>.</p>
<p>Kind regards,<br />
    {{bafirstname}} {{balastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{baemail}} by moodle.arup.com - Appraisal status: {{status}} - Email1Appraiser</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:subject:status:0_to_1:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Appraisal started';

// ** WORKFLOW Email2 **
$string['email:body:status:2_to_3:appraiser'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have completed a draft of my appraisal and it is ready for you to review prior to our face to face meeting.</p>
{{comment}}
<p>My appraisal can be accessed by clicking <a href="{{linkappraiser}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiseefirstname}} {{appraiseelastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{appraiseeemail}} by moodle.arup.com - Appraisal status: {{status}} - Email2</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:subject:status:2_to_3:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Draft submitted, awaiting your review';

// *** WORKFLOW Email3 ***
$string['email:body:status:3_to_4:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p> Following our meeting, I have reviewed your appraisal and have added my comments to each section, along with my summary and agreed actions. Please review and write your comments in the Summaries section.</p>
{{comment}}
<p>Your appraisal can be accessed by clicking <a href="{{linkappraisee}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiserfirstname}} {{appraiserlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{appraiseremail}} by moodle.arup.com - Appraisal status: {{status}} - Email3</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:status:3_to_4:appraisee'] = 'Your Appraisal - Reviewed by appraiser, awaiting your review';

// *** WORKFLOW Email3R ***
$string['email:body:status:3_to_2:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have reviewed your draft appraisal and added my comments below. Please review and make any necessary changes.</p>
{{comment}}
<p>Your appraisal can be accessed by clicking <a href="{{linkappraisee}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiserfirstname}} {{appraiserlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{appraiseremail}} by moodle.arup.com - Appraisal status: {{status}} - Email3R</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:status:3_to_2:appraisee'] = 'Your Appraisal - Reviewed by appraiser, further edits required';

// **** WORKFLOW Email4 ****
$string['email:body:status:4_to_5:appraiser'] = '<p>Dear {{appraiserfirstname}}, </p>
<p>I have reviewed my appraisal and have written my comments in the Summaries section. It is now ready for you to review and send for sign off.</p>
{{comment}}
<p> My appraisal can be accessed by clicking <a href="{{linkappraiser}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiseefirstname}} {{appraiseelastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{appraiseeemail}} by moodle.arup.com - Appraisal status: {{status}} - Email4</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:subject:status:4_to_5:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Appraisee reviewed, awaiting your sign off';

// **** WORKFLOW Email4R ****
$string['email:body:status:4_to_3:appraiser'] = '<p>Dear {{appraiserfirstname}}, </p>
<p>I have reviewed my appraisal and added my comments below. Please review and make any necessary changes.</p>
{{comment}}
<p>My appraisal can be accessed by clicking <a href="{{linkappraiser}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiseefirstname}} {{appraiseelastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{appraiseeemail}} by moodle.arup.com - Appraisal status: {{status}} - Email4R</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:subject:status:4_to_3:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Appraisee reviewed, further edits required';

// ***** WORKFLOW Email5 *****
$string['email:body:status:5_to_6:signoff'] = '<p>Dear {{signofffirstname}},</p>
<p>The appraisal for {{appraiseefirstname}} {{appraiseelastname}} is almost complete, and is now ready for you to write your summary and sign off.</p>
{{comment}}
<p>The appraisal can be accessed by clicking <a href="{{linksignoff}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiserfirstname}} {{appraiserlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{signoffemail}} from {{appraiseremail}} by moodle.arup.com - Appraisal status: {{status}} - Email5</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linksignoff}} into your browser.</p>';

$string['email:subject:status:5_to_6:signoff'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Appraiser reviewed, awaiting sign off';

// ***** WORKFLOW Email5R *****
$string['email:body:status:5_to_4:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have reviewed your appraisal and added my comments below. Please review and make any necessary changes.</p>
{{comment}}
<p>Your appraisal can be accessed by clicking <a href="{{linkappraisee}}">here</a>.</p>
<p>Kind regards,<br />
    {{appraiserfirstname}} {{appraiserlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{appraiseremail}} by moodle.arup.com - Appraisal status: {{status}} - Email5R</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:status:5_to_4:appraisee'] = 'Your Appraisal - Appraiser reviewed, further edits required';

// ****** WORKFLOW Email6-APPRAISEE ******
$string['email:body:status:6_to_7:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have reviewed and signed off your appraisal which can be viewed by clicking <a href="{{linkappraisee}}">here</a>.</p>
{{groupleaderextra}}
{{comment}}
<p>Kind regards,<br />
    {{signofffirstname}} {{signofflastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraisee</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:body:status:6_to_7:appraisee:groupleaderextra'] = '<p>Your completed appraisal now awaits leader review and summary. You will be notified once this has happened.</p>';

$string['email:subject:status:6_to_7:appraisee'] = 'Your Appraisal is Complete';

// ****** WORKFLOW Email6-APPRAISER ******
$string['email:body:status:6_to_7:appraiser'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have signed off the appraisal for {{appraiseefirstname}} {{appraiseelastname}} which can be viewed by clicking <a href="{{linkappraiser}}">here</a>.</p>
{{groupleaderextra}}
{{comment}}
<p>Kind regards,<br />
    {{signofffirstname}} {{signofflastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraiser</p>
<p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

$string['email:body:status:6_to_7:appraiser:groupleaderextra'] = '<p>The completed appraisal now awaits leader review and summary. You will be notified once this has happened.</p>';
$string['email:subject:status:6_to_7:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) is complete';

// ****** WORKFLOW Email7-GROUPLEADER ******
$string['email:body:status:6_to_7:groupleader'] = '<p>Dear {{groupleaderfirstname}},</p>
<p>The appraisal for {{appraiseefirstname}} {{appraiseelastname}} is complete, and is ready for you to review and provide your summary.</p>
{{comment}}
<p>The appraisal can be accessed by clicking <a href="{{linkgroupleader}}">here</a>.</p>
<p>Kind regards,<br />
    {{signofffirstname}} {{signofflastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{groupleaderemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email7Leader</p>
<p>Trouble viewing? To view your leader dashboard online please copy and paste this URL {{linkgroupleaderdashboard}} into your browser.</p>';

$string['email:subject:status:6_to_7:groupleader'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) is ready for review';

$string['email:replacement:comment'] = '<p>My comments:<br />{$a}</p>';

// ******* WORKFLOW Email9-APPRAISEE ********
$string['email:body:status:7_to_9:appraisee'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have reviewed and added my comments to your completed appraisal which can be viewed by clicking <a href="{{linkappraisee}}">here</a>.</p>
{{comment}}
<p>Kind regards,<br />
    {{groupleaderfirstname}} {{groupleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{groupleaderemail}} by moodle.arup.com - Appraisal status: {{status}} - Email9Appraisee</p>
<p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:status:7_to_9:appraisee'] = 'Leader comments added to your appraisal';

// ******* WORKFLOW Email9-APPRAISER ********
$string['email:body:status:7_to_9:appraiser'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have added my comments to the appraisal for {{appraiseefirstname}} {{appraiseelastname}} which can be viewed by clicking <a href="{{linkappraiser}}">here</a>.</p>
{{comment}}
<p>Kind regards,<br />
    {{groupleaderfirstname}} {{groupleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{groupleaderemail}} by moodle.arup.com - Appraisal status: {{status}} - Email9Appraiser</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraiser}} into your browser.</p>';

$string['email:subject:status:7_to_9:appraiser'] = 'Appraisal {{appraiseefirstname}} {{appraiseelastname}} - Leader comments added.';

// ******* WORKFLOW Email9-SIGNOFF ********
$string['email:body:status:7_to_9:signoff'] = '<p>Dear {{signofffirstname}},</p>
<p>I have added my comments to the appraisal for {{appraiseefirstname}} {{appraiseelastname}} which can be viewed by clicking <a href="{{linksignoff}}">here</a>.</p>
{{comment}}
<p>Kind regards,<br />
    {{groupleaderfirstname}} {{groupleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{signoffemail}} from {{groupleaderemail}} by moodle.arup.com - Appraisal status: {{status}} - Email9Signoff</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linksignoff}} into your browser.</p>';

$string['email:subject:status:7_to_9:signoff'] = 'Appraisal {{appraiseefirstname}} {{appraiseelastname}} - Leader comments added.';

// USER CHANGE EMAIL
$string['email:body:appraisal:update'] = '<p>Dear {{newfirstname}},</p>
<p>I am writing to confirm that you have been assigned as the new {{usertype}} for {{appraiseefirstname}} {{appraiseelastname}}.</p>
<p>The previous {{usertype}} was {{oldfirstname}} {{oldlastname}}.</p>
<p>If you were unaware of this proposed change and the reason for it, then please contact me as soon as possible.</p>
 <p>Kind regards,<br />
    {{bafirstname}} {{balastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{newemail}} and CC\\\'d to {{ccemails}} from {{baemail}} by moodle.arup.com - Appraisal Updated - {{usertype}}</p>';

$string['email:subject:appraisal:update'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - {{usertype}} Updated';

$string['email:appraisal:update:ccseparator'] = ', ';

// Succession Development Plan add/remove EMAIL.
$string['email:extras:statuswhich:start'] = 'when you have started your appraisal';
$string['email:extras:statuswhich:now'] = 'now';
$string['email:extras:statuswhich:draft'] = 'when the appraisee has submitted a draft of their appraisal';
$string['email:extras:linkwhich:dashboard'] = 'your dashboard';
$string['email:extras:linkwhich:overview'] = 'appraisal overview';
$string['email:extras:linkwhich:successionplan'] = 'appraisal succession development plan';

$string['email:subject:togglesuccessionplan:appraisee:has'] = 'Succession Development Plan added to your appraisal';
$string['email:body:togglesuccessionplan:appraisee:has'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have added a Succession Development Plan to your appraisal which can be viewed {{statusappraiseewhich}}.</p>
<p><a href="{{linkappraisee}}">View {{linkappraiseewhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{hrleaderemail}} by moodle.arup.com - Succession Development Plan Added (Appraisee)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:togglesuccessionplan:appraisee:hasnot'] = 'Succession Development Plan removed from your appraisal';
$string['email:body:togglesuccessionplan:appraisee:hasnot'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have removed the Succession Development Plan from your appraisal.</p>
<p><a href="{{linkappraisee}}">View {{linkappraiseewhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{hrleaderemail}} by moodle.arup.com - Succession Development Plan Removed (Appraisee)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:togglesuccessionplan:appraiser:has'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Succession Development Plan Added';
$string['email:body:togglesuccessionplan:appraiser:has'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have added a Succession Development Plan to the appraisal for {{appraiseefirstname}} {{appraiseelastname}} which can be viewed {{statusappraiserwhich}}.</p>
<p><a href="{{linkappraiser}}">View {{linkappraiserwhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{hrleaderemail}} by moodle.arup.com - Succession Development Plan {{addedremoved}} (Appraiser)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraiser}} into your browser.</p>';

$string['email:subject:togglesuccessionplan:appraiser:hasnot'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Succession Development Plan Removed';
$string['email:body:togglesuccessionplan:appraiser:hasnot'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have removed the Succession Development Plan from the appraisal for {{appraiseefirstname}} {{appraiseelastname}}.</p>
<p><a href="{{linkappraiser}}">View {{linkappraiserwhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{hrleaderemail}} by moodle.arup.com - Succession Development Plan {{addedremoved}} (Appraiser)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraiser}} into your browser.</p>';

// Leadership Development Plan add/remove EMAIL.
$string['email:extras:statuswhich:start'] = 'when you have started your appraisal';
$string['email:extras:statuswhich:now'] = 'now';
$string['email:extras:statuswhich:draft'] = 'when the appraisee has submitted a draft of their appraisal';
$string['email:extras:linkwhich:dashboard'] = 'your dashboard';
$string['email:extras:linkwhich:overview'] = 'appraisal overview';
$string['email:extras:linkwhich:leaderplan'] = 'appraisal leadership development plan';

$string['email:subject:toggleleaderplan:appraisee:has'] = 'Leadership Development Plan added to your appraisal';
$string['email:body:toggleleaderplan:appraisee:has'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have added a Leadership Development Plan to your appraisal which can be viewed {{statusappraiseewhich}}.</p>
<p><a href="{{linkappraisee}}">View {{linkappraiseewhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{hrleaderemail}} by moodle.arup.com - Leadership Development Plan Added (Appraisee)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:toggleleaderplan:appraisee:hasnot'] = 'Leadership Development Plan removed from your appraisal';
$string['email:body:toggleleaderplan:appraisee:hasnot'] = '<p>Dear {{appraiseefirstname}},</p>
<p>I have removed the Leadership Development Plan from your appraisal.</p>
<p><a href="{{linkappraisee}}">View {{linkappraiseewhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseeemail}} from {{hrleaderemail}} by moodle.arup.com - Leadership Development Plan Removed (Appraisee)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

$string['email:subject:toggleleaderplan:appraiser:has'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Leadership Development Plan Added';
$string['email:body:toggleleaderplan:appraiser:has'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have added a Leadership Development Plan to the appraisal for {{appraiseefirstname}} {{appraiseelastname}} which can be viewed {{statusappraiserwhich}}.</p>
<p><a href="{{linkappraiser}}">View {{linkappraiserwhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{hrleaderemail}} by moodle.arup.com - Leadership Development Plan {{addedremoved}} (Appraiser)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraiser}} into your browser.</p>';

$string['email:subject:toggleleaderplan:appraiser:hasnot'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) - Leadership Development Plan Removed';
$string['email:body:toggleleaderplan:appraiser:hasnot'] = '<p>Dear {{appraiserfirstname}},</p>
<p>I have removed the Leadership Development Plan from the appraisal for {{appraiseefirstname}} {{appraiseelastname}}.</p>
<p><a href="{{linkappraiser}}">View {{linkappraiserwhich}}</a>.</p>
<p>Kind regards,<br />
    {{hrleaderfirstname}} {{hrleaderlastname}}</p>
<br />
<hr>
<p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p>
<p>This is an auto generated message sent to {{appraiseremail}} from {{hrleaderemail}} by moodle.arup.com - Leadership Development Plan {{addedremoved}} (Appraiser)</p>
<p>Trouble viewing? To view the appraisal online please copy and paste this URL {{linkappraiser}} into your browser.</p>';

// Forms.

// Standard alerts (Can be customised on a per form basis - see feedback/addfeedback for examples).
$string['form:alert:cancelled'] = 'Editing cancelled, your changes were not saved.';
$string['form:alert:error'] = 'Sorry, an error occurred saving your changes.';
$string['form:alert:saved'] = 'Your changes have been successfully saved.';

// Confirmations.
$string['form:confirm:cancel:title'] = 'Cancel editing';
$string['form:confirm:cancel:question'] = 'Are you sure you wish to cancel editing this appraisal?';
$string['form:confirm:cancel:yes'] = 'Yes';
$string['form:confirm:cancel:no'] = 'No';

// Modals.
$string['form:modal:savenag:content'] = 'It\'s been 15 minutes since you opened this form, please consider saving your work to avoid potential data loss.';
$string['form:modal:savenag:dismiss'] = 'Dismiss';
$string['form:modal:savenag:save'] = 'Save Now';
$string['form:modal:savenag:title'] = 'Save your work?';
$string['modal:printconfirm:cancel'] = 'No, it\'s OK';
$string['modal:printconfirm:content'] = 'Do you really need to print this document?';
$string['modal:printconfirm:continue'] = 'Yes, carry on';
$string['modal:printconfirm:title'] = 'Think before you print';

// General.
$string['form:add'] = 'Add';
$string['form:all'] = 'All';
$string['form:cancel'] = 'Cancel';
$string['form:choosedots'] = 'Choose...';
$string['form:clear'] = 'Clear';
$string['form:delete'] = 'Delete';
$string['form:edit'] = 'Edit';
$string['form:filter'] = 'Filter';
$string['form:go'] = 'Go';
$string['form:language'] = 'Language';
$string['form:notrequired'] = 'Not required';
$string['form:save'] = 'Save';
$string['form:select'] = 'Select';
$string['form:submitcontinue'] = 'Save and Continue';
$string['form:undo'] = 'Undo';
$string['form:nextpage'] = 'Continue';

// Userinfo.
$string['form:userinfo:title'] = 'Appraisee Info';
$string['form:userinfo:intro'] = 'Please complete the details below. Some fields have been pre-populated using your TAPS record. If any of the pre-populated information is incorrect, please contact your HR representative.';
$string['form:userinfo:name'] = 'Appraisee name';
$string['form:userinfo:staffid'] = 'Staff ID';
$string['form:userinfo:grade'] = 'Grade';
$string['form:userinfo:jobtitle'] = 'Job title';
$string['form:userinfo:operationaljobtitle'] = 'Operational job title';
$string['form:userinfo:facetoface'] = 'Proposed face to face date';
$string['form:userinfo:facetofaceheld'] = 'Face to face meeting held';
$string['form:userinfo:setf2f'] = 'Set your face to face meeting time and date';
$string['form:userinfo:refresh'] = 'Refresh';
$string['form:userinfo:refresh:tooltip'] = 'Update field from datahub';

// Feedback.
$string['form:addfeedback:firstname'] = 'Feedback provider firstname';
$string['form:addfeedback:lastname'] = 'Feedback provider lastname';
$string['form:addfeedback:alert:cancelled'] = 'Sending cancelled, your appraisal feedback has not been sent.';
$string['form:addfeedback:alert:error'] = 'Sorry, there was an error sending your appraisal feedback.';
$string['form:addfeedback:alert:saved'] = 'Thank you, your appraisal feedback has been successfully sent.';
$string['form:addfeedback:confirm'] = 'Are you sure you want to submit your feedback? Once you have done so you will be unable to change it.';
$string['form:addfeedback:saveddraft'] = 'You have saved a draft version of your feedback. Until you Send Appraisal Feedback your feedback won\'t be seen by the appraiser or appraisee.';
$string['form:addfeedback:savefeedback'] = 'Save Feedback';
$string['form:addfeedback:notfound'] = 'No Feedback request found';
$string['form:addfeedback:sendemailbtn'] = 'Send Appraisal Feedback';
$string['form:addfeedback:savedraftbtn'] = 'Save as draft';
$string['form:addfeedback:savedraftbtntooltip'] = 'Save to draft to complete later. This will not send a copy of your feedback to the appraiser / appraisee';
$string['form:addfeedback:title'] = 'Feedback Contribution';
$string['form:addfeedback:closed'] = 'The window to submit your feedback is now closed';
$string['form:addfeedback:submitted'] = 'Feedback submitted';
$string['form:addfeedback:addfeedback'] = 'Please describe up to three areas in which you have valued the Appraisee\'s contribution in the last 12 months.';
$string['form:addfeedback:addfeedbackhelp'] = '<div class="well well-sm">..</div>';
$string['form:addfeedback:addfeedback_help'] = 'Please just copy and paste your feedback received into the "valued contribution" box unless you are able to split between "valued" and "more effective".';
$string['form:addfeedback:addfeedback_2'] = 'Please give details of up to three areas in which you feel they could have been more effective. Be honest, but be constructively critical, as this feedback will help your colleague to tackle issues more effectively.';
$string['form:addfeedback:addfeedback_2help'] = '<div class="well well-sm">..</div>';
$string['form:addfeedback:warning'] = 'Note: The feedback you provide will be visible to the appraisee.';
$string['form:feedback:alert:cancelled'] = 'Sending cancelled, your appraisal feedback request has not been sent.';
$string['form:feedback:alert:error'] = 'Sorry, there was an error sending your appraisal feedback request.';
$string['form:feedback:alert:saved'] = 'Your appraisal feedback request has been successfully sent.';
$string['form:feedback:email'] = 'Email address';
$string['form:feedback:firstname'] = 'First Name';
$string['form:feedback:lastname'] = 'Last Name';
$string['form:feedback:language'] = 'Select feedback email language';
$string['form:feedback:sendemailbtn'] = 'Send email to Contributor';
$string['form:feedback:resendemailbtn'] = 'Save and resend email to Contributor';
$string['form:feedback:title'] = 'Feedback - Add a new Contributor';
$string['form:feedback:title:resend'] = 'Feedback - Edit and resend request';
$string['form:feedback:resendhelp'] = '<i class="fa fa-exclamation-triangle"></i> Please check names and dates in the email below are correct before resending, particularly if you are changing details above and/or your appraisal meeting date or appraiser has been changed.';
$string['form:feedback:editemail'] = 'Edit';
$string['form:feedback:providefirstnamelastname'] = 'Please enter the recipient firstname and lastname before clicking the edit button.';

// Last Year Review
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = 'Note: We notice that you don\'t have a previous appraisal in the system. Please upload your last appraisal as a pdf / word document below.';
$string['form:lastyear:intro'] = 'In this section both the appraisee and appraiser discuss what was achieved in the last twelve months and how it was delivered. The <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide To Appraisal</a> gives more information about the nature of this discussion.';
$string['form:lastyear:upload'] = 'Upload Appraisal';
$string['form:lastyear:appraiseereview'] = '1.1 Appraisee review of last year\'s performance';
$string['form:lastyear:appraiseereviewhelp'] = '<div class="well well-sm"> <em>Overall, how well did you perform, in terms of projects, people and clients, since your last appraisal?</em> <ul class="m-b-0"> <li><em>How have you collaborated and shared information and expertise? What were the outcomes?</em></li> <li><em>Was any part of your performance below expectations?</em></li> <li><em>If you are responsible for other people, have you properly managed their performance and behaviour, both good and bad?</em></li> <li><em>How did you use technology to make you more effective?</em></li> </ul> </div>';
$string['form:lastyear:appraiserreview'] = '1.2 Appraiser review of last year\'s performance';
$string['form:lastyear:appraiserreviewhelp'] = '<div class="well well-sm">
    <em>Please comment on the appraisee\'s review of their performance since their last appraisal.</em>
    <ul class="m-b-0">
        <li><em>What progress have they made?</em></li>
        <li><em>Summarise any feedback the appraisee received from nominated contributors.</em></li>
    </ul>
    <em>If any part of their performance or behaviour was below expectation this <strong>must</strong> be discussed and recorded in this section. This may relate to their projects, their team, their clients or other people generally.</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 Appraisee review of last year\'s development';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
    <em>Please comment on your personal development since your last appraisal:</em>
    <ul class="m-b-0">
        <li><em>How did you develop your skills, knowledge or behaviours?</em></li>
        <li><em>What development did you plan for last year that is still outstanding?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 Is there anything that either impacts on, or could enhance, your performance or the team\'s performance?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>To be completed by the appraisee</em></div>';
$string['form:lastyear:file'] = '<strong>A review file has been uploaded by the appraisee: <a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:heading'] = 'Import from last year';
$string['form:lastyear:cardinfo:title'] = 'Title';
$string['form:lastyear:cardinfo:date'] = 'Date';
$string['form:lastyear:cardinfo:description'] = 'Description';
$string['form:lastyear:cardinfo:heading'] = 'Import from last year';
$string['form:lastyear:cardinfo:competency'] = 'Competency';
$string['form:lastyear:cardinfo:progress'] = 'Progress Required';
$string['form:lastyear:cardinfo:action'] = 'Action Required';
$string['form:lastyear:cardinfo:developmentlink'] = 'Last Year Development';
$string['form:lastyear:cardinfo:performancelink'] = 'Last Year Impact Plan';
$string['form:lastyear:cardinfo:none'] = 'You do not have any information from last year available.';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Last year\'s appraisal</a> is available to view (PDF - opens in new window).';

// Career Direction
$string['form:careerdirection:title'] = 'Section 2: Career Direction';
$string['form:careerdirection:intro'] = 'The purpose of this section is to allow the appraisee to consider their career aspirations and discuss these in a practical way with their appraiser. For junior members of staff, the horizon for this conversation is likely to be about 1-3 years. For more senior members of staff we would expect it to be 3-5 years.';
$string['form:careerdirection:mobility'] = 'Mobility: preparedness to relocate';
$string['form:careerdirection:mobilityhelp'] = 'Please expand on your reason with the comments section 2.1.';
$string['form:careerdirection:mobility:answer:1'] = 'Fully internationally mobile';
$string['form:careerdirection:mobility:answer:2'] = 'Fully mobile in current region';
$string['form:careerdirection:mobility:answer:3'] = 'Fully mobile in current country';
$string['form:careerdirection:mobility:answer:4'] = 'Limited mobility in current country';
$string['form:careerdirection:mobility:answer:5'] = 'Not currently mobile';
$string['form:careerdirection:progress'] = '2.1 How do you want your career to progress?';
$string['form:careerdirection:progresshelp'] = '<div class="well well-sm"> <em>You should consider:</em> <ul class="m-b-0"> <li><em>What type of work do you want to be doing and with what level of responsibility?</em></li> <li><em>What is important to you about your work over the next few years e.g. breadth, depth, specialisation, generalisation, mobility, design, responsibility for people, etc?</em></li>
<li><em>Where would you like to be located?</em></li> </ul> </div>';
$string['form:careerdirection:comments'] = '2.2 Appraiser comments';
$string['form:careerdirection:commentshelp'] = '<div class="well well-sm">
    <em>You should consider:</em>
    <ul class="m-b-0">
        <li><em>How realistic, challenging and ambitious are the appraisee\'s aspirations?</em></li>
        <li><em>What roles, projects, and other work opportunities would provide the experience, skills and behavioural development required?</em></li>
    </ul>
</div>';

// Impact Plan
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = 'The Agreed Impact Plan sets out how the appraisee wants to make a difference over the coming year, in terms of the work they do, and their impact on the firm overall. The plan should include how the appraisee will improve their work, or their project / team / office / group. In practice this means providing specifics about timelines, quality, budget, design/innovation and impact on people, clients or work overall.<br /><br />The <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Contribution Guide</a> and the <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide To Appraisal</a> will give suggestions for how these improvements might be made.';
$string['form:impactplan:intro_2'] = 'For those in leadership roles, you may wish to refer to the <a href="https://moodle.arup.com/appraisal/leadershipattributes" target="_blank">Arup Leadership Attributes</a> - the 16 qualities which define us as leaders - and the accompanying <a href="https://moodle.arup.com/appraisal/leadershipattributesguide" target="_blank">guidance</a>.';

$string['form:impactplan:impact'] = '3.1 Describe the impact you want to have on your projects, your clients, your team or the firm next year:';
$string['form:impactplan:impacthelp'] = '<div class="well well-sm">
    <em>In your statement you might include:</em>
    <ul class="m-b-0">
        <li><em>Your areas of focus</em></li>
        <li><em>Why they are important</em></li>
        <li><em>How you will achieve them</em></li>
        <li><em>Who you will collaborate with</em></li>
        <li><em>The approximate timeframe: 3/6/12/18 months or longer</em></li>
        <li><em>How your Agreed Impact Plan fits with and supports your desired career progression</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 Appraiser comments';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>To be completed by appraiser</em></div>';
$string['form:impactplan:support'] = '3.2 What support do you need from Arup to achieve this?';
$string['form:impactplan:supporthelp'] = '<div class="well well-sm">
    <em>You might consider:</em>
    <ul class="m-b-0">
        <li><em>Assistance from others</em></li>
        <li><em>Supervision</em></li>
        <li><em>Resources (time, budget, equipment)</em></li>
        <li><em>Personal development</em></li>
        <li><em>Tools (software, hardware)</em></li>
    </ul>
</div>';

// Development Plan
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = 'The Development Plan sets out what personal skills, knowledge or behavioural changes are needed to support the appraisee\'s career progression and Agreed Impact Plan.<br /><br />
How do you need to develop in the next 12-18 months to achieve this? What support will you need and when do you plan to undertake this development?<br /><br />
<div class="well well-sm">At Arup we use the principle of "70-20-10" in personal development. This means that for most people, 70% of development should be "on the job" and learned from experience. 20% should be via other people, perhaps through coaching or mentoring. The final 10% should be by formal learning methods, like classroom courses or formal e-learning. The percentages are of course just a guideline.</div>';
$string['form:development:leadership'] = 'Do you hold, or aspire to hold, a leadership role?';
$string['form:development:leadership:answer:1'] = 'No';
$string['form:development:leadership:answer:2'] = 'Yes';
$string['form:development:leadershiproles:1'] = 'Additionally, do you hold, or aspire to hold, any of the following roles?';
$string['form:development:leadershiproles:2'] = 'Please select up to two options from the list below:';
$string['form:development:leadershiproles:popover'] = 'The roles listed here are some of the key leadership roles in our firm.
    Profiles have been developed to further define which attributes are considered foundational for that role.
    The profile then sets out specific deliverables that illustrate that particular attribute.
    If you do not hold or aspire to hold one of these roles, please select \'other\'.
    For further information click <a href="https://moodle.arup.com/appraisal/attributeslearningburst" target="_blank">here</a>';
$string['form:development:leadershiproles:links'] = '<a href="https://moodle.arup.com/appraisal/rolespecificattributes" target="_blank">Role Specific Leadership Attribute Guides</a><br>
    <a href="https://moodle.arup.com/appraisal/leadershipattributes" target="_blank">Generic Leadership Attribute Guide</a><br>
    <a href="https://moodle.arup.com/appraisal/attributeslearningburst" target="_blank">Leadership Attributes Learning Burst</a>';
$string['form:development:leadershiproles:answer:generic'] = 'Other';
$string['form:development:leadershiproles:error'] = 'Please select a maximum of two options';
$string['form:development:leadershipattributes:popover'] = 'The 16 Arup Leadership Attributes set out what the form expects of our leaders both in terms of what and how they deliver.
    It is expected that Arup Leaders will possess all of these qualities to a degree but show real strength in some of them and want to develop others.
        For further information click <a href="https://moodle.arup.com/appraisal/attributeslearningburst" target="_blank">here</a>';
$string['form:development:leadershipattributes:generic'] = file_get_contents($CFG->wwwroot . '/local/onlineappraisal/lang/en/leadership-attributes-generic.json');
$string['form:development:leadershipattributes:role'] = file_get_contents($CFG->wwwroot . '/local/onlineappraisal/lang/en/leadership-attributes-role.json');
$string['form:development:leadershipattributes:error:wrongnumber'] = 'Please select two or three options';
$string['form:development:leadershipattributes:error:toomany'] = 'Please select no more than three options';
$string['form:development:seventy'] = 'Learning that takes place in the course of your work - about 70%';
$string['form:development:seventyhelp'] = '<div class="well well-sm"> <em>For example:</em> <ul class="m-b-0"> <li><em>Project assignments</em></li> <li><em>Team assignments</em></li> <li><em>Mobility</em></li> <li><em>Discussion of work and feedback</em></li> <li><em>Project reviews, design charrettes</em></li> <li><em>Reading</em></li> <li><em>Research</em></li> </ul> </div>';
$string['form:development:twenty'] = 'Learning from other people - about 20%';
$string['form:development:twentyhelp'] = '<div class="well well-sm"> <em>For example:</em> <ul class="m-b-0"> <li><em>Team members</em></li> <li><em>Experts</em></li>
<li><em>Clients</em></li>
<li><em>Collaborators</em></li> <li><em>Conferences</em></li> <li><em>Coaching</em></li> <li><em>Mentoring</em></li> </ul> </div>';
$string['form:development:ten'] = 'Learning from formal courses - face to face or online - about 10%';
$string['form:development:tenhelp'] = '<div class="well well-sm">
    <em>For example:</em>
    <ul class="m-b-0">
        <li><em>Classroom courses</em></li>
        <li><em>Formal e-learning</em></li>
        <li><em>Virtual classroom learning</em></li>
    </ul>
</div>';
$string['form:development:comments'] = 'Appraiser comments';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>To be completed by appraiser</em></div>';

// Summaries
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = 'The purpose of this section is to summarise the content of the appraisal for later reference by anyone involved in pay, promotion or development decisions.';
$string['form:summaries:appraiser'] = '5.1 Appraiser summary of overall performance';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm">
    <em>The appraiser should provide a clear, concise summary of performance that can also be easily understood by people connected to future salary/promotion/development decisions. In particular, the appraiser needs to indicate clearly where performance overall has fallen short of - or exceeded - expectations.</em>
</div>';
$string['form:summaries:recommendations'] = '5.2 Agreed actions';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>To be completed by appraiser</em><br/>
    <em>What needs to happen now? For example:</em>
    <ul>
        <li><em>Development</em></li>
        <li><em>Mobility</em></li>
        <li><em>Assignments</em></li>
        <li><em>Performance support</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 Appraisee comments';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>To be completed by appraisee</em></div>';
$string['form:summaries:signoff'] = '5.4 Sign Off summary';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>To be completed by leader / designated sign off.</em></div>';
$string['form:summaries:grpleader'] = '5.5 Leader summary';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>To be completed by senior leader as the final sign off.</em></div>';
$string['form:summaries:grpleadercaption'] = 'Completed by {$a->fullname}{$a->date}';
$string['form:summaries:promotion'] = 'Please provide your assessment of the Appraisee\'s adequacy in their grade by choosing the best option from the list below.
This information is your recommendation to Local Practice Leader & Group Leader and should NOT be discussed with the Appraisee.';
$string['form:summaries:promotion:answer:1'] = 'Recommend promotion to next grade this cycle';
$string['form:summaries:promotion:answer:2'] = 'Well place in current grade';
$string['form:summaries:promotion:answer:3'] = 'Needs development in current grade';
$string['form:summaries:promotion:answer:4'] = 'Not acceptable in current grade';
$string['form:summaries:promotion:answer:5'] = 'Too new to assess';

// Six month review [Legacy].
$string['form:sixmonth:title'] = 'Six Month Review';
$string['form:sixmonth:intro'] = 'The purpose of this section is to provide a review approximately six months after the appraisal took place.';
$string['form:sixmonth:sixmonthreview'] = 'Six Month Review';
$string['form:sixmonth:sixmonthreviewhelp'] = '<div class="well well-sm">To be completed by appraisee and/or appraiser.<br /><br />Last modified: {$a}</div>';
$string['form:sixmonth:never'] = 'Never';

// Succession plan.
$string['form:successionplan:title'] = 'Succession Development Plan';
$string['form:successionplan:intro'] = 'This section informs the succession plan. This will be shared with region board, management board as appropriate. ';
$string['form:successionplan:assessment'] = 'Assessment of Career Path';
$string['form:successionplan:assessment:answer:1'] = 'Significantly larger role';
$string['form:successionplan:assessment:answer:2'] = 'Potential for lateral move to broaden experience';
$string['form:successionplan:assessment:answer:3'] = 'Moderate growth in role';
$string['form:successionplan:assessment:answer:4'] = 'Focus on current role for 12 months';
$string['form:successionplan:readiness'] = 'Readiness for next step';
$string['form:successionplan:readiness:answer:1'] = 'Ready Now';
$string['form:successionplan:readiness:answer:2'] = 'Ready in 1-2 years';
$string['form:successionplan:readiness:answer:3'] = 'Ready in 3-5 years';
$string['form:successionplan:readiness:answer:4'] = 'N/A';
$string['form:successionplan:potential'] = 'Potential Future Roles';
$string['form:successionplan:potential:answer:1'] = 'Business Leader';
$string['form:successionplan:potential:answer:2'] = 'Group Leader';
$string['form:successionplan:potential:answer:3'] = 'Practice Leader (Americas Region ONLY)';
$string['form:successionplan:potential:answer:4'] = 'Project Director';
$string['form:successionplan:potential:answer:5'] = 'Technical Leader';
$string['form:successionplan:potential:answer:6'] = 'Business Services Leader';
$string['form:successionplan:strengths'] = 'Strengths';
$string['form:successionplan:strengths:add'] = 'Add another strength';
$string['form:successionplan:strengths:add:noscript'] = 'Save to add another strength input';
$string['form:successionplan:developmentareas'] = 'Areas for development';
$string['form:successionplan:developmentareas:add'] = 'Add another area for development';
$string['form:successionplan:developmentareas:add:noscript'] = 'Save to add another area for development input';
$string['form:successionplan:developmentplan'] = 'Succession Development Plan';
$string['form:successionplan:locked'] = 'Lock Succession Development Plan';
$string['form:successionplan:islocked'] = 'Succession Development Plan has been locked and cannot be edited unless unlocked.';
$string['form:successionplan:unlock'] = 'Unlock Succession Development Plan';
$string['form:successionplan:confirm:unlock:title'] = 'Unlock Succession Development Plan';
$string['form:successionplan:confirm:unlock:question'] = 'Are you sure you wish to unlock this Succession Development Plan?';
$string['form:successionplan:confirm:unlock:yes'] = 'Yes, unlock it';
$string['form:successionplan:confirm:unlock:no'] = 'No, leave it locked';

// Leadership plan.
$string['form:leaderplan:cardinfo:702010link'] = '70 | 20 | 10';
$string['form:leaderplan:cardinfo:none'] = 'You have not added any 70 | 20 | 10 information yet.';
$string['form:leaderplan:title'] = 'Leadership Development Plan (Beta)';
$string['form:leaderplan:intro'] = 'This section informs the leadership plan. This will be shared with region board, management board as appropriate. ';
$string['form:leaderplan:ldppotential'] = 'Potential Future Roles (Choose and/or add alternative)';
$string['form:leaderplan:ldppotential:answer:1'] = 'Greater scope and influence in current role';
$string['form:leaderplan:ldppotential:answer:2'] = 'Business Leader';
$string['form:leaderplan:ldppotential:answer:3'] = 'Group Leader';
$string['form:leaderplan:ldppotential:answer:4'] = 'Practice Leader (Americas Region ONLY)';
$string['form:leaderplan:ldppotential:answer:5'] = 'Project Director';
$string['form:leaderplan:ldppotential:answer:6'] = 'Technical Leader';
$string['form:leaderplan:ldppotential:answer:7'] = 'Business Services Leader';
$string['form:leaderplan:ldpstrengths'] = 'Strengths';
$string['form:leaderplan:ldpstrengths:add'] = 'Add another strength';
$string['form:leaderplan:ldpstrengths:add:noscript'] = 'Save to add another strength input';
$string['form:leaderplan:ldpdevelopmentareas'] = 'Areas for development';
$string['form:leaderplan:ldpdevelopmentareas:add'] = 'Add another area for development';
$string['form:leaderplan:ldpdevelopmentareas:add:noscript'] = 'Save to add another area for development input';
$string['form:leaderplan:ldpdevelopmentplan'] = 'Leadership Development Plan<br>(If an agreed development objective is already captured in your appraisal, please feel free to reference that here)';
$string['form:leaderplan:ldplocked'] = 'Tick here to indicate that the Leadership Development Plan has been reviewed and is complete. This will lock the fields and prevent further changes being made. To enable editing, untick the box at any time.';
$string['form:leaderplan:ldplocked:tooltip'] = 'To be completed by the supervisor (appraiser)';
$string['form:leaderplan:islocked'] = 'Leadership Development Plan has been marked as complete and cannot be edited unless unlocked.';
$string['form:leaderplan:confirm:unlock:title'] = 'Unlock Leadership Development Plan';
$string['form:leaderplan:confirm:unlock:question'] = 'Are you sure you wish to unlock this Leadership Development Plan?';
$string['form:leaderplan:confirm:unlock:yes'] = 'Yes, unlock it';
$string['form:leaderplan:confirm:unlock:no'] = 'No, leave it locked';

// Events.
$string['eventappraisaladminviewed'] = 'Appraisal admin viewed';
$string['eventappraisaldashboardviewed'] = 'Appraisal dashboard viewed';
$string['eventappraisalprinted'] = 'Appraisal printed';
$string['eventappraisalviewed'] = 'Appraisal viewed';
$string['eventfeedbackcompleted'] = 'Appraisal feedback completed';
$string['eventfeedbackcopysent'] = 'Appraisal feedback copy sent';
$string['eventfeedbacklistviewed'] = 'Appraisal feedback list viewed';

// Statuses.
$string['status:1'] = 'Not started';
$string['status:2'] = 'Appraisee Draft';
$string['status:3'] = 'Appraiser Review';
$string['status:4'] = 'Appraisee Final Comments';
$string['status:5'] = 'Appraiser Final Review';
$string['status:6'] = 'Sign Off';
$string['status:7'] = 'Appraisal Complete';
$string['status:8'] = 'Appraisal Complete'; // For legacy where there was a six month status.
$string['status:9'] = 'Appraisal Complete';
$string['status:7:leadersignoff'] = 'Leader Sign Off';

// Overview page.
$string['overview:alert:archived'] = '<strong>Archived Appraisal</strong>';
$string['overview:alert:legacy'] = '<strong>Legacy Appraisal</strong>';
$string['overview:lastsaved'] = 'Last saved: {$a}';
$string['overview:lastsaved:never'] = 'Never';

// Overview page buttons.
$string['overview:button:appraisee:2:extra'] = 'Start completing your appraisal';
$string['overview:button:appraisee:2:submit'] = 'Share with {$a->plainappraisername}';

$string['overview:button:appraisee:4:return'] = 'Return to {$a->plainappraisername} to make changes';
$string['overview:button:appraisee:4:submit'] = 'Submit complete appraisal to {$a->plainappraisername}';

$string['overview:button:appraiser:3:return'] = 'Request further info from {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Send to {$a->plainappraiseename} for final comments';

$string['overview:button:appraiser:5:return'] = 'Further editing required before sign off';
$string['overview:button:appraiser:5:submit'] = 'Send to {$a->plainsignoffname} for sign off';

$string['overview:button:appraiser:6:extra'] = 'Complete sign off for this appraisal';
$string['overview:button:signoff:6:submit'] = 'Sign Off';

$string['overview:button:groupleader:7:submit'] = 'Sign Off';

$string['overview:button:returnit'] = 'Return';
$string['overview:button:submitit'] = 'Send';

// Overview page APPRAISEE Content.
$string['overview:content:appraisee:1'] = ''; // Never seen...
$string['overview:content:appraisee:2'] = 'Please begin completing your appraisal.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Insert the intended face to face meeting date</li>
    <li>Request feedback</li>
    <li>Reflect and comment on Last Year\'s Performance and Development</li>
    <li>Fill in the Career Direction, Impact and Development Plan sections for discussion during your face to face meeting.</li>
<li>Share your draft with {$a->styledappraisername}, your appraiser.</li>
</ul>
Please share your draft with your appraiser at least a <strong><u>week</u></strong> before the face to face meeting. You will be able to continue to edit further once shared.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Note:</strong> Your appraiser will not be able to see your draft until you share it with them.</div>';

$string['overview:content:appraisee:2:3'] = 'Your appraiser has requested changes to your draft appraisal.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Make changes as requested by your appraiser (please see activity log for further information on what has been requested).</li>
    <li>Share your draft with {$a->styledappraisername}.</li>
</ul>';

$string['overview:content:appraisee:3'] = 'You have now submitted your draft appraisal to {$a->styledappraisername} for review.<br /><br />
<strong>Next Steps:</strong>
<ul class="m-b-20">
    <li>Have your face to face meeting - before the meeting you may wish to:</li>
    <ul class="m-b-0">
        <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Download the Appraisal</a></li>
        <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Download the Quick Reference Guide</a></li>
    </ul>
    <li>Following your meeting, the appraiser will return the appraisal to you. You will either be asked to make changes agreed during the face to face meeting or write your final comments</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You can continue to edit the appraisal whilst it is with your appraiser but suggest you use the activity log to highlight any changes you make.</div>';

$string['overview:content:appraisee:3:4'] = 'You have returned your appraisal to {$a->styledappraisername} to make changes.<br /><br /> You will receive a notification when they have updated the appraisal, ready for you to review again.<br /><br /> <div class="alert alert-danger" role="alert"><strong>Note:</strong> You can continue to edit the appraisal whilst it is with the appraiser but suggest you use the activity log to highlight any changes you make.</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername} has now added their comments and the appraisal is back with you.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Please review your appraiser\'s comments and summary. If necessary return the appraisal to your appraiser if you require any changes.</li>
    <li>Write your comments in the Summaries section</li> <li>Send to your appraiser for final review before sign off. Once submitted you will no longer be able to edit the appraisal.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You can continue to edit your sections of the appraisal but suggest you use the activity log to highlight any changes to your appraiser</div>';

$string['overview:content:appraisee:5'] = 'You have now submitted your completed appraisal to {$a->styledappraisername} for final review.<br /><br /> <strong>Next steps:</strong> <ul class="m-b-20"> <li>Your appraiser will now send the appraisal to {$a->styledsignoffname} for sign off.</li> </ul> <div class="alert alert-danger" role="alert"><strong>Note:</strong> You can no longer make changes to the appraisal unless the appraiser returns it back to you for further edits.</div>';

$string['overview:content:appraisee:6'] = 'Your appraisal has been sent to {$a->styledsignoffname} to review and write their summary.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Note:</strong> The appraisal is now locked and no further edits can be made.</div>';

$string['overview:content:appraisee:7'] = 'Your appraisal is now complete. You can download a PDF copy at any time by clicking on "Download appraisal".';
$string['overview:content:appraisee:7:groupleadersummary'] = 'Your appraisal is now complete and awaits leader review and summary. You will be notified once this has happened.';
$string['overview:content:appraisee:8'] = $string['overview:content:appraisee:7']; // For legacy where there was a six month status.
$string['overview:content:appraisee:9'] = $string['overview:content:appraisee:7']; // When Groupleader added summary.

// Overview page APPRAISER Content.
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = 'The appraisal is currently being drafted by {$a->styledappraiseename}. You will be notified when it is ready for review.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You will not be able to view the appraisal until it has been shared with you.</div>';

$string['overview:content:appraiser:2:3'] = 'You have returned the appraisal to {$a->styledappraiseename} to make changes. You will receive a notification when they have updated their draft appraisal, ready for you to review again.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You are still able to make changes to your sections.</div>';

$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} has submitted a draft in preparation for your face to face meeting.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Please review the appraisal in preparation for your meeting. If necessary return the appraisal to the appraisee if you require any additional information.</li>
    <li>Before the meeting you should</li>
    <ul class="m-b-0">
        <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Download the appraisal</a></li>
        <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Download any feedback received</a></li>
        <li>You may also wish to <a href="https://moodle.arup.com/appraisal/reference" target="_blank">download the quick reference guide</a></li>
    </ul>
    <li>Following the face to face meeting please</li>
    <ul class="m-b-0">
        <li>Mark that the face to face meeting has taken place in the Appraisee Info section</li>
        <li>Add your comments to each section</li>
        <li>Write your summary and agreed actions in the Summaries section</li>
        (If necessary you may return the appraisal to the appraisee to modify before you add your comments.)
    </ul>
    <li>Send to the appraisee to review your comments, view feedback and for them to add their final comments</li>
</ul>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} has requested changes to their appraisal.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Make changes as requested by the appraisee (please see activity log for further information on what has been requested).</li>
    <li>Share the appraisal with {$a->styledappraiseename} for final comments.</li>
</ul>';


$string['overview:content:appraiser:4'] = 'You have added your comments and summaries and the appraisal is back with {$a->styledappraiseename} to add their final comments. You will be notified when it is ready for your final review.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You can continue to edit your sections of the appraisal but suggest you use the activity log to highlight any changes to your appraisee.</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} has now added their final comments. <br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Please review the completed appraisal ready for sign off.</li>
    <li>Send to {$a->styledsignoffname} to review and add their summary.</li>
    <li>You and the appraisee will be notified once the appraisal is complete.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You can no longer make changes to the appraisal unless you return it to the appraisee.</div>';

$string['overview:content:appraiser:6'] = 'You have now submitted this appraisal to {$a->styledsignoffname} for completion.<br /><br />
    <div class="alert alert-danger" role="alert"><strong>Note:</strong> The appraisal is now locked and no further edits can be made.</div>';

$string['overview:content:appraiser:7'] = 'This appraisal is complete and signed off.';
$string['overview:content:appraiser:7:groupleadersummary'] = 'This appraisal is now complete and awaits leader review and summary. You will be notified once this has happened.';

$string['overview:content:appraiser:8'] = $string['overview:content:appraiser:7']; // For legacy where there was a six month status.
$string['overview:content:appraiser:9'] = $string['overview:content:appraiser:7']; // When Groupleader added summary.

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = 'Appraisal in progress.';
$string['overview:content:groupleader:3'] = 'Appraisal in progress.';
$string['overview:content:groupleader:4'] = 'Appraisal in progress.';
$string['overview:content:groupleader:5'] = 'Appraisal in progress.';
$string['overview:content:groupleader:6'] = 'Appraisal in progress.';
$string['overview:content:groupleader:7'] = 'This appraisal is complete and signed off.';
$string['overview:content:groupleader:7:groupleadersummary'] = 'This appraisal is complete and now awaits your review and summary.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Please add your Leader summary to the Summaries section.</li>
    <li>Click the Sign Off button.</li>
    <li>The appraisee, appraiser and sign off will be notified once your comments have been added.</li>
</ul>';
$string['overview:content:groupleader:7:groupleadersummary:generic'] = 'This appraisal is now complete and awaits leader review and summary.';
$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.
$string['overview:content:groupleader:9'] = $string['overview:content:groupleader:7'];

// Overview page HR LEADER Content.
$string['overview:content:hrleader:1'] = $string['overview:content:groupleader:1'];
$string['overview:content:hrleader:2'] = $string['overview:content:groupleader:2'];
$string['overview:content:hrleader:3'] = $string['overview:content:groupleader:3'];
$string['overview:content:hrleader:4'] = $string['overview:content:groupleader:4'];
$string['overview:content:hrleader:5'] = $string['overview:content:groupleader:5'];
$string['overview:content:hrleader:6'] = $string['overview:content:groupleader:6'];
$string['overview:content:hrleader:7'] = $string['overview:content:groupleader:7'];
$string['overview:content:hrleader:7:groupleadersummary'] = $string['overview:content:groupleader:7:groupleadersummary'];
$string['overview:content:hrleader:8'] = $string['overview:content:groupleader:8']; // For legacy where there was a six month status.
$string['overview:content:hrleader:9'] = $string['overview:content:groupleader:9'];

// Overview page SIGN OFF Content.
$string['overview:content:signoff:1'] = ''; // Never seen...
$string['overview:content:signoff:2'] = 'Appraisal in progress.<br /><br /><div class="alert alert-danger" role="alert"><strong>Note:</strong> You will be notified when the appraisal is ready for review and sign off.</div>';
$string['overview:content:signoff:3'] = 'Appraisal in progress.<br /><br /><div class="alert alert-danger" role="alert"><strong>Note:</strong> You will be notified when the appraisal is ready for review and sign off.</div>';
$string['overview:content:signoff:4'] = 'Appraisal in progress.<br /><br /><div class="alert alert-danger" role="alert"><strong>Note:</strong> You will be notified when the appraisal is ready for review and sign off.</div>';
$string['overview:content:signoff:5'] = 'Appraisal in progress.<br /><br /><div class="alert alert-danger" role="alert"><strong>Note:</strong> You will be notified when the appraisal is ready for review and sign off.</div>';
$string['overview:content:signoff:6'] = 'The appraisal for {$a->styledappraiseename} has been sent to you for your review.<br /><br />
<strong>Next steps:</strong>
<ul class="m-b-20">
    <li>Please review the appraisal</li>
    <li>Write your summary in the Summaries section</li>
    <li>Click the Sign Off button to complete the appraisal</li>
</ul>';

$string['overview:content:signoff:7'] = 'This appraisal is complete and signed off.';
$string['overview:content:signoff:7:groupleadersummary'] = 'This appraisal is now complete and awaits leader review and summary. You will be notified once this has happened.';

$string['overview:content:signoff:8'] = $string['overview:content:signoff:7']; // For legacy where there was a six month status.
$string['overview:content:signoff:9'] = $string['overview:content:signoff:7']; // When groupleader added summary.

// General archived/legacy messages.
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">This appraisal has been archived.<br />It is now only possible to <a class="oa-print-confirm" href="{$a->printappraisalurl}">download the appraisal</a>.</div>';
$string['overview:content:special:legacy'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to add comments or <a class="oa-print-confirm" href="{$a->printappraisalurl}">download the appraisal</a>.</div>';

// Special case for appraisee archived.
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">This appraisal has been archived.<br />It is now only possible to <a class="oa-print-confirm" href="{$a->printappraisalurl}">download your appraisal</a>.</div>';

// Special cases for appraisee/appraiser legacy.
$string['overview:content:special:legacy:appraisee'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to update your six month review, add comments or <a class="oa-print-confirm" href="{$a->printappraisalurl}">download your appraisal</a>.</div>';
$string['overview:content:special:legacy:appraiser'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to update the six month review, add comments or <a hclass="oa-print-confirm" ref="{$a->printappraisalurl}">download the appraisal</a>.</div>';

// Special cases as only appraisee can print at permissions status 2.
$string['overview:content:special:archived:appraiser:2'] = '<div class="alert alert-danger" role="alert">This appraisal has been archived.<br />You do not have access to any further actions.</div>';
$string['overview:content:special:archived:groupleader:2'] = '<div class="alert alert-danger" role="alert">This appraisal has been archived.<br />You do not have access to any further actions.</div>';
$string['overview:content:special:archived:signoff:2'] = '<div class="alert alert-danger" role="alert">This appraisal has been archived.<br />You do not have access to any further actions.</div>';

$string['overview:content:special:legacy:appraiser:2'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to add comments or update the six month review.</div>';
$string['overview:content:special:legacy:agroupleader:2'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to add comments.</div>';
$string['overview:content:special:legacy:signoff:2'] = '<div class="alert alert-danger" role="alert">This appraisal is from the old system.<br />It is only possible to add comments.</div>';

$string['overview:label:comment:return'] = 'Please add a comment before you return the appraisal';
$string['overview:label:comment:submit'] = 'You may add a comment before you submit the appraisal';

// Navbar Menu
$string['navbar:contribdashboard'] = 'Contributor Dashboard';
$string['navbar:currentappraisal'] = 'Current Appraisal';
$string['navbar:returntomoodle'] = 'Moodle';
$string['navbar:newappraisal'] = 'new';
$string['navbar:appraiserdashboard'] = 'Appraiser Dashboard';
$string['navbar:appraiseedashboard'] = 'My Appraisals';
$string['navbar:signoffdashboard'] = 'Sign Off Dashboard';
$string['navbar:businessadmindashboard'] = 'Appraisal Admin';
$string['navbar:costcentreadmindashboard'] = 'Cost Centre Setup';
$string['navbar:groupleaderdashboard'] = 'Leader Dashboard';
$string['navbar:hrleaderdashboard'] = 'HR Dashboard';
$string['navbar:itadmindashboard'] = 'IT Admin';

// Feedback requests page.
$string['feedbackrequests:heading'] = 'Contributor Dashboard';
$string['feedbackrequests:moodle'] = 'Return to Moodle';
$string['feedbackrequests:description'] = 'This dashboard shows any outstanding feedback requests you have and allows you to access any feedback you\'ve contributed in the past.';
$string['feedbackrequests:outstanding'] = 'Outstanding Requests';
$string['feedbackrequests:norequests'] = 'No outstanding feedback requests';
$string['feedbackrequests:completed'] = 'Completed Requests';
$string['feedbackrequests:nocompleted'] = 'No completed feedback requests for the selected cycle';
$string['feedbackrequests:filter:label'] = 'Appraisal Cycle';
$string['feedbackrequests:th:requestby'] = 'Requested by';
$string['feedbackrequests:th:requestfor'] = 'Requested for';
$string['feedbackrequests:th:requestdate'] = 'Requested date';
$string['feedbackrequests:th:facetofacedate'] = 'Face to face date';
$string['feedbackrequests:th:facetofaceheld'] = 'Face to face held';
$string['feedbackrequests:th:completeddate'] = 'Completed date';
$string['feedbackrequests:th:actions'] = 'Actions';
$string['feedbackrequests:emailcopy'] = 'Email me a copy';
$string['feedbackrequests:submitfeedback'] = 'Submit feedback';
$string['feedbackrequests:continuefeedback'] = 'Continue feedback';
$string['email:subject:myfeedback'] = 'Your appraisal feedback for {{appraisee}}';
$string['email:body:myfeedback'] = '<p>Dear {{recipient}},</p>
<p>You submitted the following {{confidential}} feedback for {{appraisee}}:</p> <div>{{feedback}}</div> <div>{{feedback_2}}</div>';
$string['feedbackrequests:confidential'] = 'confidential';
$string['feedbackrequests:nonconfidential'] = 'non confidential';
$string['feedbackrequests:received:confidential'] = 'Received (confidential)';
$string['feedbackrequests:received:draft'] = 'In draft';
$string['feedbackrequests:received:nonconfidential'] = 'Received';
$string['feedbackrequests:paneltitle:confidential'] = 'Feedback (confidential)';
$string['feedbackrequests:paneltitle:nonconfidential'] = 'Feedback';
$string['feedbackrequests:paneltitle:requestmail'] = 'Feedback request email';
$string['feedbackrequests:legend'] = '* denotes contributor added by appraiser';
$string['add_feedback_title'] = 'Add your feedback';

// Checkins
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'Throughout the year, it is expected that the appraisee and appraiser will want to discuss progress against the Agreed Impact Plan, Development Plan, actions and performance. The appraisee and/or appraiser can use the section below to record progress. The frequency of these conversations is up to you but at least once a year is recommended.';
$string['success:checkin:add'] = 'Successfully added check-in';
$string['error:checkin:add'] = 'Failed to add check-in';
$string['error:checkin:validation'] = 'Please provide some text.';
$string['checkin:addnewdots'] = 'Check-in...';
$string['checkin:deleted'] = 'Deleted check-in';
$string['checkin:delete:failed'] = 'Failed to delete check-in';
$string['checkin:update'] = 'Update';

// Introduction Page
$string['appraisee_heading'] = 'Welcome to Online Appraisal';
$string['appraisee_welcome'] = 'Your appraisal is an opportunity for you and your appraiser to have a valuable conversation about your performance, career development and future contribution to the business. We want this to be a constructive conversation, one that is personal and useful for everyone.<br /><br /> The purpose of this online tool is to help you record the conversation and refer to it throughout the year.<br /><br />Further information about the appraisal process can be found <a href="https://moodle.arup.com/appraisal/essentials" target="_blank">here</a>';

$string['appraisee_welcome_info'] = 'Your appraisal deadline for this year is {$a}.';

$string['introduction:video'] = '<img src="https://moodle.arup.com/scorm/_assets/ArupAppraisal.png"  alt="Arup Appraisal logo"/>';

$string['introduction:targetedmessage'] = '<div class="alert alert-info">For those in leadership roles, you may wish to refer to the <a href="https://moodle.arup.com/appraisal/leadershipattributes" target="_blank">Arup Leadership Attributes</a> - the 16 qualities which define us as leaders.  For further information please refer to the accompanying <a href="https://moodle.arup.com/appraisal/leadershipattributesguide" target="_blank">Introduction to Arup Leadership Attributes</a>.</div>';

// PDF.
$string['pdf:appraisername'] = 'Appraiser Name';

$string['pdf:checkins:none'] = 'No check-ins to display';
$string['pdf:activitylogs:none'] = 'No activity logs to display';
$string['pdf:completed'] = 'Appraisal completed:';

$string['pdf:duedate'] = 'Appraisal Due Date';

$string['pdf:feedback:appraiserflag'] = '*';
$string['pdf:feedback:appraiserrequest'] = '* Denotes feedback requested by Appraiser';
$string['pdf:feedback:confidentialflag'] = '#';
$string['pdf:feedback:confidentialhelp'] = '# Denotes Confidential Feedback which is not visible to the appraisee.';
$string['pdf:feedback:confidentialhelp:appraisee'] = '# Denotes Confidential Feedback which is not visible to you.';
$string['pdf:feedback:nofeedback'] = 'No feedback to display';
$string['pdf:feedback:notyetavailable'] = 'Not yet visible.';
$string['pdf:feedback:title'] = 'Feedback';
$string['pdf:feedback:requested'] = 'Feedback requested from:';
$string['pdf:feedback:requestedhelp'] = '* Denotes feedback requested by your Appraiser which is not yet visible to you.';
$string['pdf:feedback:requested:none'] = 'No feedback has been requested.';
$string['pdf:feedback:requestedfrom'] = 'Reviewer {$a->firstname} {$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:form:summaries:appraisee'] = 'Appraisee comments';
$string['pdf:form:summaries:appraiser'] = 'Appraiser summary of overall performance';
$string['pdf:form:summaries:signoff'] = 'Sign Off summary';
$string['pdf:form:summaries:grpleader'] = 'Leader summary';
$string['pdf:form:summaries:recommendations'] = 'Agreed actions';

$string['pdf:group'] = 'Group';

$string['pdf:header:appraisal'] = '- Appraisal';
$string['pdf:header:helddate'] = 'Appraisal Date:';
$string['pdf:header:confidential'] = 'STRICTLY CONFIDENTIAL';
$string['pdf:header:staffid'] = 'Staff ID:';
$string['pdf:header:warning'] = 'Downloaded by: {$a->who} on {$a->when}<br>Please do not file or leave somewhere unsafe.';
$string['pdf:heading:learninghistory'] = 'Last 3yrs Learning History';
$string['pdf:heading:summaries'] = 'Overall summaries';
$string['pdf:heading:summary'] = 'Appraisal Summary';
$string['pdf:helddate'] = 'Appraisal Date';

$string['pdf:leaderplan:appraiser'] = 'Current performance summary (as per appraisal)';
$string['pdf:leaderplan:comments'] = 'Career aspiration and next steps (as per appraisal)<br>Appraiser comments';
$string['pdf:leaderplan:ldplocked'] = 'Has this plan been marked as complete?';
$string['pdf:leaderplan:progress'] = 'Career aspiration and next steps (as per appraisal)<br>Appraisee comments';
$string['pdf:leaderplan:seventy'] = 'Learning that takes place in the course of your work - about 70% (as per appraisal)';
$string['pdf:leaderplan:ten'] = 'Learning from formal courses - face to face or online - about 10% (as per appraisal)';
$string['pdf:leaderplan:twenty'] = 'Learning from other people - about 20% (as per appraisal)';
$string['pdf:learninghistory:classroom'] = 'Classroom';
$string['pdf:learninghistory:elearning'] = 'Classroom';
$string['pdf:learninghistory:none'] = 'No learning history available.';
$string['pdf:learninghistory:th:course'] = 'Module';
$string['pdf:learninghistory:th:category'] = 'Category';
$string['pdf:learninghistory:th:date:completion'] = 'Completion date';
$string['pdf:learninghistory:th:date:expiry'] = 'Expiry date';
$string['pdf:learninghistory:th:duration'] = 'Duration';
$string['pdf:learninghistory:th:type'] = 'Type';
$string['pdf:learninghistory:type:classroom'] = 'Classroom';
$string['pdf:learninghistory:type:elearning'] = 'E-learning';
$string['pdf:location'] = 'Location';

$string['pdf:notcomplete'] = 'Not Complete';
$string['pdf:notset'] = 'Not set';

$string['pdf:successionplan:appraiser'] = 'Current performance summary (as per appraisal)';
$string['pdf:successionplan:comments'] = 'Career aspiration and next steps (as per appraisal)<br>Appraiser comments';
$string['pdf:successionplan:locked'] = 'Has this plan been locked?';
$string['pdf:successionplan:progress'] = 'Career aspiration and next steps (as per appraisal)<br>Appraisee comments';

// Legacy PDF (extra strings).
$string['z:legacy:pdf:completed:sixmonthreview'] = 'Six month review completed:';

$string['z:legacy:pdf:heading:objective:dev:ly'] = 'Last Year\'s Development Objectives';
$string['z:legacy:pdf:heading:objective:dev:ny'] = 'This Year\'s Development Objectives';
$string['z:legacy:pdf:heading:objective:per:ly'] = 'Last Year\'s Performance Objectives';
$string['z:legacy:pdf:heading:objective:per:ny'] = 'This Year\'s Performance Objectives';

$string['z:legacy:pdf:objective:dev:action_required'] = 'Action required';
$string['z:legacy:pdf:objective:dev:appraiser_comments'] = 'Appraiser comments';
$string['z:legacy:pdf:objective:dev:competency'] = 'Competency / skill';
$string['z:legacy:pdf:objective:dev:desc'] = 'Progress required in skill / knowledge / behaviour';
$string['z:legacy:pdf:objective:dev:due_date'] = 'Due date';
$string['z:legacy:pdf:objective:dev:further_development'] = 'Further development required';
$string['z:legacy:pdf:objective:dev:none:ly'] = 'No development objectives for last year';
$string['z:legacy:pdf:objective:dev:none:ny'] = 'No development objectives for this year';
$string['z:legacy:pdf:objective:dev:progress'] = 'Progress made';
$string['z:legacy:pdf:objective:dev:status'] = 'Status';
$string['z:legacy:pdf:objective:dev:title'] = 'Development objective title';
$string['z:legacy:pdf:objective:new'] = 'New objective (created):';
$string['z:legacy:pdf:objective:per:appraisee_comments'] = 'Appraisee comments';
$string['z:legacy:pdf:objective:per:appraiser_comments'] = 'Appraiser comments';
$string['z:legacy:pdf:objective:per:desc'] = 'Description';
$string['z:legacy:pdf:objective:per:due_date'] = 'Due date';
$string['z:legacy:pdf:objective:per:none:ly'] = 'No performance objectives for last year';
$string['z:legacy:pdf:objective:per:none:ny'] = 'No performance objectives for this year';
$string['z:legacy:pdf:objective:per:status'] = 'Status';
$string['z:legacy:pdf:objective:per:title'] = 'Performance objective title';
$string['z:legacy:pdf:objective:previous'] = 'Previous objective (created):';

$string['z:legacy:pdf:sixmonthreview'] = 'Six Month Review';
$string['z:legacy:pdf:summary:contribution_summary'] = 'Feedback Contributors:';
$string['z:legacy:pdf:summary:appraiser_summary'] = 'Appraiser\'s summary';
$string['z:legacy:pdf:summary:appraisee_summary'] = 'Appraisee\'s summary';
$string['z:legacy:pdf:summary:teamperformance_comments'] = 'Impact Summary:';
$string['z:legacy:pdf:summary:groupleader_comments'] = 'Group Leader\'s Comments:';
$string['z:legacy:pdf:summary:six_month_review'] = 'Six Month Review Comments:';

// Itadmin lines.
$string['itadmin:appraisaladministrator'] = 'Appraisal Administrator';
$string['itamdin:appraisalsummary'] = 'Appraisal Summary';
$string['itadmin:changeroles'] = 'If you need to change the Appraiser, Leader Sign Off or Sign Off then you will need to contact one of the appraisal administrators, listed below, for this cost centre ({$a})';
$string['itadmin:appraisalsummary'] = 'Appraisal Summary';
$string['itadmin:appraisalstatus'] = 'Appraisal Status';
$string['itadmin:statuschangetext'] = 'You can change the status of the appraisal by using the drop down below. NB: before the change occurs you will be asked to enter a reason (i.e. Service Desk Ticket) that will be displayed to all who have access to the appraisal to ensure transparency and that there is a full audit trail.';
$string['itadmin:changestatus'] = 'Change Status';
$string['itadmin:change'] = 'Change';
$string['itadmin:selectstatus'] = 'Select Status';
$string['itadmin:updatesuccess'] = 'Update status successful';
$string['itadmin:feedback'] = 'Feedback';
$string['itadmin:remove'] = 'Remove';
$string['itadmin:reason'] = 'Please provide a reason for removing feedback from {$a}';
$string['itadmin:reasonstatus'] = 'Please provide a reason for changing the status';
$string['itadmin:faqs'] = 'FAQs';
$string['itadmin:deletefeedback'] = 'Delete Feedback';
$string['itadmin:feedbackdeleted'] = 'Feedback Deleted';
$string['itadmin:faqcontent'] = '<h4>How do I change the status of an appraisal?</h4>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ullamcorper, metus laoreet efficitur maximus, elit lorem egestas justo, sed lacinia nisl urna a nibh. Vestibulum vel nunc massa. Cras sit amet turpis accumsan, luctus felis sed, elementum diam. Sed eu metus tempor turpis auctor scelerisque at vel lectus. Pellentesque odio turpis, venenatis id nulla in, eleifend maximus sem. Aliquam vitae vestibulum felis. Integer ultrices neque vitae odio aliquam, nec tristique enim vulputate. Pellentesque porta sagittis diam vitae facilisis. Duis eleifend iaculis neque, non luctus turpis cursus sed. In eget nibh quis enim finibus pharetra vel fringilla arcu. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.';
$string['itadmin:feedbackstatus:draft'] = "DRAFT";

// Fake blocks.
$string['navigation'] = 'Navigation';
$string['quicklinks'] = 'Quick links';