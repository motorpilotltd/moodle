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

class local_regions_model_subregioncourse extends local_regions_model {

    public function delete($id) {
        return $this->db->delete_records('local_regions_sub_cou', array('id' => $id));
    }

    public function delete_bysubregion($subregionid) {
        return $this->db->delete_records('local_regions_sub_cou', array('subregionid' => $subregionid));
    }

    public function count_subregioncourses($subregionid) {
        return $this->db->count_records('local_regions_sub_cou', array('subregionid' => $subregionid));
    }

    public function fetch_all_subregioncourses($subregionid) {
        return $this->db->get_records('local_regions_sub_cou', array('subregionid' => $subregionid));
    }

    public function fetch_subregioncourse_bysubregionandcourse($subregionid, $courseid) {
        return $this->db->get_records('local_regions_sub_cou', array('subregionid' => $subregionid, 'courseid' => $courseid));
    }

    public function fetch_all_courses_menu($regionid) {
        $sql = "SELECT c.id, c.shortname FROM {course} c INNER JOIN {local_regions_reg_cou} lrrc ON (lrrc.courseid = c.id AND lrrc.regionid = :regionid) ORDER BY c.shortname ASC";
        return $this->db->get_records_sql_menu($sql, array('regionid' => $regionid));
    }

    public function fetch_subregioncourses_menu($id) {
        return $this->db->get_records_menu('local_regions_sub_cou', array('subregionid' => $id), '', 'id, courseid');
    }

    public function fetch_subregioncourses_formapping($regionid) {
        $where = 'subregionid IN (SELECT id FROM {local_regions_sub} WHERE regionid = :regionid)';
        $subregioncourses = $this->db->get_records_select('local_regions_sub_cou', $where, array('regionid' => $regionid), '');
        if ($subregioncourses) {
            foreach ($subregioncourses as $subregioncourse) {
                $return[$subregioncourse->courseid][$subregioncourse->subregionid] = 1;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    public function save_subregioncourse($data) {
        if (isset($data->id) && $data->id > 0) {
            $this->db->update_record('local_regions_sub_cou', (object) $data);
            return $data->id;
        } else {
            return $this->db->insert_record('local_regions_sub_cou', (object) $data, true);
        }
    }
}
