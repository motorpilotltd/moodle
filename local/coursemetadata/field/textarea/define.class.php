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
 * Textarea local_coursemetadata field.
 *
 * @package    coursemetadatafield_textarea
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class coursemetadata_define_textarea.
 *
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemetadata_define_textarea extends \local_coursemetadata\define_base {

    /**
     * Add elements for creating/editing a textarea coursemetadata field.
     *
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        // Default data.
        $form->addElement('editor', 'defaultdata', get_string('coursemetadatadefaultdata', 'local_coursemetadata'));
        $form->setType('defaultdata', PARAM_RAW); // We have to trust person with capability to edit this default description.
    }

    /**
     * Returns an array of editors used when defining this type of coursemetadata field.
     * @return array
     */
    public function define_editors() {
        return array('defaultdata');
    }
}
