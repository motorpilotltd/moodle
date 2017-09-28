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
 * English language strings for local_coursemetadata.
 *
 * @package   local_coursemetadata
 * @copyright 2016 Motorpilot
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course metadata';
$string['pluginversiontoolow'] = 'Plugin "{$a->name}" could not be upgraded to version {$a->version}.'
        . ' Upgrading requires at least version {$a->requiredversion} to be installed (Current version: {$a->currentversion}).';

$string['coursemetadata'] = 'Custom course metadata fields';
$string['coursemetadata:accessall'] = 'Access all coursemetadata (no edit restrictions)';
$string['coursemetadata:restricted'] = 'Edit restricted coursemetadata';

$string['coursemetadatacategory'] = 'Category';
$string['coursemetadatacategoryname'] = 'Category name (must be unique)';
$string['coursemetadatacategorynamenotunique'] = 'This category name is already in use';
$string['coursemetadatacommonsettings'] = 'Common settings';
$string['coursemetadataconfirmcategorydeletion'] = 'There is/are {$a} field/s in this category which will be moved into the category above (or below if in the top category).<br />Do you still wish to delete this category?';
$string['coursemetadataconfirmfielddeletion'] = 'There is/are {$a} metadata record/s for this field which will be deleted.<br />Do you still wish to delete this field?';
$string['coursemetadatacreatecategory'] = 'Create a new metadata category';
$string['coursemetadatacreatefield'] = 'Create a new metadata field:';
$string['coursemetadatacreatenewcategory'] = 'Creating a new category';
$string['coursemetadatacreatenewfield'] = 'Creating a new \'{$a}\' metadata field';

$string['coursemetadatadefaultcategory'] = 'Other fields';
$string['coursemetadatadefaultdata'] = 'Default value';
$string['coursemetadatadefaultchecked'] = 'Checked by default';
$string['coursemetadatadeletecategory'] = 'Deleting a category';
$string['coursemetadatadeletefield'] = 'Deleting field \'{$a}\'';
$string['coursemetadatadescription'] = 'Description of the field';

$string['coursemetadataeditcategory'] = 'Editing category: {$a}';
$string['coursemetadataeditfield'] = 'Editing metadata field: {$a}';

$string['coursemetadatafield'] = 'Course metadata field';
$string['coursemetadatafieldcolumns'] = 'Columns';
$string['coursemetadatafieldispassword'] = 'Is this a password field?';
$string['coursemetadatafieldlink'] = 'Link';
$string['coursemetadatafieldlink_help'] = 'To transform the text into a link, enter a URL containing $$, where $$ will be replaced with the text. For example, to transform a Twitter ID to a link, enter http://twitter.com/$$.';
$string['coursemetadatafieldlinktarget'] = 'Link target';
$string['coursemetadatafieldmaxlength'] = 'Maximum length';
$string['coursemetadatafieldrows'] = 'Rows';
$string['coursemetadatafieldsize'] = 'Display size';
$string['coursemetadataforceunique'] = 'Should the data be unique?';

$string['coursemetadatainvaliddata'] = 'Invalid value';

$string['coursemetadatalocked'] = 'Is this field locked?';
$string['coursemetadatalocked_help'] = 'Field is locked unless editing user has local/coursemetadata:accessall capability.';

$string['coursemetadatamenudefaultnotinoptions'] = 'The default value is not one of the options';
$string['coursemetadatamenunooptions'] = 'No menu options supplied';
$string['coursemetadatamenuoptions'] = 'Menu options (one per line)';
$string['coursemetadatamenutoofewoptions'] = 'You must provide at least 2 options';

$string['coursemetadataname'] = 'Name';
$string['coursemetadatanofieldsdefined'] = 'No fields have been defined';

$string['coursemetadatarequired'] = 'Is this field required?';
$string['coursemetadatarequired_help'] = 'Field is required unless editing user has local/coursemetadata:accessall capability.';
$string['coursemetadatarestricted'] = 'Restricted editing?';
$string['coursemetadatarestricted_help'] = 'Only users with the local/coursemetadata:restricted capability will be able to edit if this flag is set.';
$string['coursemetadataroles'] = 'coursemetadata visible roles';

$string['coursemetadatashortname'] = 'Short name (must be unique)';
$string['coursemetadatashortnamenotunique'] = 'This short name is already in use';
$string['coursemetadatasignup'] = 'Display on signup page?';
$string['coursemetadataspecificsettings'] = 'Specific settings';

$string['coursemetadatavisible'] = 'Who is this field visible to?';
$string['coursemetadatavisible_help'] = '* Not visible - For private data only viewable by course administrators
* Visible to everyone';
$string['coursemetadatavisibleall'] = 'Visible to everyone';
$string['coursemetadatavisiblenone'] = 'Not visible';

$string['courseswithoutdata'] = 'Courses without metadata';

$string['tasktidycoursemappings'] = 'Tidy redundant course<->metadata mappings.';
