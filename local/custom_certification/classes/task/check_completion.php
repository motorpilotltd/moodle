<?php
namespace local_custom_certification\task;

use local_custom_certification\certification;
use local_custom_certification\completion;

class check_completion extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('taskcheckcompletion', 'local_custom_certification');
    }

    /**
     * Check completion for assigned users
     */
    public function execute()
    {
        global $DB;
        /**
         * Get all completed courses to avoid checking that for each user assignment
         */
        $query = "
            SELECT
                cc.id,
                cc.userid,
                cc.course
            FROM {course_completions} cc
            WHERE cc.timecompleted > 0
        ";

        $allcompletedcourses = $DB->get_records_sql($query);
        $completedcourses = [];
        foreach($allcompletedcourses as $completedcourse){
            if(!isset($completedcourses[$completedcourse->userid])){
                $completedcourses[$completedcourse->userid] = [];
            }
            $completedcourses[$completedcourse->userid][$completedcourse->course] = $completedcourse->course;
        }

        /**
         * Get all not completed assignments
         */
        $query = "
            SELECT 
              cua.id,
              cua.userid,
              cua.certifid,
              cc.id as ccid, 
              cc.certifpath
            FROM {certif_user_assignments} cua
            LEFT JOIN {certif_completions} cc ON cc.userid = cua.userid AND cc.certifid = cua.certifid
            JOIN {certif} c ON c.id = cua.certifid
            WHERE ((cc.status != :statuscompleted AND cc.cronchecked = :cronchecked)
            OR cc.id IS NULL)
            AND c.visible = :visible
            AND c.deleted = :deleted
            ORDER by cua.certifid
        ";

        $params = [];
        $params['statuscompleted'] = completion::COMPLETION_STATUS_COMPLETED;
        $params['visible'] = 1;
        $params['deleted'] = 0;
        $params['cronchecked'] = 0;

        $assignments = $DB->get_records_sql($query, $params, 0, 100);
        $currentcertification = 0;
        if(count($assignments) == 0){
            $DB->execute("UPDATE {certif_completions} set cronchecked = 0");
        }
        foreach($assignments as $assignment){
            if($currentcertification != $assignment->certifid){
                $certification = new certification($assignment->certifid);
                $currentcertification = $assignment->certifid;
            }
            if($assignment->certifpath == null){
                $assignment->certifpath = certification::CERTIFICATIONPATH_BASIC;
            }
            mtrace("Check completion for user: ".$assignment->userid.", certification: ".$certification->id);
            completion::check_completion($certification, $assignment->userid, $assignment->certifpath, isset($completedcourses[$assignment->userid])?$completedcourses[$assignment->userid]:[]);
        }
    }
}


