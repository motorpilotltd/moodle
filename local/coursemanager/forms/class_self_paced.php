<?php
// This file is part of the Arup Course Management system
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
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/local/coursemanager/forms/class.php');

class cmform_class_self_paced extends cmform_class {

    public function definition() {
        parent::definition();
        $mform = $this->_form;

        $mform->addElement("hidden", "classtype", "Self Paced");
        $mform->setType('classtype', PARAM_TEXT);

        $mform->addRule('classdurationunitscode', get_string('required', 'local_coursemanager'), 'required', null, 'client');
        $mform->addRule('classduration', get_string('required', 'local_coursemanager'), 'required', null, 'client');

        $mform->removeElement('location');
        $mform->removeElement('trainingcenter');

        $mform->removeElement('page');

        $mform->removeElement('classendtime');
        $endgroup = $this->optional_time_selector('classendtime');
        $mform->insertElementBefore($endgroup, 'enrolmentstartdate');
        
        $this->add_element("page", "hidden", PARAM_TEXT, null, "class_self_paced");

        $classstatus = array(
            "Normal" => $this->str('class_self_paced_normal'),
            "Planned" => $this->str('class_self_paced_planned')
        ); // 'Planned' indicates waiting list.
        // add help
        $status = $this->create_element("classstatus", "select", $classstatus);
        $mform->insertElementBefore($status, 'coursenamedisplay');

        $max = $mform->createElement('advcheckbox', 'unlimitedattendees', get_string('form:class:unlimitedattendees', 'local_coursemanager'), '', array('group' => 1), array(0, 1));
        $mform->insertElementBefore($max, 'maximumattendees');
        $mform->setDefault('unlimitedattendees', 1);
        $mform->disabledIf("maximumattendees", "unlimitedattendees", 'eq', 1);
    }

    public function definition_after_data() {
        $mform = $this->_form;
        // Necessary as seem to hang on to previous values (when editing and changing type) for some reason.
        $mform->getElement('classtype')->setValue('Self Paced');
    }
    
    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        if ($data['classdurationunitscode'] == "0") {
            $errors['classdurationunitscode'] = get_string('required', 'local_coursemanager');
        }
        return $errors;
    }
}