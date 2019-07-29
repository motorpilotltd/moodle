<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 21/02/2019
 * Time: 15:56
 */

/*
 Classroom
eBook
eLearning
Learning Burst
Masters Programme
Other
Video Learning
Virtual Classroom
 */
function xmldb_coursemetadatafield_arup_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015111611) {
        require_once("$CFG->dirroot/local/coursemetadata/lib.php");

        // Define table coursemetadatafield_arup_user_update_log to be created.
        $table = new xmldb_table('coursemetadata_arup');

        $field = new xmldb_field('methodology', XMLDB_TYPE_INTEGER, '10');

        // Conditionally launch create table for coursemetadatafield_arup_user_update_log
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $compare = $DB->sql_compare_text('shortname ');
        $field = $DB->get_record_sql("SELECT * FROM {coursemetadata_info_field} WHERE $compare = :methodology",
                ['methodology' => 'Methodology']);

        if (isset($field->id)) {
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

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111611, 'coursemetadatafield', 'arup');
    }

    return true;
}
