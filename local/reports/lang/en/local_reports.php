<?php
// This file is part of the Arup Reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Arup Reports';
$string['learninghistory'] = 'Learning History';
$string['noaccess'] = 'You are not allowed to access this page.';
$string['exportreport'] = 'Export Report';
$string['csv_export_limit'] = 'Maximum records for CSV export';
$string['csv_export_limit_desc'] = 'If you go over this number of records the CSV export button is hidden';
$string['csvdelimitor'] = 'CSV delimitor';
$string['csvdelimitor_desc'] = 'Use a ; or , as a delimitor';
$string['settings'] = 'Local Reports configuration';
$string['cleanuptempreports'] = 'Clean up temporary stored reports';

//DB fields
$string['learninghistory:staffid'] = 'Employee Number';
$string['learninghistory:classname'] = 'Class Name';
$string['learninghistory:provider'] = 'Provider';
$string['learninghistory:location'] = 'Venue Location';
$string['learninghistory:first_name'] = 'First Name';
$string['learninghistory:last_name'] = 'Last Name';
$string['learninghistory:leaver_flag'] = 'Current Employees';
$string['learninghistory:email_address'] = 'Email Address';
$string['learninghistory:grade'] = 'Grade';
$string['learninghistory:full_name'] = 'Full Name';
$string['learninghistory:employment_category'] = 'Employment Category';
$string['learninghistory:discipline_name'] = 'Discipline';
$string['learninghistory:group_name'] = 'Group';
$string['learninghistory:companycentrearupunit'] = 'Accounting Centre';
$string['learninghistory:location_name'] = 'Office Location';
$string['learninghistory:classname'] = 'Class Name';
$string['learninghistory:classstatus'] = 'Class Status';
$string['learninghistory:classstartdate'] = 'Start Date';
$string['learninghistory:classenddate'] = 'End Date';
$string['learninghistory:duration'] = 'Duration';
$string['learninghistory:durationunits'] = 'Duration Units';
$string['learninghistory:bookingstatus'] = 'Booking Status';
$string['learninghistory:classcost'] = 'Standard Price';
$string['learninghistory:classcostcurrency'] = 'Price Currency';
$string['learninghistory:cpdid'] = 'CPD id';
$string['learninghistory:cpd'] = 'CPD';
$string['learninghistory:lms'] = 'LMS';
$string['learninghistory:cpdandlms'] = 'CPD and LMS';
$string['learninghistory:learningdesc'] = 'Description';
$string['learninghistory:bookingplaceddate'] = 'Date Booking Placed';
$string['learninghistory:classcompletiondate'] = 'Completion Date';
$string['learninghistory:coursecode'] = 'Course Code';
$string['learninghistory:coursename'] = 'Course Name';
$string['learninghistory:provider'] = 'Provider';
$string['learninghistory:classtype'] = 'Delivery Mode';
$string['learninghistory:expirydate'] = 'Certificate Expiry Date';
$string['learninghistory:notset'] = 'Not Set';
$string['learninghistory:actualregion'] = 'Region';
$string['learninghistory:georegion'] = 'Geographic Region';
$string['learninghistory:groupname'] = 'Group Name';
$string['learninghistory:company_code'] = 'Company Code';
$string['learninghistory:centre_code'] = 'Centre Code';
$string['learninghistory:costcentre'] = 'Cost Centre';
$string['learninghistory:classroom'] = 'Classroom';
$string['learninghistory:elearning'] = 'e-Learning';
$string['learninghistory:exclusion'] = 'Exclusion Report';
$string['learninghistory:bookingok'] = 'Enrolled';
$string['learninghistory:bookingnotokay'] = 'No enrolment';
$string['learninghistory:allbookings'] = 'All Booking statuses';
$string['learninghistory:employee_number'] = 'Employee number';
//Help in search form.
$string['learninghistory:cpd_help'] = 'CPD helptext';
$string['learninghistory:coursename_help'] = 'Course Name helptext';
$string['learninghistory:classname_help'] = 'Class Name helptext';
$string['learninghistory:location_name_help'] = 'Office Location helptext';
$string['learninghistory:staffid_help'] = 'Employee Number helptext';
$string['learninghistory:costcentre_help'] = 'Cost Centre helptext';
$string['learninghistory:groupname_help'] = 'Group helptext';
$string['learninghistory:actualregion_help'] = 'Region helptext';
$string['learninghistory:georegion_help'] = 'Geographic region helptext';
$string['learninghistory:leaver_flag_help'] = 'Active Users helptext';
$string['learninghistory:region_help'] = 'Region Help';
$string['learninghistory:exclusion_help'] = 'Exclusion Help';
$string['learninghistory:employee_number_help'] = 'Employee number Help';



$string['elearningstatus'] = 'Elearning Status';
// Other UI elements
$string['next'] = 'Next';
$string['previous'] = 'Previous';
$string['recordsfound'] = 'Number of records found ';
$string['moreoptions'] = 'Add Filter';
$string['showingall'] = 'in all users';
$string['showingactive'] = 'in active users';
$string['incorrectcostcentreformat'] = 'Incorrect cost centre format. use digits only or digits with a - divider';
$string['allregions'] = 'All regions';
$string['learninghistory:geo_region'] = 'Geographic Region';
$string['learninghistory:region'] = 'Region';
$string['learninghistory:region_name'] = 'Region';
$string['pleasewait'] = 'Please wait while we process your request. Generating exports can take up to 2 minutes. Once your export is ready your download link will appear here';
$string['downloadreport'] = 'Download your report';
$string['processsingfile'] = 'Your Report is being generated';
$string['showall'] = 'Uncheck to show all employees (including past)';
$string['all'] = 'All';
