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
function xmldb_local_taps_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022803) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/local/taps/version.php');

        $a = new stdClass();
        $a->name = 'local_taps';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022803';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'local_taps'));

        throw new moodle_exception('pluginversiontoolow', 'local_taps', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'local', 'taps');
    }

    if ($oldversion < 2015111601) {
        // Define table local_taps_enrolment_updates to be dropped.
        $table = new xmldb_table('local_taps_enrolment_updates');

        // Conditionally launch drop table for local_taps_enrolment_updates.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'local', 'taps');
    }

    if ($oldversion < 2015111605) {

        // Define field archived to be added to local_taps_course.
        $table = new xmldb_table('local_taps_course');
        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'usedtimezone');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field archived to be added to local_taps_class.
        $table = new xmldb_table('local_taps_class');
        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'classcostcurrency');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111605, 'local', 'taps');
    }

    if ($oldversion < 2015111606) {

        // Define field archived to be added to local_taps_enrolment.
        $table = new xmldb_table('local_taps_enrolment');
        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'classcontext');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111606, 'local', 'taps');
    }

    if ($oldversion < 2015111607) {

        // Define field classhidden to be added to local_taps_class.
        $table = new xmldb_table('local_taps_class');
        $field = new xmldb_field('classhidden', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'archived');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111607, 'local', 'taps');
    }

    if ($oldversion < 2015111608) {

        // Define field active to be added to local_taps_enrolment.
        $table = new xmldb_table('local_taps_enrolment');
        $field = new xmldb_field('active', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 1, 'classcontext');

        // Conditionally launch add field archived.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111608, 'local', 'taps');
    }

    if ($oldversion < 2015111609) {

        // Define fields bookingplaceddate and lastupdatedate to be added to local_taps_enrolment.
        $table = new xmldb_table('local_taps_enrolment');
        $fields = [
            new xmldb_field('bookingplaceddate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'classcontext'),
            new xmldb_field('lastupdatedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'bookingplaceddate'),
        ];

        // Conditionally launch add fields.
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111609, 'local', 'taps');
    }

    if ($oldversion < 2015111610) {
        // Clear down language strings to remove old ones from DB.
        $componentid = $DB->get_field('tool_customlang_components', 'id', array('name' => 'local_taps'));
        if ($componentid) {
            $DB->delete_records('tool_customlang', array('componentid' => $componentid));
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111610, 'local', 'taps');
    }

    if ($oldversion < 2015111611) {
        // Add some indexes to improve reporting.

        // Index on local_taps_enrolment.
        $table = new xmldb_table('local_taps_enrolment');
        $index = new xmldb_index('staffid', XMLDB_INDEX_NOTUNIQUE, array('staffid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Index on HUB view.
        // Open second connection as we need no prefix.
        $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = array();
        }
        // Pretend it's external to remove prefix injection.
        $DB2 = \moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary, true);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, false, $cfg->dboptions);
        $dbman2 = $DB2->get_manager();
        $table = new xmldb_table('ARUP_ALL_STAFF_V');
        $index = new xmldb_index('arupallstafv_emp_ix', XMLDB_INDEX_NOTUNIQUE, array('EMPLOYEE_NUMBER'));
        if ($dbman2->table_exists($table) && !$dbman2->index_exists($table, $index)) {
            // Use of execute to avoid field names being forced to lowercase when using database_manager::add_index().
            $DB2->execute('CREATE INDEX arupallstafv_emp_ix ON SQLHUB.ARUP_ALL_STAFF_V (EMPLOYEE_NUMBER)');
        }

        // Taps savepoint reached.
        upgrade_plugin_savepoint(true, 2015111611, 'local', 'taps');
    }


    return true;
}

