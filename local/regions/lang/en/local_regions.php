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
 * The local_regions English language file.
 *
 * @package    local_regions
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Region plugin';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['regions:global'] = 'View global';

$string['form:details:region'] = 'Region details';
$string['form:error:subregionmustmatch'] = 'The location must be in the selected region';
$string['form:error:subregionmustmatch_multiple'] = 'The location(s) must be in the selected region(s)';
$string['form:name:region'] = 'Region name';
$string['form:names:region'] = 'Region names';
$string['form:name:subregion'] = 'Location name';
$string['form:names:subregion'] = 'Location names';
$string['form:name:region_mapping_course'] = 'Catalogue location';
$string['form:name:region_mapping_user'] = 'Your location';
$string['form:name:subregions_all'] = 'All locations for chosen region(s)';
$string['form:name:tapsname'] = 'TAPS name';
$string['form:name:userselectable'] = 'User selectable';

$string['form:hint:details:region'] = 'One region entry per line, comma separated fields \"name,tapsname,userselectable\" where userselectable is 1 or 0, e.g. \"UKMEA,UK-MEA Region,1\"';
$string['form:hint:region_preferred'] = 'You can change your regional view of Arup University here to see content targetting other regions.';
$string['form:hint:region_preferred_set'] = 'Your selected region for viewing Arup University is currently different to your HR defined region ({$a}), please update if necessary.';
$string['form:hint:region_update_location_1'] = 'Unfortunately, we could not determine your location automatically, please update it here.<br/>Providing your region enables us to provide information relevant to your location.';
$string['form:hint:region_update_location_2'] = 'Your region has been automatically updated.<br/>If you wish you can change your regional view of Moodle here to see content targetting other regions.';
$string['form:hint:tapsname'] = 'The exact name as stored in TAPS, for example \"UK-MEA Region\"';

$string['courses'] = 'Courses';

$string['deleted'] = 'User deleted';

$string['global'] = 'Global';

$string['mapcourses'] = 'Select which {$a->courses} should be associated with the {$a->region} {$a->currentregion} in the catalogue (and choose relevant {$a->subregions})...';

$string['notapplicable'] = 'N/A';
$string['notchanged'] = 'Not changed';
$string['nouserstoprocess'] = 'No users to process';
$string['nouserstoreport'] = 'No users with issues to report';

$string['required'] = '{$a} is required.';
$string['regiondetails'] = 'Region details';
$string['regionsmanage'] = 'Manage region';
$string['region'] = 'Region';
$string['regions'] = 'Regions';
$string['regions:catalogue'] = 'Catalogue regions';
$string['regionsaddbulk'] = 'Bulk add regions';
$string['regionsdelete'] = 'Delete region';
$string['regionsedit'] = 'Add/Edit region';
$string['regionsmanage'] = 'Manage regions';
$string['regionsmapcourses'] = 'Map to {$a}';

$string['staffid'] = 'Employee number';
$string['subregiondetails'] = 'Location details';
$string['subregion'] = 'Location';
$string['subregions'] = 'Locations';
$string['subregionsaddbulk'] = 'Bulk add locations';
$string['subregionsdelete'] = 'Delete location';
$string['subregionsedit'] = 'Add/Edit location';
$string['subregionsmanage'] = 'Manage locations';
$string['subregionsmapcourses'] = 'Map to {$a}';
$string['suspended'] = 'User suspended';

$string['tasktidycoursemappings'] = 'Tidy redundant course<->region mappings.';
$string['tasktidyusermappings'] = 'Tidy redundant user<->region mappings.';

$string['unabletoretrievedata'] = 'Unable to retrieve data';
$string['userreport'] = 'User report : Flagged issues';
$string['userupdate:all'] = 'Updating user regions (All)';
$string['userupdate:missing'] = 'Updating user regions (Missing)';

$string['viewingas'] = ' (Viewing as: {$a})';

// Status Flags.
$string['INACTIVE'] = 'Flagged inactive by TAPS ';
$string['NO_REGION'] = 'Flagged as no region by TAPS';
$string['NOT_FOUND'] = 'Flagged as not found by TAPS';
$string['FAILED'] = 'Failed to retrieve from TAPS';
$string['NOT_CHANGED'] = 'Region unchanged';
$string['UPDATED_OK'] = 'Updated OK';