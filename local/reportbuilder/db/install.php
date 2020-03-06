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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @package t0tara
 * @subpackage local_reportbuilder
 */


/**
 * This function is run when reportbuilder is first installed
 *
 * Add code here that should be run when the module is first installed
 */
function xmldb_local_reportbuilder_install() {
    global $DB;

    // Create stored procedure for aggregating text by concatenation.
    // MySQL supports by default. The code below adds Postgres support.
    // See \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat() function for usage.
    if ($DB->get_dbfamily() == 'postgres') {
        $typesql = "SELECT 1
                      FROM pg_type t
                      JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
                     WHERE n.nspname = current_schema AND t.typname = 'tp_concat'";
        $type_exists = $DB->get_record_sql("SELECT EXISTS ($typesql) AS exst");

        if ($type_exists->exst == 'f') {
            try {
                $DB->change_database_structure("CREATE TYPE tp_concat AS (data TEXT[], delimiter TEXT)");
            } catch (Exception $e) {
                // Let's ignore errors here, we will get the failure in the next query if there are problems.
            }
        }

        $sql = '
            CREATE OR REPLACE FUNCTION group_concat_iterate(_state
                tp_concat, _value TEXT, delimiter TEXT, is_distinct boolean)
                RETURNS tp_concat AS
                $BODY$
                SELECT
                CASE
                    WHEN $1 IS NULL THEN ARRAY[$2]
                    WHEN $4 AND $1.data @> ARRAY[$2] THEN $1.data
                    ELSE $1.data || $2
                        END,
                        $3
                        $BODY$
                        LANGUAGE \'sql\' VOLATILE;

            CREATE OR REPLACE FUNCTION group_concat_finish(_state tp_concat)
                RETURNS text AS
                $BODY$
                SELECT array_to_string($1.data, $1.delimiter)
                $BODY$
                LANGUAGE \'sql\' VOLATILE;

            DROP AGGREGATE IF EXISTS group_concat(text, text, boolean);
            CREATE AGGREGATE group_concat(text, text, boolean) (SFUNC =
                group_concat_iterate, STYPE = tp_concat, FINALFUNC =
                group_concat_finish)';
        $DB->change_database_structure($sql);

        /* To undo this, use the following:
         * DROP AGGREGATE group_concat(text, text, boolean);
         * DROP FUNCTION group_concat_finish(tp_concat);
         * DROP FUNCTION group_concat_iterate(tp_concat, text, text, boolean);
         * DROP TYPE tp_concat;
         */

        return true;
    }

    $dbman = $DB->get_manager();

    // Index on local_taps_enrolment.
    $table = new xmldb_table('SQLHUB.ARUP_ALL_STAFF_V');
    $index = new xmldb_index('EMPLOYEE_NUMBER', XMLDB_INDEX_NOTUNIQUE, array('EMPLOYEE_NUMBER'));
    if ($dbman->table_exists($table) && !$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }

    return true;
}
