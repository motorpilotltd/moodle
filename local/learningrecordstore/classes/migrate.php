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
                                           endtime,
       locked)
       SELECT (CASE WHEN mc.id IS NOT NULL
      THEN 'moodle'
      ELSE mlte.provider
 END),
       healthandsafetycategory,
       location,
       coalesce (providerid, courseid),
       staffid,
       coalesce(mlte.duration, mca.duration),
       coalesce(mlte.durationunits, mca.durationunits),
       classcompletiontime as completiontime,
       coalesce (mlte.learningdesc, mca.description)        as description,
       certificateno,
       coalesce(coursename, mc.fullname, classname, learningdesc),
       coalesce (classcategory, mcc.name),
       classcost,
       classcostcurrency,
       mlte.timemodified,
       expirydate,
       classtype,
       classstarttime,
       classendtime,
       locked
FROM {local_taps_enrolment} mlte
left join {course} mc on mc.id = mlte.courseid
left join {course_categories} mcc on mcc.id = mc.category
left join {coursemetadata_arup} mca on mca.course = mlte.courseid
WHERE cpdid is not null OR mlte.bookingstatus = 'Full Attendance'
");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'D' where durationunits = 'Day(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'H' where durationunits = 'Hour(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'HPM' where durationunits = 'Hour(s) Per Month'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'HPW' where durationunits = 'Hour(s) Per Week'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'M' where durationunits = 'Month(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'MIN' where durationunits = 'Minute(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'Q' where durationunits = 'Quarter Hour(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'W' where durationunits = 'Week(s)'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'Y' where durationunits = 'Year(s)'");

        $DB->execute("update {local_learningrecordstore} set durationunits = 'D' where durationunits = 'days'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'H' where durationunits = 'hours'");
        $DB->execute("update {local_learningrecordstore} set durationunits = 'MIN' where durationunits = 'minutes'");

        $DB->execute("UPDATE {local_learningrecordstore} SET staffid = REPLACE(LTRIM(REPLACE(staffid, '0', ' ')), ' ', '0')");
    }
}