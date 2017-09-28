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
 *
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die;

class local_regions_model_regioncourse extends local_regions_model {

    public function delete($id) {
        $courseid = $this->db->get_field('local_regions_reg_cou', 'courseid', array('id' => $id));

        // Cascade delete.
        $result = $this->db->delete_records_select(
            'local_regions_sub_cou',
            "subregionid IN (SELECT id FROM {local_regions_sub} WHERE regionid = :regionid) AND courseid = :courseid",
            array('regionid' => $regionid, 'courseid' => $courseid)
        );
        if (!$result) {
            return $result;
        }

        return $this->db->delete_records('local_regions_reg_cou', array('id' => $id));
    }

    public function delete_byregion($regionid) {
        // Cascade delete.
        $result = $this->db->delete_records_select(
            'local_regions_sub_cou',
            "subregionid IN (SELECT id FROM {local_regions_sub} WHERE regionid = :regionid)",
            array('regionid' => $regionid)
        );
        if (!$result) {
            return $result;
        }

        return $this->db->delete_records('local_regions_reg_cou', array('regionid' => $regionid));
    }

    public function delete_byregionandcourse($regionid, $courseid) {
        // Cascade delete.
        $result = $this->db->delete_records_select(
            'local_regions_sub_cou',
            "subregionid IN (SELECT id FROM {local_regions_sub} WHERE regionid = :regionid) AND courseid = :courseid",
            array('regionid' => $regionid, 'courseid' => $courseid)
        );
        if (!$result) {
            return $result;
        }

        return $this->db->delete_records('local_regions_reg_cou', array('regionid' => $regionid, 'courseid' => $courseid));
    }

    public function count_regioncourses($regionid) {
        return $this->db->count_records('local_regions_reg_cou', array('regionid' => $regionid));
    }

    public function fetch_all_regioncourses($regionid) {
        return $this->db->get_records('local_regions_reg_cou', array('regionid' => $regionid));
    }

    public function fetch_all_courses_menu($options = null) {
        return $this->db->get_records_menu('course', array(), 'shortname ASC', 'id, shortname');
    }

    public function fetch_regioncourses_menu($id) {
        return $this->db->get_records_menu('local_regions_reg_cou', array('regionid' => $id), '', 'id, courseid');
    }

    public function fetch_regioncourses_formapping($id) {
        $regioncourses = $this->db->get_records('local_regions_reg_cou', array('regionid' => $id));
        if ($regioncourses) {
            foreach ($regioncourses as $regioncourse) {
                $return[$regioncourse->courseid] = 1;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    public function save_regioncourse($data) {
        if (isset($data->id) && $data->id > 0) {
            $this->db->update_record('local_regions_reg_cou', (object) $data);
            return $data->id;
        } else {
            return $this->db->insert_record('local_regions_reg_cou', (object) $data, true);
        }
    }
}
