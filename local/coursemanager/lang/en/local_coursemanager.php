<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course Manager';
$string['managecourses'] = 'Manage Courses';

// Events.
$string['eventclasscreated'] = 'Class Created';
$string['eventclassupdated'] = 'Class Updated';
$string['eventcoursecreated'] = 'Course Created';
$string['eventcourseupdated'] = 'Course Updated';

// Form strings.
$string['form:alert:attendedenrolments'] = 'This class has completed enrolments associated with it, therefore not all fields are editable.';
$string['form:alert:cancelled'] = 'Form Cancelled';
$string['form:alert:saved'] = 'Form Saved';
$string['form:alert:error'] = 'Failed to Save';
$string['required'] = 'Required';
$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['addclass'] = 'Add Class';
$string['addcourse'] = 'Add the Moodle Course';
$string['newcourse'] = 'New Course';
$string['newclass'] = 'New Class';
$string['clearsearch'] = 'Clear Search';
$string['notset'] = 'None';
$string['actions'] = 'Actions';
$string['confirmclassdelete'] = '{$a} Delegates are enrolled on this class. Do you wish to unenroll these and cancel the class?';
$string['confirmdelete'] = 'Yes';
$string['confirmcoursedelete'] = 'This course has classes associated, these classes need to be removed before this course can be deleted';
$string['formerror'] = 'Form Error';
$string['duplicateclassname'] = 'This class name is already in use for this course, please use a unique classname';
$string['duplicatecoursecode'] = 'This course code is already in use, please use a unique course code';
$string['resendinvites'] = 'As you have updated the class information for an upcoming class you may wish to <a href="{$a}">resend invites</a>.';
// Overview tables.
$string['active'] = 'Active';
$string['coursetableintro'] = 'Lorum Ipsum dolor sit amet';
$string['classtableintro'] = 'Lorum Ipsum dolor sit amet';
$string['moodlepage'] = 'Moodle page';
$string['unlimited'] = 'unlimited';
$string['coursenotended'] = 'Course has not ended yet';
$string['courseended'] = 'Course has ended';
$string['moodlecourseavailable'] = 'A Moodle course is available';
$string['moodlecoursehidden'] = 'A hidden Moodle course is available';

// Permissions.
$string['coursemanager:view'] = 'View Course Manager';
$string['coursemanager:add'] = 'Add Course Manager items';
$string['coursemanager:addcpd'] = 'Add CPD records';
$string['coursemanagercourse:view'] = 'View a Course Manager Course';
$string['coursemanagercourse:add'] = 'Add a Course Manager Course';
$string['coursemanagerclass:view'] = 'View a Course Manager Class';
$string['coursemanagerclass:add'] = 'Add a Course Manager Class';
$string['coursemanager:deleteclass'] = 'Delete a Course Manager Class';
$string['coursemanager:deletecourse'] = 'Delete a Course Manager Course';

