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
 * @author Kamil Helmrich <kamil.helmrich@webanywhere.co.uk>
 */

namespace local_custom_certification\task;

use local_custom_certification\certification;

global $CFG;
require_once($CFG->dirroot . '/cohort/externallib.php');

class check_enrolments extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('taskcheckenrolments', 'local_custom_certification');
    }

    /**
     * Cron task for adding new users to certif_user_assignments and
     * if they are unassignned from certif deleting them from this table
     * and unenrolling them from course (only enrol method for unassignned certif)
     *
     * @throws \coding_exception
     */
    public function execute()
    {
        global $DB;

        $certifications = certification::get_all(['visible' => 1]);
        $plugin = enrol_get_plugin('certification');
        /**
         * We want to run only 100 action per cron crun
         */
        $actionscounter = 0;
        $maxactions = 100;
        foreach ($certifications as $certification) {
            $certif = new certification($certification->id);
            $assignmentusers = [];
            foreach($DB->get_records('certif_assignments_users', ['certifid' => $certification->id]) as $au){
                if(!isset($assignmentusers[$au->assignmentid])){
                    $assignmentusers[$au->assignmentid] = [];
                }
                $assignmentusers[$au->assignmentid][] = $au->userid;
            }
            $assignments = $certif->assignments;
            $assignedusers = [];

            foreach ($assignments as $assignment) {
                //get assigned users
                $users = certification::get_users_from_assignment($assignment->id);
                //prepare list of user to check if they are still assigned
                $assignedusers = array_merge($assignedusers, $users);
                foreach ($users as $user) {
                    if($actionscounter >= $maxactions){
                        break;
                    }
                    if (!isset($certif->assignedusers[$user])) {
                        //add new user to certif_user_assingments
                        certification::add_user_to_assignment($user, $certif->id, $assignment->id);
                        //update table to prevent checked this user once again
                        $userupdate = (object)['id' => $user, 'assignmentid' => $assignment->id];
                        $certif->assignedusers[$user] = $userupdate;
                        $actionscounter++;
                    }
                    if(!isset($assignmentusers[$assignment->id]) || !in_array($user, $assignmentusers[$assignment->id])){
                        certification::add_user_assignment_pair($user, $assignment->id, $certification->id);
                        $actionscounter++;
                    }
                }
            }

            foreach ($certif->assignedusers as $assigneduser) {
                //check if user is still assigned
                if($actionscounter >= $maxactions){
                    break;
                }
                if (!in_array($assigneduser->id, $assignedusers)) {
                    //if not get coursesets and courses for certif and unenrol user
                    foreach ($certif->certificationcoursesets as $certificationcourseset) {
                        foreach ($certificationcourseset->courses as $course) {
                            //get enrol instance for this certification - customint8 keeps certification id
                            $instance = $DB->get_record('enrol', ['courseid' => $course->courseid, 'enrol' => 'certification', 'customint8' => $certif->id], '*');
                            //if false user is not enroled to course via enrol method for this certif
                            if ($instance) {
                                //unenrol user for each course in coursesets
                                $plugin->unenrol_user($instance, $assigneduser->id);
                            }
                        }
                    }
                    //delete record for user in certif_user_assignment
                    $certif->delete_users_from_assignment($assigneduser->assignmentid, $assigneduser->id);
                    $actionscounter++;
                }
            }

            $query = "
                SELECT
                    cau.id,
                    cau.userid,
                    cau.certifid,
                    cau.assignmentid
                FROM {certif_assignments_users} cau
                LEFT JOIN {certif_assignments} ca ON cau.assignmentid = ca.id
                WHERE ca.id IS NULL
                AND cau.certifid = :certifid
            ";

            $assignmentusers = $DB->get_records_sql($query, ['certifid' => $certif->id]);

            foreach($assignmentusers as $assignmentuser){
                if($actionscounter >= $maxactions){
                    break;
                }
                $actionscounter++;
                certification::delete_user_assignment_pair($assignmentuser->userid, $assignmentuser->assignmentid);
                certification::set_user_assignment_due_date($assignmentuser->userid, $assignmentuser->certifid);
            }

        }
        mtrace("Actions: ".$actionscounter);
    }
}