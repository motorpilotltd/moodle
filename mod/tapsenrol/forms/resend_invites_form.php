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

class mod_tapsenrol_resend_invites_form extends moodleform {

    public function definition() {
        global $DB;

        $mform =& $this->_form;

        $id = $this->_customdata['id'];
        $classid = $this->_customdata['classid'];
        $enrolments = $this->_customdata['enrolments'];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('textarea', 'extrainfo', get_string('resendinvites:extrainfo', 'tapsenrol'));
        $mform->setType('extrainfo', PARAM_TEXT);
        $mform->addHelpButton('extrainfo', 'resendinvites:extrainfo', 'tapsenrol');

        foreach ($enrolments as $enrolment) {
            $mform->addElement('advcheckbox', "enrolment[{$enrolment->enrolmentid}]", fullname($enrolment), null, array('group' => 1));
        }
        $this->add_checkbox_controller(1, null);

        $this->add_action_buttons(true, get_string('resendinvites', 'tapsenrol'));
    }
}