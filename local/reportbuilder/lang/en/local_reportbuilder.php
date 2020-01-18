<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

$string['abstractmethodcalled'] = 'Abstract method {$a} called - must be implemented';
$string['access'] = 'Access';
$string['accessbyrole'] = 'Restrict access by role';
$string['accesscontrols'] = 'Access Controls';
$string['accessiblereportsonly'] = 'Only reports accessible to the report viewer';
$string['activeonly'] = 'Active users only';
$string['activeuser'] = 'Active user';
$string['activities'] = 'Activities';
$string['actions'] = 'Actions';
$string['add'] = 'Add';
$string['addanothercolumn'] = 'Add another column...';
$string['addanotherfilter'] = 'Add another filter...';
$string['addanothersearchcolumn'] = 'Add another search column...';
$string['addbadges'] = 'Add badges';
$string['addcohorts'] = 'Add audiences';
$string['addedscheduledreport'] = 'Added new scheduled report';
$string['addexternalemail'] = 'Add email';
$string['addanewscheduledreport'] = 'Add a new scheduled report to the list: ';
$string['addscheduledreport'] = 'Add scheduled report';
$string['addsystemusers'] = 'Add system user(s)';
$string['addnewscheduled'] = 'Add scheduled';
$string['advanced'] = 'Advanced?';
$string['advancedcolumnheading'] = 'Aggregation or grouping';
$string['advancedgroupaggregate'] = "Aggregations";
$string['advancedgrouptimedate'] = "Time and date (DB server time zone)";
$string['aggregatetypeavg_heading'] = 'Average of {$a}';
$string['aggregatetypeavg_name'] = 'Average';
$string['aggregatetypecountany_heading'] = 'Count of {$a}';
$string['aggregatetypecountany_name'] = 'Count';
$string['aggregatetypecountdistinct_heading'] = 'Count unique values of {$a}';
$string['aggregatetypecountdistinct_name'] = 'Count unique';
$string['aggregatetypegroupconcat_heading'] = '{$a}';
$string['aggregatetypegroupconcat_name'] = 'Comma separated values';
$string['aggregatetypegroupconcatdistinct_heading'] = '{$a}';
$string['aggregatetypegroupconcatdistinct_name'] = 'Comma separated values without duplicates';
$string['aggregatetypemaximum_heading'] = 'Maximum value from {$a}';
$string['aggregatetypemaximum_name'] = 'Maximum';
$string['aggregatetypeminimum_heading'] = 'Minimum value from {$a}';
$string['aggregatetypeminimum_name'] = 'Minimum';
$string['aggregatetypepercent_heading'] = 'Percentage of {$a}';
$string['aggregatetypepercent_name'] = 'Percentage';
$string['aggregatetypestddev_heading'] = 'Standard deviation of {$a}';
$string['aggregatetypestddev_name'] = 'Standard deviation';
$string['aggregatetypesum_name'] = 'Sum';
$string['aggregatetypesum_heading'] = 'Sum of {$a}';
$string['alldata'] = 'All data';
$string['allofthefollowing'] = 'All of the following';
$string['allowtotalcount'] = 'Allow reports to show total count';
$string['allowtotalcount_desc'] = 'When enabled Report Builder reports can be configured to show a total count of records, before filters have been applied. Please be aware that getting this count can be an expensive operation, and for performance reasons we recommend you leave this setting off.';
$string['allembeddedreports'] = 'All embedded reports';
$string['alluserreports'] = 'All user reports';
$string['allrestrictions'] = '&laquo; All Restrictions';
$string['allscheduledreports'] = 'All scheduled reports';
$string['and'] = ' and ';
$string['anycontext'] = 'Users may have role in any context';
$string['anyofthefollowing'] = 'Any of the following';
$string['anyrole'] = 'Any role';
$string['ascending'] = 'Ascending (A to Z, 1 to 9)';
$string['assigned'] = 'Assigned';
$string['assignedactivities'] = 'Assigned activities';
$string['assignedanyrole'] = 'Assigned any role';
$string['assignedgroups'] = 'Assigned groups';
$string['assignedusers'] = 'Assigned users';
$string['assignedrole'] = 'Assigned role \'{$a->role}\'';
$string['assigngroup'] = 'Assign a group to restriction';
$string['assigngrouprecord'] = 'Assign restriction records';
$string['assigngroupuser'] = 'Assign restricted users';
$string['at'] = 'at';
$string['backtoallgroups'] = 'Back to all groups';
$string['badcolumns'] = 'Invalid columns';
$string['badcolumnsdesc'] = 'The following columns have been included in this report, but do not exist in the report\'s source. This can occur if the source changes on disk after reports have been generated. To fix, either restore the previous source file, or delete the columns from this report.';
$string['baseactivity'] = 'Base activity';
$string['basedon'] = 'Group based on';
$string['baseitem'] = 'Base item';
$string['baseitemdesc'] = 'The aggregated data available to this group is based on the questions in the activity \'<a href="{$a->url}">{$a->activity}</a>\'.';
$string['both'] = 'Both';
$string['bydateenable'] = 'Show records based on the record date';
$string['bytrainerenable'] = 'Show records by trainer';
$string['byuserenable'] = 'Show records by user';
$string['cache'] = 'Enable Report Caching';
$string['cachedef_rb_ignored_embedded'] = 'Report builder ignored embedded reports cache';
$string['cachedef_rb_ignored_sources'] = 'Report builder ignored report sources cache';
$string['cachegenfail'] = 'The last attempt to generate cache failed. Please try again later.';
$string['cachegenstarted'] = 'Cache generation started at {$a}. This process can take several minutes.';
$string['cachenow'] = 'Generate Now';
$string['cachenow_help'] = 'If **Generate now** is checked, then report cache will be generated immediately after form submit.';
$string['cachenow_title'] = 'Report cache';
$string['cachepending'] = '{$a} There are changes to this report\'s configuration that have not yet been applied. The report will be updated next time the report is generated.';
$string['cachereport'] = 'Generate report cache';
$string['cannotviewembedded'] = 'Embedded reports can only be accessed through their embedded url';
$string['category'] = 'Category';
$string['checktablestatus'] = 'Check table status';
$string['choosecatplural'] = 'Choose Categories';
$string['chooseman'] = 'Choose Manager...';
$string['choosemanplural'] = 'Choose Managers';
$string['chooserestrictiondesc'] = 'You have access to records belonging to multiple groups of users. Select which groups of records you want to show when viewing the report:';
$string['chooserestrictiontitle'] = 'Viewing records for:';
$string['chooserole'] = 'Choose role...';
$string['clearform'] = 'Clear';
$string['clone'] = 'Clone';
$string['clonecompleted'] = 'Report cloned successfully';
$string['clonedescrhtml'] = 'Report "{$a->origname}" will be cloned as "{$a->clonename}" including the following properties: {$a->properties}';
$string['clonereportaccesswarning'] = 'Warning: Report content and access controls may change when copying an embedded report as content or access controls that are applied by the embedded page will be lost.';
$string['clonereportaccessreset'] = 'Access properties will be reset to system default for clone of embedded report';
$string['clonereportfilters'] = 'Report filters';
$string['clonereportcolumns'] = 'Report columns';
$string['clonereportsearchcolumns'] = 'Report text search columns';
$string['clonereportsettings'] = 'Report settings';
$string['clonereportgraph'] = 'Report graph and aggregation settings';
$string['clonenamepattern'] = 'Clone of {$a}';
$string['clonefailed'] = 'Could not make copy of report';
$string['clonereport'] = 'Clone report';
$string['column'] = 'Column';
$string['column_deleted'] = 'Column deleted';
$string['column_moved'] = 'Column moved';
$string['column_vis_updated'] = 'Column visibility updated';
$string['columns'] = 'Columns';
$string['columns_updated'] = 'Columns updated';
$string['configenablereportcaching'] = 'This will allow administrators to configure report caching';
$string['confirmdeleterestrictionheader'] = 'Confirm deletion of "{$a}" restriction';
$string['confirmdeleterestriction'] = 'Are you sure you want to delete this restriction? All restriction data will be lost.';
$string['confirmcoldelete'] = 'Are you sure you want to delete this column?';
$string['confirmcolumndelete'] = 'Are you sure you want to delete this column?';
$string['confirmdeletereport'] = 'Confirm Deletion';
$string['confirmfilterdelete'] = 'Are you sure you want to delete this filter?';
$string['confirmfilterdelete_rid_enabled'] = 'Are you sure? Removing all filters means this report will display automatically on page load{$a}.';
$string['confirmfilterdelete_grid_enabled'] = ' (the enabled \'Restrict initial display in all report builder reports\' setting will no longer apply)';
$string['confirmrecord'] = 'Confirm {$a}';
$string['confirmreloadreport'] = 'Confirm Reset';
$string['confirmsearchcolumndelete'] = 'Are you sure you want to delete this search column?';
$string['content'] = 'Content';
$string['contentclassnotexist'] = 'Content class {$a} does not exist';
$string['contentcontrols'] = 'Content Controls';
$string['contentdesc_userown'] = 'The {$a->field} is "{$a->user}"';
$string['contentdesc_delim'] = '" or "';
$string['context'] = 'Context';
$string['couldnotsortjoinlist'] = 'Could not sort join list. Source either contains circular dependencies or references a non-existent join';
$string['course_completion'] = 'Course Completion';
$string['courseenddate'] = 'End date';
$string['courseenrolavailable'] = 'Open enrolment';
$string['courseenroltype'] = 'Enrolment type';
$string['courseenroltypes'] = 'Enrolment Types';
$string['courseexpandlink'] = 'Course Name (expanding details)';
$string['coursecategory'] = 'Course Category';
$string['coursecategoryid'] = 'Course Category ID';
$string['coursecategorylinked'] = 'Course Category (linked to category)';
$string['coursecategorylinkedicon'] = 'Course Category (linked to category with icon)';
$string['coursecategorymultichoice'] = 'Course Category (multichoice)';
$string['coursecategoryidnumber'] = 'Course Category ID Number';
$string['coursecompletedon'] = 'Course completed on {$a}';
$string['coursedatecreated'] = 'Course Date Created';
$string['courseenrolledincohort'] = 'Course is enrolled in by audience';
$string['courseicon'] = 'Course Icon';
$string['courseid'] = 'Course ID';
$string['courseidnumber'] = 'Course ID Number';
$string['courselanguage'] = 'Course language';
$string['coursemultiitem'] = 'Course (multi-item)';
$string['coursemultiitemchoose'] = 'Choose Courses';
$string['coursename'] = 'Course Name';
$string['coursenameandsummary'] = 'Course Name and Summary';
$string['coursenamelinked'] = 'Course Name (linked to course page)';
$string['coursenamelinkedicon'] = 'Course Name (linked to course page with icon)';
$string['coursenotset'] = 'Course Not Set';
$string['courseprogress'] = 'Progress';
$string['courseshortname'] = 'Course Shortname';
$string['coursestartdate'] = 'Course Start Date';
$string['coursestatuscomplete'] = 'You have completed this course';
$string['coursestatusenrolled'] = 'You are currently enrolled in this course';
$string['coursestatusnotenrolled'] = 'You are not currently enrolled in this course';
$string['coursesummary'] = 'Course Summary';
$string['coursetypeicon'] = 'Type';
$string['coursetype'] = 'Course Type';
$string['coursevisible'] = 'Course Visible';
$string['coursevisibledisabled'] = 'Course Visible (not applicable)';
$string['createasavedsearch'] = 'Create a saved search';
$string['createreport'] = 'Create report';
$string['csvformat'] = 'CSV format';
$string['currentfinancial'] = 'The current financial year';
$string['currentsearchparams'] = 'Settings to be saved';
$string['customiseheading'] = 'Customise heading';
$string['customisename'] = 'Customise Field Name';
$string['daily'] = 'Daily';
$string['data'] = 'Data';
$string['dateafter'] = 'After {$a}';
$string['datebefore'] = 'Before {$a}';
$string['datebetween'] = '{$a->from} to {$a->to}';
$string['dateisbetween'] = 'is between start of today and ';
$string['datelabelisafter'] = '{$a->label} is after {$a->after}';
$string['datelabelisafterandnotset'] = '{$a->label} is after {$a->after} and includes dates that are blank';
$string['datelabelisbefore'] = '{$a->label} is before {$a->before}';
$string['datelabelisbeforeandnotset'] = '{$a->label} is before {$a->before} and includes dates that are blank';
$string['datelabelisbetween'] = '{$a->label} is between {$a->after} and {$a->before}';
$string['datelabelisbetweenandnotset'] = '{$a->label} is between {$a->after} and {$a->before} and includes dates that are blank';
$string['datelabelisdaysafter'] = '{$a->label} is after today\'s date and before {$a->daysafter}';
$string['datelabelisdaysafterandnotset'] = '{$a->label} is after today\'s date and before {$a->daysafter} including dates that are not set';
$string['datelabelisdaysbefore'] = '{$a->label} is before today\'s date and after {$a->daysbefore}.';
$string['datelabelisdaysbeforeandnotset'] = '{$a->label} is before today\'s date and after {$a->daysbefore} including dates that are not set';
$string['datelabelisdaysbetween'] = '{$a->label} is after {$a->daysbefore} and before {$a->daysafter}';
$string['datelabelisdaysbetweenandnotset'] = '{$a->label} is after {$a->daysbefore} and before {$a->daysafter} including dates that are not set';
$string['datelabelnotset'] = 'Blank date records';
$string['datenotset'] = 'show blank date records';
$string['defaultsortcolumn'] = 'Default column';
$string['defaultsortorder'] = 'Default order';
$string['delete'] = 'Delete';
$string['deleterecord'] = 'Delete {$a}';
$string['deletecheckschedulereport'] = 'Are you sure you would like to delete the \'{$a}\' scheduled report?';
$string['deletedescrhtml'] = 'Report "{$a}" will be completely deleted.';
$string['deletedonly'] = 'Deleted users only';
$string['deletedscheduledreport'] = 'Successfully deleted Scheduled Report \'{$a}\'';
$string['deleteduser'] = 'Deleted user';
$string['deletereport'] = 'Report Deleted';
$string['deletescheduledreport'] = 'Delete scheduled report?';
$string['descending'] = 'Descending (Z to A, 9 to 1)';
$string['disabled'] = 'Disabled?';
$string['duration_hours_minutes'] = '{$a->hours}h {$a->minutes}m';
$string['edit'] = 'Edit';
$string['editingsavedsearch'] = 'Editing saved search';
$string['editreport'] = 'Edit Report \'{$a}\'';
$string['editrestriction'] = 'Edit restriction \'{$a}\'';
$string['editscheduledreport'] = 'Edit Scheduled Report';
$string['editrecord'] = 'Edit {$a}';
$string['editthisreport'] = 'Edit this report';
$string['emailexternaluserisonthelist'] = 'This email is already on the external users email list';
$string['emailexternalusers'] = 'External users email';
$string['emailexternalusers_help'] = 'Please enter one email address in the box below.';
$string['embedded'] = 'Embedded';
$string['embeddedaccessnotes'] = '<strong>Warning:</strong> Embedded reports may have their own access restrictions applied to the page they are embedded into. They may ignore the settings below, or they may apply them as well as their own restrictions.';
$string['embeddedcontentnotes'] = '<strong>Warning:</strong> Embedded reports may have further content restrictions applied via <em>embedded parameters</em>. These can further limit the content that is shown in the report';
$string['embeddedreports'] = 'Embedded Reports';
$string['enablereportcaching'] = 'Enable report caching';
$string['enablereportgraphs'] = 'Enable report builder graphs';
$string['enablereportgraphsinfo'] = 'This option will let you: enable (show) or disable report builder graphs on this site.

