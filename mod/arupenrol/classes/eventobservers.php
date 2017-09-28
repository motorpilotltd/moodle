<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Observer class containing methods monitoring various events.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupenrol;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via user_enrolment_* events.
     *
     * @param stdClass $event
     * @return void
     */
    public static function user_enrolment(\core\event\base $event) {
        global $DB;

        $allowedevents = array(
            '\core\event\user_enrolment_created',
            '\core\event\user_enrolment_deleted',
            '\core\event\user_enrolment_updated',
        );

        if (!in_array($event->eventname, $allowedevents)) {
            return;
        }
        
        $course = $DB->get_record('course', array('id' => $event->courseid));
        $completion = new \completion_info($course);
        $cms = get_coursemodules_in_course('arupenrol', $event->courseid);

        foreach ($cms as $cm) {
            $arupenrol = $DB->get_record('arupenrol', array('id' => $cm->instance));
            if (!$arupenrol) {
                continue;
            }
            switch ($event->eventname) {
                case '\core\event\user_enrolment_created':
                case '\core\event\user_enrolment_updated':
                    if ($arupenrol->action == 1) {
                        $completion->update_state($cm, COMPLETION_COMPLETE, $event->relateduserid);
                    }
                    break;
                case '\core\event\user_enrolment_deleted':
                    $ue = (object)$event->other['userenrolment'];
                    if ($arupenrol->action == 1 && $ue->lastenrol) {
                        $DB->delete_records('arupenrol_completion', array('arupenrolid' => $arupenrol->id, 'userid' => $ue->userid));
                        $completion->update_state($cm, COMPLETION_INCOMPLETE, $ue->userid);
                    } else if (in_array($arupenrol->action, array(2, 3)) && $arupenrol->enroluser) {
                        $enrolinstances = enrol_get_instances($course->id, true);
                        $selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
                        $selfenrolinstance = array_shift($selfenrolinstances);
                        if ($selfenrolinstance && $ue->enrolid == $selfenrolinstance->id) {
                            $DB->delete_records('arupenrol_completion', array('arupenrolid' => $arupenrol->id, 'userid' => $ue->userid));
                            $completion->update_state($cm, COMPLETION_INCOMPLETE, $ue->userid);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Triggered via course_module_* events.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_module(\core\event\base $event) {
        global $DB;

        $allowedevents = array(
            '\core\event\course_module_created',
            '\core\event\course_module_updated',
        );

        if (!in_array($event->eventname, $allowedevents)) {
            return;
        }

        $course = $DB->get_record('course', array('id' => $event->courseid));
        if (!$course) {
            return;
        }
        $cm = get_coursemodule_from_id('arupenrol', $event->objectid, $course->id);
        if (!$cm) {
            return;
        }
        $arupenrol = $DB->get_record('arupenrol', array('id' => $cm->instance));
        if (!$arupenrol) {
            return;
        }
        $completion = new \completion_info($course);
        if ($arupenrol->action == 1) {
            $enrolledusers = get_enrolled_users(\context_course::instance($course->id), '', 0, 'u.id');
            foreach ($enrolledusers as $enrolleduser) {
                $completion->update_state($cm, COMPLETION_COMPLETE, $enrolleduser->id);
            }
        } else if ($arupenrol->enroluser) {
            $enrolinstances = enrol_get_instances($course->id, true);
            $selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
            $selfenrolinstance = array_shift($selfenrolinstances);
            $enrolself = enrol_get_plugin('self');
            if ($selfenrolinstance && $enrolself) {
                $enrolledusers = $DB->get_records('user_enrolments', array('enrolid' => $selfenrolinstance->id));
                foreach ($enrolledusers as $enrolleduser) {
                    $complete = $DB->get_record('arupenrol_completion', array('userid' => $enrolleduser->userid, 'arupenrolid' => $arupenrol->id));
                    if (!$complete) {
                        $complete = new \stdClass();
                        $complete->arupenrolid = $arupenrol->id;
                        $complete->userid = $enrolleduser->userid;
                        $complete->completed = 1;
                        $DB->insert_record('arupenrol_completion', $complete);
                    } else if ($complete->completed != 1) {
                        $complete->completed = 1;
                        $DB->update_record('arupenrol_completion', $complete);
                    }
                    $completion->update_state($cm, COMPLETION_COMPLETE, $enrolleduser->userid);
                }
            }
        }
    }
}
