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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_lynda plugin
 *
 * @param   int $oldversion The old version of the local_coursemetadata plugin
 * @return  bool
 */
function xmldb_local_lynda_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016080510) {
        $table = new xmldb_table('local_lynda_progress');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('remotecourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('lastviewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('percentcomplete', XMLDB_TYPE_NUMBER, '12', null, XMLDB_NOTNULL, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2016080510, 'local', 'lynda');
    }

    if ($oldversion < 2016080513) {
        $table = new xmldb_table('local_lynda_course');
        $field = new xmldb_field('thumbnail', XMLDB_TYPE_TEXT);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016080513, 'local', 'lynda');
    }

    if ($oldversion < 2016080515) {
        $table = new xmldb_table('local_lynda_course');
        $field = new xmldb_field('durationinseconds', XMLDB_TYPE_INTEGER, 10);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016080515, 'local', 'lynda');
    }

    if ($oldversion < 2016080518) {
        $table = new xmldb_table('local_lynda_progress');
        $field = new xmldb_field('regionid', XMLDB_TYPE_INTEGER, 10);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016080518, 'local', 'lynda');
    }

    return true;
}