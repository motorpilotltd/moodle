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
 * Defines the restore_arupenrol_activity_structure_step class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Define all the restore steps that will be used by the restore_arupenrol_activity_task
 *
 * @package    mod_arupenrol
 */

defined('MOODLE_INTERNAL') || die;

/**
 * The restore_arupenrol_activity_structure_step class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_arupenrol_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define structure.
     *
     * @return array
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('arupenrol', '/activity/arupenrol');

        if ($userinfo) {
            $paths[] = new restore_path_element('completions', '/activity/arupenrol/completions/completion');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process arupenrol tag information.
     *
     * @param array $data information
     * @return void
     */
    protected function process_arupenrol($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('arupenrol', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process completion tracking restore.
     *
     * @param stdClass $data The data in object form
     * @return void
     */
    protected function process_arupenrol_completion($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->arupenrolid = $this->get_new_parentid('arupenrol');

        if ($data->userid > 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        $newitemid = $DB->insert_record('arupenrol_completion', $data);
    }

    /**
     * Process after execution steps.
     *
     * @return void
     */
    protected function after_execute() {
        // Add arupenrol related files.
        $this->add_related_files('mod_arupenrol', 'intro', null);
        $this->add_related_files('mod_arupenrol', 'outro', null);
    }
}
