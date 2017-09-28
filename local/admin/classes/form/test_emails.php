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

namespace local_admin\form;

defined('MOODLE_INTERNAL') || die();

class test_emails extends \moodleform {

    protected $_textreplacements;

    public function definition() {
        global $DB, $USER;

        $mform =& $this->_form;

        $mform->addElement('header', 'header-email', get_string('testemail', 'local_admin'));
        $mform->setExpanded('header-email', true);

        $mform->addElement('text', 'to', get_string('testemails:to', 'local_admin'));
        $mform->setType('to', PARAM_RAW);
        $mform->addRule('to', get_string('required'), 'required', null, 'client');
        $mform->setDefault('to', $USER->email);

        $mform->addElement('text', 'cc', get_string('testemails:cc', 'local_admin'));
        $mform->setType('cc', PARAM_RAW);

        $mform->addElement('text', 'subject', get_string('testemails:subject', 'local_admin'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        $mform->setDefault('subject', get_string('testemails:subject:default', 'local_admin'));

        $mform->addElement('checkbox', 'html', get_string('testemails:usehtml', 'local_admin'));
        $mform->setDefault('html', 1);

        $mform->addElement('textarea', 'body', get_string('testemails:body', 'local_admin'));
        $mform->setType('body', PARAM_RAW);
        $mform->addRule('body', get_string('required'), 'required', null, 'client');

        if (get_config('local_invites', 'version')) {
            $mform->addElement('checkbox', 'invite', get_string('testemails:sendinvite', 'local_admin'));
        }

        // Submit buttons.
        $this->add_action_buttons(false, get_string('testemails:send', 'local_admin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $emails = array('to', 'cc');
        foreach ($emails as $email) {
            if (!empty($data[$email]) && !validate_email($data[$email])) {
                $errors[$email] = get_string('err_email', 'form');
            }
        }

        return $errors;
    }
}
