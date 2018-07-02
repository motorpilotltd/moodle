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
 * The local_costcentre update users task.
 *
 * @package    local_costcentre
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\task;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

/**
 * The local_costcentre tidy users task class.
 *
 * @package    local_costcentre
 * @since      Moodle 3.0
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tidy_users extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasktidyusers', 'local_costcentre');
    }

    /**
     * Run the tidy users task.
     *
     * @global \moodle_database $DB
     */
    public function execute() {
        global $DB;

        mtrace('Begin user tidying...');

        $countsql = <<<EOS
SELECT COUNT(lcu.id)
FROM {local_costcentre_user} lcu
JOIN {user} u ON u.id = lcu.userid
WHERE u.suspended != 0 OR u.deleted != 0
EOS;
        $count = $DB->count_records_sql($countsql);

        mtrace("Removing {$count} record(s).");

        $sql = <<<EOS
DELETE FROM {local_costcentre_user}
WHERE id IN (
    SELECT lcu.id
    FROM {local_costcentre_user} lcu
    JOIN {user} u ON u.id = lcu.userid
    WHERE u.suspended != 0 OR u.deleted != 0
)
EOS;
        $DB->execute($sql);

        mtrace('...end user tidying.');
    }
}