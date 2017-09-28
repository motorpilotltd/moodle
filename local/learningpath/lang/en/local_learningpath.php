<?php
/**
 * English Language File
 *
 * @package    local_learningpath
 */

// Capabilities and settings related strings.
$string['learningpath:add'] = 'Add learning path';
$string['learningpath:delete'] = 'Delete learning path';
$string['learningpath:edit'] = 'Edit learning path';

$string['nopermissions:info'] = 'Manage learning paths';

$string['setting:category'] = 'Learning path category';
$string['setting:category_desc'] = 'Under which category can learning paths be created and use {$a} from';
$string['setting:show_coursemetadata_info'] = 'Show metadata information';
$string['setting:show_coursemetadata_info_desc'] = 'Will show selected fields as metadata alongside {$a} summary.
    <br />NB. fields are sorted as per sortorder set in metadata plugin, i.e. the order in the menu above.';
$string['setting:show_region_filter'] = 'Show region filter';
$string['setting:show_region_filter_desc'] = 'Will show region filtering options in block when viewing learning paths.';
$string['setting:show_region_info'] = 'Show region information';
$string['setting:show_region_info_desc'] = 'Will show region information as metadata alongside {$a} summary.';
$string['setting:show_region_position'] = 'Choose region position';
$string['setting:show_region_position_desc'] = 'Determines the position that region information is injected in metadata.
    <br />NB. Available positions account for possible selection of all available metadata fields, any number higher than the
    total number of selected fields will position region information last in the list.';
$string['settings'] = 'Learning paths configuration';
$string['settings:anycategory'] = 'ANY CATEGORY';
$string['settings:nooptions'] = 'No options available';

// The rest.
$string['actions'] = 'Actions';
$string['add:added'] = 'Your new learning path has been sucessfully created.';
$string['add:cancelled'] = 'Learning path creation cancelled.';
$string['addcolumn'] = 'Add column';
$string['addrow'] = 'Add row';
$string['andukmea'] = '&nbsp;&amp;&nbsp;UKMEA';

$string['backtolearningpath'] = 'Return to learning path';
$string['button:create'] = 'Create';
$string['button:save'] = 'Save';
$string['button:saveandclose'] = 'Save and close';

$string['delete:confirm'] = '<p>Are you sure you want to delete the {$a->what} \'{$a->name}\'?<br /><br />{$a->yeslink}&nbsp;{$a->nolink}</p>';

$string['edit:cancelled'] = 'Learning path editing cancelled.';
$string['edit:saved'] = 'Your learning path has been sucessfully saved.';
$string['edit_cell:cancelled'] = 'Learning path cell editing cancelled.';
$string['edit_cell:saved'] = 'Your learning path cell update has been sucessfully saved.';
$string['error:cell:nodescription'] = 'Please provide a description for this cell or remove all course mappings before saving.';
$string['error:axis:xaxis'] = 'Column';
$string['error:axis:yaxis'] = 'Row';
$string['error:axis:totallimit'] = 'Total number of {$a->axis} names ({$a->count}) cannot exceed {$a->totallimit}.';
$string['error:axisname:empty'] = '{$a->axis} name cannot be empty (line {$a->line}).';
$string['error:axisname:inuse'] = '{$a->axis} name \'{$a->name}\' (line {$a->line}) duplicates an earlier {$a->axis2} name.';
$string['error:axisname:length'] = '{$a->axis} name \'{$a->name}\' (line {$a->line}) is longer than the allowed {$a->length} characters.';
$string['error:couldnotdelete'] = 'Could not delete {$a}.';
$string['error:maxreached:xaxis'] = 'Maximum number of columns has been reached.';
$string['error:maxreached:yaxis'] = 'Maximum number of rows has been reached.';
$string['eventlearningpathcellviewed'] = 'Learning path cell viewed';
$string['eventlearningpathcourseviewed'] = 'Learning path course viewed';
$string['eventlearningpathviewed'] = 'Learning path viewed';

$string['footer:add'] = 'Text TBC';
$string['footer:edit'] = 'Text TBC';
$string['footer:edit_cell'] = 'Text TBC';

$string['header:add'] = 'Text TBC';
$string['header:edit'] = 'Text TBC';
$string['header:edit_cell'] = 'Text TBC';
$string['heading:elective'] = 'Elective modules';
$string['heading:essential'] = 'Essential modules';
$string['heading:recommended'] = 'Recommended modules';
$string['hiddencourse'] = 'HIDDEN {$a}';

$string['label:categoryid'] = 'Please select the category you would like to associate this learning path with';
$string['label:categoryid_help'] = 'Coming soon...';
$string['label:celldescription'] = 'Description of requirement';
$string['label:celldescription_help'] = 'Coming soon..';
$string['label:description'] = 'Description';
$string['label:elective'] = 'Elective {$a}';
$string['label:elective_help'] = 'Coming soon..';
$string['label:essential'] = 'Essential {$a}';
$string['label:essential_help'] = 'Coming soon..';
$string['label:name'] = 'Learning path title';
$string['label:name_help'] = 'Coming soon...';
$string['label:recommended'] = 'Recommended {$a}';
$string['label:recommended_help'] = 'Coming soon..';
$string['label:visible'] = 'Status';
$string['label:xaxis'] = 'Column names';
$string['label:xaxis_help'] = 'Coming soon...';
$string['label:yaxis'] = 'Row names';
$string['label:yaxis_help'] = 'Coming soon...';
$string['learningpath'] = 'Learning path';
$string['learningpath:add'] = 'Add learning path';
$string['learningpath:delete'] = 'Delete';
$string['learningpath:edit'] = 'Edit';
$string['learningpaths'] = 'Learning paths';

$string['newcolumn'] = 'New column';
$string['newrow'] = 'New row';
$string['nolearningpaths'] = 'No learning paths';
$string['nomodules'] = 'No modules.';
$string['nomodules:elective'] = 'There are no elective modules.';
$string['nomodules:essential'] = 'There are no essential modules.';
$string['nomodules:recommended'] = 'There are no recommended modules.';
$string['nosummary'] = 'No summary available...';
$string['notsetcategory'] = 'NOT SET';

$string['option:hidden'] = 'Draft';
$string['option:visible'] = 'Published';

$string['pluginname'] = 'Learning paths';

$string['title:add'] = 'Add learning path';
$string['title:edit'] = 'Edit learning path - {$a}';
$string['title:edit_cell'] = 'Edit cell ({$a->x}, {$a->y})';
$string['title:view_cell'] = '{$a->name} ({$a->x}, {$a->y})';

$string['visible'] = 'Published';

// Fake Filter Block
$string['allregions'] = 'All regions';

$string['currentregion'] = 'Your region is <strong>{$a}</strong>';
$string['currentsubregion'] = 'Your location is <strong>{$a}</strong>';

$string['filter'] = 'Region filter';

$string['locationfilters'] = 'Filter by region';
$string['locationfilters_help'] = 'The chosen options will be used to update the filtered results until changed or you log out.';

$string['notspecified'] = 'Not specified';

$string['region'] = 'Region';

$string['subregion'] = 'Location';

$string['ukmea:show'] = 'Show <strong>UKMEA</strong> modules';