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
 *
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die();

class local_regions_form_editregion extends local_regions_form {

    public function definition() {
        $this->_form->addElement('hidden', 'id', '', array('id' => 'id_region'));
        $this->_form->setType('id', PARAM_INT);

        $this->_form->addElement('header', 'region', $this->get_string('regiondetails'));

        $this->_form->addElement('text', 'name', $this->get_string('form:name:region'));
        $this->_form->setType('name', PARAM_TEXT);
        $this->_form->addRule('name', null, 'required', null, 'server');

        $this->_form->addElement('text', 'tapsname', $this->get_string('form:name:tapsname'));
        $this->_form->setType('tapsname', PARAM_TEXT);
        $this->_form->addRule('tapsname', null, 'required', null, 'server');

        $this->_form->addElement('static', 'tapsnamehint', '', $this->get_string('form:hint:tapsname'));

        $this->_form->addElement('selectyesno', 'userselectable', $this->get_string('form:name:userselectable'));
        $this->_form->setDefault('userselectable', 1);

        $this->add_action_buttons(true, $this->get_string_fromcore('submit'));
    }

    public function validation($data, $files) {
        $errors = array();

        if ($data['name'] == '') {
            $errors['name'] = $this->get_string('required', $this->get_string('form:name:region'));
        }

        return $errors;
    }
}
