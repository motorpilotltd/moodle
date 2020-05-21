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
 * @package   mod_coursera
 * @category  backup
 * @copyright 2018 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute coursera upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_coursera_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2018101106) {
        $table = new xmldb_table('courseracourse');
        $field = new xmldb_field('promophoto', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');

        // Conditionally launch add field services.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018101106, 'coursera');
    }

    if ($oldversion < 2018101109) {
        $table = new xmldb_table('courseraprogrammember');

        // Adding fields to table local_admin_user_update_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_CHAR, '35');
        $table->add_field('externalid', XMLDB_TYPE_CHAR, '35');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('joined', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('left', XMLDB_TYPE_INTEGER, '10', null, false);

        // Adding keys to table local_admin_user_update_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_admin_user_update_log.
        $table->add_index('courseraprogrammemberexternalid', XMLDB_INDEX_UNIQUE, array('programid,externalid,joined'));
        $table->add_index('courseraprogrammemberuserid', XMLDB_INDEX_NOTUNIQUE, array('programid,userid,joined'));

        // Conditionally launch create table for local_admin_user_update_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2018101109, 'coursera');
    }

    if ($oldversion < 2018101111) {
        $table = new xmldb_table('coursera');
        $field = new xmldb_field('moduleaccessperiod', XMLDB_TYPE_INTEGER, '10');

        // Conditionally launch add field services.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('courseramoduleaccess');

        // Adding fields to table local_admin_user_update_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseraid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('timeend', XMLDB_TYPE_INTEGER, '10');

        // Adding keys to table local_admin_user_update_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_admin_user_update_log.
        $table->add_index('courseraprogrammemberexternalid', XMLDB_INDEX_UNIQUE, array('courseraid,userid'));

        // Conditionally launch create table for local_admin_user_update_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2018101111, 'coursera');
    }

    return true;
}
