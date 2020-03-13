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
 * @package    datasource_course
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace datasource_course;
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
     * @return external_function_parameters
     * @since Moodle 3.5
     */
    public static function get_courses_parameters() {
        return new external_function_parameters([
                'query' => new external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED)
        ]);
    }

    /**
     * Fetch the details of a course's data request.
     *
     * @param string $query The search request.
     * @return array
     * @throws required_capability_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @since Moodle 3.5
     */
    public static function get_courses($query) {
        global $DB;
        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/panels:manage', $context);

        $params = external_api::validate_parameters(self::get_courses_parameters(), [
                'query' => $query
        ]);
        $query = $params['query'];

        $searchterms = trim(strtolower($query));

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'datasource_course', 'courses');
        $courses = $cache->get('courses');
        if (!$courses) {
            $courses = $DB->get_records_sql("SELECT id, fullname as name FROM {course} WHERE visible = 1 ORDER BY fullname");
            $cache->set('courses', $courses);
        }

        $result = [];
        foreach ($courses as $course) {
            if (strpos(strtolower($course->name), $searchterms) !== false ) {
                $result[] = $course;
            }
        }

        return $result;
    }

    /**
     * Parameter description for get_courses().
     *
     * @return external_description
     * @throws coding_exception
     * @since Moodle 3.5
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(new external_single_structure(
                [
                        'id'   => new external_value(PARAM_INT, 'ID of the course'),
                        'name' => new external_value(PARAM_TEXT, 'The fullname of the course'),
                ]
        ));
    }
}