* If Show is selected, all features related to report builder graphs will be visible and accessible.
* If Disable is selected, no report builder graphs features will be visible or accessible.';
$string['enrol'] = 'Enrol';
$string['enrolledcoursecohortids'] = 'Enrolled course audience IDs';
$string['enrolledprogramcohortids'] = 'Enrolled program audience IDs';
$string['enrolusing'] = 'Enrol with - {$a}';
$string['error:addscheduledreport'] = 'Error adding new Scheduled Report';
$string['error:bad_sesskey'] = 'There was an error because the session key did not match';
$string['error:cachenotfound'] = 'Cannot purge cache. Seems it is already clean.';
$string['error:column_not_deleted'] = 'There was a problem deleting that column';
$string['error:column_not_moved'] = 'There was a problem moving that column';
$string['error:column_vis_not_updated'] = 'Column visibility could not be updated';
$string['error:columnextranameid'] = 'Column extra field \'{$a}\' alias must not be \'id\''; // Obsolete.
$string['error:columnnameid'] = 'Field \'{$a}\' alias must not be \'id\'';
$string['error:columnoptiontypexandvalueynotfoundinz'] = 'Column option with type "{$a->type}" and value "{$a->value}" not found in source "{$a->source}"';
$string['error:columns_not_updated'] = 'There was a problem updating the columns.';
$string['error:couldnotcreatenewreport'] = 'Could not create new report';
$string['error:couldnotgenerateembeddedreport'] = 'There was a problem generating that report';
$string['error:couldnotsavesearch'] = 'Could not save search';
$string['error:couldnotupdateglobalsettings'] = 'There was an error while updating the global settings';
$string['error:couldnotupdatereport'] = 'Could not update report';
$string['error:creatingembeddedrecord'] = 'Error creating embedded record: {$a}';
$string['error:emailrequired'] = 'At least one recipient email address is required for export option you selected';
$string['error:emptyexportfilesystempath'] = 'If you enabled export to file system, you need to specify file system path.';
$string['error:failedtoremovetempfile'] = 'Failed to remove temporary report export file';
$string['error:filter_not_deleted'] = 'There was a problem deleting that filter';
$string['error:filter_not_moved'] = 'There was a problem moving that filter';
$string['error:filteroptiontypexandvalueynotfoundinz'] = 'Filter option with type "{$a->type}" and value "{$a->value}" not found in source "{$a->source}"';
$string['error:filters_not_updated'] = 'There was a problem updating the filters';
$string['error:fusion_oauthnotsupported'] = 'Fusion export via OAuth is not currently supported.';
$string['error:graphdeleteseries'] = 'This column is the data source for Graph construction. Please delete the column first under Graph tab.';
$string['error:graphisnotvalid'] = 'The report graph settings are invalid, please review.';
$string['error:grouphasreports'] = 'You cannot delete a group that is being used by reports.';
$string['error:groupnotcreated'] = 'Group could not be created';
$string['error:groupnotcreatedinitfail'] = 'Group could not be created - failed to initialize tables!';
$string['error:groupnotcreatedpreproc'] = 'Group could not be created - preprocessor not found!';
$string['error:groupnotdeleted'] = 'Group could not be deleted';
$string['error:invalidreportid'] = 'Invalid report ID';
$string['error:invalidreportscheduleid'] = 'Invalid scheduled report ID';
$string['error:invalidsavedsearchid'] = 'Invalid saved search ID';
$string['error:invalidsourceforfilter'] = 'Filter cannot be used with report source.';
$string['error:invaliduserid'] = 'Invalid user ID';
$string['error:joinsforfiltertypexandvalueynotfoundinz'] = 'Joins for filter with type "{$a->type}" and value "{$a->value}" not found in source "{$a->source}"';
$string['error:joinsfortypexandvalueynotfoundinz'] = 'Joins for columns with type "{$a->type}" and value "{$a->value}" not found in source "{$a->source}"';
$string['error:joinxhasdependencyyinz'] = 'Join name "{$a->join}" contains a dependency "{$a->dependency}" that does not exist in the joinlist for source "{$a->source}"';
$string['error:joinxisreservediny'] = 'Join name "{$a->join}" in source "{$a->source}" is an SQL reserved word. Please rename the join';
$string['error:joinxusedmorethanonceiny'] = 'Join name "{$a->join}" used more than once in source "{$a->source}"';
$string['error:missingdependencytable'] = 'In report source {$a->source}, missing dependency table in joinlist: {$a->join}!';
$string['error:mustselectsource'] = 'You must pick a source for the report';
$string['error:nocolumns'] = 'No columns found. Ask your developer to add column options to the \'{$a}\' source.';
$string['error:nocolumnsdefined'] = 'No columns have been defined for this report. Ask you site administrator to add some columns.';
$string['error:nocontentrestrictions'] = 'No content restrictions are available for this source. To use restrictions, ask your developer to add the necessary code to the \'{$a}\' source.';
$string['error:nographseries'] = 'There are no columns suitable for construction of a graph. You need to add some columns with numeric data to this report or set "Graph type" to "None".';
$string['error:nopdf'] = 'No PDF plugin found';
$string['error:norolesfound'] = 'No roles found';
$string['error:nosavedsearches'] = 'This report does not yet have any saved searches';
$string['error:nosources'] = 'No sources found. You must have at least one source before you can add reports. Ask your developer to add the necessary files to the codebase.';
$string['error:nosvg'] = 'SVG not supported';
$string['error:notapathexportfilesystempath'] = 'Specified file system path contains invalid characters.';
$string['error:notdirexportfilesystempath'] = 'Specified file system path does not exist or is not a directory.';
$string['error:notwriteableexportfilesystempath'] = 'Specified file system path is not writeable.';
$string['error:problemobtainingcachedreportdata'] = 'There was a problem obtaining the cached data for this report. It might be due to cache regeneration. Please, try again. If problem persist, disable cache for this report. <br /><br />{$a}';
$string['error:problemobtainingreportdata'] = 'There was a problem obtaining the data for this report: {$a}';
$string['error:processfile'] = 'Unable to create process file. Please, try later.';
$string['error:propertyxmustbesetiny'] = 'Property "{$a->property}" must be set in class "{$a->class}"';
$string['error:reportcacheinitialize'] = 'Cache is disabled for this report';
$string['error:reportgraphsdisabled'] = 'Report Builder graphs are not enabled on this site.';
$string['error:savedsearchnotdeleted'] = 'Saved search could not be deleted';
$string['error:unknownbuttonclicked'] = 'Unknown button clicked';
$string['error:updatescheduledreport'] = 'Error updating Scheduled Report';
$string['excludetags'] = 'Exclude records tagged with';
$string['export'] = 'Export';
$string['exportas'] = 'Export as';
$string['exportcsv'] = 'Export in CSV format';
$string['exportfilesystemoptions'] = 'Export options';
$string['exportfilesystempath'] = 'File export path';
$string['exportfilesystempath_help'] = 'Absolute file system path to a writeable directory where reports can be exported and stored.

**Warning!** Make sure to configure a correct system path if you are going to export reports to file system.';
$string['exportfusion'] = 'Export to Google Fusion';
$string['exportods'] = 'Export in ODS format';
$string['exportoptions'] = 'Format export options';
$string['exportpdf_landscape'] = 'Export in PDF (Landscape) format';
$string['exportpdf_mramlimitexceeded'] = 'Notice: Ram memory limit exceeded! Probably the report being exported is too big, as it took almost {$a} MB of ram memory to create it, please consider reducing the size of the report, applying filters or splitting the report in several files.';
$string['exportpdf_portrait'] = 'Export in PDF (Portrait) format';
$string['exportproblem'] = 'There was a problem downloading the file';
$string['exporttoemail'] = 'Email scheduled report';
$string['exporttoemailandsave'] = 'Email and save scheduled report to file';
$string['exporttofilesystem'] = 'Export to file system';
$string['exporttofilesystemenable'] = 'Enable exporting to file system';
$string['exporttosave'] = 'Save scheduled report to file system only';
$string['exportxls'] = 'Export in Excel format';
$string['externalemail'] = 'External email address to add';
$string['extrasqlshouldusenamedparams'] = 'get_sql_filter() extra sql should use named parameters';
$string['eventreportcloned'] = 'Report cloned';
$string['eventreportcreated'] = 'Report created';
$string['eventreportdeleted'] = 'Report deleted';
$string['eventreportexported'] = 'Report exported';
$string['eventreportupdated'] = 'Report updated';
$string['eventreportviewed'] = 'Report viewed';
$string['filter'] = 'Filter';
$string['filterby'] = 'Filter by';
$string['filtercheckboxallyes'] = 'All values "Yes"';
$string['filtercheckboxallno'] = 'All values "No"';
$string['filtercheckboxanyyes'] = 'Any value "Yes"';
$string['filtercheckboxanyno'] = 'Any value "No"';
$string['filterdeleted'] = 'Filter deleted';
$string['filtermoved'] = 'Filter moved';
$string['filternameformatincorrect'] = 'get_filter_joins(): filter name format incorrect. Query snippets may have included a dash character.';
$string['filters'] = 'Filters';
$string['filters_updated'] = 'Filters updated';
$string['filter_assetavailable'] = 'Available between';
$string['filter_assetavailable_help'] = 'This filter allows you to find assets that are available for a session by specifying the session start and end date.';
$string['filter_roomavailable'] = 'Available between';
$string['filter_roomavailable_help'] = 'This filter allows you to find rooms that are available for a session by specifying the session start and end date.';
$string['filtercontains'] = 'Any of the selected';
$string['filtercontainsnot'] = 'None of the selected';
$string['filterdisabledwarning'] = 'This report has changed due to the removal of one or more filters. Contact your site administrator for more details.';
$string['filterequals'] = 'All of the selected';
$string['filterequalsnot'] = 'Not all of the selected';
$string['financialyear'] = 'Financial year start';
$string['financialyeardaystart'] = 'Financial year day start';
$string['financialyearmonthstart'] = 'Financial year month start';
$string['format'] = 'Format';
$string['general'] = 'General';
$string['generalperformancesettings'] = 'General Performance Settings';
$string['globalinitialdisplay'] = 'Restrict initial display in all report builder reports';
$string['globalinitialdisplay_desc'] = 'When enabled, all user-generated reports with one or more filters will not display automatically upon page load. This improves performance by avoiding display of unwanted reports. Note: Reports with no filters will display automatically.';
$string['globalinitialdisplay_enabled'] = '\'Restrict initial display in all report builder reports\' setting has been enabled.';
$string['globalsettings'] = 'General settings';
$string['globalsettingsupdated'] = 'Global settings updated';
$string['gotofacetofacesettings'] = 'To view this report go to a seminar activity and use the \'Declared interest report\' link in the \'Seminar administration\' admin menu.';
$string['gradeandgradetocomplete'] = '{$a->grade}% ({$a->pass}% to complete)';
$string['graph'] = 'Graph';
$string['graphadvancedoptions'] = 'Advanced options';
$string['graphcategory'] = 'Category';
$string['graphlegend'] = 'Legend';
$string['graphmaxrecords'] = 'Maximum number of used records';
$string['graphnocategory'] = 'Numbered';
$string['graphorientation'] = 'Orientation';
$string['graphorientation_help'] = 'Determines how the report data is interpreted to build the graph. If **Data series in columns** is selected, then report builder will treat report columns as data series. In most cases this is what you want. If **Data series in rows** is selected, report builder treats every item in the column as a separate data series - data rows will be treated as data points. Typically you only want to select **Data series in rows** if you have more columns in your report than rows.';
$string['graphorientationcolumn'] = 'Data series in columns';
$string['graphorientationrow'] = 'Data series in rows';
$string['graphseries'] = 'Data sources';
$string['graphseries_help'] = 'Select one or more columns to use as data sources for the graph. Only columns with compatible numeric data are included.';
$string['graphsettings'] = 'Custom settings';
$string['graphsettings_help'] = 'Advanced SVGGraph settings in PHP ini file format. See <a href="http://www.goat1000.com/svggraph-settings.php" target="_blank">http://www.goat1000.com/svggraph-settings.php</a> for more information.';
$string['graphstacked'] = 'Stacked';
$string['graphtype'] = 'Graph type';
$string['graphtype_help'] = 'Select graph type to display a graph in report, select **None** to remove the graph from report.';
$string['graphtypearea'] = 'Area';
$string['graphtypebar'] = 'Horizontal bar';
$string['graphtypecolumn'] = 'Column';
$string['graphtypeline'] = 'Line';
$string['graphtypepie'] = 'Pie';
$string['graphtypescatter'] = 'Scatter';
$string['graph_updated'] = 'Graph updated';
$string['groupassignlist'] = '{$a->group}: {$a->entries}';
$string['groupconfirmdelete'] = 'Are you sure you want to delete this group?';
$string['groupcontents'] = 'This group currently contains {$a->count} feedback activities tagged with the <strong>\'{$a->tag}\'</strong> official tag:';
$string['groupdeleted'] = 'Group deleted.';
$string['groupingfuncnotinfieldoftypeandvalue'] = 'Grouping function \'{$a->groupfunc}\' doesn\'t exist in field of type \'{$a->type}\' and value \'{$a->$value}\'';
$string['groupname'] = 'Group name';
$string['grouptag'] = 'Group tag';
$string['heading'] = 'Heading';
$string['headingformat'] = '{$a->column} ({$a->type})';;
$string['help:columnsdesc'] = 'The choices below determine which columns appear in the report and how those columns are labelled.';
$string['help:restrictionoptions'] = 'The checkboxes below determine who has access to this report, and which records they are able to view. If no options are checked no results are visible. Click the help icon for more information';
$string['hidden'] = 'Hide in My Reports';
$string['hiddencellvalue'] = '&lt;hidden&gt;';
$string['hide'] = 'Hide';
$string['includeemptydates'] = 'Include record if date is missing';
$string['includerecordsfrom'] = 'Include records from';
$string['includesessionroles'] = 'Show event roles where user holds any of the selected event roles';
$string['includetags'] = 'Include records tagged with';
$string['includetrainerrecords'] = 'Include records from particular trainers';
$string['includeuserrecords'] = 'Include records from particular users';
$string['initialdisplay'] = 'Restrict Initial Display';
$string['initialdisplay_disabled'] = 'This setting is not available when there are no filters enabled';
$string['initialdisplay_error'] = 'The last filter can not be deleted when initial display is restricted';
$string['initialdisplay_heading'] = 'Filters Performance Settings';
$string['initialdisplay_help'] = 'This setting controls how the report is initially displayed and is recommended for larger reports where you will be filtering the results (e.g. sitelogs). It increases the speed of the report by allowing you to apply filters and display only the results instead of initially trying to display **all** the data.

