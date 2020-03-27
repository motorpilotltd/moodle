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

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function report_columns_config_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'Reportid', VALUE_DEFAULT, 0)
            ]
        );
    }

    /**
     * Get report builder columns config.
     *
     * @param int $id
     *
     * @return array reportbuilder config
     */
    public static function report_columns_config($id) {
        global $CFG, $PAGE;

        require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

        $params = self::validate_parameters(self::report_columns_config_parameters(), [
            'id' => $id
        ]);

        $id = $params['id'];

        self::validate_context(\context_system::instance());

        $report = new \reportbuilder($id, null, false, null, null, true);

        $config = new \stdClass();
        $config->rb_reportid = $id;
        $config->rb_column_headings = $report->get_default_headings_array();
        $config->rb_grouped_columns = $report->src->get_grouped_column_options();
        $config->rb_allowed_advanced = $report->src->get_allowed_advanced_column_options();
        $config->rb_advanced_options = $report->src->get_all_advanced_column_options();

        $resultcourses = array('id' => $id, 'config' => json_encode($config));
        return $resultcourses;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.6
     */
    public static function report_columns_config_returns() {
        return new \external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'report id'),
                'config' => new external_value(PARAM_TEXT, 'config'),
            )
        );
    }

        /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function report_filters_config_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'Reportid', VALUE_DEFAULT, 0)
            ]
        );
    }

    /**
     * Get report builder columns config.
     *
     * @param int $id
     *
     * @return array reportbuilder config
     */
    public static function report_filters_config($id) {
        global $CFG, $PAGE, $USER;

        require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

        $params = self::validate_parameters(self::report_filters_config_parameters(), [
            'id' => $id
        ]);

        $id = $params['id'];

        self::validate_context(\context_system::instance());

        $report = new \reportbuilder($id, null, false, null, null, true);

        $globalinitialdisplay = get_config('local_reportbuilder', 'globalinitialdisplay');
        $initialdisplay = ($report->initialdisplay == RB_INITIAL_DISPLAY_HIDE || ($globalinitialdisplay && !$report->embedded)) ? 1 : 0;
        $sizeoffilters  = sizeof($report->filters) + sizeof($report->searchcolumns);

        $searchcolumnheadings = array();
        $defaultheadings = $report->get_default_headings_array();

        foreach ($report->columnoptions as $option) {
            if ($option->is_searchable()) {
                $key = $option->type . '-' . $option->value;
                if (isset($defaultheadings[$key])) {
                    $searchcolumnheadings[$key] = $defaultheadings[$key];
                }
            }
        }

        $filterheadings = array();
        foreach ($report->filteroptions as $option) {
            $key = $option->type . '-' . $option->value;

            // There may be more than one type of data (for exmaple, users), for example columns,
            // so add the type to the heading to differentiate the types - if required.
            if (isset($option->filteroptions['addtypetoheading']) && $option->filteroptions['addtypetoheading']) {
                $langstr = 'type_' . $option->type;
                if (get_string_manager()->string_exists($langstr, 'rbsource_' . $sourcename)) {
                    // Is there a type string in the source file?
                    $type = get_string($langstr, 'rbsource_' . $sourcename);
                } else if (get_string_manager()->string_exists($langstr, 'local_reportbuilder')) {
                    // How about in report builder?
                    $type = get_string($langstr, 'local_reportbuilder');
                } else {
                    // Display in missing string format to make it obvious.
                    $type = get_string($langstr, 'rbsource_' . $sourcename);
                }
                $text = (object) array ('column' => $option->label, 'type' => $type);
                $heading = get_string ('headingformat', 'local_reportbuilder', $text);
            } else {
                $heading = $option->label;
            }

            $filterheadings[$key] = ($heading);
        }

        $config = new \stdClass();
        $config->rb_reportid = $id;
        $config->user_sesskey = $USER->sesskey;
        $config->rb_filters = $sizeoffilters;
        $config->rb_initial_display = $initialdisplay;
        $config->rb_global_initial_display = $globalinitialdisplay;
        $config->rb_filter_headings = $filterheadings;
        $config->rb_search_column_headings = $searchcolumnheadings;

        $resultcourses = array('id' => $id, 'config' => json_encode($config));
        return $resultcourses;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.6
     */
    public static function report_filters_config_returns() {
        return new \external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'report id'),
                'config' => new external_value(PARAM_TEXT, 'config'),
            )
        );
    }
}
