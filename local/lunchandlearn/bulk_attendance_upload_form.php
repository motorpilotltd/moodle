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

require_once($CFG->dirroot.'/lib/formslib.php');

class bulk_attendance_upload_form extends moodleform {
    /**
     * The form definition
     */
    public function definition () {
        $mform = $this->_form;

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('static', 'csvhelp', '', html_writer::tag('p', get_string('bulkattendanceupload:form:help', 'local_lunchandlearn')));

        $mform->addElement('filepicker', 'csvfile', get_string('bulkattendanceupload:form:csvfile', 'local_lunchandlearn'),
            null, array('accepted_types' => '.csv'));
        $mform->addRule('csvfile', null, 'required', null, 'client');

        $this->add_action_buttons($this->_customdata, get_string('bulkattendanceupload:form:upload', 'local_lunchandlearn'));
    }

    public function empty_csv() {
        $this->_form->setElementError('csvfile', get_string('bulkattendanceupload:form:csvfile:error', 'local_lunchandlearn'));
    }
}
