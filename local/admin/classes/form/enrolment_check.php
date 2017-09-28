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

class enrolment_check extends \moodleform {

    protected $_textreplacements;

    public function definition() {
        global $DB, $USER;

        $mform =& $this->_form;

        // Choose linked Moodle course.
        $sql = "SELECT cm.id, c.fullname FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module AND m.name = 'tapsenrol' JOIN {course} c ON c.id = cm.course ORDER BY c.fullname ASC";
        $courses = $DB->get_records_sql_menu($sql);
        $mform->addElement('select', 'cm', get_string('enrolmentcheck:course', 'local_admin'), ['' => get_string('choosedots')] + $courses);
        $mform->addRule('cm', get_string('required'), 'required', null, 'client');

        // Enter Staff IDs.
        $mform->addElement('textarea', 'staffids', get_string('enrolmentcheck:staffids', 'local_admin'));
        $mform->setType('staffids', PARAM_RAW);
        $mform->addRule('staffids', get_string('required'), 'required', null, 'client');

        // Submit buttons.
        $this->add_action_buttons(false, get_string('enrolmentcheck:process', 'local_admin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!isset($errors['staffids'])) {
            $errors['staffids'] = '';
        }

        $staffids = explode("\n", $data['staffids']);
        foreach ($staffids as $origstaffid) {
            $staffid = str_pad(trim($origstaffid), 6, '0', STR_PAD_LEFT);
            if (strlen($staffid) !== 6 || !ctype_digit($staffid)) {
                
                $errors['staffids'] .= get_string('enrolmentcheck:error:staffid', 'local_admin', format_string($origstaffid));
            }
        }

        if (empty($errors['staffids'])) {
            unset($errors['staffids']);
        }

        return $errors;
    }
}
