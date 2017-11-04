<?php

namespace local_custom_certification;


use enrol_certification\certif;

class completion
{
    /**
     * Completion status started (enrollment date)
     */
    const COMPLETION_STATUS_STARTED = 1;

    /**
     * Completion status completed
     */
    const COMPLETION_STATUS_COMPLETED = 2;

    const RAG_STATUS_GREEN = 'green';
    const RAG_STATUS_AMBER = 'amber';
    const RAG_STATUS_RED = 'red';
    const RAG_STATUS_OPTIONAL = 'optional';

    /**
     * Get all completed courses for given user
     *
     * @param $userid User id
     * @return array completed courses
     */
    public static function get_completed_courses($userid){
        global $DB;

        $query = "
            SELECT
                cc.course, cc.timecompleted
            FROM {course_completions} cc
            WHERE cc.userid = :userid
            AND cc.timecompleted > 0
        ";
        $params = [];
        $params['userid'] = $userid;

        $completedcourses = $DB->get_records_sql_menu($query, $params);
        return $completedcourses;
    }

    /**
     * Checkes if this is certifcation or recertification path
     *
     * @param $certification Certification
     * @param $userid User ID
     * @return bool true if its recertification path, false if certification
     */
    public static function is_recertification($certification, $userid){
        global $DB;

        if (empty($certification->recertificationcoursesets)) {
            return false;
        }

        $completionrecord = $DB->get_record('certif_completions', ['userid' => $userid, 'certifid' => $certification->id]);
        if($completionrecord && $completionrecord->certifpath == certification::CERTIFICATIONPATH_RECERTIFICATION){
            return true;
        }

        $completionrecordarchived = $DB->get_records('certif_completions_archive', ['userid' => $userid, 'certifid' => $certification->id]);
        if($completionrecordarchived){
            return true;
        }

        return false;
    }

    /**
     * Checks completion for given certification and user
     *
     * Algorithm:
     * First check if this is certification or recertification, next check if all required courseset are completed,
     * if yes then mark status as completed.
     *
     * @param $certification Certification ID or object
     * @param $userid User ID
     * @param null $path should completion be check for certification or recertification path, if null then check
     * @return int certification / recertification status (completed or started)
     */
    public static function check_completion($certification, $userid, $path = null, $completedcourses = null){
        if(!is_object($certification)){
            $certification = new certification($certification, false);
        }
        /**
         * Get all completed courses for user if not provided
         */
        if($completedcourses === null){
            $completedcourses = self::get_completed_courses($userid);
        }
        $certificationcompleted = self::COMPLETION_STATUS_STARTED;
        $completed = true;

        /**
         * Check if this is certification or recertification path
         */
        if($path == certification::CERTIFICATIONPATH_RECERTIFICATION || ($path===null && self::is_recertification($certification, $userid))){
            $coursesets = $certification->recertificationcoursesets;
            $certifpath = certification::CERTIFICATIONPATH_RECERTIFICATION;
        }else{
            $coursesets = $certification->certificationcoursesets;
            $certifpath = certification::CERTIFICATIONPATH_BASIC;
        }

        /**
         * Check if all required courseset are completed
         */
        $lastoperator = '';
        foreach($coursesets as $courseset){
            if($lastoperator == certification::NEXTOPERATOR_OR){
                $completed = true;
            }
            if(!self::check_courseset_completion($courseset, $completedcourses)){
                $completed = false;
            }

            /**
             * Update completion status for single courseset
             */
            $completiontime = self::get_completion_time($certification, $userid, $certifpath, $courseset->id);
            self::update_courseset_completion_status($courseset->id, $userid, ($completed ? completion::COMPLETION_STATUS_COMPLETED : completion::COMPLETION_STATUS_STARTED), $completiontime);
            /**
             * If nextoperator is OR and previous coursesets are completed then mark entire certification as completed
             */
            if($courseset->nextoperator == certification::NEXTOPERATOR_OR && $completed){
                $certificationcompleted = self::COMPLETION_STATUS_COMPLETED;
            }
            $lastoperator = $courseset->nextoperator;
        }

        if($completed){
            $certificationcompleted = self::COMPLETION_STATUS_COMPLETED;
        }
        /**
         * Update certification completion record info
         */
        self::update_completion_status($certification, $userid, $certifpath, $certificationcompleted);
        return $certificationcompleted;
    }

