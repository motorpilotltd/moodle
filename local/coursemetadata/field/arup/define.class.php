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
 * coursemetadatafield_arup field.
 *
 * @package    coursemetadatafield_arup
 * @copyright  Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class coursemetadata_define_arup extends \local_coursemetadata\define_base {
    public function define_validate_specific($data, $files) {
        global $DB;

        $count = $DB->count_records('coursemetadata_info_field', ['datatype' => 'arup']);

        if (isset($data->id)) {
            $count += 1;
        }

        if ($count > 1) {
            return ['shortname' => get_string('oneinstanceonly', 'coursemetadatafield_arup')];
        }

        return array();
    }


    public function define_save_preprocess($data) {
        return $data;
    }

    public function define_form_specific($form) {
        // Default data.
        $form->addElement('hidden', 'defaultdata', '');
        $form->setType('defaultdata', PARAM_TEXT); // We have to trust person with capability to edit this default description.
    }
}
