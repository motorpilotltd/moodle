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
 * Privacy Subsystem implementation for format_aruponepage.
 *
 * @package    format_aruponepage
 * @copyright  2018 Moodle Pty Ltd <support@moodle.com>
 * @author     2018 Workplace team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */

namespace format_aruponepage\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for format_aruponepage implementing null_provider.
 *
 * @copyright  2018 Moodle Pty Ltd <support@moodle.com>
 * @author     2018 Workplace team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('format_aruponepage_opensections', 'privacy:metadata:opensections');
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        global $DB;

        $contextlist = new contextlist();

        $where = $DB->sql_like('name', ':name') . " AND userid = :userid";
        $params['name'] = 'format_aruponepage_opensections_%';
        $params['userid'] = $userid;

        $preferences = $DB->get_records_select('user_preferences', $where, $params);

        if (!$preferences) {
            return $contextlist;
        }

        $contextids = array_map(function($p) {
            $pref = explode('_', $p->name);
            return array_pop($pref);
        }, $preferences);

        list($select, $params) = $DB->get_in_or_equal($contextids);
        $sql = "SELECT id
                  FROM {context}
                 WHERE id {$select}";

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT DISTINCT(userid)
                  FROM {user_preferences}
                 WHERE name = :preference";

        $params['preference'] = 'format_aruponepage_opensections_' . $context->id;
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $names = array_map(function($c) {
            return 'format_aruponepage_opensections_' . $c;
        }, $contextlist->get_contextids());

        list($where, $params) = $DB->get_in_or_equal($names, SQL_PARAMS_NAMED);

        $where = 'userid = :userid AND name ' . $where;
        $params['userid'] = $contextlist->get_user()->id;

        $preferences = $DB->get_records_select('user_preferences', $where, $params);

        if (!$preferences) {
            return;
        }

        foreach ($preferences as $p) {
            $name = explode('_', $p->name);
            $contextid = array_pop($name);
            writer::with_context(\context_helper::instance_by_id($contextid))->export_data(
                    [get_string('privacy:metadata:opensections', 'format_aruponepage')], (object) $p);
        }
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        global $DB;

        $where = $DB->sql_like('name', ':name') . " AND userid = :userid";
        $params = ['name' => 'format_aruponepage_opensections_%', 'userid' => $userid];

        $preferences = $DB->get_records_select('user_preferences', $where, $params);

        foreach ($preferences as $p) {
            $opensections = json_decode($p->value, true);
            if (is_array($opensections)) {
                writer::export_user_preference('format_aruponepage',
                    $p->name,
                    $p->value,
                    get_string('privacy:metadata:opensections', 'format_aruponepage')
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Check what context we've been delivered.
        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('user_preferences', ['name' => 'format_aruponepage_opensections_'.$context->id]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->get_contextids())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        $preferences = array_map(function($c) {
            return 'format_aruponepage_opensections_' . $c;
        }, $contextlist->get_contextids());

        list($where , $params) = $DB->get_in_or_equal($preferences, SQL_PARAMS_NAMED);
        $where = "name {$where} AND userid = :userid";
        $params['userid'] = $userid;
        $DB->delete_records_select('user_preferences', $where, $params);
        mark_user_preferences_changed($userid);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        // Check what context we've been delivered.
        if (!$context instanceof \context_course) {
            return;
        }

        list($userids, $params) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $where = "name = :preference AND userid {$userids}";
        $params['preference'] = 'format_aruponepage_opensections_' . $context->id;

        $DB->delete_records_select('user_preferences', $where, $params);
        foreach ($userlist->get_userids() as $userid) {
            mark_user_preferences_changed($userid);
        }
    }
}
