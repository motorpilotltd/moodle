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

class reset_course extends \moodleform {

    public function definition() {
        $mform =& $this->_form;

        // Choose linked Moodle course.
        $courses = get_courses('all', 'c.fullname ASC', 'c.id, c.fullname');
        $courseoptions = [];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }

        $courseselement = $mform->addElement(
                'select',
                'courses',
                get_string('resetcourse:courses', 'local_admin'),
                array('' => '') + $courseoptions,
                array('class' => 'select2', 'data-placeholder' => get_string('resetcourse:selectcourses', 'local_admin')));
        $courseselement->setMultiple(true);

        // Choose users.
        $userselement = $mform->addElement(
                'select',
                'users',
                get_string('resetcourse:users', 'local_admin'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('resetcourse:selectusers', 'local_admin'))
                );
        $userselement->setMultiple(true);

        // Submit buttons.
        $this->add_action_buttons(false, get_string('resetcourse:process', 'local_admin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Required as AJAX populated element.
        $data['users'] = is_array($_POST['users']) ? optional_param_array('users', array(), PARAM_INT) : array();

        // Needed here due to use of Select2.
        if (empty($data['courses'])) {
            $errors['courses'] = get_string('required');
        }
        if (empty($data['users'])) {
            $errors['users'] = get_string('required');
        }

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Required as AJAX populated element.
        $data->users = is_array($_POST['users']) ? optional_param_array('users', array(), PARAM_INT) : array();

        return $data;
    }
}
