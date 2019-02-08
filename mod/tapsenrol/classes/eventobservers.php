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
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapsenrol;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \stdClass $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        $ue = (object) $event->other['userenrolment'];
        if ($ue->lastenrol) {
            $course = $DB->get_record('course', array('id' => $event->courseid));
            $completion = new \completion_info($course);
            $cms = get_fast_modinfo($event->courseid, -1)->get_instances_of('tapsenrol');
            foreach ($cms as $cm) {
                $DB->delete_records('tapsenrol_completion', array('tapsenrolid' => $cm->instance, 'userid' => $ue->userid));
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $ue->userid);
            }
        }
    }

    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        global $DB, $CFG;

        require_once("$CFG->dirroot/mod/tapsenrol/classes/tapsenrol.php");
        
        $eventdata = $event->get_record_snapshot('course_modules_completion', $event->objectid);

        if ($eventdata->completionstate != COMPLETION_COMPLETE
                && $eventdata->completionstate != COMPLETION_COMPLETE_PASS) {
            return true;
        }

        $relevantclasses = \mod_tapsenrol\enrolclass::fetch_all_visible_by_course($event->courseid,
                ['classtype' => enrolclass::TYPE_ELEARNING]);

        if (empty($relevantclasses)) {
            return true;
        }

        $allcomplete = true;

        $modinfo = get_fast_modinfo($event->courseid, $event->relateduserid);
        $ci = new \completion_info(get_course($event->courseid));
        $tapscms = $modinfo->get_instances_of('tapsenrol');

        if (empty($tapscms)) {
            return true;
        } else {
            $tapscm = reset($tapscms);
        }

        $cms = $modinfo->get_cms();
        foreach ($cms as $cm) {
            if ($cm->id == $event->contextinstanceid) {
                continue;
            }
            if ($cm->modname == 'tapsenrol') {
                continue;
            }

            if ($cm->completion !== COMPLETION_TRACKING_NONE) {
                $completiondata = $ci->get_data($cm, true, $event->relateduserid);
                if (empty($completiondata->completionstate)) {
                    $allcomplete = false;
                    break;
                }
            }
        }

        if (!$allcomplete) {
            return true;
        }

        $taps = new taps();
        $enrolments = $taps->get_enrolments($event->relateduserid, $event->courseid);

        foreach ($enrolments as $enrolment) {
            if (!isset($relevantclasses[$enrolment->classid])) {
                continue;
            }

            $tapsenrol = $DB->get_record('tapsenrol', array('id' => $tapscm->instance));

            if (!$ci->is_enabled($tapscm) == COMPLETION_TRACKING_AUTOMATIC || !$tapsenrol->completionattended) {
                return true;
            }

            $class = $relevantclasses[$enrolment->classid];

            if ($tapsenrol->completiontimetype == \tapsenrol::$completiontimetypes['classendtime']) {
                $completiontime = !empty($class->classendtime) ? $class->classendtime : time();
            } else {
                // Everyone completes now.
                $completiontime = time();
            }

            $result = $taps->set_status($enrolment, 'Full Attendance', $completiontime);

            if (!$result->success) {
                continue;
            }

            // Mark as complete.
            $record = $DB->get_record('tapsenrol_completion',
                    array('tapsenrolid' => $tapsenrol->id, 'userid' => $event->relateduserid));
            if (!$record) {
                $record = new \stdClass();
                $record->tapsenrolid = $tapsenrol->id;
                $record->userid = $event->relateduserid;
                $record->completed = true;
                $record->timemodified = time();
                $DB->insert_record('tapsenrol_completion', $record);
            } else if (!$record->completed) {
                $record->completed = true;
                $record->timemodified = time();
                $DB->update_record('tapsenrol_completion', $record);
            }
            $ci->update_state($tapscm, COMPLETION_COMPLETE, $event->relateduserid);
        }
        return true;
    }

    /** Triggered via \local\coursemanager\class_updated event
     *
     * @param \stdClass $event
     * @return void
     */
    public static function class_updated(\mod_tapsenrol\event\class_updated $event) {
        global $CFG, $DB;

        if (!isset($event->other['oldfields']['classname'])) {
            // No change in class name so groups fine.
            return;
        }

        $taps = new \mod_tapsenrol\taps();

        $class = \mod_tapsenrol\enrolclass::fetch(['id' => $event->other['classid']]);

        if (!$class) {
            // Couldn't load class.
            return;
        }

        $moodlecourse = $DB->get_record('course', array('idnumber' => $class->courseid));

        if (!$moodlecourse) {
            // Not linked to a Moodle course.
            return;
        }

        require_once($CFG->dirroot . '/group/lib.php');

        $oldgroupid = groups_get_group_by_name($moodlecourse->id, trim($event->other['oldfields']['classname']));

        if ($oldgroupid) {
            $group = new \stdClass();
            $group->id = $oldgroupid;
            $group->courseid = $moodlecourse->id;
            $group->name = trim($class->classname);
            $group->description = \html_writer::tag('p', "Group for linked course class: {$group->name}");
            $group->descriptionformat = FORMAT_HTML;
            $group->enrolmentkey = '';
            $group->picture = 0;
            $group->hidepicture = 0;
            $group->timemodified = time();
            $group->idnumber = '';
            groups_update_group($group);
        } else {
            // Create new group.
            $group = new \stdClass();
            $group->courseid = $moodlecourse->id;
            $group->name = trim($class->classname);
            $group->description = \html_writer::tag('p', "Group for linked course class: {$group->name}");
            $group->descriptionformat = FORMAT_HTML;
            $group->enrolmentkey = '';
            $group->picture = 0;
            $group->hidepicture = 0;
            $group->timecreated = time();
            $group->timemodified = $group->timecreated;
            $group->idnumber = '';
            $group->id = groups_create_group($group);
        }

        if (!empty($group->id)) {
            // Need to find current members, those who should be members, compare and add/remove.
            $currentmembers = groups_get_members($group->id, 'u.id');

            list($in, $inparams) = $DB->get_in_or_equal(
                    array_merge($taps->get_statuses('placed'), $taps->get_statuses('waitlisted'), $taps->get_statuses('attended')),
                    SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('lte.bookingstatus');
            $params = array(
                    'classid' => $class->id,
            );
            $sql = <<<EOS
SELECT
    DISTINCT lte.userid
FROM
    {tapsenrol_class_enrolments} lte
WHERE
    lte.classid = :classid
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND lte.active = 1
    AND {$compare} {$in}
EOS;
            $shouldbemembers = $DB->get_records_sql(
                    $sql,
                    array_merge($params, $inparams)
            );

            // Remove any current memebrs who shouldn't be members.
            $toremove = array_diff_key($currentmembers, $shouldbemembers);
            foreach ($toremove as $user) {
                groups_remove_member($group, $user->id);
            }
            // Add and users who should be members who aren't current members.
            $toadd = array_diff_key($shouldbemembers, $currentmembers);
            foreach ($toadd as $user) {
                groups_add_member($group, $user->id);
            }
        }
    }

    /**
     * Triggered via \local_custom_certification\event\certification_course_reset event.
     *
     * @param \stdClass $event
     * @return void
     */
    public static function certification_course_reset(\local_custom_certification\event\certification_course_reset $event) {
        global $CFG, $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, idnumber');

        if (!$user || empty($user->idnumber)) {
            return;
        }

        require_once($CFG->dirroot . '/mod/tapsenrol/classes/tapsenrol.php');

        $instances = $DB->get_records('tapsenrol', array('course' => $event->courseid));
        foreach ($instances as $instance) {
            $tapsenrol = new \tapsenrol($instance->id, 'instance');
            $tapsenrol->enrolment_check($user->id);
        }
    }

    /**
     * Triggered via course_completed event.
     *
     * @param \stdClass $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');
        require_once("$CFG->dirroot/mod/tapsenrol/classes/tapsenrol.php");

        $cms = get_fast_modinfo($event->courseid, -1)->get_instances_of('tapsenrol');

        if (empty($cms)) {
            // Not applicable to this course.
            return;
        }

        if (count($cms) > 1) {
            print_error('Multiple instances of mod_tapsenrol are not currently supported');
        }

        $cm = reset($cms);

        if ($cm->get_course()->enablecompletion != COMPLETION_ENABLED || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
            return;
        }

        $tapsenrol = new \tapsenrol($cm->instance, 'instance', $event->courseid);

        list($instatement, $inparams) = $DB->get_in_or_equal(
                $tapsenrol->taps->get_statuses('placed'),
                SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');
        $sql = "SELECT
                    lte.*
                FROM {tapsenrol_class_enrolments} lte
                INNER JOIN {user} u
                    ON u.id = lte.userid
                INNER JOIN
                    {local_taps_class} ltc
                    ON ltc.id = lte.classid
                WHERE
                    u.id = :userid
                    AND ltc.courseid = :courseid
                    AND lte.active = 1
                    AND (lte.archived = 0 OR lte.archived IS NULL)
                    AND {$compare} {$instatement}";
        $params = array('userid' => $event->relateduserid, 'courseid' => $event->courseid);
        $enrolment = $DB->get_record_sql($sql, array_merge($params, $inparams));

        if (empty($enrolment)) {
            return true;
        }

        $tccompletion =
                $DB->get_record('tapsenrol_completion', ['tapsenrolid' => $cm->instance, 'userid' => $event->relateduserid]);

        if (!$tccompletion) {
            $record = new \stdClass();
            $record->tapsenrolid = $tapsenrol->tapsenrol->id;
            $record->userid = $event->relateduserid;
            $record->completed = true;
            $record->timemodified = time();
            $DB->insert_record('tapsenrol_completion', $record);
        } else if (!$tccompletion->completed) {
            $tccompletion->completed = true;
            $tccompletion->timemodified = time();
            $DB->update_record('tapsenrol_completion', $tccompletion);
        }

        $ccompletion = new \completion_completion(['course' => $event->courseid, 'userid' => $event->relateduserid]);

        // We need to update course completion time if applicable.
        // Use completed field (stores enrolmentid), ignore if 1 for legacy (actual enrolmentid 1 is historic).
        if (!empty($enrolment->classendtime) &&
                $tapsenrol->tapsenrol->completiontimetype == \tapsenrol::$completiontimetypes['classendtime']) {
            $class = \mod_tapsenrol\enrolclass::fetch(['id' => $enrolment->classid]);
            $completiontime = $class->classendtime;
            // Update Moodle course completion date.
            // Record should exist as we're observing course completion.
            $ccompletion->timecompleted = $completiontime;
            $ccompletion->update();
        } else {
            $completiontime = time();
        }

        $tapsenrol->taps->set_status($enrolment, 'Full Attendance', $completiontime);
    }
}
