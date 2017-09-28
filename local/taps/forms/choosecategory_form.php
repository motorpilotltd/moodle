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
 * The local_taps choose category form.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * The local_taps choose category form class.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_taps_choosecategory_form extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        require_once($CFG->libdir.'/coursecatlib.php');

        $mform =& $this->_form;

        $mform->addElement('hidden', 'courseid', null);
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $this->_customdata['courseid']);

        $mform->addElement('header', 'step1', get_string('addcourse:step1', 'local_taps'));

        $categories = coursecat::make_categories_list('moodle/course:create');
        $mform->addElement('select', 'category', get_string('coursecategory'), $categories);
        $mform->addHelpButton('category', 'coursecategory');

        $this->add_action_buttons(false, get_string('addcourse:step1:submit', 'local_taps'));
    }
}