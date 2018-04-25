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
 * Upgrade code for mod_arupevidence
 *
 * @package     mod_arupevidence
 * @copyright   2017 Xantico Ltd 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_arupevidence plugin
 *
 * @param   int $oldversion The old version of the mod_arupevidence plugin
 * @return  bool
 */
function xmldb_arupevidence_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015111604) {
        $table = new xmldb_table('arupevidence');
        $fields = array(
            new xmldb_field('requireexpirydate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, 0, 0),
            new xmldb_field('requirevalidityperiod', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, 0, 0),
            new xmldb_field('mustendmonth', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, 0, 0),
            new xmldb_field('expectedvalidityperiod', XMLDB_TYPE_INTEGER, '2', null, false, 0, 0),
            new xmldb_field('expectedvalidityperiodunit', XMLDB_TYPE_CHAR, '10', null, false, 0, 0),
            new xmldb_field('approvalroles', XMLDB_TYPE_INTEGER, '10', null, false, 0, ''),
            new xmldb_field('approvalusers', XMLDB_TYPE_TEXT),
            new xmldb_field('cpdlms', XMLDB_TYPE_CHAR, '3', null, false, 0, 0),
        );

        // Conditionally launch add field
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Dropping unused fields
        $table = new xmldb_table('arupevidence_users');
        $fields2 = array(
            new xmldb_field('showcompletiondate'),
            new xmldb_field('manualindicate'),
            new xmldb_field('showcertificateupload'),
            new xmldb_field('firstname'),
            new xmldb_field('lastname'),
            new xmldb_field('email'),
        );
        // Conditionally launch drop field
        foreach ($fields2 as $field2) {
            if ($dbman->field_exists($table, $field2)) {
                $dbman->drop_field($table, $field2);
            }
        }

        $table3 = new xmldb_table('arupevidence_users');
        $fields3 = array(
            new xmldb_field('validityperiod', XMLDB_TYPE_INTEGER, '2', null, false, 0, 0),
            new xmldb_field('validityperiodunit', XMLDB_TYPE_CHAR, '10', null, false, 0, 0),
            new xmldb_field('expirydate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'completiondate'),
            new xmldb_field('provider', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'expirydate'),
            new xmldb_field('duration', XMLDB_TYPE_FLOAT, '20, 2', null, null, null, 0, 'provider'),
            new xmldb_field('durationunitscode', XMLDB_TYPE_TEXT, 10, null, null, null, null, 'duration'),
            new xmldb_field('location', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'durationunitscode'),
            new xmldb_field('classstartdate', XMLDB_TYPE_INTEGER, 10, null, false, null, null, 'location'),
            new xmldb_field('classcost', XMLDB_TYPE_NUMBER, '20, 2', null, null, null, null, 'classstartdate'),
            new xmldb_field('classcostcurrency', XMLDB_TYPE_CHAR, 10, null, null, null, null, 'classcost'),
            new xmldb_field('certificateno', XMLDB_TYPE_TEXT, 250, null, null, null, null, 'classcostcurrency')
        );

        // Conditionally launch add fields for arupevidence_users table
        foreach ($fields3 as $field3) {
            if (!$dbman->field_exists($table3, $field3)) {
                $dbman->add_field($table3, $field3);
            }
        }
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111604, 'mod', 'arupevidence');
    }

    if ($oldversion < 2015111605) {
        $table = new xmldb_table('arupevidence_users');
        $field = new xmldb_field('rejected', XMLDB_TYPE_INTEGER, '10', null, false, null, null);

        // Conditionally launch add field bookedby.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111605, 'mod', 'arupevidence');
    }

    if ($oldversion < 2015111607) {
        $table = new xmldb_table('arupevidence_users');
        $field = new xmldb_field('rejectmessage', XMLDB_TYPE_TEXT);

        // Conditionally launch add field bookedby.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111607, 'mod', 'arupevidence');
    }

    if ($oldversion < 2015111609) {
        $table = new xmldb_table('arupevidence_users');
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, 10, null, false, null, null);

        // The cpdid or enrolmentid to be use for retrieving file in mdl_files table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111609, 'mod', 'arupevidence');
    }

    // updating cpdlms field
    if ($oldversion < 2015111610) {
        $table = new xmldb_table('arupevidence');
        $field = new xmldb_field('cpdlms', XMLDB_TYPE_INTEGER, '1', null, false, null, null);

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);

            $dbman->add_field($table, $field);
        }
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111610, 'mod', 'arupevidence');
    }

    if ($oldversion < 2015111614) {
        $table = new xmldb_table('arupevidence');
        $fields = array(
            new xmldb_field('setcoursecompletion', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1),
            new xmldb_field('setcertificationcompletion', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1),
        );

        // Conditionally launch add field
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111614, 'mod', 'arupevidence');
    }

    if ($oldversion < 2015111615) {
        $table = new xmldb_table('arupevidence_users');
        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);

        // Conditionally launch add field
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111615, 'mod', 'arupevidence');
    }

    // updating cpdlms field
    if ($oldversion < 2015111616) {
        $table = new xmldb_table('arupevidence_users');
        $field = new xmldb_field('expirydate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'completiondate');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $dbman->add_field($table, $field);
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111616, 'mod', 'arupevidence');
    }

    return true;
}
