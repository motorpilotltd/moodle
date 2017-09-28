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
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once('lib.php');

/**
 * Based upon the mform class for creating and editing a calendar
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attendee_form extends moodleform {
    /**
     * The form definition
     */
    public function definition () {
        $mform = $this->_form;

        $taps = new \local_taps\taps();

        $lal = $this->_customdata;

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);

        $attendance = optional_param_array('attendance', array(), PARAM_INT);
        foreach ($attendance as $key => $value) {
            $mform->addElement('hidden', 'attendance['.$key.']', $value);
            $mform->setType('attendance['.$key.']', PARAM_INT);
        }

        $mform->addElement('header', 'general', 'General');
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

        $mform->addElement('header', 'notice', 'Notice');
        $mform->addElement('static', 'lockmessage', '', html_writer::div(get_string('lockmessage', 'local_lunchandlearn'), 'alert alert-warning'));

        $this->add_action_buttons(true, get_string('yestakeattendance', 'local_lunchandlearn'));
    }
}
