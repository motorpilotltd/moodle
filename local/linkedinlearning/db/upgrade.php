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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_linkedinlearning plugin
 *
 * @param   int $oldversion The old version of the local_coursemetadata plugin
 * @return  bool
 */
function xmldb_local_linkedinlearning_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016080527) {

        require_once("$CFG->dirroot/local/linkedinlearning/db/upgradelib.php");
        local_linkedinlearning_addmethodology();

        upgrade_plugin_savepoint(true, 2016080527, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080528) {
        $DB->execute('UPDATE {linkedinlearning_course} SET publishedat = publishedat/1000, lastupdatedat = lastupdatedat/1000');

        upgrade_plugin_savepoint(true, 2016080528, 'local', 'linkedinlearning');
    }

    if ($oldversion < 2016080530) {
        set_config('lastsuccessfulrun', 0, 'local_linkedinlearning');
    }

    return true;
}