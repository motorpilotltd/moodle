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

    require_once("$CFG->dirroot/local/coursemetadata/lib.php");
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

    $compare = $DB->sql_compare_text('shortname ');
    $field = $DB->get_record_sql("SELECT * FROM {coursemetadata_info_field} WHERE $compare = :methodology",
            ['methodology' => 'Methodology']);

    $compare = $DB->sql_compare_text('data.data');

    $sql = "UPDATE cma SET methodology = CASE 
WHEN $compare = 'Classroom' THEN :classroom
WHEN $compare = 'eBook' THEN :otherebook
WHEN $compare = 'eLearning' THEN :elearning
WHEN $compare = 'Learning Burst' THEN :learningburst
WHEN $compare = 'Masters Programme' THEN :programme
WHEN $compare = 'Other' THEN :otherother
WHEN $compare = 'Video Learning' THEN :othervideo
WHEN $compare = 'Virtual Classroom' THEN :virtualclassroom
ELSE :other
END
FROM {coursemetadata_arup} cma
INNER JOIN {coursemetadata_info_data} data
    ON data.course = cma.course AND data.fieldid = :fieldid";
    $params = [
            'fieldid'          => $field->id,
            'classroom'        => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_CLASSROOM,
            'otherebook'       => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_OTHER,
            'elearning'        => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_ELEARNING,
            'learningburst'    => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_LEARNINGBURST,
            'programme'        => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_PROGRAMMES,
            'otherother'       => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_OTHER,
            'othervideo'       => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_OTHER,
            'virtualclassroom' => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_CLASSROOM,
            'other'            => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_OTHER,

    ];

    $DB->execute($sql, $params);

    coursemetadata_delete_field($field->id);
}