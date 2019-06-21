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

    public static function migrate_orphannedcompletionstates() {
        global $DB, $CFG;

        require_once("$CFG->dirroot/lib/completionlib.php");

        $sql = "select tc.id 
                from {tapsenrol_completion} tc
                inner join {tapsenrol} te on te.id = tc.tapsenrolid
                inner join {local_taps_class} ltc on ltc.courseid = te.course
                inner join {tapsenrol_class_enrolments} ltce on ltce.classid = ltc.id and ltce.userid = tc.userid
                where tc.completed = 1 and ltce.bookingstatus not in ('Assessed', 'Full Attendance', 'Partial Attendance') and ltce.archived = 0";
        $rows = $DB->get_records_sql($sql);
        list($insql, $params) = $DB->get_in_or_equal(array_keys($rows));
        $DB->execute("UPDATE {tapsenrol_completion} SET completed = 0 where id $insql", $params);

        $sql = "select concat(cmc.id, '_', ltce.id), cmc.userid, cm.id as cmid, cm.course
from {course_modules_completion} cmc
inner join {course_modules} cm on cm.id = cmc.coursemoduleid
inner join {modules} m ON m.name = 'tapsenrol' and cm.module = m.id
inner join {local_taps_class} ltc on ltc.courseid = cm.course
inner join {tapsenrol_class_enrolments} ltce on ltce.userid = cmc.userid and ltce.classid = ltc.id
where m.name = 'tapsenrol' and cmc.completionstate  = 1 and ltce.bookingstatus not in ('Assessed', 'Full Attendance', 'Partial Attendance') and ltce.archived = 0";

        foreach ($DB->get_records_sql($sql) as $row) {
            $completion = new \completion_info(self::getcoourse($row->course));

            $completion->update_state(self::$fast_modinfo[$row->course]->get_cm($row->cmid), COMPLETION_INCOMPLETE, $row->userid);
        }
    }

    private static $courses = [];

    /**
     * @var \course_modinfo[]
     */
    private static $fast_modinfo = [];

    private static function getcoourse($courseid) {
        if (!isset(self::$courses[$courseid])) {
            self::$courses[$courseid] = get_course($courseid);
            self::$fast_modinfo[$courseid] = get_fast_modinfo(self::$courses[$courseid]);
        }
        return self::$courses[$courseid];
    }
}