    /**
     * Check if courseset is completed, basing on courseset configuration
     *
     * @param $courseset Courseset to check
     * @param $completedcourses Completed courses array
     * @return bool
     */
    public static function check_courseset_completion($courseset, $completedcourses){
        $completed = false;
        $completedcoursesnumber = 0;
        /**
         * Count completed courses in this courseset
         */
        foreach($courseset->courses as $course){
            if(array_key_exists($course->courseid, $completedcourses) ){
                $completedcoursesnumber++;
            }
        }
        if($courseset->completiontype == certification::COMPLETION_TYPE_ANY && $completedcoursesnumber > 0){
            /**
             * If courseset completion type is any and there is at least one completed course then mark courseset as completed
             */
            $completed = true;
        }elseif($courseset->completiontype == certification::COMPLETION_TYPE_ALL && $completedcoursesnumber == count($courseset->courses)){
            /**
             * If courseset completion type is all and all courses in this courseset are completed then mark courseset as completed
             */
            $completed = true;
        }elseif($courseset->completiontype == certification::COMPLETION_TYPE_SOME && $completedcoursesnumber >= $courseset->mincourses){
            /**
             * If courseset completion type is some check if number of completed courses are higher or equal than required
             */
            $completed = true;
        }
        return $completed;
    }

    /**
     * Calculate expiration date
     * If use Expiry Date
     *          Calculate the new expiry date based on the previous one.
     *          If there was no previous expiry date (for new assignments, and no due date set in the assignments tab)
     *           or the certification has expired, then the completion date is used.
     *
     * If use Completion Date
     *           When a user completes one of the certification paths (original or recertification), t
     *           he expiry date will be calculated based on the date that the completion occurred.
     *
     * @param $certification Certification ID or object
     * @param $userid User ID
     * @param $timecompletion time completed for certification
     * @return int
     */
    public static function get_expiration_time($certification, $userid, $timecompletion){
        global $DB;
        if(!is_object($certification)){
            $certification = new certification($certification, false);
        }
        /**
         * Get archived completion
         */
        $completionarchive = $DB->get_records('certif_completions_archive', ['certifid' => $certification->id, 'userid' => $userid], 'timeexpires DESC', '*', 0, 1);

        switch ($certification->recertificationdatetype){
            case certification::CERTIFICATION_COMPLETION_DATE:
                $timeexpires = $timecompletion;
                break;
            case certification::CERTIFICATION_EXPIRY_DATE:
            default:
                if(count($completionarchive) == 0){
                    $timeexpires = $timecompletion;
                }else{
                    $completionarchive = array_values($completionarchive)[0];
                    if($completionarchive->timeexpires < time()){
                        $timeexpires = $timecompletion;
                    }else{
                        $timeexpires = $completionarchive->timeexpires;
                    }
                }
                break;
        }
        if($timeexpires > 0){
            $datetime = new \DateTime();
            $datetime->setTimestamp($timeexpires);
            $interval = new \DateInterval('P'.$certification->activeperiodtime.certification::get_time_period_for_interval($certification->activeperiodtimeunit));
            $datetime->add($interval);
            $timeexpires = $datetime->getTimestamp();
        }
        return $timeexpires;
    }

    /**
     * Calculate duedate
     *      if certification not yet completed take duedate from assignment
     *      if completed take expire date
     *
     * @param $certifid Certification ID
     * @param $userid User ID
     */
    public static function calculate_duedate($certifid, $userid)
    {
        global $DB;

        $query = "
            SELECT 
              cua.duedate,
              cc.timecompleted,
              cc.timeexpires,
              cc.duedate as ccduedate,
              cc.id
            FROM {certif_user_assignments} cua
            LEFT JOIN {certif_completions} cc ON cc.certifid = cua.certifid AND cc.userid = cua.userid
            WHERE cua.userid = :userid
            AND cua.certifid = :certifid
        ";
        $params = [];
        $params['userid'] = $userid;
        $params['certifid'] = $certifid;

        $details = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);

