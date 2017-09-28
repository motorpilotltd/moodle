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
 * English strings for arupapplication
 *
 * @package    mod_arupapplication
 * @copyright  2014 Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Arup application';
$string['modulename_help'] = 'The Arup application activity provides an integrated workflow for a user to apply for an Arup run course.';
$string['modulename_link'] = 'mod/arupapplication/view';
$string['modulenameplural'] = 'Arup applications';
$string['pluginname'] = 'Arup application';
$string['pluginadministration'] = 'Arup application administration';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';
$string['arupapplication:addinstance'] = 'Add a new Arup application';
$string['arupapplication:view'] = 'View an Arup application';
$string['arupapplication:submit'] = 'Submit an Arup application';
$string['arupapplication:printapplication'] = 'Print an Arup application submission';
$string['arupapplication:downloadapplication'] = 'Download an Arup application submission';
$string['arupapplication:deleteapplication'] = 'Delete an Arup application submission';
$string['arupapplication:edititems'] = 'Edit Arup application items';
$string['arupapplication:complete'] = 'Complete an Arup application';
$string['arupapplication:viewreports'] = 'View Arup application reports';

//Progress indicators
$string['progress:notstarted'] = 'Not started';
$string['progress:started'] = 'Started';
$string['progress:inprogress'] = 'In progress';
$string['progress:submitted'] = 'Submitted';
$string['progress:applicationsubmitted'] = 'Application submitted';
$string['progress:complete'] = 'Complete';
$string['progress:awaitingtechnicalreference'] = 'Awaiting technical reference';
$string['progress:awaitingsponsorstatement'] = 'Awaiting sponsor statement of support';
$string['progress:receivedtechnicalreference'] = 'Technical reference received';
$string['progress:receivedsponsorstatement'] = 'Sponsor statement of support received';
$string['progress:completion_info'] = 'TBD';
$string['progress:notcomplete'] = 'Not complete';

$string['progress:verbose:notstarted'] = '<p>Not started</p>';
$string['progress:verbose:started'] = '<p>Started</p>';
$string['progress:verbose:inprogress'] = '<p>In progress</p>';
$string['progress:verbose:applicationsubmitted'] = '<p>Application submitted</p>';
$string['progress:verbose:complete'] = '<p>Complete</p>';
$string['progress:verbose:awaitingtechnicalreference'] = '<p>Awaiting technical reference</p>';
$string['progress:verbose:awaitingsponsorstatement'] = '<p>Awaiting sponsor statement of support</p>';
$string['progress:verbose:receivedtechnicalreference'] = '<p>Technical reference received</p>';
$string['progress:verbose:receivedsponsorstatement'] = '<p>Sponsor statement of support received</p>';

$string['completionsubmit'] = 'View as completed if the application is submitted';

//Buttons
$string['button:startapplication'] = 'Start application';
$string['button:continueapplication'] = 'Continue application';
$string['button:viewapplication'] = 'View application';
$string['button:save'] = 'Save';
$string['button:saveback'] = 'Save and go back';
$string['button:savecontinue'] = 'Save and continue';
$string['button:sendcontinue'] = 'Send and continue';
$string['button:saveexit'] = 'Save and exit';
$string['button:exit'] = 'Exit';
$string['button:exitnosave'] = 'Exit without saving';
$string['button:cancel'] = 'Cancel';
$string['button:submit'] = 'Submit';
$string['button:submitapplication'] = 'Submit application';
$string['button:resend'] = 'Resend';
$string['button:update'] = 'Update';
$string['button:continue'] = 'Continue';

$string['button:resendreferenceemail'] = 'Resend reference email';
$string['button:sendreferenceemail'] = 'Send reference email';
$string['button:resendsponsoremail'] = 'Resend sponsor email';

$string['button:submitreference'] = 'Submit technical reference';
$string['button:submitsponsor'] = 'Submit sponsor statement of support';

//Actions
$string['actions'] = 'Actions';
$string['action:add'] = 'Add';
$string['action:edit'] = 'Edit';
$string['action:view'] = 'View';
$string['action:print'] = 'Print';
$string['action:delete'] = 'Delete';
$string['action:down'] = 'Move down';
$string['action:up'] = 'Move up';
$string['action:move_here'] = 'Move here';
$string['action:move'] = 'Move';
$string['action:cancel_moving'] = 'Cancel moving';
$string['action:download'] = 'Download';
$string['action:all'] = 'All';
$string['action:complete'] = 'Complete';

//Errors
$string['error:message'] = 'There are errors in your application form which are detailed below. You can save any information you enter on this page but until you have resolved the errors you will not be able to submit your application for consideration.';
$string['error:required'] = 'Required';
$string['error:referee:notassigned'] = 'Sorry, you are not assigned as the technical referee for this application.';
$string['error:referee:nothingtodo'] = 'You do not currently have any outstanding or completed technical references for applications for this module.';
$string['error:sponsor:notassigned'] = 'Sorry, you are not assigned as the sponsor for this application.';
$string['error:sponsor:nothingtodo'] = 'You do not currently have any outstanding or completed statements of support for applications for this module.';

