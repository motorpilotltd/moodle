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
 * Installation script for local_taps.
 *
 * @package     local_taps
 * @copyright   2017 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install script.
 */
function xmldb_local_taps_install() {
    global $DB;

    // Add index on HUB view.

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
}