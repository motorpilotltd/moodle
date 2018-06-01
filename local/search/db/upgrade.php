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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_search_upgrade($oldversion = 0) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    if ($oldversion < 2015111601) {

        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily == 'mssql') {
            $DB->execute('CREATE FULLTEXT CATALOG moodlecoursesearch');
            $DB->execute('CREATE FULLTEXT INDEX ON {course} (fullname, shortname, summary) KEY INDEX mdl_cour_id_pk ON moodlecoursesearch');
            $DB->execute('CREATE FULLTEXT INDEX ON {arupadvertdatatype_custom} (keywords) KEY INDEX mdl_arupcust_id_pk ON moodlecoursesearch');
            $DB->execute('CREATE FULLTEXT INDEX ON {local_taps_course} (keywords, coursecode) KEY INDEX mdl_locatapscour_id_pk ON moodlecoursesearch');
            $DB->execute('CREATE FULLTEXT INDEX ON {local_taps_class} (classname) KEY INDEX mdl_locatapsclas_id_pk ON moodlecoursesearch');
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'local', 'search');
    }

    if ($oldversion < 2015111603) {
        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily == 'mssql') {
            $DB->execute('ALTER FULLTEXT INDEX ON {local_taps_course} ADD ([coursedescription])');
            $DB->execute('ALTER FULLTEXT INDEX ON {local_taps_course} ADD ([courseobjectives])');
            $DB->execute('ALTER FULLTEXT INDEX ON {arupadvertdatatype_custom} ADD ([objectives])');
            $DB->execute('ALTER FULLTEXT INDEX ON {arupadvertdatatype_custom} ADD ([description])');
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2015111603, 'local', 'search');
    }

    return true;
}