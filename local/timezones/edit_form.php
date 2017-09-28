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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/lib/formslib.php');

class timezone_form extends moodleform
{
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'timezone', get_string('timezone', 'local_timezones'), core_date::get_list_of_timezones());
        $mform->addRule('timezone', get_string('required'), 'required');
        $mform->setType('timezone', PARAM_SAFEPATH);

        $mform->addElement('text', 'display', get_string('display', 'local_timezones'), 'size="50"');
        $mform->addRule('display', get_string('required'), 'required');
        $mform->setType('display', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('savechanges'));
    }
}
