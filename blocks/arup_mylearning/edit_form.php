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

class block_arup_mylearning_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB;

        if (get_config('local_coursemetadata', 'version')) {
            $datatypes = array('iconmulti', 'iconsingle', 'menu', 'multiselect');
            list($usql, $params) = $DB->get_in_or_equal($datatypes);
            $sort = $DB->sql_compare_text('name').' ASC';
            $coursemetadatafields = $DB->get_records_select_menu('coursemetadata_info_field', "datatype {$usql}", $params, $sort, 'id, name');
            if ($coursemetadatafields) {
                $options = array(0 => get_string('choosedots')) + $coursemetadatafields;
                $mform->addElement('select', 'config_methodologyfield', get_string('methodologyfield', 'block_arup_mylearning'), $options);
            }
        }

        if (!$mform->elementExists('config_methodologyfield')) {
            $mform->addElement('hidden', 'config_methodologyfield', 0);
            $mform->setType('config_methodologyfield', PARAM_INT);
        }
    }
}
