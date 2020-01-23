<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Certifications';
$string['adminpage'] = 'Administration';
$string['settings'] = 'Settings';
$string['setting:send_message_rate'] = 'Send message rate';
$string['setting:send_message_rate_desc'] = 'How many queued messages to send each task (does not affect queuing rate). Setting as 0 means no messages will be sent from the queue.';
$string['custom_certification:view'] = 'View Certifications';
$string['custom_certification:manage'] = 'Manage Certifications';
$string['programdetails'] = 'Certification details';
$string['programassignments'] = 'Certification assignments';
$string['instructions:programdetails'] = 'Define the certification name, availability and description';
$string['instructions:tabdetails'] = 'Other tabs will be unlocked after you complete this form';
$string['instructions:programassignments'] = 'Assign individual learners and set fixed or relative completion criteria';
$string['instructions:programassignmentscohort'] = 'Assign learners from cohorts and set fixed or relative completion criteria';
$string['instructions:programcontentoriginal'] = 'Define the content required for the original certification path';
$string['instructions:updatesonsave'] = '(updates on save)';
$string['instructions:usermustcomplete'] = 'User must complete:';
$string['instructions:youmustcomplete'] = 'You need to complete:';
$string['category'] = 'Category';
$string['fullname'] = 'Full name';
$string['defaultprogramfullname'] = 'Certification fullname 101';
$string['shortname'] = 'Short name';
$string['programshortname'] = 'Certification Short Name';
$string['programshortname_help'] = 'The certification shortname will be used in several places where the full name isn\'t appropriate (such us in the subject line of an alert message).';
$string['defaultprogramshortname'] = 'C101';
$string['missingshortname'] = 'Missing short name';
$string['missingcategory'] = 'Missing category';
$string['idnumberprogram'] = 'ID';
$string['visibleprogram'] = 'Active';
$string['programidnumber'] = 'Certification ID number';
$string['programvisible'] = 'Is this certifcation active or not.';
$string['programidnumber_help'] = 'The ID number of a certification is only used when matching this certification against external systems - it is never displayed within Moodle. If you have an official code name for this certification then use it here ... otherwise you can leave it blank.';
$string['programvisible_help'] = 'Choose if user should see this certification or not.';
$string['programavailability'] = 'Certification Availability';
$string['programavailability_help'] = 'This option allows you to "hide" your certification completely.

It will not appear on any certification listings, except to administrators.

Even if students try to access the certification URL directly, they will not be allowed to enter.

If you set the \'Available from\' and \'Available until\' dates, students will be able to find and enter the certification during the period specified by the dates but will be prevented from accessing the certification outside of those dates.';
$string['description'] = 'Summary';
$string['summary'] = 'Summary';
$string['summary_help'] = 'Summary description of the certification';
$string['endnote'] = 'Endnote';
$string['endnote_help'] = 'Note to be displayed at the end of the certification';
$string['programoverviewfiles'] = 'Summary files';
$string['programoverviewfiles_help'] = 'Certification summary files, such as images, are displayed in the list of certifications together with the summary.';
$string['uservisible'] = 'Visible on dashboard';
$string['uservisible_help'] = 'If active, certification will be visible on user dashboard (block).';
$string['reportvisible'] = 'Visible in reports.';
$string['reportvisible_help'] = 'If active, certification will be visible in reports (block).';

$string['all'] = 'all';
$string['filter'] = 'Filter';
$string['heading'] = 'Certification';
$string['cancel'] = 'Cancel';
$string['overview'] = 'Overview';
$string['details'] = 'Details';
$string['content'] = 'Content';
$string['assignments'] = 'Assignments';
$string['messages'] = 'Messages';
$string['certification'] = 'Certification';
$string['certifications'] = 'Certifications';
$string['copy'] = 'Copy';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['deleteconfirm'] = 'Are you sure you want to remove this certification ?';
$string['nocertificationfound'] = 'No certifications found';