* **Disabled**: The report will display all results immediately (default).
* **Enabled**: The report will not generate results until a filter is applied or an empty search is run.';
$string['initialdisplay_pending'] = 'Please apply a filter to view the results of this report, or hit search without adding any filters to view all entries';
$string['is'] = 'is';
$string['isaftertoday'] = 'days after today (date of report generation)';
$string['isbeforetoday'] = 'days before today (date of report generation)';
$string['isbelow'] = 'is below';
$string['isnotempty'] = 'is not empty (NOT NULL)';
$string['isnotfound'] = ' is NOT FOUND';
$string['isnt'] = 'isn\'t';
$string['isnttaggedwith'] = 'isn\'t tagged with';
$string['istaggedwith'] = 'is tagged with';
$string['joinnotinjoinlist'] = '\'{$a->join}\' not in join list for {$a->usage}';
$string['last30days'] = 'The last 30 days';
$string['lastcached'] = 'Last cached at {$a}';
$string['lastchecked'] = 'Last process date';
$string['lastfinancial'] = 'The previous financial year';
$string['lastlogin'] = 'Last Login';
$string['legacyreportlink'] = 'Looking for the original version of this report? {$a->link_start}You can find it here.{$a->link_end}';
$string['manageembeddedreports'] = 'Manage embedded reports';
$string['managereports'] = 'Manage reports';
$string['managername'] = 'Manager\'s Name';
$string['managesavedsearches'] = 'Manage searches';
$string['manageuserreports'] = 'Manage user reports';
$string['missingsearchname'] = 'Missing search name';
$string['mnetuser'] = 'Mnet user';
$string['mnetnotsupported'] = 'Mnet is no longer supported';
$string['monthly'] = 'Monthly';
$string['movedown'] = 'Move Down';
$string['moveup'] = 'Move Up';
$string['myreports'] = 'My Reports';
$string['name'] = 'Name';
$string['name_help'] = 'This name will be used to identify the restriction on reports.';
$string['newreport'] = 'New Report';
$string['newrestriction'] = 'Create a new restriction';
$string['newreportcreated'] = 'New report created. Click settings to edit filters and columns';
$string['next30days'] = 'The next 30 days';
$string['nice_time_unknown_timezone'] = 'Unknown Timezone';
$string['nocolumnsyet'] = 'No columns have been created yet - add them by selecting a column name in the pulldown below.';
$string['nocontentrestriction'] = 'Show all records';
$string['nodeletereport'] = 'Report could not be deleted';
$string['noembeddedreports'] = 'There are no embedded reports. Embedded reports are reports that are hard-coded directly into a page. Typically they will be set up by your site developer.';
$string['noemptycols'] = 'You must include a column heading';
$string['nofilteraskdeveloper'] = 'No filters found. Ask your developer to add filter options to the \'{$a}\' source.';
$string['nofilteroptions'] = 'This filter has no options to select';
$string['nofiltersetfortypewithvalue'] = 'get_field(): no filter set in filteroptions for type\'{$a->type}\' with value \'{$a->value}\'';
$string['nofiltersyet'] = 'No search fields have been created yet - add them by selecting a search term in the pulldown below.';
$string['noheadingcolumnsdefined'] = 'No heading columns defined';
$string['noneselected'] = 'None selected';
$string['nopermission'] = 'You do not have permission to view this page';
$string['norecordsinreport'] = 'There are no records in this report';
$string['norecordswithfilter'] = 'There are no records that match your selected criteria';
$string['noreloadreport'] = 'Report settings could not be reset';
$string['norepeatcols'] = 'You cannot include the same column more than once';
$string['norepeatfilters'] = 'You cannot include the same filter more than once';
$string['noreports'] = 'No reports have been created. You can create a report using the form below.';
$string['noreportscount'] = 'No reports using this group';
$string['norestriction'] = 'All users can view this report';
$string['norestrictionsfound'] = 'No restrictions found. Ask your developer to add restrictions to /local/reportbuilder/sources/{$a}/restrictionoptions.php';
$string['noroleselected'] = 'No role selected';
$string['noscheduledreports'] = 'There are no scheduled reports';
$string['nosearchcolumnsaskdeveloper'] = 'No search columns found. Ask your developer to define text and long text fields as searchable in the \'{$a}\' source.';
$string['nosearchcolumnsyet'] = 'No search columns have been added yet - add them by selecting a column in the pulldown below.';
$string['noshortnameorid'] = 'Invalid report id or shortname';
$string['notags'] = 'No official tags exist. You must create one or more official tags to base your groups on.';
$string['notassigned'] = 'Not assigned';
$string['notassignedanyrole'] = 'Not assigned any role';
$string['notassignedrole'] = 'Not assigned role \'{$a->role}\'';
$string['notcached'] = 'Not cached yet';
$string['notspecified'] = 'Not specified';
$string['notyetchecked'] = 'Not yet processed';
$string['nouserreports'] = 'You do not have any reports. Report access is configured by your site administrator. If you are expecting to see a report, ask them to check the access permissions on the report.';
$string['numcolumns'] = 'Number of columns';
$string['numfilters'] = 'Number of filters';
$string['numresponses'] = '{$a} response(s).';
$string['numscheduled'] = 'Number of scheduled reports';
$string['numsaved'] = 'Number of saved searches';
$string['occurredafter'] = 'occurred after';
$string['occurredbefore'] = 'occurred before';
$string['occurredprevfinancialyear'] = 'occurred in the previous financial year';
$string['occurredthisfinancialyear'] = 'occurred in this finanicial year';
$string['odsformat'] = 'ODS format';
$string['on'] = 'on';
$string['onlydisplayrecordsfor'] = 'Only display records for';
$string['onthe'] = 'on the';
$string['options'] = 'Options';
$string['or'] = ' or ';
$string['pdffont'] = 'PDF export font';
$string['pdffont_help'] = 'When exporting a report from the report builder as a PDF this is the font that will be used. If appropriate default is selected Totara will select a font that is suitable for the users language.';
$string['pdf_landscapeformat'] = 'pdf format (landscape)';
$string['pdf_portraitformat'] = 'pdf format (portrait)';
$string['performance'] = 'Performance';
$string['pluginadministration'] = 'Report Builder administration';
$string['pluginname'] = 'Report Builder';
$string['preprocessgrouptask'] = 'Preprocess report groups';
$string['processscheduledtask'] = 'Generate scheduled reports';
$string['programenrolledincohort'] = 'Program is enrolled in by audience';
$string['publicallyavailable'] = 'Let other users view';
$string['publicsearch'] = 'Is search public?';
$string['records'] = 'Records';
$string['recordstoview'] = 'View records related to';
$string['recordstoviewdescription'] = 'The reports will only display records related to users selected in the "View records related to" tab.';
$string['recordsperpage'] = 'Number of records per page';
$string['refreshcachetask'] = 'Refresh report cache';
$string['refreshdataforthisgroup'] = 'Refresh data for this group';
$string['reloadreport'] = 'Report settings have been reset';
$string['report'] = 'Report';
$string['report:cachelast'] = 'Report data last updated: {$a}';
$string['report:cachenext'] = 'Next update due: {$a}';
$string['report:completiondate'] = 'Completion date';
$string['report:coursetitle'] = 'Course title';
$string['report:enddate'] = 'End date';
$string['report:learner'] = 'Learner';
$string['report:learningrecords'] = 'Learning records';
$string['report:nodata'] = 'There is no available data for that combination of criteria, start date and end date';
$string['report:startdate'] = 'Start date';
$string['reportaccess'] = 'Report access';
$string['reportactions'] = 'Actions';
$string['reportbuilder'] = 'Report builder';
$string['reportbuilder:managereports'] = 'Create, edit and delete report builder user reports and manage report builder global settings';
$string['reportbuilder:manageembeddedreports'] = 'Create, edit and reset report builder embedded reports';
$string['reportbuilder:managereports'] = 'Create, edit and delete report builder reports';
$string['reportbuilder:overridescheduledfrequency'] = 'Override minimum scheduled report frequency';
$string['reportbuilderaccessmode'] = 'Access Mode';
$string['reportbuilderaccessmode_help'] = 'Access controls are used to restrict which users can view the report.

**Restrict access** sets the overall access setting for the report.

When set to **All users can view this report** there are no restrictions applied to the report and all users will be able to view the report.

When set to **Only certain users can view this report** the report will be restricted to the user groups selected below.

Note that access restrictions only control who can view the report, not which records it contains. See the **Content** tab for controlling the report contents.';
$string['reportbuilderbaseitem'] = 'Report Builder: Base item';
$string['reportbuilderbaseitem_help'] = 'By grouping a set of activities you are saying that they have something in common, which will allow reports to be generated for all the activities in a group. The base item defines the properties that are considered when aggregation is performed on each member of the group.';
$string['reportbuildercache'] = 'Enable report caching';
$string['reportbuildercache_disabled'] = 'This setting is not available for this report source';
$string['reportbuildercache_heading'] = 'Caching Performance Settings';
$string['reportbuildercache_help'] = 'If **Enable report caching** is checked, then a copy of this report will be generated on a set schedule, and users will see data from the stored report. This will make displaying and filtering of the report faster, but the data displayed will be from the last time the report was generated rather than \'live\' data. We recommend enabling this setting only if necessary (reports are taking too long to be displayed), and only for specific reports where this is a problem.';
$string['reportbuildercachescheduler'] = 'Cache Schedule (Server Time)';
$string['reportbuildercachescheduler_help'] = 'Determines the schedule used to control how often a new version of the report is generated. The report will be generated on the cron that immediately follows the specified time.

For example, if you have set up your cron to run every 20 minutes at 10, 30 and 50 minutes past the hour and you schedule a report to run at midnight, it will actually run at 10 minutes past midnight.';
$string['reportbuildercacheservertime'] = 'Current Server Time';
$string['reportbuildercacheservertime_help'] = 'All reports are being cached based on server time. Cache status shows you current local time which might be different from server time. Make sure to take into account your server time when scheduling cache.';
$string['reportbuildercolumns'] = 'Columns';
$string['reportbuildercolumns_help'] = '**Report Columns** allows you to customise the columns that appear on your report. The available columns are determined by the data **Source** of the report. Each report source has a set of default columns set up.

Columns can be added, removed, renamed and sorted.

**Adding Columns:** To add a new column to the report choose the required column from the **Add another column...** dropdown list and click **Save changes**. The new column will be added to the end of the list.

Note that you can only create one column of each type within a single report. You will receive a validation error if you try to include the same column more than once.

**Hiding columns:** By default all columns appear when a user views the report. Use the \'show/hide\' button (the eye icon) to hide columns you do not want users to see by default.

Note that a hidden column is still available to a user viewing the report. Delete columns (the cross icon) that you do not want users to see at all.

**Moving columns:** The columns will appear on the report in the order they are listed. Use the up and down arrows to change the order.

**Deleting columns:** Click the **Delete** button (the cross icon) to the right of the report column to remove that column from the report.

**Renaming columns:** You can customise the name of a column by changing the **Heading** name and clicking **Save changes**. The **Heading** is the name that will appear on the report.

**Changing multiple column types:** You can modify multiple column types at the same time by selecting a different column from the dropdown menu and clicking **Save changes**.';
$string['reportbuildercontentmode'] = 'Content Mode';
$string['reportbuildercontentmode_help'] = 'Content controls allow you to restrict the records and information that are available when a report is viewed.

**Report content** allows you to select the overall content control settings for this report:

When **Show all records** is selected, every available record for this source will be shown and no restrictions will be placed on the content available.

When **Show records matching any of the checked criteria** is selected the report will display records that match any of the criteria set below.

Note that if no criteria is set the report will display no records.

When **Show records matching all of the checked criteria** is selected the report will display records that match all the criteria set below.
Note that if no criteria is set the report will display no records.';
$string['reportbuildercontext'] = 'Restrict Access by Role';
$string['reportbuildercontext_help'] = 'Context is the location or level within the system that the user has access to. For example a Site Administrator would have System level access (context), while a learner may only have Course level access (context).

**Context** allows you to set the context in which a user has been assigned a role to view the report.

A user can be assigned a role at the system level giving them site wide access or just within a particular context. For instance a trainer may only be assigned the role at the course level.

When **Users must have role in the system context** is selected the user must be assigned the role at a system level (i.e. at a site-wide level) to be able to view the report.

When **User may have role in any context** is selected a user can view the report when they have been assigned the selected role anywhere in the system.';
$string['reportbuilderdate'] = 'Show by date';
$string['reportbuilderdate_help'] = 'When **Show records based on the record date** is selected the report only displays records within the selected timeframe.

The **Include records from** options allow you to set the timeframe for the report:

*   When set to **The past** the report only shows records with a date older than the current date.
*   When set to **The future** the report only shows records with a future date set from the current date.
*   When set to **The last 30 days** the report only shows records between the current time and 30 days before.
*   When set to **The next 30 days** the report only shows records between the current time and 30 days into the future.';
$string['reportbuilderdescription'] = 'Description';
$string['reportbuilderdescription_help'] = 'When a report description is created the information displays in a box above the search filters on the report page.';
$string['reportbuilderdialogfilter'] = 'Report Builder: Dialog filter';
$string['reportbuilderdialogfilter_help'] = 'This filter allows you to filter information based on a hierarchy. The filter has the following options:

*   **is any value**: This option disables the filter (i.e. all information is accepted by this filter).
*   **is equal to**: This option allows only information that is equal to the value selected from the list.
*   **is not equal to**: This option allows only information that is different from the value selected from the list.

Once a framework item has been selected you can use the **Include children?** checkbox to choose whether to match only that item, or match that item and any sub-items belonging to that item.';
$string['reportbuilderexportoptions'] = 'Report Export Settings';
$string['reportbuilderexportoptions_help'] = 'Report export settings allows a user to specify the export options that are available for users at the bottom of a report page. This setting affects all Report builder reports.

When multiple options are selected the user can choose their preferred options from the export dropdown menu.

When no options are selected the export function is disabled.';
$string['reportbuilderexporttofilesystem'] = 'Enable exporting to file system';
$string['reportbuilderexporttofilesystem_help'] = 'Exporting to file system allows reports to be saved to a directory on the web server\'s file system, instead of only emailing the report to the user scheduling the report.

This can be useful when the report needs to be accessed by an external system automation, and the report directory might have SFTP access enabled.

Reports saved to the filesystem are saved as **\'Export file system root path\'**/username/report.ext where **username** is an internal username of a user who owns the scheduled report, **report** is the name of the scheduled report with non alpha-numeric characters removed, and **ext** is the appropriate export file name extension.';
$string['reportbuilderfilters'] = 'Search Options (Filters)';
$string['reportbuilderfilters_help'] = '**Search Options** allows you to customise the filters that appear on your report. The available filters are determined by the **Source** of the report. Each report source has a set of default filters.

Filters can be added, sorted and removed.

**Adding filters:** To add a new filter to the report choose the required filter from the **Add another filter...** dropdown menu and click **Save changes**. When **Advanced** is checked the filter will not appear in the **Search by** box by default, you can click **Show advanced** when viewing a report to see these filters.

**Moving filters:** The filters will appear in the **Search by** box in the order they are listed. Use the up and down arrows to change the order.

**Deleting filters:** Click the **Delete** button (the cross icon) to the right of the report filter to remove that filter from the report.

**Changing multiple filter types:** You can modify multiple filter types at the same time by selecting a different filter from the dropdown menu and clicking **Save changes**.';
$string['reportbuilderfinancialyear'] = 'Report Financial Year Settings';
$string['reportbuilderfinancialyear_help'] = 'This setting allows to set the start date of the financial year which is used in the reports content controls.';
$string['reportbuilderfullname'] = 'Report Name';
$string['reportbuilderfullname_help'] = 'This is the name that will appear at the top of your report page and in the **Report Manager** block.';
$string['reportbuilderglobalsettings'] = 'Report Builder Global Settings';
$string['reportbuildergroupname'] = 'Report Builder: Group Name';
$string['reportbuildergroupname_help'] = 'The name of the group. This will allow you to identify the group when you want to create a new report based on it. Look for the name in the report source pulldown menu.';
$string['reportbuildergrouptag'] = 'Report Builder: Group Tag';
$string['reportbuildergrouptag_help'] = 'When you create a group using a tag, any activities that are tagged with the official tag specified automatically form part of the group. If you add or remove tags from an activity, the group will be updated to include/exclude that activity.';
$string['reportbuilderhidden'] = 'Hide in My Reports';
$string['reportbuilderhidden_help'] = 'When **Hide in My Reports** is checked the report will not appear on the **My Reports** page for any logged in users.Note that the **Hide in My Reports** option only hides the link to the report. Users with the correct access permissions may still access the report using the URL.';
$string['reportbuilderinitcache'] = 'Cache Status (User Time)';
$string['reportbuilderrecordsperpage'] = 'Number of Records per Page';
$string['reportbuilderrecordsperpage_help'] = '**Number of records per page** allows you define how many records display on a report page.

