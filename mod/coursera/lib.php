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
 * @package   mod_coursera
 * @category  backup
 * @copyright 2018 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function coursera_supports($feature) {

    switch($feature) {
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the coursera into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $coursera Submitted data from the form in mod_form.php
 * @param mod_coursera_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted coursera record
 */
function coursera_add_instance(stdClass $coursera, mod_coursera_mod_form $mform = null) {
    global $DB;

    $coursera->timecreated = time();

    // You may have to add extra stuff in here.

    $coursera->id = $DB->insert_record('coursera', $coursera);

    return $coursera->id;
}

/**
 * Updates an instance of the coursera in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $coursera An object from the form in mod_form.php
 * @param mod_coursera_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function coursera_update_instance(stdClass $coursera, mod_coursera_mod_form $mform = null) {
    global $DB;

    $coursera->timemodified = time();
    $coursera->id = $coursera->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('coursera', $coursera);

    return $result;
}

/**
 * Removes an instance of the coursera from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function coursera_delete_instance($id) {
    global $DB;

    if (! $coursera = $DB->get_record('coursera', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('coursera', array('id' => $coursera->id));

    return true;
}

/**
 * Obtains the automatic completion state for this application based on the condition
 * in arup application settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function coursera_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $user = core_user::get_user($userid);

    $coursera = $DB->get_record('coursera', ['id' => $cm->instance]);

    return $DB->record_exists_sql(
            "SELECT * FROM {courseraprogress} WHERE courseracourseid = :courseracourseid AND iscompleted = 1 AND userid = :userid",
            ['userid' => $user->id, 'courseracourseid' => $coursera->contentid]
    );
}

function coursera_get_refresh_token() {
    $coursera = new \mod_coursera\coursera();
    $coursera->getrefreshtoken();
}