        if($details->timecompleted > 0){
            $duedate = $details->timeexpires;
        }else{
            $query = "
                SELECT
                  cca.timeexpires
                FROM {certif_completions_archive} cca
                WHERE cca.certifid = :certifid
                AND cca.userid = :userid
                ORDER by cca.timearchived DESC 
            ";
                $params = [];
                $params['userid'] = $userid;
                $params['certifid'] = $certifid;
            if($archivedcompletion = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE)){
                $duedate = $archivedcompletion->timeexpires;
            }else{
                $duedate = $details->duedate;
            }
        }

        if($duedate != $details->ccduedate){
            $record = new \stdClass();
            $record->id = $details->id;
            $record->duedate = $duedate;
            $DB->update_record('certif_completions', $record);
        }
    }

    /**
     * Get highest course completion time in terms of certification
     *
     * @param $cerification
     * @param $userid
     * @param $certifpath
     * @return int
     */
    public static function get_completion_time($cerification, $userid, $certifpath, $courseset = null){
        global $DB;
        $params = [];
        $coursesetwhere = '';

        if ($courseset) {
            $coursesetwhere = 'AND ccc.id = :coursesetid';
            $params['coursesetid'] = $courseset;
        }

        $query = "
            SELECT 
                MAX(c.timecompleted) as timecompleted
            FROM {certif_courseset_courses} ccc
            JOIN {certif_coursesets} cc ON cc.id = ccc.coursesetid
            JOIN {course_completions} c ON c.course = ccc.courseid AND c.userid = :userid
            WHERE ccc.certifid = :certifid
            {$coursesetwhere}
            AND cc.certifpath = :certifpath
        ";

        $params['certifid'] = $cerification->id;
        $params['certifpath'] = $certifpath;
        $params['userid'] = $userid;

        if($timecompleted = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE)){
            return $timecompleted->timecompleted;
        }
        
        return 0;
    }

    /**
     * Update users record, calculate new timewindowsopen
     *
     * @param $certification Certification ID or object
     */
    public static function update_records_completion_status($certification) {
        global $DB;

        if(!is_object($certification)){
            $certification = new certification($certification, false);
        }

        $params = [];
        $query = "
            SELECT 
              cc.*
            FROM {certif_completions} as cc
            WHERE cc.timewindowsopens > :now
            AND cc.certifid = :certifid";


        $params['now'] = time();
        $params['certifid'] = $certification->id;

        $records = $DB->get_records_sql($query, $params);

        foreach ($records as $record) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($record->timeexpires);
            $interval = new \DateInterval('P'.$certification->windowperiod.certification::get_time_period_for_interval($certification->windowperiodunit));
            $datetime->sub($interval);
            $record->timewindowsopens = $datetime->getTimestamp();

            $DB->update_record('certif_completions', $record);
        }

    }

    /**
     * Update completion status.
     * Create if not exists, calculate time expires, window open time, actual duedate
     * \
     * @param $certification Certification ID or object
     * @param $userid User ID
     * @param $certifpath Certification path
     * @param $status Status
     */
    public static function update_completion_status($certification, $userid, $certifpath, $status){
        global $DB;
        $firecompletionevent = false;
        if(!is_object($certification)){
            $certification = new certification($certification, false);
        }
        /**
         * Check if record exists or need to be created
         */
        $completionrecord = $DB->get_record('certif_completions', ['certifid' => $certification->id, 'userid' => $userid, 'certifpath' => $certifpath]);

        // Pre-load ready to override if compelted in TAPS.
        $progress = self::get_user_progress($certification, $userid);

        // Are we linked to a old TAPS course? And this user already been linked to an enrolment?
        list($tapsenrolmentid, $timecompleted) = self::check_taps_completion($certification, $userid, $completionrecord);
        // Override status if applicable (TAPS completion found).
        if (!is_null($timecompleted)) {
            // Override status and progress.
            $status = self::COMPLETION_STATUS_COMPLETED;
            $progress['certification'] = 100;
            $progress['recertification'] = 100;
        }

        if(!$completionrecord){
            $completionrecord = new \stdClass();
            $completionrecord->userid = $userid;
            $completionrecord->certifid = $certification->id;
            $completionrecord->certifpath = $certifpath;
            $completionrecord->status = $status;
            $completionrecord->timeassigned = time();
            $completionrecord->cronchecked = 1;
            $completionrecord->tapsenrolmentid = $tapsenrolmentid;

            if($status == self::COMPLETION_STATUS_COMPLETED){
                $firecompletionevent = true;
                $completionrecord->timecompleted =
                        (is_null($timecompleted) ? self::get_completion_time($certification, $userid, $certifpath) : $timecompleted);
                /**
                 * If certification has recertification path then calculate time expire and window open time
                 */
                if($certification->has_recertification()) {
                    $completionrecord->timeexpires = self::get_expiration_time($certification, $userid, $completionrecord->timecompleted);
                    $datetime = new \DateTime();
                    $datetime->setTimestamp($completionrecord->timeexpires);
                    $interval = new \DateInterval('P'.$certification->windowperiod.certification::get_time_period_for_interval($certification->windowperiodunit));
                    $datetime->sub($interval);
                    $completionrecord->timewindowsopens = $datetime->getTimestamp();
                }else{
                    $completionrecord->timeexpires = 0;
                    $completionrecord->timewindowsopens = 0;
                }
            }else{
                $completionrecord->timecompleted = null;
                $completionrecord->timeexpires = null;
                $completionrecord->timewindowsopens = null;
            }

            $completionrecord->progress = $certifpath == certification::CERTIFICATIONPATH_BASIC ? $progress['certification'] : $progress['recertification'];

            $completionrecord->id = $DB->insert_record('certif_completions', $completionrecord, true);
        } else {
            if($status == self::COMPLETION_STATUS_COMPLETED && $completionrecord->status != self::COMPLETION_STATUS_COMPLETED){
                $completionrecord->tapsenrolmentid =
                        (is_null($completionrecord->tapsenrolmentid) ? $tapsenrolmentid : $completionrecord->tapsenrolmentid);
                $completionrecord->timecompleted =
                        (is_null($timecompleted) ? self::get_completion_time($certification, $userid, $certifpath) : $timecompleted);
                /**
                 * If certification has recertification path then calculate time expire and window open time
                 */
                if($certification->has_recertification()){
                    $completionrecord->timeexpires = self::get_expiration_time($certification, $userid, $completionrecord->timecompleted);
                    $datetime = new \DateTime();
                    $datetime->setTimestamp($completionrecord->timeexpires);
                    $interval = new \DateInterval('P'.$certification->windowperiod.certification::get_time_period_for_interval($certification->windowperiodunit));
                    $datetime->sub($interval);
                    $completionrecord->timewindowsopens = $datetime->getTimestamp();
                }else{
                    $completionrecord->timeexpires = 0;
                    $completionrecord->timewindowsopens = 0;
                }

                $firecompletionevent = true;
            }
            $completionrecord->cronchecked = 1;
            $completionrecord->progress = $certifpath == certification::CERTIFICATIONPATH_BASIC ? $progress['certification'] : $progress['recertification'];
            $completionrecord->status = $status;
            $DB->update_record('certif_completions', $completionrecord);
        }
        /**
         * Actualize due date
         */
        self::calculate_duedate($completionrecord->certifid, $completionrecord->userid);
        
        /**
         * Trigger certification_completed event if status is changed to completed
         */
        if($firecompletionevent){
            $context = \context_system::instance();
            $params = [
                'context' => $context,
                'objectid' => $completionrecord->id,
                'other' => [
                    'userid' => $userid,
                    'certifid' => $certification->id
                ]
            ];
            $event = event\certification_completed::create($params);
            $event->trigger();
        }
    }

    public static function check_taps_completion($certification, $userid, $completionrecord) {
        global $DB;

        // 0 => enrolmentid, 1 => timecompleted
        $return = [0 => null, 1 => null];

        if (empty($certification->linkedtapscourseid) || !empty($completionrecord->tapsenrolmentid)) {
            // Cerificate not linked to TAPS or user already linked to enrolment.
            return $return;
        }
        $archived = $DB->get_records_select(
                'certif_completions_archive',
                'certifid = :certifid AND userid = :userid',
                ['certifid' => $certification->id, 'userid' => $userid]
            );
        if (!empty($archived)) {
            // Archived completion found so don't override.
            return $return;
        }

        // Does this user have a completed old TAPS course enrolment?
        $taps = new \local_taps\taps();
        list($insql1, $params1) = $DB->get_in_or_equal(explode(',', $certification->linkedtapscourseid), SQL_PARAMS_NAMED, 'courseid');
        list($insql2, $params2) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
        $sql = <<<EOS
SELECT
    enrolmentid, classcompletiontime
FROM
    {local_taps_enrolment}
WHERE
    staffid = :staffid
    AND courseid {$insql1}
    AND {$DB->sql_compare_text('bookingstatus')} {$insql2}
EOS;
        $params = array_merge($params1, $params2);
        $params['staffid'] = $DB->get_field('user', 'idnumber', ['id' => $userid]);

        $completions = $DB->get_records_sql_menu($sql, $params);

        if (empty($completions)) {
            return $return;
        }

        $return[1] = max($completions);
        $return[0] = array_search($return[1], $completions);
        return $return;
    }

    /**
     * Update courseset completion status or create if not exists
     *
     * @param $coursesetid Courseset ID
     * @param $userid User  ID
     * @param $status Status
     */
    public static function update_courseset_completion_status($coursesetid, $userid, $status, $completiontime = null){
        global $DB;
        if (!$completiontime) {
            $completiontime = time();
        }
        $firecompletionevent = false;
        $completionrecord = $DB->get_record('certif_courseset_completions', ['coursesetid' => $coursesetid, 'userid' => $userid]);
        if(!$completionrecord){
            $completionrecord = new \stdClass();
            $completionrecord->certifid = 0;//todo
            $completionrecord->userid = $userid;
            $completionrecord->coursesetid = $coursesetid;
            $completionrecord->status = $status;
            $completionrecord->timecompleted = $status == self::COMPLETION_STATUS_COMPLETED ? $completiontime : null;
            $completionrecord->id = $DB->insert_record('certif_courseset_completions', $completionrecord, true);
            $firecompletionevent = true;
        }else{
            if($status == self::COMPLETION_STATUS_COMPLETED && $completionrecord->status != self::COMPLETION_STATUS_COMPLETED){
                $completionrecord->timecompleted = $completiontime;
                $firecompletionevent = true;
            }
            $completionrecord->status = $status;
            $DB->update_record('certif_courseset_completions', $completionrecord);
        }
        /**
         * Trigger certification_courseset_completed event if status is changed to completed
         */
        if($firecompletionevent){
            $context = \context_system::instance();
            $params = [
                'context' => $context,
                'objectid' => $completionrecord->id,
                'other' => [
                    'userid' => $userid,
                    'coursesetid' => $coursesetid
                ]
            ];
            $event = event\certification_courseset_completed::create($params);
            $event->trigger();
        }
    }

    /**
     * Removce courseset completion data for given user and certification
     *
     * @param $certifid Certificatio ID
     * @param $userid User ID
     */
    public static function delete_courseset_completion_data($certifid, $userid){
        global $DB;
        $DB->delete_records('certif_courseset_completions', ['certifid' => $certifid, 'userid' => $userid]);
    }

    /**
     * Open window:
     *  - archive certification completion record
     *  - remove certification coursesets completion data
     *  - reset all courses for recertificatio path
     *  - create new certification completion record
     *
     * @param $completionrecord Certification
     */
    public static function open_window($completionrecord){
        global $DB;
        $certification = new certification($completionrecord->certifid, false);
        $insertrecord = clone $completionrecord;
        $insertrecord->id = null;
        $insertrecord->timearchived = time();
        if($DB->insert_record('certif_completions_archive', $insertrecord)){
            $DB->delete_records('certif_completions', ['id' => $completionrecord->id]);

            self::delete_courseset_completion_data($completionrecord->certifid, $completionrecord->userid);
            $coursesets = empty($certification->recertificationcoursesets) ? $certification->certificationcoursesets : $certification->recertificationcoursesets;
            // Track which courses have been reset.
            $resetcourses = [];
            foreach($coursesets as $courseset){
                foreach($courseset->courses as $course){
                    self::reset_course_for_user($course->courseid, $completionrecord->userid);
                    $resetcourses[] = $course->courseid;
                }
            }
            // Update linked enrolment if still needs resetting (i.e. if 'faked' certification completion from historical TAPS data.
            // Only necessary if related (Moodle) course is one of the ones being reset.
            if ($insertrecord->tapsenrolmentid
                    && $enrolment = $DB->get_record('local_taps_enrolment', ['enrolmentid' => $insertrecord->tapsenrolmentid, 'active' => 1])) {
                // Check it relates to a reset Moodle course.
                $mdlcourseid = $DB->get_field('course', 'id', ['idnumber' => $this->cmcourse->courseid]);
                if (in_array($mdlcourseid, $resetcourses)) {
                    $enrolment->active = 0;
                    $enrolment->timemodifed = time();
                    $DB->update_record('local_taps_enrolment', $enrolment);
                }
            }
            self::check_completion($completionrecord->certifid, $completionrecord->userid);

            /**
             * Trigger certification_window_opened event
             */
            $context = \context_system::instance();
            $params = [
                'context' => $context,
                'objectid' => $completionrecord->id,
                'other' => [
                    'userid' => $completionrecord->userid,
                    'certifid' => $completionrecord->certifid
                ]
            ];
            $event = event\certification_window_opened::create($params);
            $event->trigger();

            // Return all linked courses that have been reset.
            return $resetcourses;
        }
    }

    /**
     * Reset single course for single user
     * @param $courseid
     * @param $userid
     */
    public static function reset_course_for_user($courseid, $userid){
        global $DB, $CFG;

        /**
         * Archive course completion data
         */
        $coursecompletion = $DB->get_record('course_completions', ['course' => $courseid, 'userid' => $userid]);
        if($coursecompletion){
            $coursecompletion->id = null;
            $coursecompletion->timearchived = time();
            $DB->insert_record('certif_course_compl_archive', $coursecompletion);
        }
        $DB->delete_records('course_completions', ['course' => $courseid, 'userid' => $userid]);
        $DB->delete_records('course_completion_crit_compl', ['course' => $courseid, 'userid' => $userid]);
        $coursemodinfo = \get_fast_modinfo($courseid);
        $course = $DB->get_record('course', ['id' => $courseid]);
        $completion = new \completion_info($course);
        $activityreset = new activityreset();
        foreach($coursemodinfo->get_cms() as $cminfo){

            $modfile = $CFG->dirroot . '/mod/' . $cminfo->modname . '/lib.php';
            include_once($modfile);
            /**
             * Reset each activity
             */
            $method = $cminfo->modname.'_archive_completion';
            if(method_exists($activityreset, $method)){
                $activityreset->$method($userid, $courseid);
            }else{
                /**
                 * Reset manually.
                 */
                $grade = new \stdClass();
                $grade->userid   = $userid;
                $grade->rawgrade = null;
                // Reset grades.
                $updateitemfunc = $cminfo->modname . '_grade_item_update';
                if (function_exists($updateitemfunc)) {
                    $sql = "SELECT a.*,
                                    cm.idnumber as cmidnumber,
                                    m.name as modname
                                FROM {" . $cminfo->modname . "} a
                                JOIN {course_modules} cm ON cm.instance = a.id AND cm.course = :courseid
                                JOIN {modules} m ON m.id = cm.module AND m.name = :modname";
                    $params = ['modname' => $cminfo->modname, 'courseid' => $courseid];

                    if ($modinstances = $DB->get_records_sql($sql, $params)) {
                        foreach ($modinstances as $modinstance) {
                            $updateitemfunc($modinstance, $grade);
                        }
                    }
                }
            }
            /**
             * Remove activity completion
             */
            $completion->update_state($cminfo, COMPLETION_NOT_VIEWED, $userid);
            $DB->delete_records('course_modules_completion', ['userid' => $userid, 'coursemoduleid' => $cminfo->id]);
        }

        /**
         * Trigger course reset event
         */
        $params = [
            'context' => \context_course::instance($courseid),
            'objectid' => null,
            'courseid' => $courseid,
            'relateduserid' => $userid
        ];
        $event = event\certification_course_reset::create($params);
        $event->trigger();
    }

    /**
     * Get current completoin data
     *
     * @param $certifid Certification ID
     * @param $userid User ID
     * @return mixed
     */
    public static function get_completion_info($certifid, $userid){
        global $DB;

        $query = "
            SELECT 
                cc.*
            FROM {certif_completions} cc
            WHERE cc.certifid = :certifid
            AND cc.userid = :userid
        ";

        $params = [];
        $params['certifid'] = $certifid;
        $params['userid'] = $userid;

        $completion = $DB->get_record_sql($query, $params);
        return $completion;
    }

    /**
     * Get users certification details: timecompleted, timeexpire, duedate, last completion date, check if window open, check if certification is overdue
     *
     * @param $certifid Certification ID
     * @param $userid User ID
     * @return mixed
     */
    public static function get_user_certification_details($certifid, $userid){
        global $DB;
        $query = "SELECT
              comp.timecompleted,
              comp.timeexpires,
              comp.duedate,
              cua.duedate as assignmentduedate,
              cca.timeexpires as lasttimeexpires,
              cca.timewindowsopens as lasttimewindowsopens
            FROM {certif_user_assignments} cua 
            LEFT JOIN {certif_completions} comp ON comp.certifid = cua.certifid AND comp.userid = cua.userid
            LEFT JOIN (
              SELECT
                cca.userid,
                cca.certifid,
                MAX(cca.timewindowsopens) as timewindowsopens,
                MAX(cca.timecompleted) as timecompleted,
                MAX(cca.timeexpires) as timeexpires
              FROM {certif_completions_archive} cca
              GROUP by cca.userid, cca.certifid
            ) as cca ON cca.userid = cua.userid AND cca.certifid = cua.certifid
            WHERE cua.certifid = :certifid
            AND cua.userid = :userid
        ";

        $params = [];
        $params['userid'] = $userid;
        $params['certifid'] = $certifid;

        $details = $DB->get_record_sql($query, $params);

        $details->windowopen = 0;
        $details->overdue = 0;
        if($details->duedate > 0 && $details->duedate < time()){
            $details->overdue = 1;
        }
        if($details->lasttimewindowsopens > 0 && $details->lasttimewindowsopens < time()){
            $details->windowopened = 1;
        }
        return $details;
    }

    /**
     * Calulate progress for certification
     * Course progress is calculated on completed activities
     *  (number of completed activities divided by number of required activities)
     *  or 100% if course is completed (regardless to activity completion)
     *
     * Courseset progress:
     *  Check how many courses are needed to complete courseset (all, any, some)
     *  Sort courses progress descending, sum up X courses with highest progress and divide by number of required courses
     *
     * Certification progress
     * Divide coursesets by next operator OR to courseset groups, caculate each group progress (sum coursesets progress and divide by courseset number)
     * Take highest courseset group progress
     *
     * @param $certification Certificaiton object or  Certification ID
     * @param $userid User ID
     * @return array
     */
    public static function get_user_progress($certification, $userid){
        global $DB, $CFG;
        require_once($CFG->dirroot.'/completion/criteria/completion_criteria.php');
        require_once($CFG->dirroot.'/lib/completionlib.php');


        if(!is_object($certification)){
            $certification = new certification($certification, false);
        }

        $query = "
            SELECT
              c.id, 
              c.courseid,
              c.coursesetid,
              COUNT(criteria.id) as criterianumber,
              COUNT(compl.id) as completedcriterianumber,
              ccam.method,
              cc.timecompleted
            FROM {certif_courseset_courses} c
            LEFT JOIN {course_completion_criteria} criteria ON criteria.course = c.courseid AND criteria.criteriatype = :criteriatype
            LEFT JOIN {course_completion_crit_compl} compl ON compl.criteriaid = criteria.id AND compl.userid = :userid AND compl.timecompleted > 0
            LEFT JOIN {course_completion_aggr_methd} ccam ON ccam.course = c.courseid AND ccam.criteriatype = criteria.criteriatype
            LEFT JOIN {course_completions} cc ON cc.course = c.courseid AND cc.userid = :ccuserid
            WHERE c.certifid = :certifid
            GROUP by c.courseid, ccam.method, cc.timecompleted, c.coursesetid, c.id
        ";

        $params = [];
        $params['criteriatype'] = COMPLETION_CRITERIA_TYPE_ACTIVITY;
        $params['userid'] = $userid;
        $params['certifid'] = $certification->id;
        $params['ccuserid'] = $userid;

        $progress = $DB->get_records_sql($query, $params);



        $coursesprogress = [];
        $coursesetscoursesprogress = [];

        foreach($progress as $courseprogress){
            $coursesprogress[$courseprogress->courseid] = 0;
            if($courseprogress->method == COMPLETION_AGGREGATION_ANY && $courseprogress->completedcriterianumber > 0){
                /**
                 * Set progress to 100% if any acitivity is completed and completion aggreation method is "ANY"
                 */
                $coursesprogress[$courseprogress->courseid] = 100;
            }elseif($courseprogress->method == COMPLETION_AGGREGATION_ALL){
                /**
                 * Calculate progress: divide completed activities by number of all activities which need to be completed
                 */
                $coursesprogress[$courseprogress->courseid] = round($courseprogress->completedcriterianumber / max(1, $courseprogress->criterianumber) * 100);
            }
            if($courseprogress->timecompleted > 0){
                /**
                 * Set progress to 100% if course is marked as completed
                 */
                $coursesprogress[$courseprogress->courseid] = 100;
            }

            /**
             * Gather all courses progress into coursesets
             */
            if(!isset($coursesetscoursesprogress[$courseprogress->coursesetid])){
                $coursesetscoursesprogress[$courseprogress->coursesetid] = [];
            }
            $coursesetscoursesprogress[$courseprogress->coursesetid][] = $coursesprogress[$courseprogress->courseid];
        }

        $completioninfo = self::get_completion_info($certification->id, $userid);

        $certificationprogress = self::calculate_progress_for_coursesets($certification->certificationcoursesets, $coursesetscoursesprogress);
        $recertificationprogress = self::calculate_progress_for_coursesets($certification->recertificationcoursesets, $coursesetscoursesprogress);

        /**
         * Check completion info
         */
        if(isset($completioninfo->status) && $completioninfo->status == self::COMPLETION_STATUS_COMPLETED){
            if($completioninfo->certifpath == certification::CERTIFICATIONPATH_BASIC){
                $certificationprogress = 100;
            }
            if($completioninfo->certifpath == certification::CERTIFICATIONPATH_RECERTIFICATION){
                $recertificationprogress = 100;
            }
        }

        return ['courses' => $coursesprogress, 'certification' => $certificationprogress, 'recertification' => $recertificationprogress];
    }

    /**
     * Calculate progress for coursesets
     * Check how many courses are needed to complete courseset (all, any, some)
     *  Sort courses progress descending, sum up X courses with highest progress and divide by number of required courses
     *
     * @param array $coursesets Coursesets
     * @param array $coursesetscoursesprogress array with progress for each course in courseset
     * @return int
     */
    public static function calculate_progress_for_coursesets($coursesets, $coursesetscoursesprogress){
        $coursesetsprogress = [];
        $k = 0;
        $lastoperator = '';
        foreach($coursesets as $courseset){
            if($lastoperator == certification::NEXTOPERATOR_OR){
                /**
                 * Divide coursesets into groups if there is OR operator beetwen coursests
                 */
                $k++;
            }
            if(!isset($coursesetsprogress[$k])){
                $coursesetsprogress[$k] = [];
            }
            $coursesetprogress = 0;
            /**
             * Calculate each courseset progress basing on completion type setting
             */
            switch($courseset->completiontype){
                case certification::COMPLETION_TYPE_ALL:
                    $coursesetprogress = round(array_sum($coursesetscoursesprogress[$courseset->id]) / count($coursesetscoursesprogress[$courseset->id]));
                    break;
                case certification::COMPLETION_TYPE_SOME:
                    arsort($coursesetscoursesprogress[$courseset->id]);
                    $coursesetprogress = round(array_sum(array_slice($coursesetscoursesprogress[$courseset->id], 0, $courseset->mincourses)) / $courseset->mincourses);
                    break;
                case certification::COMPLETION_TYPE_ANY:
                    $coursesetprogress = max($coursesetscoursesprogress[$courseset->id]);
                    break;
            }
            $coursesetsprogress[$k][] = $coursesetprogress;

            $lastoperator = $courseset->nextoperator;
        }
        /**
         * Take max progress from grouped coursesets
         */
        $progress = 0;
        foreach($coursesetsprogress as $k => $coursesetprogress){
            $progress = max($progress, round(array_sum($coursesetprogress) / count($coursesetprogress)));
        }
        return $progress;
    }

    /**
     * Get RAG status basing on completion date and duedate
     * 
     * @param $timecompleted Completion date
     * @param $duedate Duedate
     * @return string RAG status
     */
    public static function get_rag_status($timecompleted, $duedate, $optional = null, $report = 0){
        if($optional == 1 && $report == 1){
            $rag = self::RAG_STATUS_OPTIONAL;
        }elseif($timecompleted > 0){
            $rag = self::RAG_STATUS_GREEN;
        }elseif((int)$duedate > time() || (int) $duedate == 0){
            if($optional == 1 && $duedate == 0) {
                $rag = self::RAG_STATUS_OPTIONAL;
            }else{
                $rag = self::RAG_STATUS_AMBER;
            }
        }else{
            if($optional == 1 && $report == 0){
                $rag = self::RAG_STATUS_OPTIONAL;
            }else{
                $rag = self::RAG_STATUS_RED;
            }
        }
        return $rag;
    }
}