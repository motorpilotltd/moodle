<?php

namespace mod_dsa;

use core\message\message;
use mod_dsa\task\resynccourse;

class eventhandler {
    public static function course_module_created(\core\event\course_module_created $event) {
        $task = new resynccourse();
        $task->set_custom_data(['courseid' => $event->courseid]);
        \core\task\manager::queue_adhoc_task($task);
    }

    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        $api = apiclient::getapiclient();
        $api->sync_course($event->courseid, $event->relateduserid);
    }
}