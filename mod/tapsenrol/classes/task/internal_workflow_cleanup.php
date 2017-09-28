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
 * The mod_tapsenrol internal workflow cleanup task.
 *
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapsenrol\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapsenrol internal workflow cleanup task class.
 *
 * @package    mod_tapsenrol
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class internal_workflow_cleanup extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskinternalworkflowcleanup', 'mod_tapsenrol');
    }

    /**
     * Run internal workflow cleanup task.
     */
    public function execute() {
        global $DB;

        $sql = <<<EOS
SELECT
    tit.*
FROM
    {tapsenrol_iw_tracking} tit
LEFT JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = tit.enrolmentid
WHERE
    lte.id IS NULL
EOS;

        $records = $DB->get_records_sql($sql);
        foreach ($records as $record) {
            $DB->delete_records('tapsenrol_iw_tracking', array('id' => $record->id));
        }
    }
}
