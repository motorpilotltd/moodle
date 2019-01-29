<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 12/07/2018
 * Time: 10:59
 */

namespace mod_tapsenrol;

class migrate {
    public static function migrate_tapsenrol_class_enrolments() {
        global $DB;

        $DB->execute("
        INSERT INTO {tapsenrol_class_enrolments}
    (
     userid, classid, bookingstatus, completiontime, active, archived, timemodified
        )
SELECT u.id as userid,
       classid,
       bookingstatus,
       classcompletiontime,
       active,
       coalesce(0, archived),
       lte.timemodified
FROM {local_taps_enrolment} lte
inner join {user} u on lte.staffid = u.idnumber
WHERE enrolmentid is not null
"
            );

        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily == 'mssql') {
            $sql = "update  tce
set tce.classid = tc.id
from {tapsenrol_class_enrolments} tce
inner join {local_taps_class} tc on tce.classid = tc.classid"; // MSSQL
        } else {
            $sql = "update  tce
set tce.classid = tc.id
    from {tapsenrol_class_enrolments} tce
    inner join {local_taps_class} tc on tce.classid = tc.classid"; // MySQL
        }

        $DB->execute($sql);

        $dbman = $DB->get_manager();

        // Define field groupmembersonly to be dropped from course_modules.
        $table = new \xmldb_table('local_taps_class');
        $field = new \xmldb_field('classid');
        $index = new \xmldb_index('classid', XMLDB_INDEX_NOTUNIQUE, array('classid'));

        // Conditionally launch drop field groupmembersonly.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_index($table, $index);
            $dbman->drop_field($table, $field);
        }
    }
}