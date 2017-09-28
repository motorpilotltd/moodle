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
 * Library file.
 *
 * @package     local_coursemetadata
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Some constants.
define ('COURSEMETADATA_VISIBLE_ALL',     '2'); // Visible to all.
define ('COURSEMETADATA_VISIBLE_PRIVATE', '1'); // Visible to those with moodle/course:update capability.
define ('COURSEMETADATA_VISIBLE_NONE',    '0'); // Visible to those with moodle/course:update capability, synonymous with PRIVATE.

/**
 * Load coursemetadata in to course.
 *
 * @param stdClass $course
 */
function coursemetadata_load_data($course) {
    global $CFG, $DB;

    if ($fields = $DB->get_records('coursemetadata_info_field')) {
        foreach ($fields as $field) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
            $newfield = 'coursemetadata_field_'.$field->datatype;
            $formfield = new $newfield($field->id, $course->id);
            $formfield->edit_load_course_data($course);
        }
    }
}

/**
 * Print out the coursemetadata categories and fields for course.
 *
 * @param moodleform $mform
 * @return void
 */
function coursemetadata_definition($mform) {
    global $CFG, $DB;

    $update = has_capability('moodle/course:update', context_system::instance());
    $restricted = has_capability('local/coursemetadata:restricted', context_system::instance());

    if ($categories = $DB->get_records('coursemetadata_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('coursemetadata_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {

                // Check first if *any* fields will be displayed.
                $display = false;
                foreach ($fields as $field) {
                    if (($field->visible != COURSEMETADATA_VISIBLE_NONE || $update) && ($field->restricted == 0 || $restricted)) {
                        $display = true;
                    }
                }

                // Display the header and the fields.
                if ($display) {
                    $mform->addElement('header', 'category_'.$category->id, format_string($category->name));
                    $mform->setExpanded('category_'.$category->id, true, true);
                    foreach ($fields as $field) {
                        require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
                        $newfield = 'coursemetadata_field_'.$field->datatype;
                        $formfield = new $newfield($field->id);
                        $formfield->edit_field($mform);
                    }
                }
            }
        }
    }
}

/**
 * Carry out the coursemetadata definition after data processing.
 *
 * @param moodleform $mform
 * @param int $courseid
 * @return void
 */
function coursemetadata_definition_after_data($mform, $courseid) {
    global $CFG, $DB;

    $courseid = ($courseid < 0) ? 0 : (int)$courseid;

    if ($fields = $DB->get_records('coursemetadata_info_field')) {
        foreach ($fields as $field) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
            $newfield = 'coursemetadata_field_'.$field->datatype;
            $formfield = new $newfield($field->id, $courseid);
            $formfield->edit_after_data($mform);
        }
    }
}

/**
 * Carry out the coursemetadata validation.
 *
 * @param object $coursenew
 * @param array $files
 * @return array
 */
function coursemetadata_validation($coursenew, $files) {
    global $CFG, $DB;

    $err = array();
    if ($fields = $DB->get_records('coursemetadata_info_field')) {
        foreach ($fields as $field) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
            $newfield = 'coursemetadata_field_'.$field->datatype;
            $formfield = new $newfield($field->id, $coursenew->id);
            $err += $formfield->edit_validate_field($coursenew, $files);
        }
    }
    return $err;
}

/**
 * Save the coursemetadata.
 *
 * @param object $coursenew
 * @return void
 */
function coursemetadata_save_data($coursenew) {
    global $CFG, $DB;
    if ($fields = $DB->get_records('coursemetadata_info_field')) {
        foreach ($fields as $field) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
            $newfield = 'coursemetadata_field_'.$field->datatype;
            $formfield = new $newfield($field->id, $coursenew->id);
            $formfield->edit_save_data($coursenew);
        }
    }
}

/**
 * Display the coursemetadata fields.
 *
 * @param int $courseid
 * @return void
 */
function coursemetadata_display_fields($courseid) {
    global $CFG, $USER, $DB;

    if ($categories = $DB->get_records('coursemetadata_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('coursemetadata_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
                foreach ($fields as $field) {
                    require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
                    $newfield = 'coursemetadata_field_'.$field->datatype;
                    $formfield = new $newfield($field->id, $courseid);
                    if ($formfield->is_visible() and !$formfield->is_empty()) {
                        print_row(format_string($formfield->field->name.':'), $formfield->display_data());
                    }
                }
            }
        }
    }
}