The maximum number of records that can be displayed on a page is 9999. The more records set to display on a page the longer the report pages take to display.

Recommendation is to **limit the number of records per page to 40**.';
$string['reportbuilderrolesaccess'] = 'Roles with Access';
$string['reportbuilderrolesaccess_help'] = 'When **Restrict access** is set to **Only certain users can view this report** you can specify which roles can view the report using **Roles with permission to view the report**.

You can select one or multiple roles from the list.

When **Restrict access** is set to **All users can view this report** these options will be disabled.';
$string['reportbuildershortname'] = 'Report Builder: Unique name';
$string['reportbuildershortname_help'] = 'The shortname is used by Totara to keep track of this report. No two reports can be given the same shortname, even if they are based on the same source. Avoid using special characters in this field (text, numbers and underscores are okay).';
$string['reportbuildersorting'] = 'Sorting';
$string['reportbuildersorting_help'] = '**Sorting** allows you to set a default column and sort order on a report.

A user is still able to manually sort a report while viewing it. The users preferences will be saved during the active session. When they finish the session the report will return to the default sort settings set here.';
$string['reportbuildersource'] = 'Source';
$string['reportbuildersource_help'] = 'The **Source** of a report defines the primary type of data used. Further filtering options are available once you start editing the report.

Once saved, the report source cannot be changed.

Note that if no options are available in the **Source** field, or the source you require does not appear you will need your Totara installation to be configured to include the source data you require (this cannot be done via the Totara interface).';
$string['reportbuildertag'] = 'Report Builder: Show by tag';
$string['reportbuildertag_help'] = 'This criteria is enabled by selecting the **Show records by tag** checkbox. If selected, the report will show results based on whether the record belongs to an item that is tagged with particular tags.

If any tags in the **Include records tagged with** section are selected, only records belonging to an item tagged with all the selected tags will be shown. Records belonging to items with no tags will **not** be shown.

If any tags in the **Exclude records tagged with** section are selected, records belonging to a coures tagged with the selected tags will **not** be shown. All records belonging to items without any tags will be shown.

It is possible to include and exclude tags at the same time, but a single tag cannot be both included and excluded.';
$string['reportbuildertrainer'] = 'Report Builder: Show by trainer';
$string['reportbuildertrainer_help'] = 'This criteria is enabled by selecting the **Show records by trainer** checkbox. If selected, then the report will show different records depending on who the seminar trainer was for the feedback being given.

If **Show records where the user is the trainer** is selected, the report will show feedback for sessions where the user viewing the report was the trainer.

If **Records where one of the user\'s direct reports is the trainer** is selected, then the report will show records for sessions trained by staff of the person viewing the report.

If **Both** is selected, then both of the above records will be shown.';
$string['reportbuilderuser'] = 'Show by User';
$string['reportbuilderuser_help'] = 'When **Show records by user** is selected the report will show different records depending on the user viewing the report and their relationship to other users.

**Include records from a particular user** controls what records a user viewing the report can see:

*   When **A user\'s own records** is checked the user can see their own records.

If multiple options are selected the user sees records that match any of the selected options.';
$string['reportcachingdisabled'] = 'Report caching is disabled. <a href="{$a}">Enable report caching here</a>';
$string['reportcachingincompatiblefilter'] = 'Filter "{$a}" is not compatible with report caching.';
$string['reportcolumns'] = 'Report Columns';
$string['reportconfirmdelete'] = 'Are you sure you want to delete the report "{$a}"?';
$string['reportconfirmreload'] = '"{$a}" is an embedded report so you cannot delete it (that must be done by your site developer). You can choose to reset the report settings to their original values. Do you want to continue?';
$string['reportcontents'] = 'This report contains records matching the following criteria:';
$string['reportcount'] = '{$a} report(s) based on this group:';
$string['reportembedded'] = 'Is embedded report?';
$string['reporthidden'] = 'Is hidden on My Reports?';
$string['reportid'] = 'Report ID';
$string['reportmustbedefined'] = 'Report must be defined';
$string['reportname'] = 'Report Name';
$string['reportnamelinkedit'] = 'Name (linked to edit report)';
$string['reportnamelinkeditview'] = 'Name (linked to edit report) and view link';
$string['reportnamelinkview'] = 'Name (linked to view report)';
$string['reportperformance'] = 'Performance settings';
$string['reports'] = 'Reports';
$string['reportsdirectlyto'] = 'reports directly to';
$string['reportsindirectlyto'] = 'reports indirectly to';
$string['reportsettings'] = 'Report Settings';
$string['reportshortname'] = 'Short Name';
$string['reportshortnamemustbedefined'] = 'Report shortname must be defined';
$string['reportsource'] = 'Source';
$string['reporttitle'] = 'Report Title';
$string['reporttype'] = 'Report type';
$string['reportupdated'] = 'Report Updated';
$string['reportwithidnotfound'] = 'Report with id of \'{$a}\' not found in database.';
$string['restoredefaults'] = 'Restore Default Settings';
$string['restrictaccess'] = 'Restrict access';
$string['restrictcontent'] = 'Report content';
$string['restriction'] = 'Restriction';
$string['restrictionallrecords'] = 'All records without any restrictions.';
$string['restrictionallusers'] = 'Restriction is available to all users.';
$string['restrictionactivated'] = 'Restriction "{$a}" has been activated.';
$string['restrictioncreated'] = 'New restriction "{$a}" has been created.';
$string['restrictiondeactivated'] = 'Restriction "{$a}" has been deactivated.';
$string['restrictiondeleted'] = 'Restriction "{$a}" has been deleted.';
$string['restrictiondisableallrecords'] = 'Restrict which records can be viewed';
$string['restrictiondisableallusers'] = 'Restrict which users can use this restriction';
$string['restrictionenableallrecords'] = 'Allow all records to be viewed with this restriction';
$string['restrictionenableallusers'] = 'Make this restriction available to all users';
$string['restrictionupdated'] = 'Restriction "{$a}" has been updated.';
$string['restrictedusers'] = 'Users allowed to select restriction';
$string['restrictedusersdescription'] = 'Users selected in the "Users allowed to select restriction" tab will be allowed to use the restriction in reports with enabled "Global report restrictions".<br/>Please note: Users with only one restriction will have it automatically applied and they will not see any restriction choice notifications.';
$string['restrictionswarning'] = '<strong>Warning:</strong> If none of these boxes are checked, all users will be able to view all available records from this source.';
$string['resultsfromfeedback'] = 'Results from <strong>{$a}</strong> completed feedback(s).';
$string['roleswithaccess'] = 'Roles with permission to view this report';
$string['savedsearch'] = 'Saved Search';
$string['savedsearchconfirmdelete'] = 'Are you sure you want to delete this saved search  \'{$a}\'?';
$string['savedsearchdeleted'] = 'Saved search deleted';
$string['savedsearchdesc'] = 'By giving this search a name you will be able to easily access it later or save it to your bookmarks.';
$string['savedsearches'] = 'Saved Searches';
$string['savedsearchinscheduleddelete'] = 'This saved search is currently being used in the following scheduled reports: <br/> {$a} <br/> Deleting this saved search will delete these scheduled reports.';
$string['savedsearchmessage'] = 'Only the data matching the \'{$a}\' search is included.';
$string['savedsearchnotfoundornotpublic'] = 'Saved search not found or search is not public';
$string['savesearch'] = 'Save this search';
$string['saving'] = 'Saving...';
$string['schedule'] = 'Schedule';
$string['scheduledaily'] = 'Daily';
$string['scheduledemailtosettings'] = 'Email Settings';
$string['scheduledreportfrequency'] = 'Minimum scheduled report frequency';
$string['scheduledreportfrequency_desc'] = 'This setting allows you to set the minimum period a report can be run in, this is useful to prevent reports being run too frequently on larger sites and thus causing slowness for your system';
$string['scheduledreportmessage'] = 'Attached is a copy of the \'{$a->reportname}\' report in {$a->exporttype}. {$a->savedtext}

You have been sent this report by {$a->sender}.
The report shows the data {$a->sender} has access to; YOU may see different results when viewing the report online.

You can also view this report online at:

{$a->reporturl}

You are scheduled to receive this report {$a->schedule}.
To delete or update your scheduled report settings, visit:

{$a->scheduledreportsindex}';
$string['scheduledreports'] = 'Scheduled Reports';
$string['scheduledreportsettings'] = 'Scheduled report settings';
$string['schedulemonthly'] = 'Monthly';
$string['scheduleneedssavedfilters'] = 'This report cannot be scheduled without a saved search.
To view the report, click <a href="{$a}">here</a>';
$string['schedulenotset'] = 'Schedule not set';
$string['scheduleweekly'] = 'Weekly';
$string['search'] = 'Search';
$string['searchby'] = 'Search by';
$string['searchcolumndeleted']=  'Search column deleted';
$string['searchfield'] = 'Search Field';
$string['searchname'] = 'Search Name';
$string['searchoptions'] = 'Report Search Options';
$string['selectitem'] = 'Select item';
$string['selectmanagers'] = 'Select Managers';
$string['selectsource'] = 'Select a source...';
$string['sessionroles_txtrestr'] = '{$a->rolelocalnames} {$a->title} AND {$a->userfullname}';
$string['settings'] = 'Settings';
$string['shortnametaken'] = 'That shortname is already in use';
$string['show'] = 'Show';
$string['showbasedonx'] = 'Show records based on {$a}';
$string['showbydate'] = 'Show by date';
$string['showbytag'] = 'Show by tag';
$string['showbytrainer'] = 'Show by trainer';
$string['showbyuser'] = 'Show by user';
$string['showbyx'] = 'Show by {$a}';
$string['showhidecolumns'] = 'Show/Hide Columns';
$string['showing'] = 'Showing';
$string['showtotalcount'] = 'Display a total count of records';
$string['showtotalcount_help'] = 'When enabled the report will display a total count of records when not filtered. For performance reasons we recommend you leave this setting off.';
$string['sidebarfilter'] = 'Sidebar filter options';
$string['sidebarfilterdesc'] = 'The choices below determine which filters appear to the side of the report and how they are labelled.';
$string['sidebarfilter_help'] = '**Sidebar filter options** allows you to customise the filters that appear to the side of your report. Sidebar filters have
instant filtering enabled - each change made to a filter will automatically refresh the report data (if certain system
requirements are met). The available filters are determined by the **Source** of the report. Only some types of filters can
be placed in the sidebar, so not all standard filters can be placed there. Each report source has a set of default filters.

A filter can appear in either the standard filter area or the sidebar filter area, but not both. Filters can be added, sorted
and removed.

**Adding filters:** To add a new filter to the report choose the required filter from the **Add another filter...** dropdown
menu and click **Save changes**. When **Advanced** is checked the filter will not appear in the **Search by** box by default,
you can click **Show advanced** when viewing a report to see these filters.

**Moving filters:** The filters will appear in the **Search by** box in the order they are listed. Use the up and down arrows
to change the order.

**Deleting filters:** Click the **Delete** button (the cross icon) to the right of the report filter to remove that filter
from the report.

**Changing multiple filter types:** You can modify multiple filter types at the same time by selecting a different filter
from the dropdown menu and clicking **Save changes**.';
$string['sorting'] = 'Sorting';
$string['source'] = 'Source';
$string['standardfilter'] = 'Standard filter options';
$string['standardfilterdesc'] = 'The choices below determine which filter will appear above the report and how they are labelled.';
$string['standardfilter_help'] = '**Standard filter options** allows you to customise the filters that appear above your report. The available filters are
determined by the **Source** of the report. Each report source has a set of default filters.

A filter can appear in either the standard filter area or the sidebar filter area, but not both. Filters can be added, sorted
and removed.

**Adding filters:** To add a new filter to the report choose the required filter from the **Add another filter...** dropdown
menu and click **Save changes**. When **Advanced** is checked the filter will not appear in the **Search by** box by default,
you can click **Show advanced** when viewing a report to see these filters.

**Moving filters:** The filters will appear in the **Search by** box in the order they are listed. Use the up and down arrows
to change the order.

**Deleting filters:** Click the **Delete** button (the cross icon) to the right of the report filter to remove that filter
from the report.

**Changing multiple filter types:** You can modify multiple filter types at the same time by selecting a different filter
from the dropdown menu and clicking **Save changes**.';
$string['suspendrecord'] = 'Suspend {$a}';
$string['suspendedonly'] = 'Suspended users only';
$string['suspendeduser'] = 'Suspended user';
$string['systemcontext'] = 'Users must have role in the system context';
$string['systemusers'] = 'System users';
$string['tagenable'] = 'Show records by tag';
$string['taggedx'] = 'Tagged \'{$a}\'';
$string['tagids'] = 'Tag IDs';
$string['tags'] = 'Tags';
$string['thefuture'] = 'The future';
$string['thepast'] = 'The past';
$string['toolbarsearch'] = 'Toolbar search box';
$string['toolbarsearch_help'] = '**Toolbar search box** allows you to customise the fields that will be searched when using the search box in the report header.
The available filters are determined by the **Source** of the report. Each report source has a set of default fields. If no
fields are specified then the search box is not displayed.

You can specify that a field is searched, even if it is not included as a column in the report, although this may cause
confusion for users if they cannot see why a particular record is included in their search results.

**Adding search fields:** To add a new search field to the report choose the required field from the **Add another search
field...** dropdown menu and click **Save changes**.

**Delete search fields:** Click the **Delete** button (the cross icon) to the right of the report field to remove that
search field.

