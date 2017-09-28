<?php
// Main
$string['threesixty'] = '360 Degree Diagnostics Tool';
$string['modulename'] = '360 Degree Diagnostics Tool';
$string['modulenameplural'] = '360 Degree Diagnostics Tools';
$string['pluginname'] = 'Threesixty Evaluation';
$string['pluginadministration'] = 'Threesixty Administration';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

// Capabilities
$string['threesixty:addinstance'] = 'Can add an instance';
$string['threesixty:deleterespondents'] = 'Delete external respondents from a user\'s list';
$string['threesixty:declinerequest'] = 'Decline request that has been sent to you (only for internal users)';
$string['threesixty:edit'] = 'Change someone\'s personal 360 Degree assessment';
$string['threesixty:feedbackview'] = 'View 360 feedback';
$string['threesixty:manage'] = 'Manage 360 Degree Tool';
$string['threesixty:participate'] = 'Assess yourself in the 360 Degree Tool';
$string['threesixty:viewrespondents'] = 'View the list of respondents';
$string['threesixty:remindrespondents'] = 'Send a reminder email to external respondents';
$string['threesixty:view'] = 'View 360 Degree Tool information';
$string['threesixty:viewownreports'] = 'View your own 360 Degree Tool reports';
$string['threesixty:viewreports'] = 'View 360 Degree Tool reports for all users';

// General
$string['actions'] = 'Actions';
$string['addnewcompetency'] = 'Add a new {$a}';
$string['addnewskills'] = 'Add {$a->number} new {$a->skills}';
$string['adminnotified'] = '<p>Thank you for letting us know. The administrator has been notified.</p>';
$string['allowexternalrespondents'] = 'Allow external respondents';
$string['allresults'] = 'All results';
$string['alternative'] = 'Alternative word for \'{$a}\'';
$string['alternativepluralhelp'] = 'Alternative plural word';
$string['alternativepluralhelp_help'] = 'If an alternative word is supplied and no plural is, then an \'s\' will simply be added to the alternative word where a plural is required.';
$string['analyses'] = 'Analyses';
$string['applybutton'] = 'Apply';
$string['areyousuredelete'] = 'Are you sure you want to delete this {$a}?';
$string['assess'] = 'Assess';
$string['average'] = 'Average';

$string['cannotparticipate'] = 'You do not have permission to paricipate in this activity.';
$string['change'] = 'Change';
$string['closebutton'] = 'Close';
$string['groupid'] = 'Filter by group';
$string['comparisonwith'] = 'Comparison with {$a}';
$string['competency'] = 'Competency';
$string['competencycolour'] = 'Highlight colour';
$string['competencycolour_help'] = 'Colour used as highlight in reports.';
$string['competencylabels'] = 'Show {$a} labels';
$string['competencies'] = 'Competencies';
$string['completiondate'] = 'Completion date';

$string['decline'] = 'Decline';
$string['delete'] = 'Delete';
$string['deletecompetency'] = 'Delete {$a}';
$string['deleteskill'] = 'Delete this {$a}';
$string['divisor'] = 'Divisor';
$string['done'] = 'Done';

