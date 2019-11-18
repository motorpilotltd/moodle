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
 * This is the external API for this tool.
 *
 * @package    local_reportbuilder
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reportbuilder;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/grade/grade_scale.php");

use context_system;
use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use core_user\external\user_summary_exporter;

/**
 * This is the external API for this tool.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns the description of external function parameters.
     *
     * @return external_function_parameters.
     */
    public static function search_users_parameters() {
        $query = new external_value(
                PARAM_RAW,
                'Query string'
        );
        $limitfrom = new external_value(
                PARAM_INT,
                'Number of records to skip',
                VALUE_DEFAULT,
                0
        );
        $limitnum = new external_value(
                PARAM_RAW,
                'Number of records to fetch',
                VALUE_DEFAULT,
                100
        );
        return new external_function_parameters(array(
                'query'     => $query,
                'limitfrom' => $limitfrom,
                'limitnum'  => $limitnum
        ));
    }

    /**
     * Search users.
     *
     * @param string $query
     * @param string $capability
     * @param int $limitfrom
     * @param int $limitnum
     * @return array
     */
    public static function search_users($query, $limitfrom = 0, $limitnum = 100) {
        global $DB, $CFG, $PAGE, $OUTPUT;

        $params = self::validate_parameters(self::search_users_parameters(), array(
                'query'     => $query,
                'limitfrom' => $limitfrom,
                'limitnum'  => $limitnum,
        ));
        $query = $params['query'];
        $limitfrom = $params['limitfrom'];
        $limitnum = $params['limitnum'];

        $context = context_system::instance();
        self::validate_context($context);

        $extrasearchfields = array();
        if (!empty($CFG->showuseridentity) && has_capability('moodle/site:viewuseridentity', $context)) {
            $extrasearchfields = explode(',', $CFG->showuseridentity);
        }
        $fields = \user_picture::fields('u', $extrasearchfields);

        list($wheresql, $whereparams) = users_search_sql($query, 'u', true, $extrasearchfields);
        list($sortsql, $sortparams) = users_order_by_sql('u', $query, $context);

        $countsql = "SELECT COUNT('x') FROM {user} u WHERE $wheresql";
        $countparams = $whereparams;
        $sql = "SELECT $fields FROM {user} u WHERE $wheresql ORDER BY $sortsql";
        $params = $whereparams + $sortparams;

        $count = $DB->count_records_sql($countsql, $countparams);
        $result = $DB->get_recordset_sql($sql, $params, $limitfrom, $limitnum);

        $users = array();
        foreach ($result as $key => $user) {
            // Make sure all required fields are set.
            foreach (user_summary_exporter::define_properties() as $propertykey => $definition) {
                if (empty($user->$propertykey) || !in_array($propertykey, $extrasearchfields)) {
                    if ($propertykey != 'id') {
                        $user->$propertykey = '';
                    }
                }
            }
            $exporter = new user_summary_exporter($user);
            $newuser = $exporter->export($PAGE->get_renderer('core'));

            $users[$key] = $newuser;
        }
        $result->close();

        return array(
                'users' => $users,
                'count' => $count
        );
    }

    /**
     * Returns description of external function result value.
     *
     * @return \external_description
     */
    public static function search_users_returns() {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');
        return new \external_single_structure(array(
                'users' => new external_multiple_structure(user_summary_exporter::get_read_structure()),
                'count' => new external_value(PARAM_INT, 'Total number of results.')
        ));
    }

}
