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

namespace local_lynda\task;

use local_lynda\lyndaapi;

defined('MOODLE_INTERNAL') || die();

class sync_lyndadata extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasksynclyndadata', 'local_lynda');
    }

    /**
     * Run the tidy synccourses task.
     */
    public function execute() {
        $api = new lyndaapi();

        global $CFG;
        require_once($CFG->dirroot . '/local/lynda/tests/fixtures/lyndaapimock.php');
        $api = new \local_lynda\lyndaapimock();

        $api->synccourses();

        $thisruntime = time();
        $api->synccoursecompletion($this->get_last_run_time(), $thisruntime);
        $api->synccourseprogress($this->get_last_run_time(), $thisruntime);
        $this->set_last_run_time($thisruntime);
    }
}