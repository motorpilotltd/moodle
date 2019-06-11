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
 * Upgrade code for mod_tapscompletion
 *
 * @package     mod_tapscompletion
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_tapscompletion plugin
 *
 * @param   int $oldversion The old version of the mod_tapscompletion plugin
 * @return  bool
 */
function xmldb_tapscompletion_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022802) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/mod/tapscompletion/version.php');

        $a = new stdClass();
        $a->name = 'mod_tapscompletion';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022802';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'mod_tapscompletion'));

        throw new moodle_exception('pluginversiontoolow', 'mod_tapscompletion', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111604) {
        // Define fields completiontype to be added to tapscompletion.
        $table = new xmldb_table('tapscompletion');
        $field = new xmldb_field('completiontimetype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 1, 'completionattended');

        // Conditionally launch add field completiontype.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111604, 'mod', 'tapscompletion');
    }

    if ($oldversion < 2015111605) {
        // Define fields completed to be updated in tapscompletion_completion.
        $table = new xmldb_table('tapscompletion_completion');
        $field = new xmldb_field('completed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Conditionally launch change_field_precision.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_precision($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111605, 'mod', 'tapscompletion');
    }

    return true;
}