**Changing multiple search fields:** You can modify multiple search fields at the same time by selecting a different field
from the dropdown menu and clicking **Save changes**.';
$string['toolbarsearchdesc'] = 'The choices below determine which fields will be searched when a user enters text in the toolbar search box.';
$string['toolbarsearchdisabled'] = 'Disable toolbar search box';
$string['toolbarsearchdisabled_help'] = 'Checking this box will prevent the search box from appearing in the header of the
report. This has the same result as removing all search fields.';
$string['toolbarsearchtextiscontainedinsingle'] = '"{$a->searchtext}" is contained in the column "{$a->field}"';
$string['toolbarsearchtextiscontainedinmultiple'] = '"{$a}" is contained in one or more of the following columns: ';
$string['trainerownrecords'] = 'Show records where the user is the trainer';
$string['trainerstaffrecords'] = 'Records where one of the user\'s direct reports is the trainer';
$string['transformtypeday_heading'] = '{$a} - day of month';
$string['transformtypeday_name'] = 'Day of month';
$string['transformtypedayyear_heading'] = '{$a} - day of year';
$string['transformtypedayyear_name'] = 'Day of year';
$string['transformtypehour_heading'] = '{$a} - hour of day';
$string['transformtypehour_name'] = 'Hour of day';
$string['transformtypemonth_heading'] = '{$a} - month of year';
$string['transformtypemonth_name'] = 'Month of year';
$string['transformtypemonthtextual_heading'] = '{$a} - month of year';
$string['transformtypemonthtextual_name'] = 'Month of year(textual)';
$string['transformtypequarter_heading'] = '{$a} - quarter of year';
$string['transformtypequarter_name'] = 'Quarter of year';
$string['transformtypeweekday_heading'] = '{$a} - week day';
$string['transformtypeweekday_name'] = 'Week day';
$string['transformtypeweekdaytextual_heading'] = '{$a} - week day';
$string['transformtypeweekdaytextual_name'] = 'Week day(textual)';
$string['transformtypeyear_heading'] = '{$a}';
$string['transformtypeyear_name'] = 'Date YYYY';
$string['transformtypeyearmonth_heading'] = '{$a}';
$string['transformtypeyearmonth_name'] = 'Date YYYY-MM';
$string['transformtypeyearmonthday_heading'] = '{$a}';
$string['transformtypeyearmonthday_name'] = 'Date YYYY-MM-DD';
$string['transformtypeyearquarter_heading'] = '{$a} - year quarter';
$string['transformtypeyearquarter_name'] = 'Date YYYY-Q';
$string['type'] = 'Type';
$string['type_cohort'] = 'Audience';
$string['type_course'] = 'Course';
$string['type_course_category'] = 'Category';
$string['type_course_custom_fields'] = 'Course Custom Fields';
$string['type_statistics'] = 'Statistics';
$string['type_tags'] = 'Tags';
$string['type_user'] = 'User';
$string['type_userto'] = 'Recipient User';
$string['type_user_profile'] = 'User Profile';
$string['unconfirmedonly'] = 'Unconfirmed users only';
$string['unconfirmeduser'] = 'Unconfirmed user';
$string['uniquename'] = 'Unique Name';
$string['unknown'] = 'Unknown';
$string['unknownlanguage'] = 'Unknown Language ({$a})';
$string['uninstalledlanguage'] = 'Uninstalled Language {$a->name} ({$a->code})';
$string['updatescheduledreport'] = 'Successfully updated Scheduled Report';
$string['useclonedb'] = 'Use database clone';
$string['useclonedb_help'] = 'If enabled the report will use the database clone. This may improve performance, but the data may be outdated if the clone is not synchronised properly with the main database. This option is not compatible with standard report caching.';
$string['useclonedbheader'] = 'Database connection';
$string['useralternatename'] = 'User Alternate Name';
$string['useraddress'] = 'User\'s Address';
$string['userauth'] = 'User\'s Authentication Method';
$string['usercity'] = 'User\'s City';
$string['usercohortids'] = 'User audience IDs';
$string['usercountry'] = 'User\'s Country';
$string['userdepartment'] = 'User\'s Department';
$string['useremail'] = 'User\'s Email';
$string['useremailprivate'] = 'Email is private';
$string['useremailunobscured'] = 'User\'s Email (ignoring user display setting)';
$string['userfirstaccess'] = 'User First Access';
$string['userfirstaccessrelative'] = 'User First Access (Relative)';
$string['userfirstname'] = 'User First Name';
$string['userfirstnamephonetic'] = 'User First Name - phonetic';
$string['userfullname'] = 'User\'s Fullname';
$string['usergenerated'] = 'User generated';
$string['usergeneratedreports'] = 'User generated Reports';
$string['userid'] = 'User ID';
$string['useridnumber'] = 'User ID Number';
$string['userincohort'] = 'User is a member of audience';
$string['userinstitution'] = 'User\'s Institution';
$string['userlang'] = 'User\'s Preferred Language';
$string['userlastlogin'] = 'User Last Login';
$string['userlastloginrelative'] = 'User Last Login (Relative)';
$string['userlastname'] = 'User Last Name';
$string['userlastnamephonetic'] = 'User Last Name - phonetic';
$string['usermiddlename'] = 'User Middle Name';
$string['username'] = 'Username';
$string['usernamelink'] = 'User\'s Fullname (linked to profile)';
$string['usernamelinkicon'] = 'User\'s Fullname (linked to profile with icon)';
$string['userownrecords'] = 'A user\'s own records';
$string['userphone'] = 'User\'s Phone number';
$string['userreportheading'] = 'Browse list of users: {$a}';
$string['userreports'] = 'User reports';
$string['usersmanagerall'] = 'User\'s Manager(s)';
$string['usersmanagerfirstname'] = 'User\'s Manager\'s First Name';
$string['usersmanagerfirstnameall'] = 'User\'s Manager\'s First Name(s)';
$string['usersmanageremail'] = 'User\'s Manager Email';
$string['usersmanageremailunobscured'] = 'User\'s Manager\'s Email (ignoring user display setting)';
$string['usersmanagerid'] = 'User\'s Manager ID';
$string['usersmanageridall'] = 'User\'s Manager ID(s)';
$string['usersmanageridnumber'] = 'User\'s Manager ID Number';
$string['usersmanageridnumberall'] = 'User\'s Manager ID Number(s)';
$string['usersmanagerlastname'] = 'User\'s Manager\'s Last Name';
$string['usersmanagerlastnameall'] = 'User\'s Manager\'s Last Name(s)';
$string['usersmanagername'] = 'User\'s Manager Name';
$string['usersmanagernameall'] = 'User\'s Manager Name(s)';
$string['usersmanagerobsemailall'] = 'User\'s Manager Email(s)';
$string['usersmanagerunobsemailall'] = 'User\'s Manager Email(s) (ignoring user display setting)';
$string['userstatus'] = 'User Status';
$string['usersystemrole'] = 'User System Role';
$string['usertimecreated'] = 'User Creation Time';
$string['usertimemodified'] = 'User Last Modified';
$string['undeleterecord'] = 'Undelete {$a}';
$string['unsuspendrecord'] = 'Unsuspend {$a}';
$string['unlockrecord'] = 'Unlock {$a}';
$string['value'] = 'Value';
$string['viewreport'] = 'View This Report';
$string['viewsavedsearch'] = 'View a saved search...';
$string['warngroupaggregation'] = 'This report is using data aggregation internally, custom aggregation of columns may produce unexpected results.';
$string['warngrrvisibility'] = 'Recipients of this report will be sent the report as YOU see it. If you have access to different data, ensure you are happy for recipients to see what you see.';
$string['warnrequiredcolumns'] = 'This report uses some columns internally in order to obtain the data. Custom aggregation of columns may produce unexpected results.';
$string['weekly'] = 'Weekly';
$string['withcontentrestrictionall'] = 'Show records matching <strong>all</strong> of the checked criteria below';
$string['withcontentrestrictionany'] = 'Show records matching <strong>any</strong> of the checked criteria below';
$string['withrestriction'] = 'Only certain users can view this report (any criteria below)';
$string['withrestriction_all'] = 'Only certain users can view this report (all criteria below)';
$string['xlsformat'] = 'Excel format';
$string['xofyrecord'] = '{$a->filtered} of {$a->unfiltered} record shown';
$string['xofyrecords'] = '{$a->filtered} of {$a->unfiltered} records shown';
$string['xrecord'] = '{$a} record shown';
$string['xrecords'] = '{$a} records shown';

/**
 * Deprecated strings.
 *
 * @deprecated since Totara 10.0.
 */

$string['allreports'] = 'All Reports';
$string['error:reporturlnotset'] = 'The url property for report {$a} is missing, please ask your developers to check your code';
$string['isrelativetotoday'] = ' (date of report generation)';

$string['strfdateattime'] = '%d %b %Y at %H:%M';

$string['grade'] = 'Grade';
$string['employment_category'] = 'Employment category';
$string['discipline_name'] = 'Discipline name';
$string['group_name'] = 'Group name';
$string['companycentrearupunit'] = 'Accounting Centre';
$string['location_name'] = 'Office Location';
$string['centre_code'] = 'Centre code';
$string['company_code'] = 'Company code';
$string['region_name'] = 'Region';
$string['classname'] = 'Class name';
$string['classtype'] = 'Class type';
$string['classcoursename'] = 'Class course name';
$string['location'] = 'Class location';
$string['classstartdate'] = 'Class start date';
$string['classenddate'] = 'Class end date';
$string['classduration'] = 'Class duration';
$string['classcost'] = 'Class cost';
$string['bookingstatus'] = 'Class booking status';
$string['learningdesc'] = 'Class learning description';
$string['classcategory'] = 'Class category';
$string['provider'] = 'Class provider';
$string['cpd'] = 'Learning burst';
$string['bookingplaceddate'] = 'Class booking date';
$string['expirydate'] = 'Class expiry date';
$string['coursecode'] = 'Class course code';
$string['courseregion'] = 'Class course region';
$string['tapsarchived'] = 'Exclude archived courses';
$string['excludearchivedcontent'] = 'Exclude archived content';
$string['tapscoursecode'] = 'Taps course code';
$string['tapscourseregion'] = 'Taps course region';
$string['tapscoursename'] = 'Taps course name';

$string['employee_number'] = 'Hub Staff ID';
$string['first_name'] = 'Hub First Name';
$string['middle_names'] = 'Hub Middle Names';
$string['last_name'] = 'Hub Last Name';
$string['known_as'] = 'Hub Known As';
$string['full_name'] = 'Hub Full Name';
$string['email_address'] = 'Hub Email Address';
$string['internal_location'] = 'Hub Internal Location';
$string['latest_hire_date'] = 'Hub Latest Hire Date';
$string['core_job_title'] = 'Hub Core Job Title';
$string['geo_region'] = 'Hub GEO Region';
$string['company_code'] = 'Hub Company Code';
$string['centre_code'] = 'Hub Centre Code';
$string['centre_name'] = 'Hub Centre Name';
$string['companycentrearupunit'] = 'Hub Company Centre Arup Unit';
$string['sup_employee_number'] = 'Hub SUP Employee Number';
$string['employment_category'] = 'Hub Employment Category';
$string['assignment_status'] = 'Hub Assignment Status';
$string['region_name'] = 'Hub Region Name';
$string['location_name'] = 'Hub Location Name';
$string['leaver_flag'] = 'Hub Leaver Flag';
$string['actual_termination_date'] = 'Hub Actual Termination Date';
$string['discipline_code'] = 'Hub Discipline Code';
$string['discipline_name'] = 'Hub Discipline Name';
$string['group_code'] = 'Hub Group Code';
$string['group_name'] = 'Hub Group Name';
$string['staffgrade'] = 'Hub Grade';

$string['invalidemail'] = 'Invalid email';
$string['type_arupstaff'] = 'Arup staff record';

