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

namespace mod_tapsenrol;
/**
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class cmform_class_selfpaced extends cmform_class {

    public function definition() {
        parent::definition();
        $mform = $this->_form;

        $mform->addRule('classdurationunitscode', get_string('required', 'tapsenrol'), 'required', null, 'client');
        $mform->addRule('classduration', get_string('required', 'tapsenrol'), 'required', null, 'client');

        $mform->removeElement('location');
        $mform->removeElement('trainingcenter');
        $mform->removeElement('onlineurl');

        $mform->removeElement('classendtime');
        $endgroup = $this->optional_time_selector('classendtime');
        $mform->insertElementBefore($endgroup, 'enrolmentstartdate');

        $max = $mform->createElement('advcheckbox', 'unlimitedattendees', get_string('form:class:unlimitedattendees', 'tapsenrol'), '', array('group' => 1), array(0, 1));
        $mform->insertElementBefore($max, 'maximumattendees');
        $mform->setDefault('unlimitedattendees', 1);
        $mform->disabledIf("maximumattendees", "unlimitedattendees", 'eq', 1);
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        if ($data['classdurationunitscode'] == "0") {
            $errors['classdurationunitscode'] = get_string('required', 'tapsenrol');
        }
        return $errors;
    }
}