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

namespace local_admin;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class reset_course {

    private $form;
    private $formdata;

    public function __construct() {
        global $SESSION;

        if (!isset($SESSION->local_admin)) {
            $SESSION->local_admin = new stdClass();
        }

        $this->setup_form();
        $this->process_form();
    }

    public function display_form() {
        $this->form->display();
    }

    private function setup_form() {
        $this->form = new \local_admin\form\reset_course();
        $this->formdata = $this->form->get_data();
    }

    private function process_form() {
        global $DB, $SESSION;

        if (!$this->formdata) {
            return;
        }

        $SESSION->local_admin->processingerrors = [];
        $SESSION->local_admin->processingresults = [];
        foreach ($this->formdata->courses as $courseid) {
            $a = new stdClass();
            // Process each course.
            $this->process_course($courseid, $a);
        }
        redirect('/local/admin/reset_course.php');
    }

    private function process_course($courseid, $a) {
        global $SESSION;

        $a->courseid = $courseid;
        $course = get_course($courseid);
        if (!$course) {
            $SESSION->local_admin->processingerrors[] = get_string('resetcourse:error:invalidcourse', 'local_admin', $a);
            return;
        }

        $a->coursefullname = $course->fullname;

        foreach ($this->formdata->users as $userid) {
            // Process each user for this course.
            $this->process_user($userid, $course, $a);
        }
    }

    private function process_user($userid, $course, $a) {
        global $DB, $SESSION;

        $a->userid = $userid;
        $user = $DB->get_record('user', ['id' => $userid]);
        if (!$user) {
            $SESSION->local_admin->processingerrors[] = get_string('resetcourse:error:invaliduser', 'local_admin', $a);
            return;
        }

        $a->userfullname = fullname($user);

        // Add message first as may be followed by associated certifcation resets!
        $SESSION->local_admin->processingresults[] = get_string('resetcourse:processed:course', 'local_admin', $a);

        // If part of a subsequent path of completed certification then just reset that, otherwise reset single course.
        $completioncache = \cache::make('core', 'completion');
        $certificationstoreset = $this->certifications_to_reset($user, $course);
        $resetcourses = [];
        foreach ($certificationstoreset as $certification) {
            $SESSION->local_admin->processingresults[] = get_string('resetcourse:processed:certification', 'local_admin', $certification->certifname);
            $resetcourses = \local_custom_certification\completion::open_window($certification);
            foreach ($resetcourses as $resetcourseid) {
                $completioncache->delete("{$user->id}_{$resetcourseid}");
            }
        }
        // If not already done via a linked certification, simply reset the course.
        if (!in_array($course->id, $resetcourses)) {
            \local_custom_certification\completion::reset_course_for_user($course->id, $user->id);
            $completioncache->delete("{$user->id}_{$course->id}");
        }
    }

    private function certifications_to_reset($user, $course) {
        global $DB;

        $returncompletions = [];

        $completedsql = "SELECT cc.*, c.fullname as certifname
                           FROM {certif} c
                           JOIN {certif_completions} cc ON cc.certifid = c.id
                          WHERE c.id IN (SELECT DISTINCT certifid FROM {certif_courseset_courses} WHERE courseid = :courseid)
                                AND c.deleted = 0
                                AND c.visible = 1
                                AND cc.userid = :userid
                                AND cc.timecompleted > 0";
        $completions = $DB->get_records_sql($completedsql, ['courseid' => $course->id, 'userid' => $user->id]);
        // Now check course is in applicable courseset.
        foreach ($completions as $completion) {
            $certification = new \local_custom_certification\certification($completion->certifid, false);
            $coursesets = empty($certification->recertificationcoursesets) ? $certification->certificationcoursesets : $certification->recertificationcoursesets;
            foreach ($coursesets as $courseset) {
                if (!empty($courseset->courses) && array_key_exists($course->id, $courseset->courses)) {
                    $returncompletions[] = $completion;
                }
            }
        }

        return $returncompletions;
    }

}