$string['activitycompletionunlockedtext'] = 'When you save changes, completion state for all learners who have completed this activity will be erased. If you change your mind about this, do not save the form.';
$string['activitycompletionunlockednoresettext'] = 'Completion has been unlocked without deleting activity completion data. After this change different users may have received their completion status for different reasons.';
$string['addanothercolumn'] = 'Add another column...';
$string['allf2fbookings'] = 'All Seminar Bookings';
$string['alllearningrecords'] = 'All Learning Records';
$string['allmycourses'] = 'All My Courses';
$string['allteammembers'] = 'All Team Members';
$string['alreadyselected'] = '(already selected)';
$string['ampersand'] = 'and';
$string['archivecompletionrecords'] = 'Archive completion records';
$string['assessments'] = 'Assessments';
$string['assessmenttype'] = 'Assessment Type';
$string['assessor'] = 'Assessor';
$string['assessorname'] = 'Assessor Name';
$string['assignedvia'] = 'Assigned Via';
$string['assigngroup'] = 'Assign User Group';
$string['assigngrouptype'] = 'Assignment Type';
$string['assignincludechildren'] = ' and all below';
$string['assignincludechildrengroups'] = 'Include Child Groups?';
$string['assignnumusers'] = 'Assigned Users';
$string['assignsourcename'] = 'Assigned Group';
$string['assignuser'] = 'Individual assignment';
$string['assigneduser'] = 'Assigned users';
$string['authdeleteusers'] = 'User deletion';
$string['authdeleteusers_desc'] = 'Select what happens when user account is deleted. During full delete username, email and ID number are discarded - this means that accounts cannot be undeleted later. Please note that any user delete operation discards settings and user information.';
$string['authdeleteusersfull'] = 'Full';
$string['authdeleteuserspartial'] = 'Keep username, email and ID number';
$string['blended'] = 'Blended';
$string['bookings'] = 'Bookings';
$string['bookingsfor'] = 'Bookings for ';
$string['browse'] = 'Browse';
$string['browsecategories'] = 'Browse Categories';
$string['cachedef_completion_progressinfo'] = 'Completion progressinfo cache';
$string['cachedef_flex_icons'] = 'Flex icons';
$string['cachedef_hookwatchers'] = 'Hook watchers';
$string['calendar'] = 'Calendar';
$string['cannotdownloadtotaralanguageupdatelist'] = 'Cannot download list of language updates from download.totaralms.com';
$string['cannotundeleteuser'] = 'Cannot undelete user';
$string['cloudconfigoverride'] = 'This setting is not available on Totara cloud.';
$string['column'] = 'Column';
$string['competency_typeicon'] = 'Competency type icon';
$string['completed'] = 'Completed';
$string['completedwarningtext'] = 'Modifying activity completion criteria after some users have already completed the activity is not recommended as it can lead to different users being marked as completed for different reasons.<br />
At this point you can choose to delete all completion records for users who have achieved completion in either this activity or this course. Their completion status for both this activity and this course will be recalculated next time cron runs and they may be marked as complete again.<br />
Alternatively you can choose to keep all existing completion records and accept that different users may have received their status for different accomplishments.';
$string['completionexcludefailuresoff'] = 'Users may complete activities in any way, failures are acceptable.';
$string['completionexcludefailureson'] = 'Users have to complete activities without failures.';
$string['configdynamicappraisals'] = 'This setting allows you to specify whether appraisals lock on activation and no longer update assignments and roles or continue to update after activation';
$string['configenhancedcatalog'] = 'This setting allows you to specify if the enhanced catalog appears when clicking on \'Find Learning\' or any of the menu options under \'Find Learning\'.
    The enhanced catalog supports faceted search by multiple criteria using custom fields instead of relying on a single category.
    When disabled, the standard catalog (i.e., the hierarchical category system configured in the \'Manage categories\' administration area) appears when clicking on \'Find Learning\' or any of the menu options under \'Find Learning\'.
    Note: When enabled, the standard catalog remains available for Admins to manage course and program/certification administration in the "backend" (e.g., to assign Instructors to courses and course categories).';
$string['configforcelogintotara'] = 'Normally, the entire site is only available to logged in users. If you would like to make the front page and the course listings (but not the course contents) available without logging in, then you should uncheck this setting.';
$string['core:appearance'] = 'Configure site appearance settings';
$string['core:coursemanagecustomfield'] = 'Manage a course custom field';
$string['core:delegateownmanager'] = 'Assign a temporary manager to yourself';
$string['core:delegateusersmanager'] = 'Assign a temporary manager to other users';
$string['core:editmainmenu'] = 'Edit the main menu';
$string['core:langconfig'] = 'Edit language settings';
$string['core:manageprofilefields'] = 'Manage profile fields';
$string['core:markusercoursecomplete'] = 'Mark another user\'s courses as complete';
$string['core:modconfig'] = 'Configure activity modules';
$string['core:programmanagecustomfield'] = 'Manage a program custom field';
$string['core:seedeletedusers'] = 'See deleted users';
$string['core:undeleteuser'] = 'Undelete user';
$string['core:updateuseridnumber'] = 'Update user ID number';
$string['core:viewrecordoflearning'] = 'View a learners Record of Learning';
$string['couldntreaddataforblockid'] = 'Could not read data for blockid={$a}';
$string['couldntreaddataforcourseid'] = 'Could not ready data for courseid={$a}';
$string['coursecategoryicon'] = 'Category icon';
$string['coursecompletion'] = 'Course completion';
$string['coursecompletionsfor'] = 'Course Completions for ';
$string['courseduex'] = 'Course due {$a}';
$string['courseicon'] = 'Course icon';
$string['courseprogress'] = 'Course progress';
$string['courseprogresshelp'] = 'This specifies if the course progress block appears on the homepage';
$string['coursetype'] = 'Course Type';
$string['cronscheduleregularity'] = 'Your cron is not run very regularly. We recommend configuring the cron to run every minute, this way scheduled tasks will run as configured below and system load will be minimised.';
$string['csvdateformat'] = 'CSV Import date format';
$string['csvdateformatconfig'] = 'Date format to be used in CSV imports like user uploads with date custom profile fields, or HR Import.

The date format should be compatible with the formats defined in the <a target="_blank" href="http://www.php.net/manual/en/datetime.createfromformat.php">PHP DateTime class</a>

Examples:
<ul>
<li>d/m/Y if the dates in the CSV are of the form 21/03/2012</li>
<li>d/m/y if the dates in the CSV have 2-digit years 21/03/12</li>
<li>m/d/Y if the dates in the CSV are in US form 03/21/2012</li>
<li>Y-m-d if the dates in the CSV are in ISO form 2012-03-21</li>
</ul>';
$string['csvdateformatdefault'] = 'd/m/Y';
$string['currenticon'] = 'Current icon';
$string['currentlyselected'] = 'Currently selected';
$string['customicons'] = 'Custom icons';
$string['datatable:oPaginate:sFirst'] = 'First';
$string['datatable:oPaginate:sLast'] = 'Last';
$string['datatable:oPaginate:sNext'] = 'Next';
$string['datatable:oPaginate:sPrevious'] = 'Previous';
$string['datatable:sEmptyTable'] = 'No data available in table';
$string['datatable:sInfo'] = 'Showing _START_ to _END_ of _TOTAL_ entries';
$string['datatable:sInfoEmpty'] = 'Showing 0 to 0 of 0 entries';
$string['datatable:sInfoFiltered'] = '(filtered from _MAX_ total entries)';
$string['datatable:sInfoPostFix'] = '';
$string['datatable:sInfoThousands'] = ',';
$string['datatable:sLengthMenu'] = 'Show _MENU_ entries';
$string['datatable:sLoadingRecords'] = 'Loading...';
$string['datatable:sProcessing'] = 'Processing...';
$string['datatable:sSearch'] = 'Search:';
$string['datatable:sZeroRecords'] = 'No matching records found';
$string['datepickerattime'] = 'at';
// The following date picker strings should only be used in relation to date pickers! If you want the particular format that one
// of them is using, you should probably use something from langconfig.php or define your own string.
$string['datepickerlongyeardisplayformat'] = 'dd/mm/yy';
$string['datepickerlongyearparseformat'] = 'd/m/Y';
$string['datepickerlongyearphpuserdate'] = '%d/%m/%Y';
$string['datepickerlongyearplaceholder'] = 'dd/mm/yyyy';
$string['datepickerlongyearregexjs'] = '[0-3][0-9]/(0|1)[0-9]/[0-9]{4}';
$string['datepickerlongyearregexphp'] = '@^(0?[1-9]|[12][0-9]|3[01])/(0?[1-9]|1[0-2])/([0-9]{4})$@';
$string['dailyat'] = 'Daily at';
$string['debugstatus'] = 'Debug status';
$string['delete'] = 'Delete';
$string['deleted'] = 'Deleted';
$string['deleteusercheckfull'] = 'Are you absolutely sure you want to completely delete {$a} ?<br />All associated data, including but not limited to the following, will be deleted and is not recoverable:
<ul>
<li>appraisals where the user is in the learner role</li>
<li>grades</li>
<li>tags</li>
<li>roles</li>
<li>preferences</li>
<li>user custom fields</li>
<li>private keys</li>
<li>customised pages</li>
<li>facetoface signups</li>
<li>feedback360 assignments and responses</li>
<li>position assignments</li>
<li>programs & certifications</li>
<li>goals</li>
<li>evidence items</li>
<li>scheduled reports</li>
<li>reminders</li>
<li>will be unenroled from courses</li>
<li>will be unassigned from manager, appraiser and temp manager positions</li>
<li>will be removed from audiences</li>
<li>will be removed from groups</li>
<li>messages will be marked as read</li>
</ul>
If you wish to retain any data you may wish to consider suspending the user instead.';
$string['disablefeature'] = 'Disable';
$string['downloaderrorlog'] = 'Download error log';
$string['dynamicappraisals'] = 'Dynamic Appraisals';
$string['editheading'] = 'Edit the Report Heading Block';
$string['edition'] = 'Edition';
$string['elearning'] = 'E-learning';
$string['elementlibrary'] = 'Element Library';
$string['emptyassignments'] = 'No assignments';
$string['enableteam'] = 'Enable Team';
$string['enableteam_desc'] = 'This option will let you: Enable(show)/Disable Team feature from users on this site.

* If Show is chosen, all links, menus, tabs and option related to Team will be accessible.
* If Disable is chosen, Team will disappear from any menu on the site and will not be accessible.';
$string['enableprogramextensionrequests'] = 'Enable program extension requests';
$string['enableprogramextensionrequests_help'] = 'When enabled extension requests can be turned on for individual programs. This allows the program assignee to request an extension to the due date for a program. This extension can then be accepted or denied by the assignees manager.';
$string['enhancedcatalog'] = 'Enhanced catalog';
$string['enrolled'] = 'Enrolled';
$string['error:assigncannotdeletegrouptypex'] = 'You cannot delete groups of type {$a}';
$string['error:assignmentbadparameters'] = 'Bad parameter array passed to dialog set_parameters';
$string['error:assignmentgroupnotallowed'] = 'You cannot assign groups of type {$a->grouptype} to {$a->module}';
$string['error:assignmentmoduleinstancelocked'] = 'You cannot make changes to an assignment module instance which is locked';
$string['error:assignmentprefixnotfound'] = 'Assignment class for group type {$a} not found';
$string['error:assigntablenotexist'] = 'Assignment table {$a} does not exist!';
$string['error:autoupdatedisabled'] = 'Automatic checking for updates is currently disabled in Totara';
$string['error:cannotmanagereminders'] = 'You do not have permission to manage reminders';
$string['error:cannotupgradefromnewermoodle'] = 'You cannot upgrade to Totara {$a->newtotaraversion} from this version of Moodle. Please use a newer version of Totara which is based on Moodle core {$a->oldversion} or above.';
$string['error:cannotupgradefromnewertotara'] = 'You cannot downgrade from {$a->oldversion} to {$a->newversion}.';
$string['error:categoryidincorrect'] = 'Category ID was incorrect';
$string['error:columntypenotfound'] = 'The column type \'{$a}\' was defined but is not a valid option. This can happen if you have deleted a custom field or hierarchy depth level. The best course of action is to delete this column by pressing the red cross to the right.';
$string['error:columntypenotfound11'] = 'The column type \'{$a}\' was defined but is not a valid option. This can happen if you have deleted a custom field or hierarchy type. The best course of action is to delete this column by pressing the red cross to the right.';
$string['error:couldnotcreatedefaultfields'] = 'Could not create default fields';
$string['error:couldnotupdatereport'] = 'Could not update report';
$string['error:courseidincorrect'] = 'Course id is incorrect.';
$string['error:dashboardnotfound'] = 'Cannot fully initialize page - could not retrieve dashboard details';
$string['error:dialognotreeitems'] = 'No items available';
$string['error:dialoggenericerror'] = 'An error has occurred';
$string['error:duplicaterecordsdeleted'] = 'Duplicate {$a} record deleted: ';
$string['error:duplicaterecordsfound'] = '{$a->count} duplicate record(s) found in the {$a->tablename} table...fixing (see error log for details)';
$string['error:emptyidnumberwithsync'] = 'HR Import is enabled but the ID number field is empty. Either disable HR Import for this user or provide a valid ID number.';
$string['error:findingmenuitem'] = 'Error finding the menu item';
$string['error:importtimezonesfailed'] = 'Failed to update timezone information.';
$string['error:itemhaschildren'] = 'You cannot change the parent of this item while it has children. Please move this items children first.';
$string['error:itemnotselected'] = 'Please select an item';
$string['error:menuitemcannotberemoved'] = '"{$a}" item can not be removed, please review your settings.';
$string['error:menuitemcannotremove'] = '"{$a}" has the children which can not be removed, please review your settings.';
$string['error:menuitemcannotremovechild'] = ' - can not delete this item';
$string['error:menuitemclassnametoolong'] = 'Class name too long';
$string['error:menuitemtargetattrtoolong'] = 'Menu target attribute too long';
$string['error:menuitemtitletoolong'] = 'Menu title too long';
$string['error:menuitemtitlerequired'] = 'Menu title required';
$string['error:menuitemruleaudiencerequired'] = 'At least one audience must be selected';
$string['error:menuitemrulepresetrequired'] = 'At least one preset must be selected';
$string['error:menuitemrulerequired'] = 'At least one restriction type must be selected';
$string['error:menuitemrulerolerequired'] = 'At least one role must be selected';
$string['error:menuitemurlinvalid'] = 'Menu url address is invalid. Use "/" for a relative link of your domain name or full address for external link, i.e. http://extdomain.com';
$string['error:menuitemurltoolong'] = 'Menu url address too long';
$string['error:menuitemurlrequired'] = 'Menu url address required';
$string['error:morethanxitemsatthislevel'] = 'There are more than {$a} items at this level.';
$string['error:norolesfound'] = 'No roles found';
$string['error:notificationsparamtypewrong'] = 'Incorrect param type sent to Totara notifications';
$string['error:parentnotexists'] = '"{$a}" parent item does not exists, please check your settings';
$string['error:staffmanagerroleexists'] = 'A role "staffmanager" already exists. This role must be renamed before the upgrade can proceed.';
$string['error:unknownbuttonclicked'] = 'Unknown button clicked';
$string['error:useridincorrect'] = 'User id is incorrect.';
$string['error:usernotfound'] = 'User not found';
$string['errorfindingcategory'] = 'Error finding the category';
$string['errorfindingprogram'] = 'Error finding the program';
$string['eventbulkenrolmentsfinished'] = 'Bulk enrolments finished';
$string['eventbulkenrolmentsstarted'] = 'Bulk enrolments started';
$string['eventbulkroleassignmentsfinished'] = 'Bulk role assignments finished';
$string['eventbulkroleassignmentsstarted'] = 'Bulk role assignments started';
$string['eventcoursearchived'] = 'Course was archived';
$string['eventcoursecompletionreset'] = 'Course completion was reset';
$string['eventcoursecompletionunlocked'] = 'Course completion was unlocked without reset';
$string['eventcourseinprogress'] = 'User was marked in progress for course';
$string['eventmenuadminviewed'] = 'Main menu viewed';
$string['eventmenuitemcreated'] = 'Menu item created';
$string['eventmenuitemdeleted'] = 'Menu item deleted';
$string['eventmenuitemupdated'] = 'Menu item updated';
$string['eventmodulecompletion'] = 'Activity completion';
$string['eventmodulecompletionreset'] = 'Module completion reset';
$string['eventmodulecompletionunlocked'] = 'Module completion unlocked';
$string['eventmodulecompletioncriteriaupdated']= 'Module completion criteria updated';
$string['eventmyreportviewed'] = 'User viewed his reports';
$string['eventremindercreated'] = "Reminder was created";
$string['eventreminderdeleted'] = "Reminder was deleted";
$string['eventreminderupdated'] = "Reminder was updated";
$string['eventundeleted'] = 'User undeleted';
$string['eventuserconfirmed'] = 'User confirmed';
$string['eventusersuspended'] = 'User suspended';
$string['exportformat'] = 'Export format';
$string['facetoface'] = 'Seminar';
$string['findcourses'] = 'Find Courses';
$string['findlearning'] = 'Find Learning';
$string['flexibleicons'] = 'Flexible icons';
$string['enableflexiconsinfo'] = 'Enable rendering of icons using Flexible Icons API where possible.';
$string['fontdefault'] = 'Appropriate default';
$string['framework'] = 'Framework';
$string['heading'] = 'Heading';
$string['headingcolumnsdescription'] = 'The fields below define which data appear in the Report Heading Block. This block contains information about a specific user, and can appear in many locations throughout the site.';
$string['headingmissingvalue'] = 'Value to display if no data found';
$string['hidefeature'] = 'Hide';
$string['hierarchies'] = 'Hierarchies';
$string['home'] = 'Home';
$string['hourlyon'] = 'Hourly on';
$string['icon'] = 'Icon';
$string['inforesizecustomicons'] = 'Any file with width and height greater than 35x35 will be resized.';
$string['idnumberduplicates'] = 'Table: "{$a->table}". ID numbers: {$a->idnumbers}.';
$string['idnumberexists'] = 'Record with this ID number already exists';
$string['importtimezonesskipped'] = 'Skipped updating timezone information.';
$string['importtimezonessuccess'] = 'Timezone information updated from source {$a}.';
$string['incompatiblerepository'] = 'File download is disabled for security reasons, repository "{$a}" needs to be updated by developer';
$string['inprogress'] = 'In Progress';
$string['installdemoquestion'] = 'Do you want to include demo data with this installation?<br /><br />(This will take a long time.)';
$string['installingdemodata'] = 'Installing Demo Data';
$string['invalidsearchtable'] = 'Invalid search table';
$string['itemstoadd'] = 'Items to add';
$string['lasterroroccuredat'] = 'Last error occured at {$a}';
$string['learning'] = 'Learning';
$string['learningplans'] = 'Learning Plans';
$string['learningrecords'] = 'Learning Records';
$string['loading'] = 'Loading';
$string['localpostinstfailed'] = 'There was a problem setting up local modifications to this installation.';
$string['managecertifications'] = 'Manage certifications';
$string['managecustomicons'] = 'Manage custom icons';
$string['managers'] = 'Manager\'s ';
$string['menuitem:accessbyaudience'] = 'Restrict access by audience';
$string['menuitem:accessbypreset'] = 'Restrict access by preset rule';
$string['menuitem:accessbyrole'] = 'Restrict access by role';
$string['menuitem:accesscontrols'] = 'Access Controls';
$string['menuitem:accessmode'] = 'Access Mode';
$string['menuitem:accessmode_help'] = 'Access controls are used to restrict which users can view the menu item.

**Restrict access** determines how the following criteria are applied.

When set to **any**, users will be able to see this menu item if they meet **any one** of the enabled criteria below.

When set to **all**, users will only be able to see this menu item if they meet **all** the enabled criteria below.';
$string['menuitem:accessnotenabled'] = 'The settings below are not currently active because this item\'s visibility is not set to "Use custom access settings".';
$string['menuitem:addcohorts'] = 'Add audiences';
$string['menuitem:addnew'] = 'Add new menu item';
$string['menuitem:anycontext'] = 'Users may have role in any context';
$string['menuitem:audienceaggregation'] = 'Audience aggregation';
$string['menuitem:audienceaggregation_help'] = 'Determines whether the user must be a member of all of the selected audiences, or any of the selected audiences.';
$string['menuitem:context'] = 'Context';
$string['menuitem:context_help'] = '**Context** allows you to specify where a user must have a role assigned in order to view the menu item.

A user can be assigned a role at the system level giving them site wide access or just within a particular context. For instance a trainer may only be assigned the role at the course level.

When **Users must have role in the system context** is selected the user must be assigned the role at a system level (i.e. at a site-wide level) to be able to view the menu item.

When **User may have role in any context** is selected a user can view the report when they have been assigned the selected role anywhere in the system.';
$string['menuitem:delete'] = 'Are you sure you want to delete the "{$a}" item?';
$string['menuitem:deletechildren'] = 'All children of "{$a}" will be deleted:';
$string['menuitem:deletesuccess'] = 'The item was deleted successfully';
$string['menuitem:edit'] = 'Edit menu item';
$string['menuitem:editaccess'] = 'Access';
$string['menuitem:editingx'] = 'Editing menu item "{$a}"';
$string['menuitem:formitemparent'] = 'Parent item';
$string['menuitem:formitemtargetattr'] = 'Open link in new window';
$string['menuitem:formitemtargetattr_help'] = 'If selected, clicking this menu item will open the page in a new browser window instead of the current window.';
$string['menuitem:formitemtitle'] = 'Menu title';
$string['menuitem:formitemtitle_help'] = 'The name of this menu item. This field supports the multi-language content filter.';
$string['menuitem:formitemurl'] = 'Menu default url address';
$string['menuitem:formitemurl_help'] = 'Start the URL with a **/** to make the link relative to your site URL. Otherwise start the URL with http:// or https://, i.e. http://extdomain.com

You can also use following placeholders:

* ##userid## : Current user ID.
* ##username## : Current username.
* ##useremail## : Current user email.
* ##courseid## : Current course ID.';
$string['menuitem:formitemvisibility'] = 'Visibility';
$string['menuitem:hide'] = 'Hide';
$string['menuitem:movesuccess'] = 'The item was moved successfully';
$string['menuitem:norolesfound'] = 'No roles found';
$string['menuitem:presetwithaccess'] = 'Condition required to view';
$string['menuitem:presetwithaccess_help'] = 'This criteria allows you to restrict access to the menu item using one or more predefined rules.

How these rules are required is determined by the **Preset rule aggregation** setting. If it is set to **all** then the user must meet all of the selected criteria. If it is set to **any** the user must meet only one of the selected criteria.';
$string['menuitem:presetaggregation'] = 'Preset rule aggregation';
$string['menuitem:presetaggregation_help'] = 'Determines whether the user must meet all of the selected preset rules, or any of the selected preset rules.';
$string['menuitem:resettodefault'] = 'Reset menu to default configuration';
$string['menuitem:resettodefaultconfirm'] = 'Are you absolutely sure that you want to reset the main menu to its default configuration? This will permanently erase all customisations.';
$string['menuitem:resettodefaultcomplete'] = 'Main menu reset to default configuration.';
$string['menuitem:restrictaccess'] = 'Restrict access';
$string['menuitem:restrictaccessbyaudience'] = 'Restrict access by audience';
$string['menuitem:roleaggregation'] = 'Role aggregation';
$string['menuitem:roleaggregation_help'] = 'Determines whether the user must have all of the selected roles, or any of the selected roles.';
$string['menuitem:roleswithaccess'] = 'Roles with permission to view';
$string['menuitem:roleswithaccess_help'] = 'This criteria allows you to restrict access to the menu item based upon the roles a user has been assigned. You can select as many roles as you like and use the other supporting settings to determine how Totara checks those roles.

Whether they need to have any of the selected roles or all of the selected roles is determined by the **Role aggregation** setting.

The **Context** setting can be used to control whether the role is assigned to the user as a system wide role or whether it can occur in any other context.';
$string['menuitem:rulepreset_can_view_allappraisals'] = 'User can view All Appraisals menu item';
$string['menuitem:rulepreset_can_view_appraisal'] = 'User can view Performance menu item';
$string['menuitem:rulepreset_can_view_certifications'] = 'User can view Certifications menu item';
$string['menuitem:rulepreset_can_view_feedback_360s'] = 'User can view 360&deg; Feedback menu item';
$string['menuitem:rulepreset_can_view_latest_appraisal'] = 'User can view Latest Appraisal menu item';
$string['menuitem:rulepreset_can_view_learning_plans'] = 'User can view Learning Plans menu item';
$string['menuitem:rulepreset_can_view_my_goals'] = 'User can view Goals menu item';
$string['menuitem:rulepreset_can_view_my_reports'] = 'User can view Reports menu item';
$string['menuitem:rulepreset_can_view_my_team'] = 'User can view Team menu item';
$string['menuitem:rulepreset_can_view_programs'] = 'User can view Programs menu item';
$string['menuitem:rulepreset_can_view_required_learning'] = 'User can view Required Learning menu item';
$string['menuitem:rulepreset_is_guest'] = 'User is logged in as guest';
$string['menuitem:rulepreset_is_not_guest'] = 'User is <b>not</b> logged in as guest';
$string['menuitem:rulepreset_is_logged_in'] = 'User is logged in';
$string['menuitem:rulepreset_is_not_logged_in'] = 'User is <b>not</b> logged in';
$string['menuitem:rulepreset_is_site_admin'] = 'User is site administrator';
$string['menuitem:show'] = 'Show';
$string['menuitem:showcustom'] = 'Use custom access rules';
$string['menuitem:showwhenrequired'] = 'Show when required';
$string['menuitem:systemcontext'] = 'Users must have role in the system context';
$string['menuitem:title'] = 'Item title';
$string['menuitem:updateaccesssuccess'] = 'Access rules updated successfully';
$string['menuitem:updatesuccess'] = 'Main menu updated successfully';
$string['menuitem:url'] = 'Default url address';
$string['menuitem:visibility'] = 'Visibility';
$string['menuitem:withrestrictionall'] = 'Users matching <strong>all</strong> of the criteria below can view this menu item.';
$string['menuitem:withrestrictionany'] = 'Users matching <strong>any</strong> of the criteria below can view this menu item.';
$string['menulifetime'] = 'Cache main menu';
$string['menulifetime_desc'] = 'Higher values improve performance but some changes in menu structure may be delayed.';
$string['minutelyon'] = 'Minutely on';
$string['modulearchive'] = 'Activity archives';
$string['monthlyon'] = 'Monthly on';
$string['moodlecore'] = 'Moodle core';
$string['movedown'] = 'Move Down';
$string['moveup'] = 'Move Up';
$string['mssqlgroupconcatfail'] = 'Automatic update failed with reason "{$a}". Please, copy code from textarea below and execute it in MSSQL Server as Administrator. Afterwards refresh this page.';
$string['mybookings'] = 'My Bookings';
$string['mycoursecompletions'] = 'My Course Completions';
$string['mycurrentprogress'] = 'My Current Courses';
$string['mydevelopmentplans'] = 'My development plans';
$string['myfuturebookings'] = 'My Future Bookings';
$string['mylearning'] = 'My Learning';
$string['mypastbookings'] = 'My Past Bookings';
$string['myprofile'] = 'My Profile';
$string['myrecordoflearning'] = 'My Record of Learning';
$string['mysqlneedsinnodb'] = 'The current database engine "{$a}" may not be compatible with Totara, it is strongly recommended to use InnoDB or XtraDB engine.';
$string['myteaminstructionaltext'] = 'Choose a team member from the table on the right.';
$string['noassessors'] = 'No assessors found';
$string['nogroupassignments'] = 'No groups assigned';
$string['none'] = 'None';
$string['noresultsfor'] = 'No results found for "{$a->query}".';
$string['nostaffassigned'] = 'You currently do not have a team.';
$string['notapplicable'] = 'Not applicable';
$string['notavailable'] = 'Not available';
$string['notenrolled'] = '<em>You are not currently enrolled in any courses.</em>';
$string['notfound'] = 'Not found';
$string['notimplementedtotara'] = 'Sorry, this feature is only implemented on MySQL, MSSQL and PostgreSQL databases.';
$string['activeusercountstr'] = '{$a->activeusers} users have logged in to this site in the last year ({$a->activeusers3mth} in the last 3 months)';
$string['numberofstaff'] = '({$a} staff)';
$string['old_release_security_text_plural'] = ' (including [[SECURITY_COUNT]] new security releases)';
$string['old_release_security_text_singular'] = ' (including 1 new security release)';
$string['old_release_text_plural'] = 'You are not using the most recent release available for this version. There are [[ALLTYPES_COUNT]] new releases available ';
$string['old_release_text_singular'] = 'You are not using the most recent release available for this version. There is 1 new release available ';
$string['options'] = 'Options';
$string['organisation_typeicon'] = 'Organisation type icon';
$string['organisationatcompletion'] = 'Organisation at completion';
$string['organisationsarrow'] = 'Organisations > ';
$string['participant'] = 'Participant';
$string['pastbookingsfor'] = 'Past Bookings for ';
$string['pathtowkhtmltopdf'] = 'Path to wkhtmltopdf';
$string['pathtowkhtmltopdf_help'] = 'Specify location of the wkhtmltopdf executable file. wkhtmltopdf is used for creation of PDF snapshots.';
$string['performinglocalpostinst'] = 'Local Post-installation setup';
$string['permittedcrossdomainpolicies'] = 'Permitted cross domain policies';
$string['permittedcrossdomainpolicies_desc'] = 'If set to "none" browsers are instructed to prevent embedding of content from this server in extenal Flash or PDF files. If set to "master-only" the policies can be defined in main crossdomain.xml file.';
$string['pluginname'] = 'Totara core';
$string['pluginnamewithkey'] = 'Self enrolment with key';
$string['pos_description'] = 'Description';
$string['pos_description_help'] = 'Description of the position.';
$string['position_typeicon'] = 'Position type icon';
$string['positiona'] = 'Position {$a}';
$string['positionatcompletion'] = 'Position at completion';
$string['positionsarrow'] = 'Positions > ';
$string['poweredbyx'] = 'Powered by {$a->totaralearn}';
$string['poweredbyxhtml'] = 'Powered by <a href="{$a->url}">{$a->totaralearn}</a>';
$string['execpathnotallowed'] = 'This setting is currently disabled. To enable, add<br />$CFG->preventexecpath = 0;<br /> to config.php';
$string['proficiency'] = 'Proficiency';
$string['progdoesntbelongcat'] = 'The program doesn\'t belong to this category';
$string['programicon'] = 'Program icon';
$string['queryerror'] = 'Query error. No results found.';
$string['recordnotcreated'] = 'Record could not be created';
$string['recordnotupdated'] = 'Record could not be updated';
$string['recordoflearning'] = 'Record of Learning';
$string['recordoflearningforname'] = 'Record of Learning for {$a}';
$string['registrationcode'] = 'Registration code';
$string['registrationcode_help'] = 'Production sites require a unique registration code, it can be obtained from your Totara Partner.';
$string['registrationcodeinvalid'] = 'Invalid registration code format';
$string['relative_time_days'] = '{$a} days ago';
$string['relative_time_five_minutes'] = 'Within the last five minutes';
$string['relative_time_half_hour'] = 'Within the last half-hour';
$string['relative_time_hour'] = 'Within the last hour';
$string['relative_time_month'] = 'A month ago';
$string['relative_time_months'] = '{$a} months ago';
$string['relative_time_years'] = '{$a} years ago';
$string['remotetotaralangnotavailable'] = 'Because Totara can not connect to download.totaralms.com, we are unable to do language pack installation automatically. Please download the appropriate zip file(s) from https://download.totaralms.com/lang/T{$a->totaraversion}/, copy them to your {$a->langdir} directory and unzip them manually.';
$string['replaceareyousure'] = 'Are you sure you want to replace \'{$a->search}\' with \'{$a->replace}\'? (y/n)';
$string['replacedevdebuggingrequired'] = 'Error, you must have developer debugging enabled to run this script.';
$string['replacedonotrunlive'] = 'DO NOT RUN THIS ON A LIVE SITE.';
$string['replaceenterfindstring'] = 'Enter string to find:';
$string['replaceenternewstring'] = 'Enter new string:';
$string['replacemissingparam'] = 'Missing either Search or Replace parameters.';
$string['replacereallysure'] = 'Are you really sure? This will replace all instances of \'{$a->search}\' with \'{$a->replace}\' and may break your database! (y/n)';
$string['report'] = 'Report';
$string['reports'] = 'Reports';
$string['reportedat'] = 'Reported at';
$string['requiresjs'] = 'This {$a} requires Javascript to be enabled.';
$string['returntocourse'] = 'Return to the course';
$string['roleassignmentsnum'] = 'Assignments';
$string['roledefaults'] = 'Default role settings';
$string['roledefaultsnochanges'] = 'No role changes detected';
$string['save'] = 'Save';
$string['schedule'] = 'Schedule';
$string['scheduleadvanced'] = 'The current schedule is too complex for the basic interface please, visit {$a} to edit it.';
$string['scheduleadvancedlink'] = 'here';
$string['scheduleadvancednopermission'] = 'The current schedule is too complex for the basic interface, please contact an administrator to change it.';
$string['scheduledaily'] = 'Daily';
$string['scheduleddaily'] = 'Daily at {$a}';
$string['scheduledhourly'] = 'Every {$a} hour(s) from midnight';
$string['scheduledminutely'] = 'Every {$a} minute(s) from the start of the hour';
$string['scheduledmonthly'] = 'Monthly on the {$a}';
$string['scheduledweekly'] = 'Weekly on {$a}';
$string['schedulehourly'] = 'Every X hours';
$string['scheduleminutely'] = 'Every X minutes';
$string['schedulemonthly'] = 'Monthly';
$string['scheduleweekly'] = 'Weekly';
$string['search'] = 'Search';
$string['searchcourses'] = 'Search Courses';
$string['searchx'] = 'Search {$a}';
$string['securereferrers'] = 'Secure referrers';
$string['securereferrers_desc'] = 'When enabled browsers are instructed to not send script names and page parameters to external sites which improves security and privacy. This may affect functionality of browsers that do not fully implement referrer policy.';
$string['selectanassessor'] = 'Select an assessor...';
$string['selectaproficiency'] = 'Select a proficiency...';
$string['selectionlimited'] = 'There is a maximum limit of {$a} selected managers';
$string['sendregistrationdatatask'] = 'Send site registration data';
$string['sendremindermessagestask'] = 'Send reminder messages';
$string['settings'] = 'Settings';
$string['showfeature'] = 'Show';
$string['sitemanager'] = 'Site Manager';
$string['siteregistrationemailbody'] = 'Site {$a} was not able to register itself automatically. Access to push data to our registrations site is probably blocked by a firewall.';
$string['sitetype'] = 'Type of site';
$string['sitetype_help'] = 'Select the type of site that matches its use.';
$string['sitetypedemo'] = 'Demo';
$string['sitetypedevelopment'] = 'Development';
$string['sitetypeproduction'] = 'Production';
$string['sitetypeqa'] = 'QA / Staging';
$string['staffmanager'] = 'Staff Manager';
$string['startdate'] = 'Start Date';
$string['started'] = 'Started';
$string['strftimedateshortmonth'] = '%d %b %Y';
$string['stricttransportsecurity'] = 'Strict transport security';
$string['stricttransportsecurity_desc'] = 'When enabled browsers are instructed to always use https:// protocol when accessing the server and users cannot ignore SSL negotiation warnings. Please note that if enabled browsers will remember this setting for six months and will prevent access via http:// even if this setting is later disabled.';
$string['subplugintype_tabexport'] = 'Tabular export plugin';
$string['subplugintype_tabexport_plural'] = 'Tabular exports';
$string['successuploadicon'] = 'Icon(s) successfully saved';
$string['supported_branch_old_release_text'] = 'You may also want to consider upgrading from {$a} to the most recent version ([[CURRENT_MAJOR_VERSION]]) to benefit from the latest features. ';
$string['supported_branch_text'] = 'You may want to consider upgrading from {$a} to the most recent version ([[CURRENT_MAJOR_VERSION]]) to benefit from the latest features. ';
$string['tab:futurebookings'] = 'Future Bookings';
$string['tab:pastbookings'] = 'Past Bookings';
$string['tabexports'] = 'Tabular exports';
$string['team'] = 'Team';
$string['teammembers'] = 'Team Members';
$string['teammembers_text'] = 'All members of your team are shown below.';
$string['template'] = 'Template';
$string['tempmanager'] = 'Temporary manager';
$string['timezoneinvalid'] = 'Invalid timezone: {$a}';
$string['timezoneuser'] = 'User timezone';
$string['trysearchinginstead'] = 'Try searching instead.';
$string['type'] = 'Type';
$string['typeicon'] = 'Type icon';
$string['unassignall'] = 'Unassign all';
$string['undelete'] = 'Undelete';
$string['undeletecheckfull'] = 'Are you sure you want to undelete {$a}?';
$string['undeletednotx'] = 'Could not undelete {$a} !';
$string['undeletedx'] = 'Undeleted {$a}';
$string['undeleteuser'] = 'Undelete User';
$string['undeleteusernoperm'] = 'You do not have the required permission to undelete a user';
$string['unexpected_installer_result'] = 'Unspecified component install error: {$a}';
$string['unlockcompletion'] = 'Unlock completion and delete completion data';
$string['unlockcompletionnoreset'] = 'Unlock completion and keep completion data';
$string['unsupported_branch_text'] = 'The version you are using ({$a})  is no longer supported. That means that bugs and security issues are no longer being fixed. You should upgrade to a supported version (such as [[CURRENT_MAJOR_VERSION]]) as soon as possible';
$string['unused'] = 'Unused';
$string['upgradenonlinear'] = 'Upgrades must be to a higher version built on or after the date of the current version {$a}';
$string['uploadcompletionrecords'] = 'Upload completion records';
$string['userdoesnotexist'] = 'User does not exist';
$string['userlearningdueonx'] = 'due on {$a}';
$string['userlearningoverduesincex'] = 'overdue since {$a}';
$string['userlearningoverduesincextooltip'] = 'Overdue since {$a}';
$string['viewmyteam'] = 'View My Team';
$string['weeklyon'] = 'Weekly on';
$string['xofy'] = '{$a->count} / {$a->total}';
$string['xpercent'] = '{$a}%';
$string['xpercentcomplete'] = '{$a}% complete';
$string['xpositions'] = '{$a}\'s Positions';
$string['xresultsfory'] = '<strong>{$a->count}</strong> results found for "{$a->query}"';
$string['yesdelete'] = 'Yes, delete';


// Deprecated in 9.0.

$string['choosetempmanager'] = 'Choose temporary manager';
$string['choosetempmanager_help'] = 'A temporary manager can be assigned. The assigned Temporary Manager will have the same rights as a normal manager, for the specified amount of time.

Click **Choose temporary manager** to select a temporary manager.

If the name you are looking for does not appear in the list, it might be that the user does not have the necessary rights to act as a temporary manager.';
$string['recordoflearningfor'] = 'Record of Learning for ';
$string['developmentplan'] = 'Development Planner';
$string['enablemyteam'] = 'Enable My Team';
$string['enablemyteam_desc'] = 'This option will let you: Enable(show)/Disable My Team feature from users on this site.

* If Show is chosen, all links, menus, tabs and option related to My Team will be accessible.
* If Disable is chosen, My Team will disappear from any menu on the site and will not be accessible.';
$string['enabletempmanagers'] = 'Enable temporary managers';
$string['enabletempmanagersdesc'] = 'Enable functionality that allows for assigning a temporary manager to a user. Disabling this will cause all current temporary managers to be unassigned on next cron run.';
$string['error:appraisernotselected'] = 'Please select an appraiser';
$string['error:datenotinfuture'] = 'The date needs to be in the future';
$string['error:managernotselected'] = 'Please select a manager';
$string['error:organisationnotselected'] = 'Please select an organisation';
$string['error:positionnotselected'] = 'Please select a position';
$string['error:positionvalidationfailed'] = 'The problems indicated below must be fixed before your changes can be saved.';
$string['error:tempmanagerexpirynotset'] = 'An expiry date for the temporary manager needs to be set';
$string['error:tempmanagernotselected'] = 'Please select a temporary manager';
$string['error:tempmanagernotset'] = 'Temporary manager needs to be set';
$string['myreports'] = 'My Reports';
$string['myteam'] = 'My Team';
$string['tempmanagerassignmsgmgr'] = '{$a->tempmanager} has been assigned as temporary manager to {$a->staffmember} (one of your team members).<br>Temporary manager expiry: {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerassignmsgmgrsubject'] = '{$a->tempmanager} is now temporary manager for {$a->staffmember}';
$string['tempmanagerassignmsgstaff'] = '{$a->tempmanager} has been assigned as temporary manager to you.<br>Temporary manager expiry: {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerassignmsgstaffsubject'] = '{$a->tempmanager} is now your temporary manager';
$string['tempmanagerassignmsgtmpmgr'] = 'You have been assigned as temporary manager to {$a->staffmember}.<br>Temporary manager expiry: {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerassignmsgtmpmgrsubject'] = 'You are now {$a->staffmember}\'s temporary manager';
$string['tempmanagerexpiry'] = 'Temporary manager expiry date';
$string['tempmanagerexpiry_help'] = 'Click the calendar icon to select the date the temporary manager will expire.';
$string['tempmanagerexpirydays'] = 'Temporary manager expiry days';
$string['tempmanagerexpirydaysdesc'] = 'Set a default temporary manager expiry period (in days).';
$string['tempmanagerexpiryupdatemsgmgr'] = 'The expiry date for {$a->staffmember}\'s temporary manager ({$a->tempmanager}) has been updated to {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerexpiryupdatemsgmgrsubject'] = 'Expiry date updated for {$a->staffmember}\'s temporary manager';
$string['tempmanagerexpiryupdatemsgstaff'] = 'The expiry date for {$a->tempmanager} (your temporary manager) has been updated to {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerexpiryupdatemsgstaffsubject'] = 'Expiry date updated for your temporary manager';
$string['tempmanagerexpiryupdatemsgtmpmgr'] = 'Your expiry date as temporary manager for {$a->staffmember} has been updated to {$a->expirytime}.<br>View details <a href="{$a->url}">here</a>.';
$string['tempmanagerexpiryupdatemsgtmpmgrsubject'] = 'Temporary manager expiry updated for {$a->staffmember}';
$string['tempmanagerrestrictselection'] = 'Temporary manager selection';
$string['tempmanagerrestrictselectiondesc'] = 'Determine which users will be available in the temporary manager selection dialog. Selecting \'Only staff managers\' will remove any assigned temporary managers who don\'t have the \'staff manager\' role on the next cron run.';
$string['tempmanagers'] = 'Temporary managers';
$string['tempmanagerselectionallusers'] = 'All users';
$string['tempmanagerselectiononlymanagers'] = 'Only staff managers';
$string['tempmanagersupporttext'] = ' Note, only current team managers can be selected.';
$string['totaralearn'] = 'Totara';
$string['totaralearnlink'] = '<a href="{$a->url}">{$a->totaralearn}</a>';
$string['updatetemporarymanagerstask'] = 'Update temporary managers';