// Class Form.
$string['form:class:attending'] = 'Attending';
$string['form:class:saveclass'] = 'Save Class';
$string['form:class:updateclass'] = 'Update Class';
$string['form:class:id'] ='';
$string['form:class:cmcourse'] = 'Course Manager courseid';
$string['form:class:cmclass'] = 'Course Manager classid';
$string['form:class:classid'] = 'Class id';
$string['form:class:classiddisplay'] = 'Class id';
$string['form:class:classname'] = 'Class name';
$string['form:class:classname_help'] = 'Using classnames like 2018-05 (year-month) improves sorting';
$string['form:class:courseid'] = 'Course ID';
$string['form:class:courseiddisplay'] = 'Course ID';
$string['form:class:coursename'] = 'Course name';
$string['form:class:coursenamedisplay'] = 'Course name';
$string['form:class:classtype'] = 'Class type';
$string['form:class:classtype_help'] = 'Class type help';
$string['form:class:classstatus'] = 'Class status';
$string['form:class:classstatus_help'] = 'Class status help';
$string['form:class:classdurationunits'] = 'Duration units';
$string['form:class:classdurationunitscode'] = 'Duration unit code';
$string['form:class:classduration'] = 'Class duration';
$string['form:class:classstartdate'] = 'Start date';
$string['form:class:classenddate'] = 'End date';
$string['form:class:classhidden'] = 'Hide class from users';
$string['form:class:classhidden_help'] = 'Hide class from users help';
$string['form:class:courseclasses'] = 'Course classes';
$string['form:class:enrolmentstartdate'] = 'Enrolment start date';
$string['form:class:enrolmentstartdate_help'] = 'Enrolment start date help';
$string['form:class:enrolmentenddate'] = 'Enrolment end date';
$string['form:class:enrolmentenddate_help'] = 'Enrolment end date help';
$string['form:class:trainingcenter'] = 'Room';
$string['form:class:location'] = 'Office';
$string['form:class:classstarttime'] = 'Class start time';
$string['form:class:classstarttimeenabled'] = 'Enable class start time';
$string['form:class:classendtime'] = 'Class end time';
$string['form:class:classendtimeenabled'] = 'Enable class end time';
$string['form:class:minimumattendees'] = 'Minimum attendees';
$string['form:class:maximumattendees'] = 'Maximum attendees';
$string['form:class:unlimitedattendees'] = 'Unlimited attendees';
$string['form:class:maximuminternalattendees'] = 'Maximum internal attendees';
$string['form:class:seatsremaining'] = 'Seats remaining';
$string['form:class:restrictedflag'] = 'Restricted';
$string['form:class:secureflag'] = 'Secure';
$string['form:class:selectclasstype'] = 'Select class type';
$string['form:class:selectstatustype'] = 'Select status type';
$string['form:class:pricebasis'] = 'Price basis';
$string['form:class:currencycode'] = 'Currency Code';
$string['form:class:price'] = 'Price';
$string['form:class:priceerror'] = 'Only a dot notation for the price is allowed';
$string['form:class:jobnumber'] = 'Jobnumber';
$string['form:class:classownerempno'] = 'Class owner employee number';
$string['form:class:classownerfullname'] = 'Class owner full name';
$string['form:class:classsponsor'] = 'Class sponsor';
$string['form:class:classuserstatus'] = 'Class user status';
$string['form:class:classsuppliername'] = 'Class supplier name';
$string['form:class:offeringstartdate'] = 'Offering start date';
$string['form:class:offeringenddate'] = 'Offering end date';
$string['form:class:learningpathonlyflag'] = 'Learning path only flag';
$string['form:class:page'] = 'Page';
$string['form:class:timezone'] = 'Timezone';
$string['form:class:usedtimezone'] = 'Used timezone';
$string['form:class:usedtimezone_help'] = 'Used timezone help';
$string['form:class:classcost'] = 'Class cost';
$string['form:class:classcostcurrency'] = 'Class cost currency';
$string['form:class:timemodified'] = 'Time modified';
$string['form:class:selectclass'] = 'Select class';
$string['form:class:selecttimezone'] = 'Select timezone';
$string['form:class:globaltime'] = 'Global time';
$string['form:class:start'] = 'Start';
$string['form:class:class_scheduled'] = 'Scheduled';
$string['form:class:class_self_paced'] = 'Self Paced';
$string['form:class:class_scheduled_normal'] = 'Normal';
$string['form:class:class_scheduled_planned'] = 'Planned';
$string['form:class:tab1'] = 'Class details';
$string['form:class:class_self_paced_planned'] = 'Planned';
$string['form:class:class_self_paced_normal'] = 'Normal';

// Course Form.
$string['form:course:coursename'] = 'Course Name';
$string['form:course:cmcourse'] = 'CM Course ID';
$string['form:course:savecourse'] = 'Save Course';
$string['form:course:nextstep'] = 'Next Step >>';
$string['form:course:updatecourse'] = 'Update Course';
$string['form:course:id'] = '';
$string['form:course:courseid'] = 'Course ID';
$string['form:course:coursecode'] = 'Course code';
$string['form:course:coursecode_help'] = 'Course code help';
$string['form:course:coursename'] = 'Course name';
$string['form:course:classes'] = 'Classes';
$string['form:course:startdate'] = 'Start date';
$string['form:course:enddate'] = 'End date';
$string['form:course:courseregion'] = 'Region';
$string['form:course:coursedescription'] = 'Course description';
$string['form:course:courseobjectives'] = 'Objectives';
$string['form:course:courseaudience'] = 'Audience';
$string['form:course:courseaudience_help'] = 'Audience help';
$string['form:course:globallearningstandards'] = 'Global learning standards';
$string['form:course:getdurationunits'] = 'Select duration units';
$string['form:course:onelinedescription'] = 'Oneline description';
$string['form:course:onelinedescription_help'] = 'Online description help';
$string['form:course:onelinedescription_help'] = 'Oneline description help';
$string['form:course:businessneed'] = 'Business need';
$string['form:course:businessneed_help'] = 'Business need help';
$string['form:course:accreditationgivendate'] = 'Accreditation given date';
$string['form:course:tapsurllink'] = 'Taps URL link';
$string['form:course:keywords'] = 'Keyword';
$string['form:course:keywords_help'] = 'Keyword Help';
$string['form:course:duration'] = 'Duration';
$string['form:course:durationunits'] = 'Duration units';
$string['form:course:durationunitscode'] = 'Duration units code';
$string['form:course:sponsorname'] = 'Sponsor name';
$string['form:course:courseadminempno'] = 'Course admin employee number';
$string['form:course:courseadminempname'] = 'Course admin employee name';
$string['form:course:maximumattendees'] = 'Maximum attendees';
$string['form:course:minimumattendees'] = 'Minimum attendees';
$string['form:course:futurereviewdate'] = 'Future review date';
$string['form:course:jobnumber'] = 'Jobnumber';
$string['form:course:activecourse'] = 'Active course';
$string['form:course:usedtimezone'] = 'Used timezone';
$string['form:course:timemodified'] = 'Time modified';
$string['form:course:start'] = 'Start';
$string['form:course:selectregion'] = 'Select region';
$string['form:course:tab1'] = 'Course details';
$string['form:course:tab2'] = 'Course description and information';
$string['form:course:tab3'] = 'Other details';
$string['form:course:edit'] = 'Edit details';
$string['form:course:moodlecourse'] = 'Moodle course';

