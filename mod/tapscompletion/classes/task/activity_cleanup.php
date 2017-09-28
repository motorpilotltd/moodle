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
 * The mod_tapscompletion activity cleanup task.
 *
 * @package    mod_tapscompletion
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapscompletion\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapscompletion activity cleanup task class.
 *
 * @package    mod_tapscompletion
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_cleanup extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskactivitycleanup', 'mod_tapscompletion');
    }

    /**
     * Run activity cleanup task.
     */
    public function execute() {
        global $DB;

        $sql = <<<EOS
SELECT
    t.*
FROM
    {tapscompletion} t
LEFT JOIN {course_modules} cm
    ON cm.instance = t.id
    AND cm.module = (
        SELECT
            id
        FROM {modules}
        WHERE
            name = :module
    )
WHERE cm.id IS NULL
EOS;
        $params = array('module' => 'tapscompletion');
        $records = $DB->get_records_sql($sql, $params);
        foreach ($records as $record) {
            $DB->delete_records('tapscompletion', array('id' => $record->id));
            $DB->delete_records('tapscompletion_completion', array('tapscompletionid' => $record->id));
        }
    }
}
