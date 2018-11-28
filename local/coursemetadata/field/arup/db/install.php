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
 * coursemetadatafield_arup field.
 *
 * @package    coursemetadatafield_arup
 * @copyright  Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install script.
 */
function xmldb_coursemetadatafield_arup_install() {
    global $CFG, $DB;

    require_once("$CFG->dirroot/local/coursemetadata/classes/define_base.php");
    require_once("$CFG->dirroot/local/coursemetadata/field/arup/define.class.php");
    $formfield = new coursemetadata_define_arup();

    $data = new stdClass();
    $data->shortname = 'arupmetadata';
    $data->name = 'Arup metadata';
    $data->datatype = 'arup';
    $data->description = '';
    $data->descriptionformat = FORMAT_HTML;
    $data->categoryid = 1;
    $data->sortorder = 1;
    $data->required = 0;
    $data->locked = 0;
    $data->visible = COURSEMETADATA_VISIBLE_ALL;
    $data->forceunique = 0;
    $data->defaultdata = '';
    $data->defaultdataformat = FORMAT_HTML;
    $data->restricted = 0;

    $formfield->define_save($data);

    $dbfamily = $DB->get_dbfamily();
    if ($dbfamily == 'mssql') {
        $DB->execute('CREATE FULLTEXT INDEX ON {coursemetadata_arup} (keywords) KEY INDEX {courarup_id_pk} ON moodlecoursesearch');
    }

}