$string['certificationsaved'] = 'Certification has been successfully saved';
$string['certificationcreated'] = 'You have successfully created the certification, please configure other tabs now.';



$string['assignmentslabel'] = 'Add a new';
$string['assignmentbtn'] = 'Add';
$string['programidnotfound'] = 'Certification does not exists for ID : {$a}';
$string['defaultidnumber'] = '';
$string['individualassignmentoption'] = 'Individuals';
$string['cohortassignmentoption'] = 'Cohorts';
$string['assignmenttypebutton'] = 'Add {$a} to certification';
$string['saveaftersavebutton'] = 'Changes will not be saved until click on "Save changes"  ';

$string['error:idnumberexists'] = 'Record with this ID number already exists';
$string['error:shortnameunique'] = 'Short name already exists';


$string['enrolusers'] = 'Enrol users';
$string['enrolcohorts'] = 'Add cohorts';
$string['enrolcourses'] = 'Add course set';
$string['enrolleduserscounter'] = ' user found';
$string['enrolledcohortscounter'] = ' cohorts found';
$string['enrolledcoursescounter'] = ' courses found';
$string['enrollbtn'] = 'Enrol';
$string['addcohortbtn'] = 'Add';
$string['addbtn'] = 'Add';
$string['finishenrollusersbtn'] = 'Finish enrolling users';
$string['finishenrollcohortsbtn'] = 'Finish adding cohorts';
$string['finishenrollcoursesbtn'] = 'Finish adding courses';
$string['individualsbtn'] = 'Add individuals to certification';
$string['cohortbtn'] = 'Add cohorts to certification';
$string['setduedate'] = 'Set due date';

$string['certificationcontent'] = 'Certification content';
$string['recertificationcontent'] = 'Recertification content';
$string['certificationoriginalpath'] = 'Original certification path';
$string['recertificationoriginalpath'] = 'Recertification path';
$string['instructions:certificationcourseset'] = 'Define the certification content by adding sets of courses';
$string['instructions:recertificationcourseset'] = 'Define the recertification content by adding sets of courses';
$string['coursesetbtn'] = 'Add set of courses';
$string['savedatabtn'] = 'Save changes';
$string['coursesetsetname'] = 'Set name';
$string['coursesetcompletiontype'] = 'Learner must complete';
$string['coursesetmincourses'] = 'Minimum courses completed';
$string['coursesetcourses'] = 'Courses:';
$string['coursesetdefaultname'] = 'Course set';
$string['searchbtn'] = 'Search';
$string['resetbtn'] = 'Reset';
$string['moveupbtn'] = 'Move up';
$string['movedownbtn'] = 'Move down';
$string['deletebtn'] = 'Delete';
$string['allcoursesoption'] = 'All courses';
$string['onecoursesoption'] = 'One course';
$string['somecoursesoption'] = 'Some courses';
$string['andoperator'] = 'and';
$string['oroperator'] = 'or';
$string['thenoperator'] = 'then';
$string['addcourse'] = 'Add course';

$string['subjectdefaulttext'] = 'You have been enrolled on program %programfullname%';
$string['messagedefaulttext'] = 'You are now enrolled on program %programfullname%.';
$string['messagetypes'] = 'Add new message type';
$string['addmessage'] = 'Add';
$string['addmessagegroup'] = 'Message group';

$string['addcertification'] = 'Add new certification';
$string['headerindividualname'] = 'Individual name';
$string['headeruserid'] = 'User ID';
$string['headeractions'] = 'Actions';

$string['certificationmesssageheader'] = 'Certification messages';
$string['instructions:certificationinfo'] = 'Define certification messages and reminders as required';
$string['savemessage'] = 'Save changes';

$string['subject'] = 'Subject';
$string['subject_help'] = '

## Variable substitution

In certification messages, certain variables can be inserted into the subject and/or body of a message so that they will be replaced with real values when the message is sent. The variables should be inserted into the text exactly as they are shown below. The following variables can be used:

