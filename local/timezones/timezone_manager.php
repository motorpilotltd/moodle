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
 * Description of timezone_manager
 *
 * @author paulstanyer
 */
class timezone_manager {
    public static function get_timezones() {
        global $DB;

        // Very likely we'll want actual logic here at some point?

        return self::spawn($DB->get_records('local_timezones'));
    }

    public static function spawn($records) {
        $return = array();
        foreach ($records as $row) {
            $return[] = new timezone($row->timezone, $row->display, $row->id);
        }
        return $return;
    }
}
