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

class local_regions_model_subregion extends local_regions_model {

    public function delete($subregionid) {
        // Cascade delete.
        $result = $this->db->delete_records('local_regions_sub_cou', array('subregionid' => $subregionid));
        if (!$result) {
            return $result;
        }

        $result = $this->db->delete_records('local_regions_sub', array('id' => $subregionid));
        return $result;
    }

    public function count_subregions($conditions = null) {
        return $this->db->count_records('local_regions_sub', $conditions);
    }

    public function fetch_all_subregions($conditions = null) {
        return $this->db->get_records('local_regions_sub', $conditions, 'name ASC');
    }

    public function fetch_all_subregions_menu($conditions = null) {
        return $this->db->get_records_menu('local_regions_sub', $conditions, 'name ASC', 'id, name');
    }

    public function fetch_subregion_byid($id) {
        return $this->db->get_record('local_regions_sub', array('id' => $id));
    }

    public function fetch_subregion_byname($name) {
        return $this->db->get_record('local_regions_sub', array('name' => $name));
    }

    public function save_subregion($data) {
        if (isset($data->id) && $data->id > 0) {
            $this->db->update_record('local_regions_sub', (object) $data);
            return $data->id;
        } else {
            return $this->db->insert_record('local_regions_sub', (object) $data, true);
        }
    }
}
