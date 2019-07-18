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

        $DB->delete_records('tapsenrol_class_enrolments');
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
inner join {local_taps_class} tc on tce.classid = tc.id"; // MSSQL
        } else {
            $sql = "update  tce
set tce.classid = tc.id
    from {tapsenrol_class_enrolments} tce
    inner join {local_taps_class} tc on tce.classid = tc.id"; // MySQL
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

        // All records in tapsenrol_completion should be unique by userid/tapsenrolid.
        $sql = "select concat(userid, '_', tapsenrolid ), max(completed) as completed, max(timemodified) as timemodified, userid, tapsenrolid 
                from {tapsenrol_completion} 
                group by userid, tapsenrolid
                having count(id) > 1";
        $records = $DB->get_records_sql($sql);
        foreach ($records as $record) {
            $DB->execute(
                    "DELETE FROM {tapsenrol_completion} where userid = :userid and tapsenrolid = :tapsenrolid",
                    ['userid' => $record->userid, 'tapsenrolid' => $record->tapsenrolid]
            );
            $DB->insert_record('tapsenrol_completion',
                    (object)['userid' => $record->userid, 'tapsenrolid' => $record->tapsenrolid, 'completed' => $record->completed, 'timemodified' => $record->timemodified]
            );
        }

        $sql = "select cmc.id, cmc.userid, cm.id as cmid, cm.course
                from {course_modules_completion} cmc
                inner join {course_modules} cm on cm.id = cmc.coursemoduleid
                inner join {modules} m ON m.name = 'tapsenrol' and cm.module = m.id
                inner join {tapsenrol} te on te.id = cm.instance
                left join {tapsenrol_completion} tc on te.id = tc.tapsenrolid and tc.completed = 1
                where cmc.completionstate  = 1 and tc.id is null
                group by cmc.id, cmc.userid, cm.id, cm.course";

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