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

abstract class local_regions_model {
    protected $regions;
    /**
     *
     * @var moodle_database
     */
    protected $db;

    public function __construct(local_regions $base) {
        global $DB;

        $this->db = $DB;
        $this->regions = $base;
    }

    /**
     * Load a model class
     *
     * @param string $name
     * @return local_regions_model
     */
    protected function model($name) {
        return $this->regions->model($name);
    }

    protected function table_name($name) {
        return $this->regions->get_config('prefix') . $name;
    }
}