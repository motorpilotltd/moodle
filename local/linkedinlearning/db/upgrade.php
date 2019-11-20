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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_linkedinlearning plugin
 *
 * @param   int $oldversion The old version of the local_coursemetadata plugin
 * @return  bool
 */
function xmldb_local_linkedinlearning_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016080527) {

        require_once("$CFG->dirroot/local/linkedinlearning/db/upgradelib.php");
        local_linkedinlearning_addmethodology();

        upgrade_plugin_savepoint(true, 2016080527, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080528) {
        $DB->execute('UPDATE {linkedinlearning_course} SET publishedat = publishedat/1000, lastupdatedat = lastupdatedat/1000');

        upgrade_plugin_savepoint(true, 2016080528, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080530) {
        set_config('lastsuccessfulrun', 0, 'local_linkedinlearning');
    }

    if ($oldversion < 2016080532) {

        $table = new xmldb_table('linkedinlearning_progress');

        // Adding fields to table tagcloud.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('urn', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('seconds_viewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('progress_percentage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table tagcloud.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tagcloud.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2016080532, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080533) {

        $table = new xmldb_table('linkedinlearning_progress');

        $field = new xmldb_field('first_viewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('last_viewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016080533, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080535) {

        $table = new xmldb_table('linkedinlearning_course');

        $field = new xmldb_field('ssolaunchurl', XMLDB_TYPE_TEXT);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        set_config('lastsuccessfulrun', 0, 'local_linkedinlearning');

        upgrade_plugin_savepoint(true, 2016080535, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080536) {
        set_config('local_linkedinlearning/courseprgogresssyncto', 0, 'local_linkedinlearning');

        upgrade_plugin_savepoint(true, 2016080536, 'local', 'linkedinlearning');
    }

    return true;
}