$string['edit'] = 'Edit';
$string['edit:competencies'] = 'Add/edit {$a}';
$string['edit:respondents'] = 'View/delete respondents';
$string['email:remindersubject'] = 'Reminder to complete a 360 diagnostic activity';
$string['email:reminderbody'] = '<p>Dear {$a->respondentfullname},</p>
<p>This is just a quick reminder to please complete my 360-degree diagnostics and gap analysis.</p>
<p>To do so, please click on this link:<br />
<a href="{$a->url}">My 360 degree activity</a></p>
<p>Thank you,<br />{$a->userfullname}</p>';
$string['email:requestsubject'] = 'Invitation to complete a 360 diagnostic activity';
$string['email:requestbody'] = '<p>Dear {$a->respondentfullname},</p>
<p>I would appreciate if you would please complete my 360 degree diagnostics and gap analysis.</p>
<p>To do so, please click on this link:<br />
<a href="{$a->url}">My 360 degree activity</a></p>
<p>Thank you,<br />{$a->userfullname}</p>';
$string['error:allskillneedascore'] = 'every skill needs a score';
$string['error:cannotaddcompetency'] = 'Could not add new competency';
$string['error:cannotaddskill'] = 'Could not add new skill';
$string['error:cannotdeletecompetency'] = 'Could not delete competency';
$string['error:cannotdeleterespondent'] = 'Could not delete respondent';
$string['error:cannotdeleteskill'] = 'Could not delete skill';
$string['error:cannotinviterespondent:email'] = 'There was an error while trying to email this respondent, please try sending a reminder.';
$string['error:cannotinviterespondent:insert'] = 'There was an error while trying to insert this respondent, please try and add them again.';
$string['error:cannotsavechanges'] = 'Could not save changes: {$a}';
$string['error:cannotsavescores'] = 'Could not save scores: {$a}';
$string['error:cannotsendreminder'] = 'Could not send the reminder email';
$string['error:cannotupdatecompetency'] = 'Could not update existing competency';
$string['error:cannotupdateskill'] = 'Could not update existing skill';
$string['error:databaseerror'] = 'database error. Contact the site administrator.';
$string['error:formsubmissionerror'] = 'form submission error. Contact the site administrator.';
$string['error:invalidcode'] = 'Invalid response code. Make sure you copied the full link that was emailed to you. If you continue to have problems, please contact the site administrator.';
$string['error:invalidcomptency'] = 'Competency id incorrect';
$string['error:invalidpagenumber'] = 'Invalid page number: no competency to display';
$string['error:invalidreporttype'] = 'Invalid report type: {$a}';
$string['error:nodataforuserx'] = 'No 360 Degree data for {$a}';
$string['error:noscoresyet'] = 'You need to fill in your own scores and submit the form first.';
$string['error:time'] = 'There has been a problem retrieving the time completed. Please contact your administrator.';
$string['error:unknownbuttonclicked'] = 'No action associated with the button that was clicked';
$string['error:userxhasnotsubmitted'] = '{$a} has not completed the form yet';
$string['error:unhashedrespondant'] = 'respondent unhashed; this should not happend; please refer to a programer';
$string['eventadministrationviewed'] = 'Administration viewed';
$string['eventanalysisprinted'] = 'Analysis printed';
$string['eventcompetencycreated'] = 'Competency created';
$string['eventcompetencydeleted'] = 'Competency  deleted';
$string['eventcompetencyupdated'] = 'Competency updated';
$string['eventreportviewed'] = 'Report viewed';
$string['eventrespondentrequestsent'] = 'Respondent request sent';
$string['eventrespondentsviewed'] = 'Respondents viewed';
$string['eventscoringviewed'] = 'Scoring viewed';
$string['eventskillcreated'] = 'Skill created';
$string['eventskilldeleted'] = 'Skill deleted';
$string['eventskillupdated'] = 'Skill updated';
$string['everyone'] = 'Show everyone';
$string['externalrespondentsonly'] = 'Use only external respondent form';
$string['externaluser'] = 'External User';

$string['feedback'] = 'Feedback';
$string['feedbacks'] = 'Feedbacks';
$string['filters'] = 'Filters';
$string['finishbutton'] = 'Finish';
$string['filter:self'] = 'Self';

$string['intro'] = 'Intro';

$string['key'] = 'Key';

$string['legend:heading'] = 'Scale details:';
$string['loadedxofy'] = 'Loaded [[x]] of {$a}';
$string['loading'] = 'LOADING: {$a}';

$string['makeself'] = 'Perform self-evaluation';
$string['missingscores'] = 'Some {$a} have not been scored, please ensure you have provided a score for all {$a}.';

$string['nocompetencies'] = '<p>There are no {$a} defined in this activity.</p>';
$string['noparticipants'] = 'There are currently no participants.';
$string['norequests'] = 'No pending requests';
$string['norespondents'] = 'No respondents have been entered yet.';
$string['noscore'] = '(none)';
$string['noskills'] = '<p><b>There are no {$a->skills} defined for this {$a->competency}.</b></p>';
$string['notset'] = 'not set';
$string['notapplicable'] = '0';
$string['nousers'] = 'No users to display';
$string['nousersfound'] = '<p>None of the users have completed this activity yet. There is no data to display yet.</p>';
$string['numberrespondents:sent'] = 'Invites sent';
$string['numberrespondents:received'] = 'Responses received';

$string['or'] = 'or';