%userfirstname%
:   This will be replaced by the recipient\'s first name

%userlastname%
:   This will be replaced by the recipient\'s last name

%userfullname%
:   This will be replaced by the recipient\'s full name

%username%
:   This will be replaced by the user\'s username

%certificationfullname%
:   This will be replaced by the program\'s full name';
$string['message'] = 'Message';
$string['message_help'] = 'The message body will be displayed to message recipients in their dashboard.

The message body can contain variables which will be replaced when the message is sent.

## Variable substitution

In certification messages, certain variables can be inserted into the subject and/or body of a message so that they will be replaced with real values when the message is sent. The variables should be inserted into the text exactly as they are shown below. The following variables can be used:

%userfirstname%
:   This will be replaced by the recipient\'s first name

%userlastname%
:   This will be replaced by the recipient\'s last name

%userfullname%
:   This will be replaced by the recipient\'s full name

%username%
:   This will be replaced by the user\'s username

%certificationfullname%
:   This will be replaced by the program\'s full name';

$string['messagetype:enrolment'] = 'Enrolment email';
$string['messagetype:unenrolment'] = 'Unenrolment email';
$string['messagetype:completed'] = 'Completion email';
$string['messagetype:expired'] = 'Expired email';
$string['messagetype:beforeexpiry'] = 'Expiry window notification email';
$string['messagetype:overdue'] = 'Overdue email';
$string['messagetype:overduereminder'] = 'Overdue reminder email';


$string['additionalchecklabel'] = 'Send message to 3rd Party';
$string['messagelabel'] = 'Message';
$string['triggertimehelp:6'] = 'setting days before expiry';
$string['triggertimehelp:6_help'] = 'This determines the time before the certification expires that this message will be sent to the user.';
$string['triggertimehelp:8'] = 'setting days after previous message';
$string['triggertimehelp:8_help'] = 'This determines the time after the previous message that this will be sent, repeating until the certification is completed (or the user is no longer assigned). Initially sent this number of days after the expiry or overdue email. Subsequently sent this number of days after the previous reminder.';
$string['triggertimelabel:6'] = 'Days before expiry';
$string['triggertimelabel:8'] = 'Days after previous message';
$string['donotsendtimehelp:5'] = 'setting days after assignment';
$string['donotsendtimehelp:5_help'] = 'This determines the time window, following the user being assigned to the certification, during which this message will _NOT_ be sent.';
$string['donotsendtimehelp:6'] = 'setting days after assignment';
$string['donotsendtimehelp:6_help'] = 'This determines the time window, following the user being assigned to the certification, during which this message will _NOT_ be sent.';
$string['donotsendtimehelp:7'] = 'setting days after assignment';
$string['donotsendtimehelp:7_help'] = 'This determines the time window, following the user being assigned to the certification, during which this message will _NOT_ be sent.';
$string['donotsendtimelabel:5'] = 'Days after being assigned to certification';
$string['donotsendtimelabel:6'] = 'Days after being assigned to certification';
$string['donotsendtimelabel:7'] = 'Days after being assigned to certification';
$string['additionalrecipientlabel'] = '3rd Party Email';
$string['additionalrecipient'] = '3rd Party Email';
$string['additionalrecipient_help'] = 'If You fill 3rd Party Email input, system will send email to proper user and to additional email address you give. There can be multiple email adresses seperated by ; ';
$string['additionalcheckhelp'] = 'Send message';
$string['additionalcheckhelp_help'] = 'Tick if You want to chose additional recipient for this type of email message.';

$string['completioncounterror'] = 'Minimum courses completed value should be numeric value and less or equal than courses count.';
$string['coursesetmincourseserror'] = '*';
$string['usecertif'] = 'Use the existing certification content';
$string['cohortname'] = 'Cohort name';
$string['learnerscount'] = 'Learners count';
$string['actions'] = 'Actions';
$string['cancelbtn'] = 'Discard changes';

