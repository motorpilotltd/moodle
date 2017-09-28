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
 * The local_coursemetadata tidy course mappings task.
 *
 * @package    local_coursemetadata
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemetadata\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_coursemetadata tidy course mappings task class.
 *
 * @package    local_coursemetadata
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tidy_course_mappings extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasktidycoursemappings', 'local_coursemetadata');
    }

    /**
     * Run the tidy course mappings task.
     */
    public function execute() {
        global $DB;
        $sql = "
            SELECT
                cid.course
            FROM
                {coursemetadata_info_data} cid
            LEFT JOIN
                {course} c
                ON c.id = cid.course
            WHERE
                c.id IS NULL
            GROUP BY
                cid.course
            ";
        $records = $DB->get_records_sql($sql);
        if ($records) {
            foreach ($records as $record) {
                $DB->delete_records('coursemetadata_info_data', array('course' => $record->course));
            }
        }
    }
}