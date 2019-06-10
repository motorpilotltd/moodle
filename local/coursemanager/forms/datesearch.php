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

class cmform_datesearch extends moodleform {
    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;
        if ($data['hiddenfields']) {
            $hiddenfields = $data['hiddenfields'];
            foreach ($hiddenfields as $field) {
                $mform->addElement('hidden', $field->name, $field->value);
                $mform->setType($field->name, PARAM_TEXT);
            }
        }
        $mform->addElement('date_selector', 'datesearch', '');
        $this->add_action_buttons(false, 'Submit');
    }
}