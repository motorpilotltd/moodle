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
 * This file contains the category form used for coursemetadata fields.
 *
 * @package local_coursemetadata
 * @copyright 2016 Motorpilot Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class category_form
 *
 * @copyright 2016 Motorpilot Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        $mform = $this->_form;

        $strrequired = get_string('required');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'editcategory');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'name', get_string('coursemetadatacategoryname', 'local_coursemetadata'), 'maxlength="255" size="30"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', $strrequired, 'required', null, 'client');

        $this->add_action_buttons(true);

    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $data  = (object)$data;

        $duplicate = $DB->record_exists('coursemetadata_info_category', array('name' => $data->name));

        // Check the name is unique.
        if (!empty($data->id)) { // We are editing an existing record.
            $olddata = $DB->get_record('coursemetadata_info_category', array('id' => $data->id));
            // Name has changed, new name in use, new name in use by another record.
            $dupfound = (($olddata->name !== $data->name) && $duplicate && ($data->id != $duplicate->id));
        } else { // New coursemetadata category.
            $dupfound = $duplicate;
        }

        if ($dupfound ) {
            $errors['name'] = get_string('coursemetadatacategorynamenotunique', 'local_coursemetadata');
        }

        return $errors;
    }
}


