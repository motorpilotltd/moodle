<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 02/05/2018
 * Time: 15:05
 */

namespace mod_tapscompletion;

class migrate {

    public static function deprecate_tapscompletion() {
        global $DB, $CFG;

        require_once("$CFG->dirroot/course/lib.php");

        $completionmodule = $DB->get_record('modules', ['name' => 'tapscompletion']);
        $enrolmodule = $DB->get_record('modules', ['name' => 'tapsenrol']);

        // SQL Server only.
        $DB->execute("update te set te.autocompletion = tc.autocompletion, te.completionattended = tc.completionattended, te.completiontimetype = tc.completiontimetype
                        from {tapsenrol} te
                        inner join {course_modules} cm_enrol on cm_enrol.instance = te.id and cm_enrol.module = :enrolmoduleid
                        inner join {course_modules} cm_completion on cm_enrol.course = cm_completion.course and cm_completion.module = :completionmoduleid
                        inner join {tapscompletion} tc on tc.id = cm_completion.instance",
                ['completionmoduleid' => $completionmodule->id, 'enrolmoduleid' => $enrolmodule->id]
        );

        // SQL Server only.
        $DB->execute("update l set l.objectid = te.id, l.objecttable = 'tapsenrol', component = 'mod_tapsenrol', contextid = ctx.id, contextinstanceid = cm.id
                        from {logstore_standard_log} l
                        inner join {tapsenrol} te on te.course = l.courseid
                        inner join {course_modules} cm on cm.instance = te.id AND cm.module = :enrolmoduleid
                        inner join {context} ctx on ctx.instanceid = cm.id AND ctx.contextlevel = 70
                        where component = 'mod_completion'",
                ['enrolmoduleid' => $enrolmodule->id]
        );

        $events = [
                '\mod_tapscompletion\event\course_module_instance_list_viewed' => '\mod_tapsenrol\event\course_module_instance_list_viewed',
                '\mod_tapscompletion\event\course_module_viewed'               => '\mod_tapsenrol\event\course_module_viewed',
                '\mod_tapscompletion\event\statuses_updated'                   => '\mod_tapsenrol\event\statuses_updated',
                '\local_coursemanager\event\class_created'                   => '\mod_tapsenrol\event\class_created',
                '\local_coursemanager\event\class_updated'                   => '\mod_tapsenrol\event\class_updated'
        ];

        foreach ($events as $from => $to) {
            $DB->execute("update {logstore_standard_log} set eventname = :to where eventname = :from",
                    ['from' => $from, 'to' => $to]
            );
        }

        $completionmodules =
                $DB->get_records_sql('select cm.id as cmid, tc.* from {tapscompletion} tc inner join {course_modules} cm ON tc.id = cm.instance AND cm.module = :completionmoduleid',
                        ['completionmoduleid' => $completionmodule->id]);

        foreach ($completionmodules as $completionmodule) {
            $transaction = $DB->start_delegated_transaction();
            try {
                course_delete_module($completionmodule->cmid);
            } catch (\Exception $ex) {
                $transaction->rollback($ex);
            }
            $transaction->allow_commit();
        }
    }
}