$string['error:maxlength'] = 'Maximum length is {$a} characters';

$string['error:dateofbirth'] = 'Please confirm your date of birth';
$string['error:joiningdate'] = 'Date of joining can\'t be a future date';
$string['error:email'] = 'Must contain the [[link]] or [[linkurl]] placeholder.';

//Global activity configuration

//General
$string['globalconfiguration_hint'] = 'Please enter options for the following fields one per line. These will be used to populate select menus within the application form.';
//Form
$string['generalconfig'] = 'General configuration';
$string['gradeoptions'] = 'Grades (one per line)';
$string['gradeoptions_help'] = '';
$string['gradeoptionsdefault'] = 'Default';
$string['officelocationoptions'] = 'Office locations (one per line)';
$string['officelocationoptions_help'] = '';
$string['officelocationoptionsdefault'] = 'Default';

//Activity instance configuration

//General
$string['instanceconfiguration_hint'] = 'TBD';
//Form
$string['instancename'] = 'Arup application';
$string['instanceintro'] = 'Application introduction';
$string['arupapplicationfieldset'] = 'Custom fields';
$string['technicalreferencereq'] = 'Technical reference required';
$string['sponsorstatementreq'] = 'Sponsor statement of support required';
$string['sponsordeclarationlabel'] = 'Label for sponsor declaration field';
$string['refereemessagehint'] = 'Hint for technical referee message';
$string['refereemessagehint_help'] = 'TBD';
$string['email:referee:footer'] = 'Footer for email to technical referee';
$string['email:referee:footer_help'] = 'TBD';
$string['sponsormessage_hint'] = 'Hint for sponsor message';
$string['sponsormessage_hint_help'] = 'TBD';
$string['email:sponsor:footer'] = 'Footer for email to sponsor';
$string['email:sponsor:footer_help'] = 'TBD';
$string['submission_hint'] = 'Hint for submission of application form';
$string['submission_hint_help'] = 'TBD';
$string['reference_hint'] = 'Hint for technical reference form';
$string['reference_hint_help'] = 'TBD';
$string['sponsorstatement_hint'] = 'Hint for sponsor statement of support form';
$string['sponsorstatement_hint_help'] = 'TBD';
$string['footer'] = 'Footer for all activity pages';
$string['footer_help'] = 'TBD';
$string['email:applicant:startnotification'] = 'Start notification email to applicant';
$string['email:applicant:startnotification_help'] = 'TBD';
$string['email:applicant:submissionnotification'] = 'Submission notification email to applicant';
$string['email:applicant:submissionnotification_help'] = 'TBD';
$string['email:applicant:completenotification'] = 'Completion notification email to applicant';
$string['email:applicant:completenotification_help'] = 'TBD';

//View pages

//General
$string['heading:applicantdetails'] = 'Applicant details';
$string['heading:applicationcompleted'] = 'Completed application';
$string['heading:applicationdetails'] = 'Application details';
$string['heading:date'] = 'Date';
$string['heading:declaration'] = 'Declaration';
$string['heading:declarations'] = 'Declarations';
$string['heading:declaration:add'] = 'Add declaration';
$string['heading:declaration:edit'] = 'Edit declaration';
$string['heading:deletesubmission'] = 'Delete submission';
$string['heading:errors'] = 'Errors';
$string['heading:from'] = 'From';
$string['heading:message'] = 'Message';
$string['heading:name'] = 'Name';
$string['heading:overview'] = 'Overview';
$string['heading:previousemails'] = 'Previous emails';
$string['heading:qualifications'] = 'Qualifications';
$string['heading:referee'] = 'Technical referee';
$string['heading:refereesummary'] = 'Technical referee: Summary';
$string['heading:sponsor'] = 'Sponsor';
$string['heading:sponsorstatement'] = 'Sponsor statement of support';
$string['heading:sponsorsummary'] = 'Sponsor: Summary';
$string['heading:staffid'] = 'Staff ID';
$string['heading:statement'] = 'Statement';
$string['heading:statementquestion'] = 'Statement question';
$string['heading:statementquestions'] = 'Statement questions';
$string['heading:statementquestion:add'] = 'Add statement question';
$string['heading:statementquestion:edit'] = 'Edit statement question';
$string['heading:status'] = 'Status';
$string['heading:technicalreference'] = 'Technical reference';
$string['heading:to'] = 'To';

$string['heading:field'] = 'Field name';
$string['heading:link'] = 'Link to page';
$string['heading:error'] = 'Error';
$string['heading:applyingfor'] = 'Applying for';