$string['participant'] = 'Participant';
$string['pdf:footer:line1'] = '{$a->fullname} - Page {$a->x} of {$a->y}';
$string['pdf:footer:line2'] = 'Copyright &copy; 2013 SRS - The Development Team Ltd. All Rights Reserved.';
$string['printreport'] = 'Print report';
$string['printreport:all'] = 'Print all reports (ZIP)';

$string['questionorder'] = 'Question order';
$string['questionorder:alternate'] = 'Alternately from {$a}';
$string['questionorder:competency'] = 'Grouped by {$a}';
$string['questionorder:random'] = 'Random';

$string['remindbutton'] = 'Send reminder';
$string['reportforuser'] = 'Report for user: {$a->fullname}';
$string['report:spiderweb'] = 'Spiderweb Diagram';
$string['report:table'] = 'Gap Analysis';
$string['reportsavailable'] = 'Users can access reports after';
$string['requestrespondentexplanation'] = '<p>Please enter the email address of a person you would like to invite to complete your 360 degree diagnostic activity.</p>';
$string['requestrespondentremaining'] = '<p>You need to invite at least {$a->remaining} more respondent{$a->s}.</p>';
$string['requiredifnouser'] = 'Required if existing user not selected.';
$string['requiredrespondents'] = 'Number of respondents required:';
$string['requiredrespondents_help'] = 'The number of peers (colleagues, clients, bosses) that are required to fill this assessment before this activity is considered completed.';
$string['respondentaverage'] = 'Respondent average';
$string['respondentindividual'] = 'This profile is for <b>{$a}</b>';
$string['respondentinstructions'] = '<p>Thank you for taking part in this profiling exercise.</p><p>Your input should be based on your own impressions of the performance of the person named. Please follow the steps and consider each of the statements, selecting the most appropriate descriptor for the person whose profile you are completing. If you feel unable to answer any particular questions, simply select "N/A" as your answer. On conclusion, you will have the chance to review your answers before saving the profile. Please note that your input will remain confidential.</p>';
$string['respondents'] = 'Respondents';
$string['respondentselection'] = 'Choose respondents from';
$string['respondentsremaining'] = 'The required number of respondents have not replied yet.';
$string['respondenttype'] = 'Respondent type';
$string['respondenttypes'] = 'Types of respondent';
$string['respondentuserid'] = 'Choose respondent';
$string['respondentwelcome'] = '<p><b>Welcome <tt>{$a->respondent}</tt></b></p>';

$string['selectanotheruser'] = 'Select another user...';
$string['selectatleastone'] = 'Please select at least one option';
$string['selecteduser'] = 'Selected user: {$a->fullname}';
$string['selectuser'] = '<p>Please select a user:</p>';
$string['self:incomplete'] = 'Not Completed';
$string['self:responsecompleted'] = 'Date Completed';
$string['self:responseoptions'] = 'Options';
$string['self:responsetype'] = 'Profile';
$string['selftype'] = 'Self-analysis type';
$string['selftypes'] = 'Type of self-analysis';
$string['sendemail'] = 'Send email';
$string['setting:respondenttypes'] = 'Respondent types';
$string['setting:respondenttypesdesc'] = 'The list of available respondent types, one per line. <b>Warning: only append entries to the end of this list once users have started using the 360 Degree Tool.</b>';
$string['setting:respondenttypesdefault'] = "Peer\nColleague\nManager\nOther";
$string['setting:selftypes'] = 'Self profile types';
$string['setting:selftypesdefault'] = "Self";
$string['setting:selftypesdesc'] = 'The list of available responses that need to be filled out by the participant.';
$string['showfeedback'] = 'Feedback';
$string['showfeedback_help'] = 'If this option is enabled, a free-text feedback field will be available while users rate each skill in this competency.';
$string['showrespondentaverage'] = 'Show respondent average';
$string['skill'] = 'Skill';
$string['skillaltdescription'] = 'Alternative description';
$string['skillaltdescription_help'] = 'Alternative description will be seen by the respondent, only used if not empty.';
$string['skillaltname'] = 'Alternative name';
$string['skillaltname_help'] = 'Alternative name will be seen by the respondent, only used if not empty.';
$string['skilldescription'] = 'Description';
$string['skillgrade'] = 'Scale used for {$a}';
$string['skillname'] = 'Name';
$string['skills'] = 'Skills';
$string['spiderweb:boxsize:legend'] = 'Legend box/icon size';
$string['spiderweb:colour:axis'] = 'Score label colour';
$string['spiderweb:colour:label'] = 'Competency label colour';
$string['spiderweb:colour:legend'] = 'Legend colour';
$string['spiderweb:colour:title'] = 'Title colour';
$string['spiderweb:font:axis'] = 'Score label font';
$string['spiderweb:font:label'] = 'Competency label font';
$string['spiderweb:font:legend'] = 'Legend font';
$string['spiderweb:font:title'] = 'Title font';
$string['spiderweb:fontsize:axis'] = 'Score label fontsize';
$string['spiderweb:fontsize:label'] = 'Competency label fontsize';
$string['spiderweb:fontsize:legend'] = 'Legend fontsize';
$string['spiderweb:fontsize:title'] = 'Title fontsize';
$string['spiderweb:lineweight:series'] = 'Series lineweight';
$string['spiderweb:pointradius:series'] = 'Series point radius';
$string['spiderweb:show:label'] = 'Show competency labels';
$string['spiderweb'] = 'Spiderweb settings';
$string['spiderbackground'] = 'Background for spiderweb diagram';