$string['recertificationdetails'] = 'Recertification details';
$string['instructions:recertificationdetails'] = 'Define the recertification details rules for all learners assigned to the certification';
$string['useexpirydate'] = 'Use certification expiry date';
$string['usecompletiondate'] = 'Use certification completion date';
$string['certificationperiod'] = 'Active period';
$string['instructions:certificationperiod'] = 'Define how long the certification should be valid once complete';
$string['recertificationdate'] = 'Recertification date';
$string['certificationactive'] = 'Certification is active for';
$string['recertificationperiod'] = 'Recertification window';
$string['recertificationwindowperiod'] = 'Recertification window opens before expiration';

$string['overviewheading'] = 'Overview';
$string['instructions:completioncriteria'] = 'You are required to complete this certification under the following criteria:';
$string['cohortcompletioncriteria'] = 'Member of cohort "{$a}"';
$string['userassignmentdate'] = 'Date assigned: {$a}';
$string['certifduedate'] = 'Due Date : {$a}';
$string['datenotset'] = 'Not set';
$string['coursenotstarted'] = 'Not started';
$string['summarylabel'] = 'Summary';

$string['coursename'] = 'Coursename';
$string['missingfullname'] = 'Fullname is missing';
$string['headerprogress'] = 'Progress';
$string['headerstatus'] = 'Status';
$string['completiontypeallinfo'] = 'All courses in this set must be completed';
$string['completiontypeanyinfo'] = 'Any course in this set must be completed';
$string['completiontypesomeinfo'] = 'Number of courses in this set must be completed: {$a}';
$string['launchcourse'] = 'View';
$string['editcertificationbtn'] = 'Edit certification';
$string['notenrolleduser'] = 'You are not enrolled on this certification';
$string['notavailable'] = 'N/A';
$string['recertificationpath'] = 'Recertifcation path';
$string['certificationnotset'] = 'Certifcation content not set';
$string['recertificationnotset'] = 'Recertifcation content not set';
$string['individualcompletioncriteria'] = 'Assigned as individual';
$string['nocertification'] = 'Certification with id = {$a} does not exist.';
$string['nouser'] = 'User with id = {$a} does not exist.';
$string['viewinguser'] = 'You are viewing Certification of {$a}.';
$string['missingpermission'] = 'You are not allowed to view other user certification overview.';
$string['viewingusernotenrolled'] = 'User {$a} is not enrolled to that Certification';
$string['timeperiodday'] = 'Days';
$string['timeperiodmonth'] = 'Months';
$string['timeperiodyear'] = 'Years';
$string['removemessageconfirmdialog'] = 'Are you sure you want to remove this message ?';
$string['removeassignmentconfirmdialog'] = 'Are you sure you want to remove this assignment ?';

$string['eventuserassignmentcreated'] = 'Certification user assignment created event';
$string['eventuserassignmentdeleted'] = 'Certification user assignment deleted event';
$string['eventcertificationcompleted'] = 'Certification completed event';
$string['eventcoursereset'] = 'Course reset event';
$string['eventcertificationcoursesetcompleted'] = 'Certification courseset completed event';
$string['eventcertificationexpired'] = 'Certification expired event';
$string['eventcertificationwindowopened'] = 'Certification window opened';

$string['nocertification'] = 'Certification with id = {$a} does not exist.';
$string['nouser'] = 'User with id = {$a} does not exist.';
$string['viewinguser'] = 'You are viewing Certification of {$a}.';
$string['viewingusernotenrolled'] = 'User {$a} is not enrolled to that Certification';
$string['headeridividualassignmentduedate'] = 'Assignment due date';
$string['headercohortassignmentduedate'] = 'Assignment due date';
$string['headeridividualactualduedate'] = 'Actual due date';
$string['headercohortactualduedate'] = 'Actual due date';
$string['duedatesubmitbtn'] = 'Submit';
$string['duedatecancelbtn'] = 'Cancel';
$string['duedatefromenrolment'] = 'Time from user assignment';
$string['duedatefromfirstlogin'] = 'Time from user first login';
$string['headermodalassignmentduedate'] = 'Assignment due date';
$string['duedatenotset'] = 'Not set';
$string['setduedate'] = 'Set due date';
$string['fixedduedate'] = 'Complete to {$a}';
$string['firstloginduedate'] = 'Complete within {$a} from first login';
$string['enrolmentduedate'] = 'Complete within {$a} from assignment date';
$string['cohortviewdates'] = 'View dates';
$string['userecertif'] = 'Certification requires a re-certification path';

