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
 * Upgrade code for mod_tapsenrol
 *
 * @package     mod_tapsenrol
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_tapsenrol plugin
 *
 * @param   int $oldversion The old version of the mod_tapsenrol plugin
 * @return  bool
 */
function xmldb_tapsenrol_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017051508) {

        // Define table tapsenrol_class_enrolments to be created.
        $table = new xmldb_table('tapsenrol_class_enrolments');

        // Adding fields to table oauth2_user_field_mapping.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('classid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('bookingstatus', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('completiontime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('archived', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table oauth2_user_field_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for oauth2_user_field_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Main savepoint reached.
        upgrade_mod_savepoint(true, 2017051508, 'tapsenrol');
    }

    if ($oldversion < 2017051509) {
        $table = new xmldb_table('tapsenrol_class_enrolments');

        $field = new xmldb_field('completiontime', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        $dbman->change_field_notnull($table, $field);


        $field = new xmldb_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1);
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);
        $dbman->change_field_notnull($table, $field);

        // Main savepoint reached.
        upgrade_mod_savepoint(true, 2017051509, 'tapsenrol');
    }

    if ($oldversion < 2017051510) {
        $table = new xmldb_table('local_taps_class');

        $DB->execute("UPDATE {local_taps_class} SET archived = 0 WHERE archived IS NULL");
        $DB->execute("UPDATE {local_taps_class} SET classhidden = 0 WHERE classhidden IS NULL");

        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '1', null, false, null, 0);
        $dbman->change_field_notnull($table, $field);
        $dbman->change_field_precision($table, $field);


        $field = new xmldb_field('classhidden', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);
        $dbman->change_field_notnull($table, $field);
        $dbman->change_field_precision($table, $field);

        $DB->execute("UPDATE {local_taps_class} SET classtype = :classtype WHERE classtype = 'Self Paced'", ['classtype' => \mod_tapsenrol\enrolclass::TYPE_ELEARNING]);
        $DB->execute("UPDATE {local_taps_class} SET classtype = :classtype WHERE classtype = 'Scheduled'", ['classtype' => \mod_tapsenrol\enrolclass::TYPE_CLASSROOM]);
        $field = new xmldb_field('classtype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, \mod_tapsenrol\enrolclass::TYPE_CLASSROOM);
        $dbman->change_field_type($table, $field);

        // Main savepoint reached.
        upgrade_mod_savepoint(true, 2017051510, 'tapsenrol');
    }

    if ($oldversion < 2017051511) {
        $table = new xmldb_table('tapsenrol');
        $field = new xmldb_field('autocompletion');

        // Conditionally launch drop field groupmembersonly.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_mod_savepoint(true, 2017051511, 'tapsenrol');
    }

    if ($oldversion < 2017051512) {
        $table = new xmldb_table('tapsenrol');
        $field = new xmldb_field('completiontimetype');

        // Conditionally launch drop field groupmembersonly.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_mod_savepoint(true, 2017051512, 'tapsenrol');
    }

    return true;
}
