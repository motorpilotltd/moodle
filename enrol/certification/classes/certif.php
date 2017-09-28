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

namespace enrol_certification;

use local_custom_certification\certification;
use local_custom_certification\completion;

class certif
{

    /**
     * checks if user can be enrolled to course
     *
     * @param $courseid integer course id
     *
     * @return array with information if user can be enrol if not it contains error info
     * @throws \coding_exception
     */
    public static function can_enrol_certif($courseid, $certifid)
    {
        global $USER;
        $certifpath = optional_param('certifpath', 0, PARAM_INT);

        $userassignment = self::check_if_user_is_assigned($USER->id, $certifid);

        if (empty($userassignment)) {
            $enrolstatus = ['canbeenrol' => false, 'errorinfo' => get_string('nocertif', 'enrol_certification')];

            return $enrolstatus;
        }
        $enrolstatus = self::check_course($userassignment, $courseid, $certifpath);

        return $enrolstatus;

    }

    /**
     * Check if user is assigned to certification. If true return record else return false.
     *
     * @param $userid integer user id
     * @param $certifid integer certification id
     *
     * @return object|false if record exist return array
     */
    public static function check_if_user_is_assigned($userid, $certifid)
    {
        global $DB;
        $userassignment = $DB->get_record('certif_user_assignments', ['userid' => $userid, 'certifid' => $certifid]);
        return $userassignment;
    }

    /**
     * Check course in coursesets if user can be enroll.
     * Returns array with info if user can be enroll returns true if can and
     * if not it will be false with additional string with information about error.
     *
     * Algorithm:
     *  - Find first courseset that contain course that user want enrol to
     *  - Group coursests by nextoperator OR
     *  - Take coursesets group that contains courseset we want check
     *  - If there are next operator between coursesets check if previous was completed
     *
     * @param $userassignment object record from certif_assignments for user
     * @param $courseid integer course id
     * @param $certifpath integer if 1 is recertifaction
     *
     * @return array with information if user can be enrol if not it contains error info
     * @throws \coding_exception
     */
    public static function check_course($userassignment, $courseid, $certifpath)
    {
        global $DB, $USER;

        $enrolstatus = ['canbeenrol' => false];
        //Get first available courseset which contains this course
        $coursesetid = self::get_first_courseset_for_course($courseid, $userassignment->certifid, $certifpath);
        if (empty($coursesetid)) {
            return $enrolstatus = ['canbeenrol' => false, 'errorinfo' => get_string('nocertif', 'enrol_certification')];
        }
        //Get all coursesets for certification
        $allcoursesets = $DB->get_records('certif_coursesets', ['certifid' => $userassignment->certifid, 'certifpath' => $certifpath], 'sortorder');

        $coursesetstmp = [];
        $x = 0;
        $coursesetstocheck = [];
        //checks if courseset for this course requires completed previous coursesets
        foreach ($allcoursesets as $courseset) {
            if (!isset($coursesetstmp[$x])) {
                $coursesetstmp[$x] = [];
            }
            $coursesetstmp[$x][] = $courseset;
            //If course are in checking courseset save coursesets for further processing
            if ($courseset->id == $coursesetid->coursesetid) {
                $coursesetstocheck = $coursesetstmp[$x];
            }
            if ($courseset->nextoperator == certification::NEXTOPERATOR_OR) {
                $x++;
            }
        }
        $canbenrolled = true;
        $iscompleted = true;
        $lastoperator = '';

        //checks if restrictions for saved coursesets are met
        foreach ($coursesetstocheck as $courseset) {
            //if previous courseset is not completed user cannot be enrol
            if ($lastoperator == certification::NEXTOPERATOR_THEN && $iscompleted == false) {
                $canbenrolled = false;
            }
            $coursesetcompletion = $DB->get_record('certif_courseset_completions', ['coursesetid' => $courseset->id, 'userid' => $USER->id]);

            if (!$coursesetcompletion) {
                $courseset->status = completion::COMPLETION_STATUS_STARTED;
            } else {
                $courseset->status = $coursesetcompletion->status;
            }
            if ($courseset->status != completion::COMPLETION_STATUS_COMPLETED) {
                $iscompleted = false;
            }

            if ($courseset->id == $coursesetid->coursesetid) {
                //if previous coursesets are completed or restriction are met allow to enroll
                if ($canbenrolled) {
                    $enrolstatus = ['canbeenrol' => true];
                } else {
                    $enrolstatus = ['canbeenrol' => false, 'errorinfo' => get_string('completeothercourses', 'enrol_certification')];
                }
            }
            $lastoperator = $courseset->nextoperator;
        }
        return $enrolstatus;
    }

    /**
     * Returns first courseset for course in certification
     *
     * @param $courseid integer course id
     * @param $certifid integer certification id
     * @param $certifpath integer if 1 is recertifaction
     *
     * @return object|false if record not exist
     * @throws \dml_missing_record_exception
     * @throws \dml_multiple_records_exception
     */
    public static function get_first_courseset_for_course($courseid, $certifid, $certifpath)
    {
        global $DB;
        $sql = 'SELECT cc.courseid, cc.coursesetid, c.sortorder
                FROM {certif_courseset_courses} cc
                JOIN {certif_coursesets} c on cc.certifid = c.certifid AND cc.coursesetid = c.id
                WHERE cc.courseid = :courseid 
                AND c.certifid = :certifid
                AND c.certifpath = :certifpath
                ORDER BY c.sortorder ASC 
                LIMIT 1';
        $params['courseid'] = $courseid;
        $params['certifid'] = $certifid;
        $params['certifpath'] = $certifpath;
        return $DB->get_record_sql($sql, $params);
    }
}