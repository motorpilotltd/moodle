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
 * The local_costcentre add cost centres task.
 *
 * @package    local_costcentre
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\task;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * The local_costcentre add cost centres task class.
 *
 * @package    local_costcentre
 * @since      Moodle 3.3
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_costcentres extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskaddcostcentres', 'local_costcentre');
    }

    /**
     * Run the add cost centres task.
     *
     * @global \moodle_database $DB
     */
    public function execute() {
        global $DB;

        $email = "*** ADDING NEW COST CENTRES ***\n";

        mtrace('Begin adding new cost centres...');

        // Set up base record objects.
        // local_costcentre object.
        $lac = new stdClass();
        $lac->enableappraisal = 1;
        $lac->laststaffupdate = null;
        $lac->groupleaderactive = null;
        $lac->appraiserissupervisor = 0;

        // Find new cost centres.
        // Distinct from HUB for non-leavers, that aren't in costcentre plugin set up.
        $sql = "SELECT DISTINCT icq, icq AS icq2
                  FROM {user} u
             LEFT JOIN {local_costcentre} c ON c.costcentre = u.icq
                 WHERE u.suspended = 0 AND u.deleted = 0 AND u.icq != '' AND c.id IS NULL";
        $costcentres = $DB->get_records_sql_menu($sql);
        foreach ($costcentres as $costcentre) {
            // Add to costcentre plugin.
            $lac->costcentre = $costcentre;
            $DB->insert_record('local_costcentre', $lac);
            $email .= $string = "ADDED COST CENTRE: {$costcentre}\n";
            mtrace($string);
        }

        // Email summary to admin.
        if ($costcentres) {
            $event = \local_costcentre\event\costcentres_added::create(array(
                'other' => array(
                    'costcentres' => $costcentres,
                ),
            ));
            $event->trigger();

            email_to_user(get_admin(), get_admin(), 'Newly added cost centres summary', $email);
        }

        mtrace('...end adding new cost centres.');
    }
}