$string['legend:applicantdetails:personal'] = 'Personal details';
$string['legend:applicantdetails:arup'] = 'Arup details';

//Form fields
$string['statementquestion'] = 'Statement question';
$string['declaration'] = 'Declaration';
$string['required'] = 'Required';
$string['refereeemail'] = 'Technical referee\'s email address';
$string['refereeemail_help'] = 'TBD';
$string['refereemessage'] = 'Your message to your technical referee';
$string['refereemessage_help'] = 'TBD';
$string['refereemessage_hint'] = 'Your message must include the placeholder [[link]] which will be replaced by a URL to enable your technical referee to access the response form.';
$string['title'] = 'Title';
$string['title_help'] = 'TBD';
$string['firstname'] = 'First name';
$string['surname'] = 'Surname';
$string['passportname'] = 'Name in passport';
$string['passportname_help'] = 'TBD';
$string['knownas'] = 'Prefer to be known as';
$string['knownas_help'] = 'TBD';
$string['dateofbirth'] = 'Date of birth';
$string['dateofbirth_help'] = 'TBD';
$string['countryofresidence'] = 'Country of residence';
$string['countryofresidence_help'] = 'TBD';
$string['requirevisa'] = 'Are you likely to require a visa to travel to the residential location?';
$string['requirevisa_help'] = 'TBD';
$string['staffid'] = 'Staff ID';
$string['grade'] = 'Grade';
$string['jobtitle'] = 'Job title';
$string['discipline'] = 'Discipline';
$string['discipline_help'] = 'TBD';
$string['joiningdate'] = 'Date of joining Arup';
$string['joiningdate_help'] = 'TBD';
$string['group'] = 'Group';
$string['group_help'] = 'TBD';
$string['businessarea'] = 'Business area';
$string['businessarea_help'] = 'TBD';
$string['region'] = 'Arup region';
$string['region_help'] = 'TBD';
$string['officelocation'] = 'Office location';
$string['otherofficelocation'] = 'Office location - Other';
$string['degree'] = 'Please state degree(s) including title, institution and attainment level (e.g. B.Eng Civil Engineering, University of Portsmouth, 2:1)';
$string['degree_help'] = 'TBD';
$string['cv'] = 'Please attach your CV as a PDF document';
$string['cv_help'] = 'TBD';
$string['sponsoremail'] = 'Sponsor\'s email address';
$string['sponsoremail_help'] = 'TBD';
$string['sponsormessage'] = 'Your message to your sponsor';
$string['sponsormessage_help'] = 'TBD';
$string['sponsorstatement'] = 'Please provide your statement of support';
$string['sponsorstatement_help'] = 'TBD';
$string['referencephone'] = 'Phone number';
$string['referenceposition'] = 'Position in relation to Applicant';
$string['referenceknown'] = 'How long have you known the applicant for and in what capacity?';
$string['referenceperformance'] = 'Please comment on the applicant\'s academic and technical performance, and their ability to benefit from masters level learning.';
$string['referencetalent'] = 'Taking an overall view, what do you consider to be the applicant\'s major talents and most significant weaknesses?';
$string['referencemotivation'] = 'Please comment on the applicant\'s ability to sustain their motivation for study. Has the applicant previously shown an interest in the proposed area of study?';
$string['referenceknowledge'] = 'Please comment on the applicant\'s contribution to sharing their knowledge through the Skills Networks or other means.';
$string['referencecomments'] = 'Please feel free to make any further comments which you feel will be helpful.';

$string['sortorder'] = 'Sort order';
$string['addedsuccess'] = '<p>Record added successfully</p>';
$string['updatesuccess'] = '<p>Record updated successfully</p>';

$string['applicantname'] = 'Applicant name';
$string['moduletitle'] = 'Module title';
$string['ismandatory'] = ' (Required)';
$string['optional'] = ' (Optional)';
$string['resendemail'] = 'Resend email';

$string['norecordfound'] = 'Can not find data record in database';

$string['cannotviewsubmission'] = 'You do not have the required permissions to view applications submissions in this course';
$string['cannotprintsubmission'] = 'You do not have the required permissions to print this application';
$string['cannotdownloadsubmission'] = 'You do not have the required permissions to download the application(s)';
$string['cannotdeletesubmission'] = 'You do not have the required permissions to delete applications';

$string['confirmdelete'] = 'Are you sure you want to delete - ';
$string['confirmdeletesubmission'] = 'Are you sure you want to delete the submission for {$a}?';
$string['deletesuccess'] = 'Submission successfully deleted.';

$string['cvfilefound'] = 'Please click on the push pin at top of page to download the CV';
$string['cvfilemissing'] = 'CV not uploaded';

$string['clickhere'] = 'Click <a href="{$a}">here</a> to view the link';
$string['progressindication'] = 'This is page {$a->pagenumber} of 6';

$string['norecords'] = 'No records found to download';