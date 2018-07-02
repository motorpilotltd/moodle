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
 * Page external API
 *
 * @package    mod_kalvidres
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * Page external functions
 *
 * @package    mod_kalvidres
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_kalvidres_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_kalvidres_parameters() {
        return new external_function_parameters(
            array(
                'videoid' => new external_value(PARAM_INT, 'video instance id')
            )
        );
    }

    /**
     * Simulate the page/view.php web interface page: trigger events, completion, etc...
     *
     * @param int $pageid the page instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_kalvidres($videoid) {
        global $DB;

        list($course, $cm) = get_course_and_cm_from_instance($videoid, 'kalvidres');

        $event = \mod_kalvidres\event\video_resource_viewed::create(array(
                'objectid' => $videoid,
                'context' => context_module::instance($cm->id)
        ));
        $event->trigger();

        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = [];
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_kalvidres_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_kalvidres_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_kalvidres_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of pages in a provided list of courses.
     * If no list is provided all pages that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and pages
     * @since Moodle 3.3
     */
    public static function get_kalvidres_by_courses($courseids = array()) {

        $warnings = array();
        $returnedkalvidreses = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_kalvidres_by_courses_parameters(), $params);

        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            $fs = get_file_storage();
            list($courses, $warnings) = external_util::validate_courses($params['courseids']);

            // Get the pages in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $kalvidreses = get_all_instances_in_courses("kalvidres", $courses);
            foreach ($kalvidreses as $kalvidres) {
                $context = context_module::instance($kalvidres->coursemodule);
                // Entry to return.
                $kalvidres->name = external_format_string($kalvidres->name, $context->id);

                list($kalvidres->intro, $kalvidres->introformat) = external_format_text($kalvidres->intro,
                                                                $kalvidres->introformat, $context->id, 'mod_kalvidres', 'intro', null);
                $kalvidres->introfiles = $fs->get_area_files($context->id, 'mod_kalvidres', 'intro', false, false);

                list($kalvidres->content, $kalvidres->contentformat) = external_format_text($kalvidres->content, $kalvidres->contentformat,
                                                                $context->id, 'mod_kalvidres', 'content', $kalvidres->id);
                $kalvidres->contentfiles = $fs->get_area_files($context->id, 'mod_kalvidres', 'content');

                $returnedkalvidreses[] = $kalvidres;
            }
        }

        $result = array(
            'videos' => $returnedkalvidreses,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_kalvidres_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_kalvidres_by_courses_returns() {
        return new external_single_structure(
            array(
                'videos' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Page name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'entry_id' => new external_value(PARAM_RAW, 'Entry ID'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the page was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }



    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_ks_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Simulate the page/view.php web interface page: trigger events, completion, etc...
     *
     * @param int $pageid the page instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function get_ks() {
        global $CFG, $USER;

        $warnings = array();

        if (empty($expiry) || false == is_numeric($expiry)) {
            $expiry = 86400;
        }

        require_once($CFG->dirroot . '/local/kaltura/API/KalturaClient.php');

        $configsettings = get_config('local_kaltura');
        $config = new KalturaConfiguration($configsettings->partner_id);
        if (!empty($CFG->proxyhost)) {
            $config->proxyHost = $CFG->proxyhost;
            if (!empty($CFG->proxyport)) {
                $config->proxyPort =  $CFG->proxyport;
            }
            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                $config->proxyUser =  $CFG->proxyuser;
                $config->proxyPassword =  $CFG->proxypassword;
            }
            if (!empty($CFG->proxytype)) {
                $config->proxyType =  $CFG->proxytype;
            }
        }
        $client = new KalturaClient($config);
        $privileges = '*';

        try {
            $ks = $client->generateSessionV2($configsettings->adminsecret, $USER->username, KalturaSessionType::USER, $configsettings->partner_id, $expiry, $privileges);
        } catch (Exception $ex) {
            $warnings[] = 'Cannot generate KS';
            $ks = null;
        }

        return ['ks' => $ks, 'warnings' => $warnings];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_ks_returns() {
        return new external_single_structure(
                array(
                        'ks' => new external_value(PARAM_RAW, 'Kaltura KS'),
                        'warnings' => new external_warnings()
                )
        );
    }
}
