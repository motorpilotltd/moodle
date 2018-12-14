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

namespace mod_tapsenrol;

require_once($CFG->libdir . '/formslib.php');

class cmform_class_delete extends \moodleform {
    private $class = null;

    private function is_forcedelete_required() {
        global $DB;

        $enrolments = $DB->count_records_select(
                'tapsenrol_class_enrolments',
                'classid = :classid AND (archived is NULL OR archived = 0)',
                ['classid' => $this->class->classid]
        );

        return $enrolments !== 0;
    }

    public function definition() {
        $this->class = $this->_customdata['class'];
        $mform = $this->_form;

        $mform->addElement("hidden", "id");
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $this->class->id);

        if ($this->is_forcedelete_required()) {
            $mform->addElement('checkbox', 'forcedelete', get_string('forcedeletewarning', 'mod_tapsenrol'));
        }

        $this->add_action_buttons(true, get_string('yes'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($this->is_forcedelete_required() && empty($data['forcedelete'])) {
            $errors['forcedelete'] = true;
        }

        return $errors;
    }

    public function dodelete() {
        global $DB;

        if ($this->is_forcedelete_required()) {
            $enrolments = $DB->get_records('tapsenrol_class_enrolments', array('classid' => $this->class->classid));
            foreach ($enrolments as $enrolment) {
                $enrolment->archived = 1;
                $DB->update_record('tapsenrol_class_enrolments', $enrolment);
            }
        }

        $this->class->archived = 1;
        $DB->update_record('local_taps_class', $this->class);
    }
}