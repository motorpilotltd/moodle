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
     * @param stdClass $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        $ue = (object)$event->other['userenrolment'];
        if ($ue->lastenrol) {
            $tapsenrols = $DB->get_records('tapsenrol', array('course' => $event->courseid));
            foreach ($tapsenrols as $tapsenrol) {
                $DB->delete_records('tapsenrol_completion', array('tapsenrolid' => $tapsenrol->id, 'userid' => $ue->userid));
            }
        }
    }

    /**
     * Triggered via course_module_deleted event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;

        if ($event->other['modulename'] == 'arupadvert') {
            $instances = $DB->get_records('tapsenrol', array('course' => $event->courseid));
            foreach ($instances as $instance) {
                $cm = get_coursemodule_from_instance('tapsenrol', $instance->id, $instance->course, false, MUST_EXIST);
                course_delete_module($cm->id);
            }
        }
    }

    /** Triggered via \local\coursemanager\class_updated event
     *
     * @param stdClass $event
     * @return void
     */
    public static function class_updated(\local_coursemanager\event\class_updated $event) {
        global $CFG, $DB;

        if (!isset($event->other['oldfields']['classname'])) {
            // No change in class name so groups fine.
            return;
        }

        $taps = new \local_taps\taps();

        $class = $taps->get_class_by_id($event->other['classid']);

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
                'classid' => $class->classid,
            );
            $sql = <<<EOS
SELECT
    DISTINCT u.id
FROM
    {local_taps_enrolment} lte
JOIN
    {user} u
    ON u.idnumber = lte.staffid
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
     * @param stdClass $event
     * @return void
     */
    public static function certification_course_reset(\local_custom_certification\event\certification_course_reset $event) {
        global $CFG, $DB;

        $user = $DB->get_record('user', ['id' => $event->relateduserid], 'id, idnumber');

        if (!$user || empty($user->idnumber)) {
            return;
        }

        require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

        $instances = $DB->get_records('tapsenrol', array('course' => $event->courseid));
        foreach ($instances as $instance) {
            $tapsenrol = new \tapsenrol($instance->id, 'instance');
            $tapsenrol->enrolment_check($user->idnumber);
        }
    }
}
