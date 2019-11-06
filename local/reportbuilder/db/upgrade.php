<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Local database upgrade script
 *
 * @param   int $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean always true
 */
function xmldb_local_reportbuilder_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017091904) {
        // Index on local_taps_enrolment.
        $table = new xmldb_table('SQLHUB.ARUP_ALL_STAFF_V');
        $index = new xmldb_index('EMPLOYEE_NUMBER', XMLDB_INDEX_UNIQUE, array('EMPLOYEE_NUMBER'));
        if ($dbman->table_exists($table) && !$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2017091904, 'local', 'reportbuilder');
    }

    if ($oldversion < 2017091906) {
        $DB->delete_records('report_builder', ['embedded' => true]);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2017091906, 'local', 'reportbuilder');
    }

    if ($oldversion < 2017091908) {

        $DB->execute('UPDATE {report_builder} SET accessmode = 2 WHERE accessmode = 1');

        // Iomad_track savepoint reached.
        upgrade_plugin_savepoint(true, 2017091908, 'local', 'reportbuilder');
    }

    return true;
}
