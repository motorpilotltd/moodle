<?php
// This file is part of the Arup cost centre system
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
 * Version details
 *
 * @package     local_costcentre
 * @copyright   2017 Motorpilot Ltd
 * @author      Aleks Daloso
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\local\form;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class user_select extends \moodleform {

    private $costcentre;

    public function definition() {
        $mform =& $this->_form;

        $this->costcentre = $this->_customdata['costcentre'];
        $currentuser = $this->costcentre->currentuser;
        $selected = !empty($currentuser) ? array($currentuser->id => $currentuser->name) : array();

        $mform->addElement(
            'select',
            'user',
            get_string('selectuser', 'local_costcentre'),
            $selected,
            array('class' => 'select2 select2-user', 'data-placeholder' => get_string('chooseoneuser', 'local_costcentre')));

        $mform->addElement('hidden', 'action', $this->costcentre->validaction);
        $mform->setType('action', PARAM_ALPHA);
        $this->add_action_buttons(false, get_string('loaduser', 'local_costcentre'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Needed here due to use of Select2.
        if (empty($data['costcentre'])) {
            $errors['costcentre'] = get_string('required');
        }

        return $errors;
    }

    public function render() {
        // Set data before calling parent render function.
        $data = new stdClass();
        $data->costcentre = $this->costcentre->costcentre;
        $this->set_data($data);

        return parent::render();
    }
}