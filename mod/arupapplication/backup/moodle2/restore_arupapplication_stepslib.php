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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_arupapplication_activity_task
 */

/**
 * Structure step to restore one arupapplication activity
 */
class restore_arupapplication_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('arupapplication', '/activity/arupapplication');
        $paths[] = new restore_path_element('arupstatementquestions', '/activity/arupapplication/arupstatementquestionss/arupstatementquestions');
        $paths[] = new restore_path_element('arupdeclarations', '/activity/arupapplication/arupdeclarationss/arupdeclarations');
        if ($userinfo) {
            $paths[] = new restore_path_element('arupsubmissions', '/activity/arupapplication/arupsubmissionss/arupsubmissions');
            $paths[] = new restore_path_element('arupstatementanswers', '/activity/arupapplication/arupsubmissionss/arupstatementanswerss/arupstatementanswers');
            $paths[] = new restore_path_element('arupdeclarationanswers', '/activity/arupapplication/arupsubmissionss/arupdeclarationanswerss/arupdeclarationanswers');
            $paths[] = new restore_path_element('arupapplication_tracking', '/activity/arupapplication/arupapplication_trackingss/arupapplication_tracking');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_arupapplication($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the arupapplication record
        $newapplicationid = $DB->insert_record('arupapplication', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newapplicationid);
    }

    protected function process_arupstatementquestions($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');

        $newitemid = $DB->insert_record('arupstatementquestions', $data);
        $this->set_mapping('arupstatementquestions', $oldid, $newitemid, true); // Can have files
    }

    protected function process_arupdeclarations($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');

        $newitemid = $DB->insert_record('arupdeclarations', $data);
        $this->set_mapping('arupdeclarations', $oldid, $newitemid, true); // Can have files
    }

    protected function process_arupsubmissions($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');

        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('arupsubmissions', $data);
        $this->set_mapping('arupsubmissions', $oldid, $newitemid, true); // Can have files
    }

    protected function process_arupstatementanswers($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');
        $data->questionid = $this->get_mappingid('arupstatementquestions', $data->questionid);

        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('arupstatementanswers', $data);
        $this->set_mapping('arupstatementanswers', $oldid, $newitemid, true); // Can have files
    }

    protected function process_arupdeclarationanswers($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');
        $data->declarationid = $this->get_mappingid('arupdeclarations', $data->declarationid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('arupdeclarationanswers', $data);
        $this->set_mapping('arupdeclarationanswers', $oldid, $newitemid, true); // Can have files
    }

    protected function process_arupapplication_tracking($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->applicationid = $this->get_new_parentid('arupapplication');

        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('arupapplication_tracking', $data);
        $this->set_mapping('arupapplication_tracking', $oldid, $newitemid, true); // Can have files
    }

    protected function after_execute() {
        // Add arupapplication related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_arupapplication', 'intro', null);
        $this->add_related_files('mod_arupapplication', 'submission', 'arupsubmissions');
    }
}
