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
 * The local_taps interface.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mssql;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_taps taps class.
 *
 * @package    local_taps
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dbshim {
    public static function sql_group_concat($field, $delimiter = ', ', $unique = false) {
        global $DB;

        // if not supported, just return single value - use min()
        $sql = " MIN($field) ";

        switch ($DB->get_dbfamily()) {
            case 'mysql':
                // use native function
                $distinct = $unique ? 'DISTINCT' : '';
                $sql = " GROUP_CONCAT($distinct $field SEPARATOR '$delimiter') ";
                break;
            case 'postgres':
                // use custom aggregate function - must have been defined
                // in db/upgrade.php
                $distinct = $unique ? 'TRUE' : 'FALSE';
                $sql = " GROUP_CONCAT($field, '$delimiter', $distinct) ";
                break;
            case 'mssql':
                $distinct = $unique ? 'DISTINCT' : '';
                $sql = " dbo.GROUP_CONCAT_D($distinct $field, '$delimiter') ";
                break;
        }

        return $sql;
    }
}