// Deprecated in 10

$string['mysqlneedsbarracuda'] = 'Advanced Totara features require InnoDB Barracuda storage format';
$string['mysqlneedsfilepertable'] = 'Advanced Totara features require InnoDB File-Per-Table mode to be enabled';
$string['timecompleted'] = 'Time completed';
$string['poweredby'] = 'Powered by Totara LMS';


$string['viewcourse'] = 'View course';




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
 * Strings for component 'filters', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   core_filters
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actfilterhdr'] = 'Active filters';
$string['addfilter'] = 'Add filter';
$string['anycategory'] = 'any category';
$string['anycourse'] = 'any course';
$string['anyfield'] = 'any field';
$string['anyrole'] = 'any role';
$string['anyvalue'] = 'any value';
$string['matchesanyselected'] = 'matches any selected';
$string['matchesallselected'] = 'matches all selected';
$string['applyto'] = 'Apply to';
$string['categoryrole'] = 'Category role';
$string['filtercheckbox'] = 'Checkbox filter';
$string['filtercheckbox_help'] = 'This filter allows you to filter information based on a set of checkboxes.

The filter has the following options:

* **is any value**: This option disables the filter (i.e. all information is accepted by this filter).
* **matches any selected**: This option allows information, if it matches any of the checked options.
* **matches all selected**: This option allows information, if it matches all of the checked options.';
$string['filterdate'] = 'Date filter';
$string['filterdate_help'] = 'This filter allows you to filter information from:

