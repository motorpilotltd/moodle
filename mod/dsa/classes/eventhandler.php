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

    /**
     * Triggered via course_completed event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $CFG, $DB;

        $course = get_course($event->courseid);
        if (!$course) {
            return;
        }

        $cms = get_fast_modinfo($course->id, -1)->get_instances_of('dsa');
        if (empty($cms)) {
            // Not applicable to this course.
            return;
        }

        require_once($CFG->libdir . '/completionlib.php');

        $completion = new \completion_info($course);

        // Should only be one, but returned as an array so looping.
        foreach ($cms as $cm) {

            if ($completion->is_enabled($cm) != COMPLETION_TRACKING_AUTOMATIC) {
                return;
            }

            $cmcompletion = $completion->get_data($cm, false, $event->relateduserid);

            if ($cmcompletion->completionstate > COMPLETION_INCOMPLETE) {
                $completiontime = $DB->get_field_select(
                    'dsa_assessment',
                    'MAX(completed)',
                    "userid = :userid AND state = :state AND completed > 0",
                    ['userid' => $event->relateduserid, 'state' => 'closed']);
                if ($completiontime) {
                    // Update Moodle course completion date.
                    // Record should exist as we're observing course completion.
                    $ccompletion = new \completion_completion(['course' => $course->id, 'userid' => $event->relateduserid]);
                    $ccompletion->timecompleted = $completiontime;
                    $ccompletion->update();
                }
            }
        }
    }
}