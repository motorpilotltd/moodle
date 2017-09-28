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

require_once($CFG->libdir.'/formslib.php');

class mod_tapsenrol_enrol_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        if ($this->_customdata['enrolmentkey']) {
            // There's an enrolment key.
            $mform->addElement('text', 'enrolmentkey', get_string('enrol:enrolmentkey', 'tapsenrol'));
            $mform->setType('enrolmentkey', PARAM_TEXT);
            // Server side validation to avoid issues with JS preventing multiple clicks.
            $mform->addRule('enrolmentkey', null, 'required', null, 'server');
        }

        if ($this->_customdata['internalworkflow'] && $this->_customdata['internalworkflow']->approvalrequired) {
            if ($this->_customdata['internalworkflow']->sponsors) {
                // Selection.
                $sponsors = explode("\n", $this->_customdata['internalworkflow']->sponsors);
                $options = array('' => get_string('choosedots')) + array_combine($sponsors, $sponsors);
                $mform->addElement('select', 'sponsoremail', get_string('enrol:sponsoremail', 'tapsenrol'), $options);
            } else {
                // Free text.
                $mform->addElement('text', 'sponsoremail', get_string('enrol:sponsoremail', 'tapsenrol'));
                $mform->setType('sponsoremail', PARAM_TEXT);
            }
            $mform->addHelpButton('sponsoremail', 'enrol:sponsoremail', 'tapsenrol');
            $mform->addRule('sponsoremail', null, 'required', null, 'server');
/* Temporarily disable setting latest sponsor.
            if (!is_null($this->_customdata['internalworkflow']->latestsponsor)) {
                $mform->setDefault('sponsoremail', $this->_customdata['internalworkflow']->latestsponsor->sponsoremail);
            }
*/
            $mform->addElement('textarea', 'comments', get_string('enrol:comments', 'tapsenrol'));
            $mform->setType('comments', PARAM_TEXT);
            // Server side validation to avoid issues with JS preventing multiple clicks.
            $mform->addRule('comments', null, 'required', null, 'server');
            $mform->addHelpButton('comments', 'enrol:comments', 'tapsenrol');
        }

        if ($this->_customdata['internalworkflow'] && $this->_customdata['internalworkflow']->declarations) {
            foreach ($this->_customdata['internalworkflow']->declarations as $declaration) {
                $mform->addElement('checkbox', 'declaration-'.$declaration->declarationid, '', $declaration->declaration);
            }
        }

        if ($this->_customdata['internalworkflow'] && !$this->_customdata['internalworkflow']->approvalrequired) {
            $submitstring = get_string('enrol:submit:noapproval', 'tapsenrol');
        } else {
            $submitstring = get_string('enrol:submit', 'tapsenrol');
        }
        $this->add_action_buttons('true', $submitstring);
    }

    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if ($this->_customdata['enrolmentkey'] && $data['enrolmentkey'] != $this->_customdata['enrolmentkey']) {
            $errors['enrolmentkey'] = get_string('enrol:enrolmentkey:error', 'tapsenrol');
        }

        if ($this->_customdata['internalworkflow'] && $this->_customdata['internalworkflow']->declarations) {
            foreach ($this->_customdata['internalworkflow']->declarations as $declaration) {
                if (!isset($data['declaration-'.$declaration->declarationid])) {
                    $errors['declaration-'.$declaration->declarationid] = get_string('enrol:declaration:required', 'tapsenrol');
                }
            }
        }

        // Only if internal workflow and email has been selected ('required' will already have been validated).
        if ($this->_customdata['internalworkflow'] && !empty($data['sponsoremail'])) {
            if (!filter_var($data['sponsoremail'], FILTER_VALIDATE_EMAIL)) {
                $errors['sponsoremail'] = get_string('enrol:sponsoremail:error:invalid', 'tapsenrol');
            } else if (!$this->_customdata['internalworkflow']->sponsors && strtolower($data['sponsoremail']) == strtolower($USER->email)) {
                // Can approve self if select menu but not if free text.
                $errors['sponsoremail'] = get_string('enrol:sponsoremail:error:notself', 'tapsenrol');
            } else if (!is_enabled_auth('saml')) {
                $errors['sponsoremail'] = get_string('enrol:sponsoremail:error:noldap', 'tapsenrol');
            } else {
                $samlauth = get_auth_plugin('saml');
                $users = $samlauth->ldap_get_userlist('(mail='.$data['sponsoremail'].')');
                if (empty($users)) {
                    $errors['sponsoremail'] = get_string('enrol:sponsoremail:error:notfound', 'tapsenrol');
                } else if (count($users) > 1) {
                    $errors['sponsoremail'] = get_string('enrol:sponsoremail:error:toomany', 'tapsenrol');
                } else {
                    $username = array_pop($users);
                    $userdetails = $samlauth->get_userinfo($username);
                    $firstname = isset($userdetails['firstname']) ? $userdetails['firstname'] : '';
                    $lastname = isset($userdetails['lastname']) ? $userdetails['lastname'] : '';
                    $mform =& $this->_form;
                    $mform->addElement('hidden', 'sponsorfirstname', $firstname);
                    $mform->setType('sponsorfirstname', PARAM_TEXT);
                    $mform->addElement('hidden', 'sponsorlastname', $lastname);
                    $mform->setType('sponsorlastname', PARAM_TEXT);
                }
            }
        }

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        $empties = array('sponsoremail', 'sponsorfirstname', 'sponsorlastname', 'comments');
        foreach ($empties as $empty) {
            if (empty($data->{$empty})) {
                $data->{$empty} = '';
            }
        }
        return $data;
    }
}