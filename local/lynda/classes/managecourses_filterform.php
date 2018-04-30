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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class managecourses_filterform extends \moodleform {
    /**
     * Form definition method.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'filteroptions', get_string('filteroptions', 'local_lynda'));

        $tags = lyndatagtype::fetch_full_taxonomy();

        foreach ($tags as $tagcategory) {
            $options = [];
            foreach ($tagcategory->tags as $tag) {
                $options[$tag->remotetagid] = $tag->name;
            }
            $tagtypeselectname = $tagcategory->gettagtypeselectname();
            $mform->addElement('select', $tagtypeselectname, $tagcategory->name, $options, ['multiple' => true]);
        }

        $mform->addElement('submit', 'submit', get_string('update'));
    }
}
