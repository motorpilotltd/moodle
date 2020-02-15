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
 * @author Valerii Kuznetsov <valerii.kuznetsov@t0taralms.com>
 * @package local_reportbuilder
 */

namespace local_sqlhub\task;

class checktablestatus extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('checktablestatus', 'local_reportbuilder');
    }


    /**
     * Preprocess report groups
     */
    public function execute() {
        global $DB;

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
        $table = new \xmldb_table('ARUP_ALL_STAFF_V');
        $indexes = [
            new \xmldb_index('arupallstafv_emp_ix', XMLDB_INDEX_UNIQUE, array('EMPLOYEE_NUMBER')),
            new \xmldb_index('arupallstafv_reg_ix', XMLDB_INDEX_NOTUNIQUE, array('REGION_NAME')),
            new \xmldb_index('arupallstafv_geo_ix', XMLDB_INDEX_NOTUNIQUE, array('GEO_REGION')),
            new \xmldb_index('arupallstafv_loc_ix', XMLDB_INDEX_NOTUNIQUE, array('LOCATION_NAME')),
            new \xmldb_index('arupallstafv_gro_ix', XMLDB_INDEX_NOTUNIQUE, array('GROUP_NAME')),
            new \xmldb_index('arupallstafv_ccc_ix', XMLDB_INDEX_NOTUNIQUE, array('CENTRE_CODE')),
            new \xmldb_index('arupallstafv_ccn_ix', XMLDB_INDEX_NOTUNIQUE, array('CENTRE_NAME')),
            new \xmldb_index('arupallstafv_com_ix', XMLDB_INDEX_NOTUNIQUE, array('COMPANY_CODE')),
        ];
        foreach ($indexes as $index) {
            if ($dbman2->table_exists($table) && !$dbman2->index_exists($table, $index)) {
                $name = $index->getName();
                $fields = implode(', ', $index->getFields());
                $unique = ($index->getUnique()) ? 'UNIQUE' : '';
                // Use of execute to avoid field names being forced to lowercase when using database_manager::add_index().
                $DB2->execute("CREATE {$unique} INDEX {$name} ON SQLHUB.ARUP_ALL_STAFF_V ({$fields})");
            }
        }
    }
}