/**
 * Returns an object with the coursemetadata fields set for the given course.
 *
 * @param int $courseid
 * @return object
 */
function coursemetadata_course_record($courseid) {
    global $CFG, $DB;

    $coursecustomfields = new stdClass();

    if ($fields = $DB->get_records('coursemetadata_info_field')) {
        foreach ($fields as $field) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
            $newfield = 'coursemetadata_field_'.$field->datatype;
            $formfield = new $newfield($field->id, $courseid);
            if ($formfield->is_course_object_data()) {
                $coursecustomfields->{$field->shortname} = $formfield->data;
            }
        }
    }

    return $coursecustomfields;
}

/**
 * Reorder the coursemetadata fields within a given category starting at the field at the given startorder.
 */
function coursemetadata_reorder_fields() {
    global $DB;

    if ($categories = $DB->get_records('coursemetadata_info_category')) {
        foreach ($categories as $category) {
            $i = 1;
            if ($fields = $DB->get_records('coursemetadata_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
                foreach ($fields as $field) {
                    $f = new stdClass();
                    $f->id = $field->id;
                    $f->sortorder = $i++;
                    $DB->update_record('coursemetadata_info_field', $f);
                }
            }
        }
    }
}

/**
 * Reorder the coursemetadata categoriess starting at the category at the given startorder.
 */
function coursemetadata_reorder_categories() {
    global $DB;

    $i = 1;
    if ($categories = $DB->get_records('coursemetadata_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $cat) {
            $c = new stdClass();
            $c->id = $cat->id;
            $c->sortorder = $i++;
            $DB->update_record('coursemetadata_info_category', $c);
        }
    }
}

/**
 * Delete a coursemetadata category.
 *
 * @param int $id of the category to be deleted
 * @return bool success of operation
 */
function coursemetadata_delete_category($id) {
    global $DB;

    // Retrieve the category.
    if (!$category = $DB->get_record('coursemetadata_info_category', array('id' => $id))) {
        print_error('invalidcategoryid');
    }

    if (!$categories = $DB->get_records('coursemetadata_info_category', null, 'sortorder ASC')) {
        print_error('nocate', 'debug');
    }

    unset($categories[$category->id]);

    if (!count($categories)) {
        return false; // We can not delete the last category.
    }

    // Does the category contain any fields.
    if ($DB->count_records('coursemetadata_info_field', array('categoryid' => $category->id))) {
        if (array_key_exists($category->sortorder - 1, $categories)) {
            $newcategory = $categories[$category->sortorder - 1];
        } else if (array_key_exists($category->sortorder + 1, $categories)) {
            $newcategory = $categories[$category->sortorder + 1];
        } else {
            $newcategory = reset($categories); // Get first category if sortorder broken.
        }

        $sortorder = $DB->count_records('coursemetadata_info_field', array('categoryid' => $newcategory->id)) + 1;

        if ($fields = $DB->get_records('coursemetadata_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
            foreach ($fields as $field) {
                $f = new stdClass();
                $f->id = $field->id;
                $f->sortorder = $sortorder++;
                $f->categoryid = $newcategory->id;
                $DB->update_record('coursemetadata_info_field', $f);
            }
        }
    }

    // Finally we get to delete the category.
    $DB->delete_records('coursemetadata_info_category', array('id' => $category->id));
    coursemetadata_reorder_categories();
    return true;
}

/**
 * Deletes a coursemetadata field.
 *
 * @param int $id
 */
function coursemetadata_delete_field($id) {
    global $CFG, $DB;

    $field = $DB->get_record('coursemetadata_info_field', array('id' => $id));

    if (!$field) {
        print_error('cannotdeletecustomfield');
    }

    // Remove any coursemetadata associated with this field.
    if (!$DB->delete_records('coursemetadata_info_data', array('fieldid' => $id))) {
        print_error('cannotdeletecustomfield');
    }

    require_once($CFG->dirroot.'/local/coursemetadata/field/'.$field->datatype.'/field.class.php');
    $fieldclassname = 'coursemetadata_field_'.$field->datatype;
    $fieldclass = new $fieldclassname($field->id);

    $fieldclass->delete_field();

    // Note: Any availability conditions that depend on this field will remain,
    // but show the field as missing until manually corrected to something else.

    // Need to rebuild course cache to update the info.
    rebuild_course_cache();

    // Try to remove the record from the database.
    $DB->delete_records('coursemetadata_info_field', array('id' => $id));

    // Reorder the remaining fields in the same category.
    coursemetadata_reorder_fields();
}

/**
 * Change the sort order of a field.
 *
 * @param int $id of the field
 * @param string $move direction of move
 * @return bool success of operation
 */
function coursemetadata_move_field($id, $move) {
    global $DB;

    // Get the field object.
    if (!$field = $DB->get_record('coursemetadata_info_field', array('id' => $id), 'id, sortorder, categoryid')) {
        return false;
    }
    // Count the number of fields in this category.
    $fieldcount = $DB->count_records('coursemetadata_info_field', array('categoryid' => $field->categoryid));

    // Calculate the new sort order.
    if (($move == 'up') and ($field->sortorder > 1)) {
        $neworder = $field->sortorder - 1;
    } else if (($move == 'down') and ($field->sortorder < $fieldcount)) {
        $neworder = $field->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the field object that is currently residing in the new position.
    $params = array('categoryid' => $field->categoryid, 'sortorder' => $neworder);
    if ($swapfield = $DB->get_record('coursemetadata_info_field', $params, 'id, sortorder')) {

        // Swap the sort orders.
        $swapfield->sortorder = $field->sortorder;
        $field->sortorder     = $neworder;

        // Update the field records.
        $DB->update_record('coursemetadata_info_field', $field);
        $DB->update_record('coursemetadata_info_field', $swapfield);
    }

    coursemetadata_reorder_fields();
    return true;
}

/**
 * Change the sort order of a category.
 *
 * @param int $id of the category
 * @param string $move direction of move
 * @return bool success of operation
 */
function coursemetadata_move_category($id, $move) {
    global $DB;
    // Get the category object.
    if (!($category = $DB->get_record('coursemetadata_info_category', array('id' => $id), 'id, sortorder'))) {
        return false;
    }

    // Count the number of categories.
    $categorycount = $DB->count_records('coursemetadata_info_category');

    // Calculate the new sort order.
    if (($move == 'up') and ($category->sortorder > 1)) {
        $neworder = $category->sortorder - 1;
    } else if (($move == 'down') and ($category->sortorder < $categorycount)) {
        $neworder = $category->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the category object that is currently residing in the new position.
    if ($swapcategory = $DB->get_record('coursemetadata_info_category', array('sortorder' => $neworder), 'id, sortorder')) {

        // Swap the sortorders.
        $swapcategory->sortorder = $category->sortorder;
        $category->sortorder     = $neworder;

        // Update the category records.
        $DB->update_record('coursemetadata_info_category', $category) and $DB->update_record('coursemetadata_info_category', $swapcategory);
        return true;
    }

    return false;
}

/**
 * Retrieve a list of all the available data types.
 *
 * @return array a list of the datatypes suitable to use in a select statement
 */
function coursemetadata_list_datatypes() {
    $datatypes = array();

    $plugins = core_component::get_plugin_list('coursemetadatafield');
    foreach ($plugins as $type => $unused) {
        $datatypes[$type] = get_string('pluginname', 'coursemetadatafield_'.$type);
    }
    asort($datatypes);

    return $datatypes;
}

/**
 * Retrieve a list of categories and ids suitable for use in a form.
 *
 * @return array
 */
function coursemetadata_list_categories() {
    global $DB;
    if (!$categories = $DB->get_records_menu('coursemetadata_info_category', null, 'sortorder ASC', 'id, name')) {
        $categories = array();
    }
    return $categories;
}


/**
 * Edit a category
 *
 * @param int $id
 * @param string $redirect
 */
function coursemetadata_edit_category($id, $redirect) {
    global $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot.'/local/coursemetadata/index_category_form.php');
    $categoryform = new category_form();

    if ($category = $DB->get_record('coursemetadata_info_category', array('id' => $id))) {
        $categoryform->set_data($category);
    }

    if ($categoryform->is_cancelled()) {
        redirect($redirect);
    } else {
        if ($data = $categoryform->get_data()) {
            if (empty($data->id)) {
                unset($data->id);
                $data->sortorder = $DB->count_records('coursemetadata_info_category') + 1;
                $DB->insert_record('coursemetadata_info_category', $data, false);
            } else {
                $DB->update_record('coursemetadata_info_category', $data);
            }
            coursemetadata_reorder_categories();
            redirect($redirect);
        }

        if (empty($id)) {
            $strheading = get_string('coursemetadatacreatenewcategory', 'local_coursemetadata');
        } else {
            $strheading = get_string('coursemetadataeditcategory', 'local_coursemetadata', format_string($category->name));
        }

        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $categoryform->display();
        echo $OUTPUT->footer();
        die;
    }

}

/**
 * Edit a coursemetadata field.
 *
 * @param int $id
 * @param string $datatype
 * @param string $redirect
 */
function coursemetadata_edit_field($id, $datatype, $redirect) {
    global $CFG, $DB, $OUTPUT, $PAGE;

    if (!$field = $DB->get_record('coursemetadata_info_field', array('id' => $id))) {
        $field = new stdClass();
        $field->datatype = $datatype;
        $field->description = '';
        $field->descriptionformat = FORMAT_HTML;
        $field->defaultdata = '';
        $field->defaultdataformat = FORMAT_HTML;
    }

    // Clean and prepare description for the editor.
    $field->description = clean_text($field->description, $field->descriptionformat);
    $field->description = array('text' => $field->description, 'format' => $field->descriptionformat, 'itemid' => 0);

    require_once($CFG->dirroot.'/local/coursemetadata/coursemetadata_field_form.php');
    $fieldform = new coursemetadata_field_form(null, $field->datatype);

    // Convert the data format.
    if (is_array($fieldform->editors())) {
        foreach ($fieldform->editors() as $editor) {
            if (isset($field->$editor)) {
                $field->$editor = clean_text($field->$editor, $field->{$editor.'format'});
                $field->$editor = array('text' => $field->$editor, 'format' => $field->{$editor.'format'}, 'itemid' => 0);
            }
        }
    }

    $fieldform->set_data($field);

    if ($fieldform->is_cancelled()) {
        redirect($redirect);

    } else {
        if ($data = $fieldform->get_data()) {
            require_once($CFG->dirroot.'/local/coursemetadata/field/'.$datatype.'/define.class.php');
            $newfield = 'coursemetadata_define_'.$datatype;
            $formfield = new $newfield();

            // Collect the description and format back into the proper data structure from the editor.
            // Note: This field will ALWAYS be an editor.
            $data->descriptionformat = $data->description['format'];
            $data->description = $data->description['text'];

            // Check whether the default data is an editor, this is (currently) only the textarea field type.
            if (is_array($data->defaultdata) && array_key_exists('text', $data->defaultdata)) {
                // Collect the default data and format back into the proper data structure from the editor.
                $data->defaultdataformat = $data->defaultdata['format'];
                $data->defaultdata = $data->defaultdata['text'];

            }

            // Convert the data format.
            if (is_array($fieldform->editors())) {
                foreach ($fieldform->editors() as $editor) {
                    if (isset($field->$editor)) {
                        $field->{$editor.'format'} = $field->{$editor}['format'];
                        $field->$editor = $field->{$editor}['text'];
                    }
                }
            }

            $formfield->define_save($data);
            coursemetadata_reorder_fields();
            coursemetadata_reorder_categories();
            redirect($redirect);
        }

        $datatypes = coursemetadata_list_datatypes();

        if (empty($id)) {
            $strheading = get_string('coursemetadatacreatenewfield', 'local_coursemetadata', $datatypes[$datatype]);
        } else {
            $strheading = get_string('coursemetadataeditfield', 'local_coursemetadata', $field->name);
        }

        // Print the page.
        $PAGE->navbar->add($strheading);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $fieldform->display();
        echo $OUTPUT->footer();
        die;
    }
}

/**
 * Serves plugin files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function local_coursemetadata_pluginfile($course,
                $cm,
                context $context,
                $filearea,
                $args,
                $forcedownload,
                array $options=array()) {
    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'local_coursemetadata', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
