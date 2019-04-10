<?php
namespace local_custom_certification\task;

defined('MOODLE_INTERNAL') || die();

use local_custom_certification\completion;

class window_open extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('taskwindowopen', 'local_custom_certification');
    }

    /**
     * Check if window should be open and opens it
     */
    public function execute()
    {
        global $DB;

        $query = "
            SELECT
                cc.*
            FROM {certif_completions} cc
            JOIN {certif_user_assignments} cua ON cua.userid = cc.userid AND cua.certifid = cc.certifid
            JOIN {certif} c ON c.id = cc.certifid
            WHERE cc.timewindowsopens <= :now
            AND cc.timewindowsopens > 0
            AND c.deleted = :deleted
            AND c.visible = :visible
        ";
        $params = [];
        $params['now'] = time();
        $params['deleted'] = 0;
        $params['visible'] = 1;
        $completionrecords = $DB->get_records_sql($query, $params, 0, 50); // Limit number.

        foreach($completionrecords as $completionrecord){
            completion::open_window($completionrecord);
        }

        // Just purge the whole thing as potentially lots of users and lots of courses.
        \cache::make('core', 'completion')->purge();
        \cache::make('core', 'coursecompletion')->purge();
    }
}