$string['tab:activity'] = 'Activity';
$string['tab:edit'] = 'Administration';
$string['tab:reports'] = 'Reports';
$string['tab:requests'] = 'Requests';
$string['tab:respondents'] = 'Respondents';
$string['taskcleanup'] = 'Clean up cached data';
$string['thankyoumessage'] = '<p>Thank you for assessing this user.</p><p>You may now close this window.</p>';

$string['unknown'] = 'Unknown';

$string['validation:emailnotunique'] = 'This email address has already been used.';
$string['validation:respondentalreadyselected'] = 'This user has already been chosen as a respondent.';
$string['view'] = 'View evaluators';
$string['viewchart'] = 'View chart';
$string['viewentries'] = 'View entries';
$string['viewself'] = 'View self-evaluation';

$string['xofy'] = '{$a->page} of {$a->nbpages}';

$string['yourscore'] = 'Your score';

// Topping and tailing of pages
$string['page:profiles:block:title'] = 'Profiles page block title';
$string['page:profiles:block:content'] = '<p>Profiles page block content...</p>';
$string['page:profiles:header:complete'] = '<p style="margin:10px 0;">Thank you for completing the 360 Degree Diagnostics Tool. Please forward these questions '
    . 'to {$a} or more colleagues using the \'Respondents\' tab.</p>';
$string['page:profiles:header:incomplete'] = '<h3>How well do you achieve through influence?</h3>'
    . '<p>The results of this questionnaire will be used on your workshop to enable you to compare your view of how you influence '
    . 'with the views of others with whom you work.</p>'
    . '<p>When you review the results, alongside the feedback you receive from other programme participants, you will be able to '
    . 'build a picture of what really works for you and how you might extend the range and flexibility of your Influencing Spectrum.</p>'
    . '<p>Please follow the link below to complete your self-evaluation.</p>';
$string['page:profiles:footer'] = '';

$string['page:report:block:title'] = 'Report page block title';
$string['page:report:block:content'] = '<p>Report page block content...</p>';
$string['page:report:header'] = '';
$string['page:report:footer'] = '';

$string['page:requests:block:title'] = 'Requests page block title';
$string['page:requests:block:content'] = '<p>Requests page block content...</p>';
$string['page:requests:header'] = '';
$string['page:requests:footer'] = '';

$string['page:respondents:block:title'] = 'Respondents page block title';
$string['page:respondents:block:content'] = '<p>Respondents page block content...</p>';
$string['page:respondents:header'] = '';
$string['page:respondents:footer'] = '';

$string['page:score:block:title'] = 'Score page block title';
$string['page:score:block:content'] = '<p>Score page block content...</p>';
$string['page:score:header'] = '<p style="margin: 10px 0;">Complete the self-evaluation below by reading each statement and score it '
    . 'in relation to how closely the statement describes you.</p>';
$string['page:score:footer'] = '<p style="margin-top: 10px;">Copyright &copy; 2013 SRS-The Development Team Ltd. All Rights Reserved.</p>';

$string['page:thankyou:block:title'] = 'Thankyou page block title';
$string['page:thankyou:block:content'] = '<p>Thankyou page block content...</p>';
$string['page:thankyou:header'] = '';
$string['page:thankyou:footer'] = '';
