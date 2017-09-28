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

class local_regions_form_bulkaddregions extends local_regions_form {

    public function definition() {
        $this->_form->addElement('header', 'region', $this->get_string('regiondetails'));

        $this->_form->addElement('textarea', 'details', $this->get_string('form:details:region'));
        $this->_form->setType('details', PARAM_TEXT);
        $this->_form->addRule('details', null, 'required', null, 'server');

        $this->_form->addElement('static', 'detailshint', '', $this->get_string('form:hint:details:region'));

        $this->add_action_buttons(true, $this->get_string_fromcore('submit'));
    }

    public function validation($data, $files) {
        $errors = array();

        if ($data['details'] == '') {
            $errors['details'] = $this->get_string('required', $this->get_string('form:details:region'));
        }

        return $errors;
    }
}
