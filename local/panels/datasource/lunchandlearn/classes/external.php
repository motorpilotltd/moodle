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
 * Class containing the external API functions functions for the Data Privacy tool.
 *
 * @package    datasource_lunchandlearn
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace datasource_lunchandlearn;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/dataprivacy/lib.php');

use coding_exception;
use context_system;
use dml_exception;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use required_capability_exception;
use restricted_context_exception;

/**
 * Class external.
 *
 * The external API for the Data Privacy tool.
 *
 * @copyright  2017 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Parameter description for get_data_request().
     *
     * @since Moodle 3.5
     * @return external_function_parameters
     */
    public static function get_lunchandlearns_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED)
        ]);
    }

    /**
     * Fetch the details of a lunchandlearn's data request.
     *
     * @since Moodle 3.5
     * @param string $query The search request.
     * @return array
     * @throws required_capability_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function get_lunchandlearns($query) {
        global $DB;

        $params = external_api::validate_parameters(self::get_lunchandlearns_parameters(), [
            'query' => $query
        ]);
        $query = $params['query'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/panels:manage', $context);

        $params = ['now' => time()];

        $sql = "select ll.id, e.name, e.timestart from {local_lunchandlearn} ll
inner join {event} e on ll.eventid = e.id
where e.timestart > :now";

        $results = $DB->get_records_sql($sql, $params);

        global $CFG;
                    $dbf = $CFG->dataroot . '/temp/events.txt';
            $fh = fopen($dbf, 'w');
            fwrite($fh, print_r($results, true));
            fclose($fh);

        foreach($results as $result) {
            $result->id = (int)$result->id;
            $result->timestart = userdate($result->timestart, get_string('strftimedatetimeshort', 'langconfig'));
        }

        return $results;
    }

    /**
     * Parameter description for get_lunchandlearns().
     *
     * @since Moodle 3.5
     * @return external_description
     * @throws coding_exception
     */
    public static function get_lunchandlearns_returns() {
        return new external_multiple_structure(new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'ID of the lunchandlearn'),
                'name' => new external_value(PARAM_TEXT, 'The fullname of the lunchandlearn'),
                'timestart' => new external_value(PARAM_TEXT, 'The lunchandlearn\'s start time as formatted date'),
            ]
        ));
    }
}
