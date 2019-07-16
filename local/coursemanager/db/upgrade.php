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
 * Upgrade code for local_taps
 *
 * @package     local_taps
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_taps plugin
 *
 * @param   int $oldversion The old version of the local_taps plugin
 * @return  bool
 */
function xmldb_local_coursemanager_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017011703) {
        // Define field archived to be added to local_taps_enrolment.
        $table = new xmldb_table('local_taps_enrolment');
        $field = new xmldb_field('locked', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'archived');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('providerid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'classcontext');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2017011703, 'local', 'coursemanager');
    }

    return true;
}

