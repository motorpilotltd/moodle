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
 * Define all the restore steps that will be used by the restore_threesixty_activity_task
 */

/**
 * Structure step to restore one threesixty activity
 */
class restore_threesixty_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('threesixty', '/activity/threesixty');
        $paths[] = new restore_path_element('threesixty_competency', '/activity/threesixty/competencies/competency');
        $paths[] = new restore_path_element('threesixty_skill', '/activity/threesixty/competencies/competency/skills/skill');
        if ($userinfo) {
            $paths[] = new restore_path_element('threesixty_analysis', '/activity/threesixty/analyses/analysis');
            $paths[] = new restore_path_element('threesixty_respondent', '/activity/threesixty/analyses/analysis/respondents/respondent');
            $paths[] = new restore_path_element('threesixty_response', '/activity/threesixty/analyses/analysis/respondents/respondent/responses/response');
            $paths[] = new restore_path_element('threesixty_response_skill', '/activity/threesixty/analyses/analysis/respondents/respondent/responses/response/responseskills/responseskill');
            $paths[] = new restore_path_element('threesixty_response_comp', '/activity/threesixty/analyses/analysis/respondents/respondent/responses/response/responsecomps/responsecomp');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_threesixty($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        if ($data->grade < 0) { // scale found, get mapping
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('threesixty', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_threesixty_competency($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');

        $newitemid = $DB->insert_record('threesixty_competency', $data);
        $this->set_mapping('threesixty_competency', $oldid, $newitemid);
    }

    protected function process_threesixty_skill($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->competencyid = $this->get_new_parentid('threesixty_competency');

        $newitemid = $DB->insert_record('threesixty_skill', $data);
        $this->set_mapping('threesixty_skill', $oldid, $newitemid);
    }

    protected function process_threesixty_analysis($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('threesixty_analysis', $data);
        $this->set_mapping('threesixty_analysis', $oldid, $newitemid);
    }

    protected function process_threesixty_respondent($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');
        $data->analysisid = $this->get_new_parentid('threesixty_analysis');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($data->respondentuserid){
            $data->respondentuserid = $this->get_mappingid('user', $data->respondentuserid);
        }

        $data->declinetime = $this->apply_date_offset($data->declinetime);

        $newitemid = $DB->insert_record('threesixty_respondent', $data);
        $this->set_mapping('threesixty_respondent', $oldid, $newitemid);
    }

    protected function process_threesixty_response($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->analysisid = $this->get_new_parentid('threesixty_analysis');
        $data->respondentid = $this->get_new_parentid('threesixty_respondent');
        $data->timecompleted = $this->apply_date_offset($data->timecompleted);

        $newitemid = $DB->insert_record('threesixty_response', $data);
        $this->set_mapping('threesixty_response', $oldid, $newitemid);
    }

    protected function process_threesixty_response_skill($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->responseid = $this->get_new_parentid('threesixty_response');
        $data->skillid = $this->get_mappingid('threesixty_skill', $data->skillid);

        $newitemid = $DB->insert_record('threesixty_response_skill', $data);
        $this->set_mapping('threesixty_response_skill', $oldid, $newitemid);
    }

    protected function process_threesixty_response_comp($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->responseid = $this->get_new_parentid('threesixty_response');
        $data->competencyid = $this->get_mappingid('threesixty_competency', $data->competencyid);

        $newitemid = $DB->insert_record('threesixty_response_comp', $data);
        $this->set_mapping('threesixty_response_comp', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add threesixty related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_threesixty', 'intro', null);
    }
}
