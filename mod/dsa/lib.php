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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function dsa_supports($feature) {
    switch ($feature) {
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

function dsa_cm_info_view(cm_info $cm) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('mod_dsa');

    $cm->set_content($renderer->render_cm_info_view());
}

/**
 * Saves a new instance of the dsa into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_dsa_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function dsa_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('dsa', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the dsa in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_dsa_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function dsa_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('dsa', $moduleinstance);
}

/**
 * Removes an instance of the dsa from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function dsa_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('dsa', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('dsa', array('id' => $id));
    $DB->delete_records('dsa_assessment', array('id' => $id));

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
function dsa_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    list($sql, $params) = $DB->get_in_or_equal(['closed', 'abandoned'], SQL_PARAMS_NAMED, 'param', false);
    $params['userid'] = $userid;

    return
            $DB->record_exists_sql("SELECT * FROM {dsa_assessment} WHERE userid = :userid", ['userid' => $userid])
            &&
            !$DB->record_exists_sql("SELECT * FROM {dsa_assessment} WHERE userid = :userid AND state $sql", $params);
}