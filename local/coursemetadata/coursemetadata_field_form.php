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
 * This file contains the field form used for coursemetadata fields.
 *
 * @package local_coursemetadata
 * @copyright 2016 Motorpilot Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class coursemetadata_field_form.
 *
 * @copyright 2016 Motorpilot Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemetadata_field_form extends moodleform {

    /** @var define_base */
    public $field;

    /**
     * Define the form
     */
    public function definition () {
        global $CFG;

        $mform = $this->_form;

        // Everything else is dependant on the data type.
        $datatype = $this->_customdata;
        require_once($CFG->dirroot.'/local/coursemetadata/field/'.$datatype.'/define.class.php');

        $newfield = 'coursemetadata_define_'.$datatype;
        $this->field = new $newfield();

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'editfield');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'datatype', $datatype);
        $mform->setType('datatype', PARAM_ALPHA);

        $this->field->define_form($mform);

        $this->add_action_buttons(true);
    }

    /**
     * Alter definition based on existing or submitted data
     */
    public function definition_after_data () {
        $mform = $this->_form;
        $this->field->define_after_data($mform);
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        return $this->field->define_validate($data, $files);
    }

    /**
     * Returns the defined editors for the field.
     *
     * @return mixed
     */
    public function editors() {
        return $this->field->define_editors();
    }

    /**
     * Set the default data.
     *
     * @param stdClass|array $defaultvalues
     */
    public function set_data($defaultvalues) {
        $this->field->set_data($defaultvalues);
        parent::set_data($defaultvalues);
    }
}
