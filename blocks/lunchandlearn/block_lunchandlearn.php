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

class block_lunchandlearn extends block_base {

    protected $_addcontrols = true;

    public function init() {
        $this->title = get_string('blocktitle', 'block_lunchandlearn');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function init_blank_content() {
        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';
    }

    public function get_content() {
        global $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->init_blank_content();
        if (empty($this->instance)) {
            return $this->content;
        }
        // Get lunch and learns.
        $renderer = $PAGE->get_renderer('local_lunchandlearn');
        $this->content->text = $renderer->block($this->get_data(), $this->_addcontrols);

        return $this->content;
    }

    public function get_data() {
        global $COURSE, $SITE, $CFG;

        // Get some lunch and learns.
        $coursecatid = $COURSE->id == $SITE->id ? 0 : $COURSE->category;
        $regionid = lunchandlearn_get_region();
        $sessions = lunchandlearn_manager::get_sessions(
                new DateTime(),
                $regionid,
                $coursecatid,
                null,
                false,
                '',
                0,
                $CFG->calendar_maxevents);

        return $sessions;
    }
}
