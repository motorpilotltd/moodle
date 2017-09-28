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
 * This file contains the define_base class.
 *
 * @package local_coursemetadata
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemetadata;

defined('MOODLE_INTERNAL') || die();

/**
 * Class couremetadata_define_base.
 *
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class define_base {

    /**
     * Prints out the form snippet for creating or editing a coursemetadata field.
     *
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form($form) {
        $form->addElement('header', '_commonsettings', get_string('coursemetadatacommonsettings', 'local_coursemetadata'));
        $this->define_form_common($form);

        $form->addElement('header', '_specificsettings', get_string('coursemetadataspecificsettings', 'local_coursemetadata'));
        $this->define_form_specific($form);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a coursemetadata field common to all data types.
     *
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form_common($form) {

        $strrequired = get_string('required');

        $form->addElement('text', 'shortname', get_string('coursemetadatashortname', 'local_coursemetadata'), 'maxlength="100" size="25"');
        $form->addRule('shortname', $strrequired, 'required', null, 'client');
        $form->setType('shortname', PARAM_ALPHANUM);

        $form->addElement('text', 'name', get_string('coursemetadataname', 'local_coursemetadata'), 'size="50"');
        $form->addRule('name', $strrequired, 'required', null, 'client');
        $form->setType('name', PARAM_MULTILANG);

        $form->addElement('editor', 'description', get_string('coursemetadatadescription', 'local_coursemetadata'), null, null);

        $form->addElement('selectyesno', 'required', get_string('coursemetadatarequired', 'local_coursemetadata'));
        $form->addHelpButton('required', 'coursemetadatarequired', 'local_coursemetadata');

        $form->addElement('selectyesno', 'locked', get_string('coursemetadatalocked', 'local_coursemetadata'));
        $form->addHelpButton('locked', 'coursemetadatalocked', 'local_coursemetadata');

        $form->addElement('selectyesno', 'forceunique', get_string('coursemetadataforceunique', 'local_coursemetadata'));

        $form->addElement('selectyesno', 'restricted', get_string('coursemetadatarestricted', 'local_coursemetadata'));
        $form->addHelpButton('restricted', 'coursemetadatarestricted', 'local_coursemetadata');

        $choices = array();
        $choices[COURSEMETADATA_VISIBLE_NONE]    = get_string('coursemetadatavisiblenone', 'local_coursemetadata');
        $choices[COURSEMETADATA_VISIBLE_ALL]     = get_string('coursemetadatavisibleall', 'local_coursemetadata');
        $form->addElement('select', 'visible', get_string('coursemetadatavisible', 'local_coursemetadata'), $choices);
        $form->addHelpButton('visible', 'coursemetadatavisible', 'local_coursemetadata');
        $form->setDefault('visible', COURSEMETADATA_VISIBLE_ALL);

        $choices = coursemetadata_list_categories();
        $form->addElement('select', 'categoryid', get_string('coursemetadatacategory', 'local_coursemetadata'), $choices);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a coursemetadata field specific to the current data type.
     *
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form_specific($form) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Validate the data from the add/edit coursemetadata field form.
     *
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function define_validate($data, $files) {

        $data = (object)$data;
        $err = array();

        $err += $this->define_validate_common($data, $files);
        $err += $this->define_validate_specific($data, $files);

        return $err;
    }

    /**
     * Validate the data from the add/edit coursemetadata field form that is common to all data types.
     * 
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @param array $files
     * @return arrayassociative array of error messages
     */
    public function define_validate_common($data, $files) {
        global $DB;

        $err = array();

        // Check the shortname was not truncated by cleaning.
        if (empty($data->shortname)) {
            $err['shortname'] = get_string('required');

        } else {
            // Fetch field-record from DB.
            $field = $DB->get_record('coursemetadata_info_field', array('shortname' => $data->shortname));
            // Check the shortname is unique.
            if ($field and $field->id <> $data->id) {
                $err['shortname'] = get_string('coursemetadatashortnamenotunique', 'local_coursemetadata');
            }
        }

        // No further checks necessary as the form class will take care of it.
        return $err;
    }

    /**
     * Validate the data from the add/edit coursemetadata field form that is specific to the current data type.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function define_validate_specific($data, $files) {
        // Do nothing - overwrite if necessary.
        return array();
    }

    /**
     * Alter form based on submitted or existing data.
     *
     * @param moodleform $mform
     */
    public function define_after_data($mform) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Add a new coursemetadata field or save changes to current field.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @return bool status of the insert/update record
     */
    public function define_save($data) {
        global $DB;

        $data = $this->define_save_preprocess($data); // Hook for child classes.

        $old = false;
        if (!empty($data->id)) {
            $old = $DB->get_record('coursemetadata_info_field', array('id' => (int)$data->id));
        }

        // Check to see if the category has changed.
        if (!$old or $old->categoryid != $data->categoryid) {
            $data->sortorder = $DB->count_records('coursemetadata_info_field', array('categoryid' => $data->categoryid)) + 1;
        }

        if (empty($data->id)) {
            unset($data->id);
            $data->id = $DB->insert_record('coursemetadata_info_field', $data);
        } else {
            $DB->update_record('coursemetadata_info_field', $data);
        }

        $this->define_save_postprocess($data); // Hook for child classes.
    }

    /**
     * Preprocess data from the add/edit coursemetadata field form before it is saved.
     * 
     * This method is a hook for the child classes to overwrite.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @return stdClass|array processed data object
     */
    public function define_save_preprocess($data) {
        // Do nothing - overwrite if necessary.
        return $data;
    }

    /**
     * Postprocess saved data from the add/edit coursemetadata field form.
     *
     * This method is a hook for the child classes to overwrite.
     *
     * @param stdClass|array $data from the add/edit coursemetadata field form
     * @return stdClass|array processed data object
     */
    public function define_save_postprocess($data) {
        // Do nothing - overwrite if necessary.
        return $data;
    }

    /**
     * Provides a method by which we can allow the default data in coursemetadata_define_* to use an editor.
     *
     * This should return an array of editor names (which will need to be formatted/cleaned).
     *
     * @return array
     */
    public function define_editors() {
        return array();
    }

    /**
     * Set default data.
     *
     * @param stdClass $defaultvalues
     */
    public function set_data($defaultvalues) {
        // Do nothing, override if necessary.
    }
}
