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
 * Upgrade code for mod_arupadvert
 *
 * @package     mod_arupadvert
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_arupadvert plugin
 *
 * @param   int $oldversion The old version of the mod_arupadvert plugin
 * @return  bool
 */
function xmldb_arupadvert_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022802) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/mod/arupadvert/version.php');

        $a = new stdClass();
        $a->name = 'mod_arupadvert';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022802';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'mod_arupadvert'));

        throw new moodle_exception('pluginversiontoolow', 'mod_arupadvert', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'mod', 'arupadvert');
    }

    if ($oldversion < 2015111601) {
        // Define field showheadings to be added to arupadvert
        $table = new xmldb_table('arupadvert');
        $field = new xmldb_field('showheadings', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'altword');

        // Conditionally launch add field showheadings
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'mod', 'arupadvert');
    }

    return true;
}
