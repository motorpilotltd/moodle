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
 * Upgrade code for local_admin
 *
 * @package     local_admin
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_admin plugin
 *
 * @param   int $oldversion The old version of the local_admin plugin
 * @return  bool
 */
function xmldb_local_admin_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022807) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/local/admin/version.php');

        $a = new stdClass();
        $a->name = 'local_admin';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022807';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'local_admin'));

        throw new moodle_exception('pluginversiontoolow', 'local_admin', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'local', 'admin');
    }

    if ($oldversion < 2015111601) {
        // Define table local_admin_user_update_log to be created.
        $table = new xmldb_table('local_admin_user_update_log');

        // Adding fields to table local_admin_user_update_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('staffid', XMLDB_TYPE_CHAR, '6');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('action', XMLDB_TYPE_CHAR, '100');
        $table->add_field('status', XMLDB_TYPE_CHAR, '100');
        $table->add_field('extrainfo', XMLDB_TYPE_TEXT);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10');

        // Adding keys to table local_admin_user_update_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_admin_user_update_log.
        $table->add_index('staffid', XMLDB_INDEX_NOTUNIQUE, array('staffid'));
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('action', XMLDB_INDEX_NOTUNIQUE, array('action'));
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, array('status'));
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, array('timecreated'));

        // Conditionally launch create table for local_admin_user_update_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'local', 'admin');
    }

    return true;
}
