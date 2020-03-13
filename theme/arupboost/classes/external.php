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
 * Starred courses block external API
 *
 * @package    theme_arupboost
 * @category   external
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_arupboost;

defined('MOODLE_INTERNAL') || die;

use theme_arupboost\external\page_footer_exporter;
use core_user\external\user_summary_exporter;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
usecoding_exception;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/local/regions/lib.php');

/**
 * The arup boost external services.
 *
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function set_timezone_parameters() {
        return new external_function_parameters([
            'timezone' => new external_value(PARAM_RAW, 'NEW TIMEZONE')
        ]);
    }

    /**
     * Set a user timezone.
     *
     * @param string $timezone New timezone
     *
     * @return array new time and warnings
     */
    public static function set_timezone($timezone) {
        global $USER, $DB;

        $params = self::validate_parameters(self::set_timezone_parameters(), [
            'timezone' => $timezone
        ]);

        $timezone = $params['timezone'];

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $result = [];
        $result['success'] = 0;

        if ($userrecord = $DB->get_record('user', array('id' => $USER->id))) {
            $timezone = \core_date::normalise_timezone($timezone);
            $userrecord->timezone = $timezone;
            $USER->timezone = $timezone;
            $usertimezone = \core_date::get_user_timezone_object();

            if ($DB->update_record('user', $userrecord)) {
                $result->success = true;
                $result->message = $timezone;

                $time = new \DateTime();
                $time->setTimezone($usertimezone);
                $result['time'] = '<div class="mr-1">' . $time->format('G:i') . '</div>
                    <div class="tz">' . $time->format('(T)') . '</div>';
                $result['success'] = 1;
            }
        }
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.5
     */
    public static function set_timezone_returns() {
        return new external_single_structure(
            array(
                'time' => new external_value(PARAM_RAW, 'updated time'),
                'success' => new external_value(PARAM_INT, 'success')
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function get_footer_content_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context ID', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Get footer content.
     *
     * @param int $limit Limit
     * @param int $offset Offset
     *
     * @return  array list of courses and warnings
     */
    public static function get_footer_content($contextid) {
        global $PAGE, $DB, $USER;

        // Get and process the contextid.
        $params = self::validate_parameters(self::get_footer_content_parameters(), [
            'contextid' => $contextid
        ]);
        $contextid = $params['contextid'];
        $context = context::instance_by_id($contextid);
        self::validate_context($context);
        $PAGE->set_context($context);

        $renderer = $PAGE->get_renderer('core');

        $footer = (object) [
            'content' => "",
            'contacts' => []
        ];

        $footercontacts = [];

        // Get cohort based footer contacts.
        $cohorts = $DB->get_records('cohort', ['idnumber' => 'footer']);
        if (!empty($cohorts)) {
            $first = array_shift($cohorts);
            $members = $DB->get_records('cohort_members', ['cohortid' => $first->id]);

            foreach ($members as $member) {
                $mem = \core_user::get_user($member->userid, '*');
                $use = new user_summary_exporter($mem);
                $footercontacts[$mem->id] = $use->export($renderer);
            }
        }

        // Get role based footer contacts.
        if ($context->contextlevel == CONTEXT_COURSE) {
            $roles = get_roles_with_capability('theme/arupboost:showinfooter', CAP_ALLOW, $context);
            foreach ($roles as $role) {
                $roleusers = get_role_users($role->id, $context);
                foreach ($roleusers as $ruser) {
                    $mem = \core_user::get_user($ruser->id, '*');
                    $use = new user_summary_exporter($mem);
                    $footercontacts[$mem->id] = $use->export($renderer);
                }
            }
        }

        // Get role based footer contacts.
        $usercohorts = cohort_get_user_cohorts($adam->id);
        $regions = \local_regions_get_user_region($adam);
        foreach ($usercohorts as $uchort) {
            if (!empty($uchort->idnumber)) {
                if ($footercohort = $DB->get_record('cohort', ['idnumber' => 'footer_' . $uchort->idnumber])) {
                    $members = $DB->get_records('cohort_members', ['cohortid' => $footercohort->id]);
                    foreach ($members as $member) {
                        $mem = \core_user::get_user($member->userid, '*');
                        $use = new user_summary_exporter($mem);
                        $footercontacts[$mem->id] = $use->export($renderer);
                    }
                }
            }
        }

        $footer->contacts = array_values($footercontacts);

        $exporter = new page_footer_exporter($footer, ['context' => $context]);
        $formattedfooter = $exporter->export($renderer);

        return $formattedfooter;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.6
     */
    public static function get_footer_content_returns() {
        return page_footer_exporter::get_read_structure();
    }

    /**
     * @return \external_function_parameters
     */
    public static function saveimage_parameters() {
        $parameters = [
            'params' => new \external_single_structure([
                'imagedata' => new \external_value(PARAM_TEXT, 'Image data', VALUE_REQUIRED),
                'imagefilename' => new \external_value(PARAM_TEXT, 'Image filename', VALUE_REQUIRED),
                'imageid' => new \external_value(PARAM_INT, 'Image Id', VALUE_OPTIONAL),
                'type' => new \external_value(PARAM_TEXT, 'Image type', VALUE_OPTIONAL),
                'cropped' => new \external_value(PARAM_INT, 'Cropped version', VALUE_OPTIONAL),
            ], 'Params wrapper - just here to accommodate optional values', VALUE_REQUIRED)
        ];
        return new \external_function_parameters($parameters);
    }

    /**
     * @param string $imagedata
     * @param string $imagefilename
     * @param int $imageid Related to the type contextid
     * @param string $type image type
     * @param int $cropped 1 if cropped version
     * @return array
     */
    public static function saveimage($params) {
        $params = self::validate_parameters(self::saveimage_parameters(), ['params' => $params])['params'];

        if (empty($params['imageid'])) {
            throw new coding_exception('Error - imageid must be provided');
        }

        if ($params['type'] === 'catalogue') {
            $context = \context_coursecat::instance($params['imageid']);
        } else if ($params['type'] === 'course') {
            $context = \context_course::instance($params['imageid']);
        } else {
            throw new coding_exception('Error - type must be either catalogue or course');
        }

        self::validate_context($context);

        $coverimage = self::setcoverimage($context, $params['type'], $params['imagedata'],
            $params['imagefilename'], $params['cropped']);
        return $coverimage;
    }

    /**
     * @return \external_single_structure
     */
    public static function saveimage_returns() {
        $keys = [
            'success' => new \external_value(PARAM_BOOL, 'Was the cover image successfully changed', VALUE_REQUIRED),
            'fileurl' => new \external_value(PARAM_TEXT, 'New file', VALUE_REQUIRED)
        ];

        return new \external_single_structure($keys, 'coverimage');
    }

    /**
     * @param \context $context
     * @param string $type
     * @param string $data
     * @param string $filename
     * @return array
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public static function setcoverimage(\context $context, $type, $data, $filename, $cropped) {

        global $CFG;

        $fs = get_file_storage();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;

        if (!in_array($ext, ['jpg', 'png', 'gif', 'svg', 'webp'])) {
            return ['success' => false, 'warning' => get_string('unsupportedcoverimagetype', 'theme_arupboost', $ext)];
        }

        $newfilename = $type . 'image.' . $ext;

        $binary = base64_decode($data);
        if (strlen($binary) > get_max_upload_file_size($CFG->maxbytes)) {
            throw new \moodle_exception('error:coverimageexceedsmaxbytes', 'theme_arupboost');
        }

        $filearea = $type;
        if ($cropped) {
            $filearea = $type . '_cropped';
        }

        if ($context->contextlevel === CONTEXT_COURSECAT && $type === 'catalogue') {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_arupboost',
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $newfilename);

            // Remove everything from poster area for this context.
            if ($cropped) {
                $fs->delete_area_files($context->id, 'theme_arupboost', $filearea);
            } else {
                $fs->delete_area_files($context->id, 'theme_arupboost', $type);
                $fs->delete_area_files($context->id, 'theme_arupboost', $type. '_cropped');
            }
        } else if ($context->contextlevel === CONTEXT_COURSE && $type === 'course') {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_arupboost',
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $newfilename);

            // Remove everything from poster area for this context.
            if ($cropped) {
                $fs->delete_area_files($context->id, 'theme_arupboost', $filearea);
            } else {
                $fs->delete_area_files($context->id, 'theme_arupboost', $type);
                $fs->delete_area_files($context->id, 'theme_arupboost', $type. '_cropped');
            }
        } else {
            throw new coding_exception('Unsupported context level '.$context->contextlevel);
        }

        // Create new cover image file and process it.
        $storedfile = $fs->create_file_from_string($fileinfo, $binary);
        $success = $storedfile instanceof \stored_file;
        $fileurl = \moodle_url::make_pluginfile_url(
            $storedfile->get_contextid(),
            $storedfile->get_component(),
            $storedfile->get_filearea(),
            $storedfile->get_timemodified(), // Used as a cache buster.
            $storedfile->get_filepath(),
            $storedfile->get_filename()
        );
        return ['success' => $success, 'fileurl' => $fileurl->out()];
    }
}
