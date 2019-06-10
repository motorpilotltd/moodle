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


    if ($oldversion < 2015111610) {
        // Define table local_admin_user_update_log to be created.
        $table = new xmldb_table('local_learningrecordstore');

        // Adding fields to table local_admin_user_update_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('provider', XMLDB_TYPE_TEXT);
        $table->add_field('healthandsafetycategory', XMLDB_TYPE_TEXT);
        $table->add_field('providerid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('providername', XMLDB_TYPE_TEXT);
        $table->add_field('staffid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('duration', XMLDB_TYPE_FLOAT, '10,5');
        $table->add_field('durationunits', XMLDB_TYPE_TEXT);
        $table->add_field('completiontime', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('description', XMLDB_TYPE_TEXT);
        $table->add_field('certificateno', XMLDB_TYPE_TEXT);
        $table->add_field('classcategory', XMLDB_TYPE_TEXT);
        $table->add_field('classcost', XMLDB_TYPE_NUMBER, '20,5', null, null, null, null);
        $table->add_field('classcostcurrency', XMLDB_TYPE_TEXT);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('expirydate', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('classtype', XMLDB_TYPE_TEXT);
        $table->add_field('archived', XMLDB_TYPE_INTEGER, '4');
        $table->add_field('locked', XMLDB_TYPE_INTEGER, '4');
        $table->add_field('location', XMLDB_TYPE_TEXT);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_admin_user_update_log.
        $table->add_index('staffid', XMLDB_INDEX_NOTUNIQUE, array('staffid'));

        // Conditionally launch create table for local_admin_user_update_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111610, 'local', 'learningrecordstore');
    }

    if ($oldversion < 2015111611) {
        $table = new xmldb_table('local_learningrecordstore');

        $field = new xmldb_field('starttime', XMLDB_TYPE_INTEGER, '10', null, false, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('endtime', XMLDB_TYPE_INTEGER, '10', null, false, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2015111611, 'local', 'learningrecordstore');
    }

    return true;
}
