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
 * Assignment upgrade script.
 *
 * @package   mod_assignment
 * @copyright 2013 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Inform admins about assignments that still need upgrading.
 */
function local_search_install_fulltextindexes() {
    global $DB;

    $dbfamily = $DB->get_dbfamily();
    if ($dbfamily !== 'mssql') {
        return;
    }

    $DB->execute('CREATE FULLTEXT CATALOG moodlecoursesearch');
    $DB->execute('CREATE FULLTEXT INDEX ON mdl_course (fullname, shortname, summary) KEY INDEX mdl_cour_id_pk ON moodlecoursesearch');
    $DB->execute('CREATE FULLTEXT INDEX ON mdl_arupadvertdatatype_custom (keywords) KEY INDEX mdl_arupcust_id_pk ON moodlecoursesearch');
    $DB->execute('CREATE FULLTEXT INDEX ON mdl_local_taps_course (keywords, coursecode) KEY INDEX mdl_locatapscour_id_pk ON moodlecoursesearch');
    $DB->execute('CREATE FULLTEXT INDEX ON mdl_local_taps_class (classname) KEY INDEX mdl_locatapsclas_id_pk ON moodlecoursesearch');
}
