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
 * The lunch and learn block
 *
 * @package   block_lunchandlearn
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');
require_once($CFG->dirroot . '/blocks/lunchandlearn/block_lunchandlearn.php');

class block_mylunchandlearn extends block_lunchandlearn {

    protected $_addcontrols = false;

    public function init() {
        $this->title = get_string('pluginname', 'block_mylunchandlearn');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_data() {
        global $USER, $CFG;
        return  lunchandlearn_manager::get_user_sessions(
                $USER,
                new DateTime(),
                0, $CFG->calendar_maxevents);
    }
}
