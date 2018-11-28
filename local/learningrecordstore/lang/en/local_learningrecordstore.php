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

$string['pluginname'] = 'Learning record store';
$string['csvimport'] = 'CSV Import';


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
$string['form:csv:completiontime'] = 'Completion Date';
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
$string['form:csv:startnewimport'] = 'Start new import';
$string['csvloaderror'] = 'You CSV file might use a different filed separator or misses some required columns. Please try again with different settings.';


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
$string['cpd:error:completiontime'] = 'Invalid Completion date';
