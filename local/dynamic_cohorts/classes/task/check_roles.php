<?php
namespace local_dynamic_cohorts\task;


class check_roles extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('checkroles', 'local_dynamic_cohorts');
    }

    /**
     * Add or remove roles for cohort members
     */
    public function execute()
    {
        global $DB;

        /**
         * We want to run only 100 action per cron crun
         */
        $actionscounter = 0;
        $maxactions = 100;
        
        /**
         * Add mising roles
         * we need to create uniq value from userid and roleid - moodle requirement
         */
        $query = "
            SELECT
              ".$DB->sql_concat('cm.userid', "'#'", 'cr.roleid')." as uniqid, 
              cm.userid,
              cr.roleid
            FROM {cohort_members} cm
            JOIN {wa_cohort_roles}  cr ON cr.cohortid = cm.cohortid
            LEFT JOIN {role_assignments} ra ON ra.roleid = cr.roleid AND ra.userid = cm.userid AND ra.component = :component
            WHERE ra.id IS NULL
        ";

        $params = [];
        $params['component'] = 'local_dynamic_cohorts';

        $users = $DB->get_records_sql($query, $params);

        $context = \context_system::instance();
        foreach($users as $user){
            if($actionscounter >= $maxactions){
                break;
            }
            mtrace('Add role ID: '.$user->roleid.' for user ID:'.$user->userid);
            role_assign($user->roleid, $user->userid, $context, 'local_dynamic_cohorts');   
            $actionscounter++;
        }

        /**
         * Remove roles assignment that are no longer valid
         */
        $query = "
            SELECT 
              ra.id,
              ra.roleid,
              ra.userid,
              ra.contextid
            FROM {role_assignments} ra
            LEFT JOIN (
                SELECT
                  DISTINCT 
                  cm.userid,
                  cr.roleid
                FROM {cohort_members} cm
                JOIN {wa_cohort_roles}  cr ON cr.cohortid = cm.cohortid
            ) cm ON cm.userid = ra.userid AND cm.roleid = ra.roleid
            WHERE ra.component = :component
            AND cm.userid IS NULL
        ";

        $params = [];
        $params['component'] = 'local_dynamic_cohorts';

        $users = $DB->get_records_sql($query, $params);

        foreach($users as $user){
            if($actionscounter >= $maxactions){
                break;
            }
            mtrace('REMOVE role ID: '.$user->roleid.' for user ID:'.$user->userid);
            role_unassign($user->roleid, $user->userid, $user->contextid, 'local_dynamic_cohorts');
            $actionscounter++;
        }
    }
}
