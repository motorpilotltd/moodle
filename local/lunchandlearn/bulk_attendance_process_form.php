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

class bulk_attendance_process_form extends moodleform {
    /**
     * The form definition
     */
    public function definition () {
        $mform = $this->_form;

        $taps = new \local_taps\taps();

        $lal = $this->_customdata['session'];

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $staffids = optional_param_array('staffids', $this->_customdata['staffids'], PARAM_INT);
        foreach ($staffids as $key => $value) {
            $mform->addElement('hidden', 'staffids['.$key.']', $value);
            $mform->setType('staffids['.$key.']', PARAM_INT);
        }

        $mform->addElement('header', 'general', get_string('bulkattendanceupload:form:details', 'local_lunchandlearn'));
        $mform->addElement('static', 'classname', get_string('classname', 'local_lunchandlearn'), $lal->get_name());
        $mform->addElement('static', 'provider', get_string('provider', 'local_lunchandlearn'), $lal->get_supplier());
        $mform->addElement('static', 'completiondate', get_string('completiondate', 'local_lunchandlearn'), strtoupper($lal->scheduler->get_date_string('d-M-Y', '', false)));
        $mform->addElement('static', 'duration', get_string('duration', 'local_lunchandlearn'), $lal->scheduler->get_duration(). ' minutes');
        $mform->addElement('static', 'location', get_string('location', 'local_lunchandlearn'), $lal->scheduler->get_office());

        // Learning Method: Drop down showing default at Lunch and Learn.

        $mform->addElement('select', 'p_learning_method', get_string('p_learning_method', 'local_lunchandlearn'), $taps->get_classtypes('cpd'));
        $mform->setDefault('p_learning_method', 'LUNCH_AND_LEARN');

        $mform->addElement('editor', 'p_learning_desc', get_string('p_learning_desc', 'local_lunchandlearn'));
        $mform->setType('p_learning_desc', PARAM_RAW);

        $mform->addElement('header', 'staffids', 'Users being Added');

        $notfound = html_writer::start_div('alert alert-warning');
        $notfound .= html_writer::tag('p', get_string('bulkattendanceupload:form:notfound', 'local_lunchandlearn'));
        $notfound .= html_writer::start_tag('p');
        $notfound .= implode(html_writer::empty_tag('br'), $this->_customdata['users']['notfound']);
        $notfound .= html_writer::end_tag('p');
        $notfound .= html_writer::end_div();
        $mform->addElement('static', 'usersnotfound', '', $notfound);

        $found = html_writer::start_div('alert alert-success');
        $found .= html_writer::tag('p', get_string('bulkattendanceupload:form:found', 'local_lunchandlearn'));
        $found .= html_writer::start_tag('p');
        $foundusers = [];
        foreach ($this->_customdata['users']['found'] as $founduser) {
            $foundusers[] = "{$founduser->idnumber}, {$founduser->firstname} {$founduser->lastname} ({$founduser->email})";
        }
        $found .= implode(html_writer::empty_tag('br'), $foundusers);
        $found .= html_writer::end_tag('p');
        $found .= html_writer::end_div();
        $mform->addElement('static', 'usersfound', '', $found);

        $this->add_action_buttons(true, get_string('bulkattendanceupload:form:submit', 'local_lunchandlearn'));
    }
}
