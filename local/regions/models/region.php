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

class local_regions_model_region extends local_regions_model {

    public function delete($regionid) {
        // Cascade delete.
        $result = $this->db->delete_records_select(
            'local_regions_sub_cou',
            "subregionid IN (SELECT id FROM {$this->table_name('local_regions_sub')} WHERE regionid = {$regionid})"
        );
        if (!$result) {
            return $result;
        }

        $result = $this->db->delete_records('local_regions_sub', array('regionid' => $regionid));
        if (!$result) {
            return $result;
        }

        $result = $this->db->delete_records('local_regions_reg_cou', array('regionid' => $regionid));
        if (!$result) {
            return $result;
        }

        $result = $this->db->delete_records('local_regions_reg', array('id' => $regionid));
        return $result;
    }

    public function count_regions($conditions = null) {
        return $this->db->count_records('local_regions_reg', $conditions);
    }

    public function fetch_all_regions($options = null) {
        return $this->db->get_records_sql("SELECT * FROM {local_regions_reg} ORDER BY name ASC");
    }

    public function fetch_region_byid($id) {
        return $this->db->get_record('local_regions_reg', array('id' => $id));
    }

    public function fetch_region_byname($name) {
        return $this->db->get_record('local_regions_reg', array('name' => $name));
    }

    public function fetch_region_bytapsname($tapsname) {
        return $this->db->get_record('local_regions_reg', array('tapsname' => $tapsname));
    }

    public function save_region($data) {
        if (isset($data->id) && $data->id > 0) {
            $this->db->update_record('local_regions_reg', (object) $data);
            return $data->id;
        } else {
            return $this->db->insert_record('local_regions_reg', (object) $data, true);
        }
    }
}
