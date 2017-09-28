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
 * Install code for local_admin
 *
 * @package     local_admin
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install local_admin plugin
 *
 * @param   int $oldversion The old version of the local_admin plugin
 * @return  bool
 */
function xmldb_local_admin_install() {
    global $DB;

    $dbman = $DB->get_manager();

    // Increase phone1 and phone2 to 255 chars.
    $table = new xmldb_table('user');

    // Changing precision of field phone1 on table user to (255).
    $phone1 = new xmldb_field('phone1', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $dbman->change_field_precision($table, $phone1);

    // Changing precision of field phone2 on table user to (255).
    $phone2 = new xmldb_field('phone2', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $dbman->change_field_precision($table, $phone2);
}