// CPD UPload
$string['cpduploadheading'] = 'Upload CPD Records';
$string['cpduploaddesc'] = 'This form allows you to upload CPD records. Please use this <a href="{$a}">Sample Excel File</a> for your CPD records. When you have completed the excel file please save it as a CSV format before uploading it here';
$string['form:csv:csv'] = 'CSV file';
$string['form:csv:encoding'] = 'Encoding';
$string['form:csv:rowpreviewnum'] = 'Preview rows';
$string['form:csv:rowpreviewnum_help'] = 'Number of rows from the CSV file that will be previewed in the next page. This option exists in order to limit the next page size.';
$string['form:csv:csvdelimiter'] = 'CSV delimiter';
$string['form:csv:csvfileerror'] = 'There was an error in your CSV upload file';
$string['form:csv:csvline'] = 'CSV line';
$string['form:csv:execute'] = 'Start Import';
$string['form:csv:continue'] = 'Continue';
$string['form:csv:uploadcsv'] = 'Upload CSV';
$string['form:csv:staffid'] = 'Employee number';
$string['form:csv:classname'] = 'Title';
$string['form:csv:classcompletiondate'] = 'Completion Date';
$string['form:csv:provider'] = 'Supplier/Trainer';
$string['form:csv:duration'] = 'Duration';
$string['form:csv:durationunits'] = 'Duration Units';
$string['form:csv:classtype'] = 'Learning Method';
$string['form:csv:classcategory'] = 'Subject Category';
$string['form:csv:location'] = 'Location';
$string['form:csv:healthandsafetycategory'] = 'Healt and Safety Category';
$string['form:csv:classcost'] = 'Course Cost';
$string['form:csv:certificateno'] = 'Certificate Number';
$string['form:csv:learningdesc'] = 'Learning Description';
$string['form:csv:classcostcurrency'] = 'Currency';
$string['form:csv:classstarttime'] = 'Start Date';
$string['form:csv:expirydate'] = 'Certificate Expiry Date';
$string['form:csv:notallowed'] = 'You are not allowed to add CPD records';
$string['form:csv:result'] = 'Result';
$string['form:csv:rowpreviewnum'] = 'Preview rows';
$string['form:csv:rowpreviewnum_help'] = 'Number of rows from the CSV file that will be previewed in the next page. This option exists in order to limit the next page size.';
$string['form:csv:backtocm'] = 'Back to coursemanager';
$string['csvloaderror'] = 'You CSV file might use a different filed separator or misses some required columns. Please try again with different settings.';

// Page Names
$string['pagename:overview'] = 'Overview';
$string['pagename:course'] = 'Course Form';
$string['pagename:class'] = 'Class Form';

// CPD records
$string['cpd:error:statusmissingfields'] = 'missing required field(s): {$a}';
$string['cpd:error:staffid'] = 'Incorrect / Non existing Employee number';
$string['cpd:error:nocoursetitle'] = 'No Title';
$string['cpd:error:durationunits'] = 'Incorrect duration units code';
$string['cpd:error:classtype'] = 'Incorrect Learning Method';
$string['cpd:error:classcategory'] = 'Incorrect Class Category';
$string['cpd:error:healthandsafetycategory'] = 'Incorrect Health and Safety Category';
$string['cpd:error:classcost'] = 'Class cost is not numeric';
$string['cpd:error:certificateno'] = 'Certificate number should not be numeric';
$string['cpd:error:learningdesc'] = 'Learning description should not be numeric';
$string['cpd:error:classcostcurrency'] = 'Invalid Class Cost Currency';
$string['cpd:error:classstarttime'] = 'Invalid Class start time';
$string['cpd:error:expirydate'] = 'Invalid Certificate expiry date';
$string['cpd:error:classcompletiondate'] = 'Invalid Completion date';