* Before and/or after given dates.
* A number of days before or after today.
* List any records where no date is set.';
$string['filternumber'] = 'Number filter';
$string['filternumber_help'] = 'This filter allows you to filter numerical information based on its value.

The filter has the following options:

* **is equal to**: This option allows only information that is equal to the text entered (if no text is entered, then the filter is disabled).
* **is not equal to**: This option allows only information that is not equal to the text entered (if no text is entered, then the filter is disabled).
* **is greater than**: This option allows only information that has a numerical value greater than the text entered (if no text is entered, then the filter is disabled).
* **is greater than**: This option allows only information that has a numerical value greater than the text entered (if no text is entered, then the filter is disabled).
* **is less than**: This option allows only information that has a numerical value less than the text entered (if no text is entered, then the filter is disabled).
* **is greater than or equal to**: This option allows only information that has a numerical value greater than or equal to the text entered (if no text is entered, then the filter is disabled).
* **is less than or equal to**: This option allows only information that has a numerical value less than or equal to the text entered (if no text is entered, then the filter is disabled).';
$string['filtersimpleselect'] = 'Simple select filter';
$string['filtersimpleselect_help'] = 'This filter allows you to filter information based on a dropdown list. This filter does not have any extra options.';
$string['filtertext'] = 'Text filter';
$string['filtertext_help'] = 'This filter allows you to filter information based on a free form text.

The filter has the following options:

* **contains**: This option allows only information that contains the text entered (if no text is entered, then the filter is disabled).
* **doesn\'t contain**: This option allows only information that does not contain the text entered (if no text is entered, then the filter is disabled).
* **is equal to**: This option allows only information that is equal to the text entered (if no text is entered, then the filter is disabled).
* **starts with**: This option allows only information that starts with the text entered (if no text is entered, then the filter is disabled).
* **ends with**: This option allows only information that ends with the text entered (if no text is entered, then the filter is disabled).
* **is empty**: This option allows only information that is equal to an empty string (the text entered is ignored).';
$string['filterenrol'] = 'Enrol filter';
$string['filterenrol_help'] = 'This filter allows you to filter information based on whether a user is or isn\'t enrolled in a particular course.

The filter has the following options:

* **Is any value**: This option disables the filter (i.e. all information is accepted by this filter).
* **Yes**: This option only returns records where the user is enrolled in the specified course.
* **No**: This option only returns records where the user is not enrolled in the specified course.';
$string['filterselect'] = 'Select filter';
$string['filterselect_help'] = 'This filter allows you to filter information via a dropdown list of options.

The filter has the following options:

* **is any value**: This option disables the filter (i.e. all information is accepted by this filter).
* **is equal to**: This option allows only information that is equal to the value selected from the list.
* **is not equal to**: This option allows only information that is different from the value selected from the list.';
$string['filterurl'] = 'URL filter';
$string['filterurl_help'] = 'This filter allows you to filter information based on a dropdown list.

The filter has the following options:

* **is any value**: This option disables the filter (i.e. all information is accepted by this filter).
* **is empty**: This option allows only information that is equal to an empty string.
* **is not empty (NOT NULL)**: This option allows only information that is not equal to an empty string.';
$string['contains'] = 'contains';
$string['content'] = 'Content';
$string['contentandheadings'] = 'Content and headings';
$string['coursecategory'] = 'course category';
$string['courserole'] = 'Course role';
$string['courserolelabel'] = '{$a->label} is {$a->rolename} in {$a->coursename} from {$a->categoryname}';
$string['courserolelabelerror'] = '{$a->label} error: course {$a->coursename} does not exist';
$string['coursevalue'] = 'course value';
$string['datelabelisafter'] = '{$a->label} is after {$a->after}';
$string['datelabelisbefore'] = '{$a->label} is before {$a->before}';
$string['datelabelisbetween'] = '{$a->label} is between {$a->after} and {$a->before}';
$string['defaultx'] = 'Default ({$a})';
$string['disabled'] = 'Disabled';
$string['doesnotcontain'] = 'doesn\'t contain';
$string['endswith'] = 'ends with';
$string['filterallwarning'] = 'Applying filters to headings as well as content can greatly increase the load on your server. Please use that \'Apply to\' settings sparingly. The main use is with the multilang filter.';
$string['filtersettings'] = 'Filter settings';
$string['filtersettings_help'] = 'This page lets you turn filters on or off in a particular part of the site.

Some filters may also let you set local settings, in which case there will be a settings link next to their name.';
$string['filtersettingsforin'] = 'Filter settings for {$a->filter} in {$a->context}';
$string['filtersettingsin'] = 'Filter settings in {$a}';
$string['firstaccess'] = 'First access';
$string['globalrolelabel'] = '{$a->label} is {$a->value}';
$string['includesubcategories'] = 'Include sub-categories?';
$string['isactive'] = 'Active?';
$string['isafter'] = 'is after';
$string['isanyvalue'] = 'is any value';
$string['isbefore'] = 'is before';
$string['isdefined'] = 'is defined';
$string['isempty'] = 'is empty';
$string['isequalto'] = 'is equal to';
$string['isgreaterthan'] = 'is greater than';
$string['islessthan'] = 'is less than';
$string['isgreaterorequalto'] = 'is greater than or equal to';
$string['islessthanorequalto'] = 'is less than or equal to';
$string['isenrolled'] = 'The user is enrolled in the course';
$string['isnotenrolled'] = 'The user is not enrolled in the course';
$string['isnotdefined'] = 'isn\'t defined';
$string['isnotequalto'] = 'isn\'t equal to';
$string['limiterfor'] = '{$a} field limiter';
$string['neveraccessed'] = 'Never accessed';
$string['nevermodified'] = 'Never modified';
$string['newfilter'] = 'New filter';
$string['nofiltersenabled'] = 'No filter plugins have been enabled on this site.';
$string['off'] = 'Off';
$string['offbutavailable'] = 'Off, but available';
$string['on'] = 'On';
$string['profilefilterfield'] = 'Profile field name';
$string['profilefilterlimiter'] = 'Profile field operator';
$string['profilelabel'] = '{$a->label}: {$a->profile} {$a->operator} {$a->value}';
$string['profilelabelnovalue'] = '{$a->label}: {$a->profile} {$a->operator}';
$string['removeall'] = 'Remove all filters';
$string['removeselected'] = 'Remove selected';
$string['selectlabel'] = '{$a->label} {$a->operator} {$a->value}';
$string['selectlabelnoop'] = '{$a->label} {$a->value}';
$string['startswith'] = 'starts with';
$string['tablenosave'] = 'Changes in table above are saved automatically.';
$string['textlabel'] = '{$a->label} {$a->operator} {$a->value}';
$string['textlabelnovalue'] = '{$a->label} {$a->operator}';
$string['valuefor'] = '{$a} value';

$string['strfdateshortmonth'] = '%d %b %Y';

$string['andchildren'] = ' (and children)';

$string['accessbycohort'] = 'Access by cohort';
$string['enablecondition'] = 'Enable condition';
$string['user'] = 'User';
$string['cohortmembers'] = 'Cohort members';
$string['enrolledcourses'] = 'Enrolled courses';
$string['includeenrolledcoursesrecords'] = '';
$string['reportbuilderenrolledcourses'] = '';
$string['reportbuilderenrolledcourses_help'] = '';
$string['enrolledcoursesownrecords'] = 'Enrolled courses';
$string['costcentre'] = 'Cost centre';
$string['costcentreroles'] = 'Cost centre role';
$string['accessbycostcentrerole'] = 'Access by cost centre role';
$string['regionown'] = 'Own';
$string['regionany'] = 'Any';
$string['regionother'] = 'Other';

$string['costcentre'] = 'Cost centre';