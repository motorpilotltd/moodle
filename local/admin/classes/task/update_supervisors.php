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
 * The local_admin update supervisors task.
 *
 * @package    local_admin
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_admin\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_admin update supervisors task class.
 *
 * @package    local_admin
 * @since      Moodle 3.0
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_supervisors extends \core\task\scheduled_task {

    const CATEGORY = [
        'name' => 'Arup Admin',
    ];
    const FIELD = [
        'shortname' => 'arupadminissupervisor',
        'name' => 'ADMIN: Is supervisor?',
        'datatype' => 'checkbox',
        'description' => 'Whether or not this user flagged as a supervisor in the hub.',
        'descriptionformat' => 1,
        'required' => 0,
        'locked' => 1,
        'visible' => 0,
        'foreceunique' => 0,
        'signup' => 0,
        'defaultdata' => 0,
        'defaultdataformat' => 0,
    ];

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskupdatesupervisors', 'local_admin');
    }

    /**
     * Run the update supervisors task.
     */
    public function execute() {
        global $DB;

        // Open second connection as we need no prefix.
        $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = array();
        }
        // Pretend it's external to remove prefix injection.
        $DB2 = \moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary, true);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, false, $cfg->dboptions);

        // Don't run if we can't find the HUB table.
        if (!$DB2->get_manager()->table_exists('ARUP_ALL_STAFF_V')) {
            mtrace('SQLHUB.ARUP_ALL_STAFF_V does not exist. Aborting...');
            return;
        }

        // Check for our category/custom profile field.
        $category = $DB->get_record('user_info_category', ['name' => self::CATEGORY['name']]);
        if (!$category) {
            $category = (object) self::CATEGORY;
            $category->sortorder = $DB->count_records('user_info_category') + 1;
            $category->id = $DB->insert_record('user_info_category', $category);
        }

        if (!$category->id) {
            throw new Exception('Category not found/created.');
        }

        $field = $DB->get_record('user_info_field', ['shortname' => self::FIELD['shortname']]);
        if (!$field) {
            $field = (object) self::FIELD;
            $field->categoryid = $category->id;
            $field->sortorder = $DB->count_records('user_info_field', ['categoryid' => $category->id]) + 1;
            $field->id = $DB->insert_record('user_info_field', $field);
        }

        if (!$field->id) {
            throw new Exception('Profile field not found/created.');
        }

        // Find (active) supervisors of current staff.
        $supervisors = "SELECT DISTINCT u.id
                          FROM SQLHUB.ARUP_ALL_STAFF_V h
                          JOIN {user} u ON {$DB->sql_cast_char2real('u.idnumber')} = h.SUP_EMPLOYEE_NUMBER
                          WHERE h.LEAVER_FLAG = 'N' AND u.suspended = 0 AND u.deleted = 0";

        // Clear those who are no longer supervisors.
        $clearsql = "DELETE
                       FROM {user_info_data}
                      WHERE fieldid = :fieldid
                            AND userid NOT IN (
                                {$supervisors}
                            )";
        $DB->execute($clearsql, ['fieldid' => $field->id]);

        // Update existing.
        $updatesql = "UPDATE {user_info_data}
                         SET data = 1
                         WHERE fieldid = :fieldid
                               AND userid IN (
                                   {$supervisors}
                               )";
        $DB->execute($updatesql, ['fieldid' => $field->id]);

        // Finally add any missing.
        $missingsql = "SELECT u.id, u.id as id2
                         FROM {user} u
                    LEFT JOIN {user_info_data} uid
                              ON uid.userid = u.id
                                 AND uid.fieldid = :fieldid
                        WHERE u.id IN (
                                   {$supervisors}
                              )
                              AND uid.id IS NULL";
        $missings = $DB->get_records_sql_menu($missingsql, ['fieldid' => $field->id]);
        $uid = new \stdClass();
        $uid->fieldid = $field->id;
        $uid->data = 1;
        $uid->dataformat = 0;
        foreach ($missings as $missing) {
            $uid->userid = $missing;
            $DB->insert_record('user_info_data', $uid);
        }
    }
}