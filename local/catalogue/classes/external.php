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
 * External API.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_catalogue;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/local/catalogue/lib.php");
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_user;
use context_module;
use context_system;
use invalid_parameter_exception;
use local_catalogue\external\catalogue_course_exporter;
use catalogue_courses;

/**
 * External API class.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_by_classification_parameters() {
        return new external_function_parameters(
            array(
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'metadata' => new external_value(PARAM_RAW, 'Metadata Filters', VALUE_DEFAULT, null),
                'category' => new external_value(PARAM_INT, 'Category id', VALUE_DEFAULT, null),
                'search' => new external_value(PARAM_RAW, 'Search string', VALUE_DEFAULT, null)
            )
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * @param  int $limit Result set limit
     * @param  int $offset Offset the full course set before timeline classification is applied
     * @return array list of courses and warnings
     * @throws  invalid_parameter_exception
     */
    public static function get_courses_by_classification(
        int $limit = 0,
        int $offset = 0,
        string $metadata = null,
        int $category = 0,
        string $search = null
    ) {
        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        $params = self::validate_parameters(self::get_courses_by_classification_parameters(),
            array(
                'limit' => $limit,
                'offset' => $offset,
                'metadata' => $metadata,
                'category' => $category,
                'search' => $search
            )
        );

        $limit = $params['limit'];
        $offset = $params['offset'];
        $metadata = $params['metadata'];
        $category = $params['category'];
        $search = $params['search'];

        self::validate_context(context_user::instance($USER->id));

        $courses = [];

        $requiredproperties = catalogue_course_exporter::define_properties();
        $fields = join(',', array_keys($requiredproperties));

        list($filteredcourses, $processedcount) = \local_catalogue\catalogue_courses::get_filtered_courses($category,
            $fields, $offset, $limit, $metadata, $search);

        $renderer = $PAGE->get_renderer('core');

        $formattedcourses = [];

        $context = context_system::instance();
        foreach ($filteredcourses as $course) {
            $exporter = new catalogue_course_exporter($course, ['context' => $context]);
            $formattedcourses[] = $exporter->export($renderer);
        }

        return [
            'courses' => $formattedcourses,
            'nextoffset' => $offset + $processedcount
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_courses_by_classification_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(catalogue_course_exporter::get_read_structure(), 'Course'),
                'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request')
            )
        );
    }
}
