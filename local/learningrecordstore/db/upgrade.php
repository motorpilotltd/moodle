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
defined('MOODLE_INTERNAL') || die();
/**
 * Upgrade code for local_learningrecordstore
 *
 * @package     local_learningrecordstore
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_learningrecordstore plugin
 *
 * @param   int $oldversion The old version of the local_learningrecordstore plugin
 * @return  bool
 */
function xmldb_local_learningrecordstore_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015111608) {
        $table = new xmldb_table('local_learningrecordstore');

        $field = new xmldb_field('starttime', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('endtime', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        $dbman->change_field_notnull($table, $field);

        upgrade_plugin_savepoint(true, 2015111608, 'local', 'learningrecordstore');
    }

    return true;
}
