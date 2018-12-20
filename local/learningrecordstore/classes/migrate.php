<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 12/07/2018
 * Time: 10:59
 */

namespace local_learningrecordstore;

class migrate {
    public static function migrate_lrs_data() {
        global $DB;
        $DB->execute("
insert into {local_learningrecordstore} (provider,
                                           healthandsafetycategory,
                                           location,
                                           providerid,
                                           staffid,
                                           duration,
                                           durationunits,
                                           completiontime,
                                           description,
                                           certificateno,
                                           providername,
                                           classcategory,
                                           classcost,
                                           classcostcurrency,
                                           timemodified,
                                           expirydate,
                                           classtype,
                                           starttime,
                                           endtime)
SELECT provider,
       healthandsafetycategory,
       location,
       coalesce(providerid, courseid),
       staffid,
       duration,
       durationunits,
       classcompletiontime as completiontime,
       learningdesc        as description,
       certificateno,
       coursename,
       classcategory,
       classcost,
       classcostcurrency,
       timemodified,
       expirydate,
       classtype,
       classstarttime,
       classendtime
FROM {local_taps_enrolment}
WHERE cpdid is not null
");
    }
}