$string['tasksendmessages'] = 'Sends email messages';
$string['taskwindowopen'] = 'Window open';
$string['taskcheckcompletion'] = 'Check certification completion';

$string['enable'] = 'Enable';
$string['notloggedin'] = "Not logged in";
$string['enrolledbyothermethod'] = '{$a} (User associated with other assignment)';
$string['error:fixeddate'] = "Date can't be earlier than today.";
$string['error:windowperiodtime'] = "Recertification window period need to be smaller than certification active period";
$string['error:certifpathcontent'] = "* Recertification path can't be save without Certification path set.";
$string['notyetadded'] = 'Waiting for CRON run';

$string['error:emailemptyfield'] = "* Email field can't be empty";
$string['error:emailinvalid'] = '* Invalid email address';

$string['taskcheckenrolments'] = 'Check enrolments';
$string['certificationenrolname'] = 'Certification enrolment id:({$a})';
$string['error:subjectemptyfield'] = "* Message subject can't be empty";
$string['headeruser']='User';

$string['statusnote'] = 'Please note your progress is still being recorded. This can take up to 10 minutes to complete';
$string['individualassignments'] = 'Individual Certifications Assignments';
$string['cohortassignments'] = 'Cohort Certifications Assignments';

$string['certifstatusred'] = 'Overdue';
$string['certifstatusamber'] = 'In progress';
$string['certifstatusgreen'] = 'Completed';
$string['certifstatusoptional'] = 'Optional';

$string['mandatorytraining'] = 'Mandatory training';
$string['mandatorytrainingrevalidation'] = 'Mandatory training (revalidation)';

$string['optional'] = 'Optional';

// Arup additions.
$string['linkedtapscourseid'] = 'Linked course(s) (optional)';
$string['linkedtapscourseid_help'] = 'Optionally select linked course(s) to connect this certifcation to.<br /><br />'
        . 'On initial completion check it will take the completion time of the most recently completed linked course enrolment and complete the certificate.<br /><br />'
        . 'This will take precedence over any existing Moodle course completions so care should be taken when applying to certifications with courses that may already be completed in Moodle.';
$string['noapplicablecourses'] = 'No applicable linked courses available';




$string['certifexpandlink'] = 'Certification Expander';
$string['certificationid'] = 'Certification ID';
$string['certificationidnumber'] = 'Certification ID number';
$string['certifname'] = 'Certification Name';
$string['certificationidnumber_help'] = 'The ID number of a certification is only used when matching this certification against external systems - it is never displayed within Moodle. If you have an official code name for this certification then use it here ... otherwise you can leave it blank.';

$string['certificationsummary'] = 'Certification Summary';

$string['findcertifications'] = 'Find certifications';
$string['certificationname'] = 'Certification name';
$string['certificationshortname'] = 'Certification shortname';
$string['certificationidnumber'] = 'Certification ID number';
$string['certificationid'] = 'Certification ID';
$string['certificationexpandlink'] = 'Certification expand link';
$string['certificationvisible'] = 'Certification visible';

$string['activeperiod'] = 'Active period';
$string['windowperiod'] = 'Window period';
$string['recertdatetype'] = 'Recertification date type';

$string['complete'] = 'Complete';
$string['incomplete'] = 'Incomplete';
$string['notstarted'] = 'Not started';
$string['eventcontentupdated'] = 'Content updated';
$string['certifvisible'] = 'Certification Visible';