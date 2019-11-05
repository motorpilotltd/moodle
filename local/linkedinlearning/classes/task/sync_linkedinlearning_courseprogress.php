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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning\task;

use local_linkedinlearning\api;

defined('MOODLE_INTERNAL') || die();

class sync_linkedinlearning_courseprogress extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasksynclinkedinlearningcourses', 'local_linkedinlearning');
    }

    /**
     * Run the tidy synccourses task.
     */
    public function execute() {
        global $DB;

        $now = time();
        $since = get_config('local_linkedinlearning', 'courseprgogresssyncto');
        if (!$since) {
            $since = 0;
        }
        $api = new api();
        $api->synccourseprogress($since);

        $syncedto = $since + 14 * DAYSECS;

        if ($syncedto > $now) {
            $syncedto = $now;
        }

        $DB->execute("
        update {linkedinlearning_progress}
        set userid = coalesce((select id from {user} where {user}.email = {linkedinlearning_progress}.email), 0)
        where userid = 0");

        set_config('courseprgogresssyncto', $syncedto, 'local_linkedinlearning');
    }
}