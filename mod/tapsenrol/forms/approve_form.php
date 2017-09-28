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

class mod_tapsenrol_approve_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $action = $this->_customdata['action'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);
        $mform->setConstant('action', $action);

        // Info messages.

        if ($action != 'approve' && $this->_customdata['rejectioncomments']) {
            $mform->addElement('textarea', 'comments', get_string('approve:comments', 'tapsenrol'));
            $mform->setType('comments', PARAM_TEXT);
            $mform->addHelpButton('comments', 'approve:comments', 'tapsenrol');
        }

        $buttonarray = array();
        if ($action == 'approve') {
            $buttonarray[] = &$mform->createElement('submit', 'approve', get_string('button:approve:confirm', 'tapsenrol'), array('class' => 'btn-primary'));
        } else if ($action == 'reject') {
            $buttonarray[] = &$mform->createElement('submit', 'reject', get_string('button:reject:confirm', 'tapsenrol'), array('class' => 'btn-danger'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'approve', get_string('button:approve', 'tapsenrol'), array('class' => 'btn-primary'));
            $buttonarray[] = &$mform->createElement('submit', 'reject', get_string('button:reject', 'tapsenrol'), array('class' => 'btn-danger'));
        }
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbtn', get_string('button:cancel', 'tapsenrol'), array('class' => 'btn-default'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate reject for comments based on submit button/iw settings.
        if ($this->_customdata['rejectioncomments'] && $this->_customdata['action'] == 'reject' && empty($data['comments'])) {
            $errors['comments'] = get_string('approve:error:rejectioncomments', 'tapsenrol');
        }

        return $errors;
    }
}