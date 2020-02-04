<?php
// This file is part of the Arup cost centre system
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
 * Upgrade code for local_costcentre
 *
 * @package     local_costcentre
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade local_costcentre plugin
 *
 * @param   int $oldversion The old version of the local_costcentre plugin
 * @return  bool
 */
function xmldb_local_costcentre_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015060101) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/local/costcentre/version.php');

        $a = new stdClass();
        $a->name = 'local_costcentre';
        $a->version = $plugin->version;
        $a->requiredversion = '2015060101';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'local_costcentre'));

        throw new moodle_exception('pluginversiontoolow', 'local_costcentre', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'local', 'costcentre');
    }

    if ($oldversion < 2016071900) {
        // Define table local_costcentre to be updated.
        $table = new xmldb_table('local_costcentre');

        // Add 'laststaffupdate' field to 'local_costcentre' table.
        $field = new xmldb_field('laststaffupdate', XMLDB_TYPE_INTEGER, '10');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2016071900, 'local', 'costcentre');
    }

    if ($oldversion < 2016072100) {
        // Clear down language strings to remove old ones from DB.
        $componentid = $DB->get_field('tool_customlang_components', 'id', array('name' => 'local_costcentre'));
        if ($componentid) {
            $DB->delete_records('tool_customlang', array('componentid' => $componentid));
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2016072100, 'local', 'costcentre');
    }

    if ($oldversion < 2016080300) {

        // Define field groupleaderactive to be added to local_costcentre.
        $table = new xmldb_table('local_costcentre');
        $field = new xmldb_field('groupleaderactive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'laststaffupdate');

        // Conditionally launch add field groupleaderactive.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2016080300, 'local', 'costcentre');
    }

    if ($oldversion < 2016080301) {
        // Define table local_costcentre to be updated.
        $table = new xmldb_table('local_costcentre');

        // Drop 'laststaffupdate' field from 'local_costcentre' table.
        $field = new xmldb_field('laststaffupdate');

        // Conditionally launch drop field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Clear down language strings to remove old ones from DB.
        $componentid = $DB->get_field('tool_customlang_components', 'id', array('name' => 'local_costcentre'));
        if ($componentid) {
            $DB->delete_records('tool_customlang', array('componentid' => $componentid));
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2016080301, 'local', 'costcentre');
    }

    if ($oldversion < 2016080302) {

        // Define field groupleaderactive to be added to local_costcentre.
        $table = new xmldb_table('local_costcentre');
        $field = new xmldb_field('appraiserissupervisor', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);

        // Conditionally launch add field groupleaderactive.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2016080302, 'local', 'costcentre');
    }

    if ($oldversion < 2017051501) {

        // Increase costcentre field(s) 255 chars.
        // Field definition is same for both tables.
        $field = new xmldb_field('costcentre', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        $table1 = new xmldb_table('local_costcentre');
        $table2 = new xmldb_table('local_costcentre_user');

        $index1 = new xmldb_index('costcentre', XMLDB_INDEX_UNIQUE, ['costcentre']);
        $index2 = new xmldb_index('costcentre', XMLDB_INDEX_NOTUNIQUE, ['costcentre']);

        // Conditionally launch drop index.
        if ($dbman->index_exists($table1, $index1)) {
            $dbman->drop_index($table1, $index1);
        }
        if ($dbman->index_exists($table2, $index2)) {
            $dbman->drop_index($table2, $index2);
        }

        // Conditionally launch change field.
        if ($dbman->field_exists($table1, $field)) {
            $dbman->change_field_precision($table1, $field);
        }
        if ($dbman->field_exists($table2, $field)) {
            $dbman->change_field_precision($table2, $field);
        }

        // Conditionally launch re-add index.
        if (!$dbman->index_exists($table1, $index1)) {
            $dbman->add_index($table1, $index1);
        }
        if (!$dbman->index_exists($table2, $index2)) {
            $dbman->add_index($table2, $index2);
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2017051501, 'local', 'costcentre');
    }

    if ($oldversion < 2017051505) {
        // Add index to icq column on user table (cost centre code).
        $table = new xmldb_table('user');
        $index = new xmldb_index('user_icq', XMLDB_INDEX_NOTUNIQUE, ['icq']);

        // Conditionally launch add index.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Costcentre savepoint reached.
        upgrade_plugin_savepoint(true, 2017051505, 'local', 'costcentre');
    }

    return true;
}