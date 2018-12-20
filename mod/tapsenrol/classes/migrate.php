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
    }
}