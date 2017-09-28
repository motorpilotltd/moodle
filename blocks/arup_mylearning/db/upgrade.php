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
 * Upgrade code for block_arup_mylearning
 *
 * @package     block_arup_mylearning
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade block_arup_mylearning plugin
 *
 * @param   int $oldversion The old version of the block_arup_mylearning plugin
 * @return  bool
 */
function xmldb_block_arup_mylearning_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022801) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/local/admin/version.php');

        $a = new stdClass();
        $a->name = 'block_arup_mylearning';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022801';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'block_arup_mylearning'));

        throw new moodle_exception('pluginversiontoolow', 'block_arup_mylearning', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_block_savepoint(true, 2015111600, 'arup_mylearning');
    }

    return true;
}
