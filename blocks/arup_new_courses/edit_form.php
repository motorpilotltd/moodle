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

/**
 * Form for editing Arup New courses block instances.
 *
 * @package   block_arup_new_courses
 */

class block_arup_new_courses_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB;

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_arup_new_courses'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('select', 'config_numberofcourses', get_string('numberofcourses', 'block_arup_new_courses'), array_combine(range(1, 5), range(1, 5)));
        $mform->setDefault('config_numberofcourses', BLOCK_ARUP_NEW_COURSES_DEFAULTNUMBEROFCOURSES);

        if (get_config('local_coursemetadata', 'version')) {
            $datatypes = array('iconsingle');
            list($usql, $params) = $DB->get_in_or_equal($datatypes);
            $sort = $DB->sql_compare_text('name').' ASC';
            $coursemetadatafields = $DB->get_records_select_menu('coursemetadata_info_field', "datatype {$usql}", $params, $sort, 'id, name');
            if ($coursemetadatafields) {
                $options = array(0 => get_string('choosedots')) + $coursemetadatafields;
                $mform->addElement('select', 'config_methodologyfield', get_string('methodologyfield', 'block_arup_new_courses'), $options);
                $mform->addHelpButton('config_methodologyfield', 'methodologyfield', 'block_arup_new_courses');
            }
        }

        if (!$mform->elementExists('config_methodologyfield')) {
            $mform->addElement('hidden', 'config_methodologyfield', 0);
            $mform->setType('config_methodologyfield', PARAM_INT);
        }
    }
}
