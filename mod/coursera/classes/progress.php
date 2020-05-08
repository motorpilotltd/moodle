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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursera;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once("$CFG->dirroot/completion/data_object.php");

class progress extends \data_object {
    public $table = 'courseraprogress';
    public $required_fields = ['id', 'courseracourseid', 'contentid', 'userid', 'externalid', 'iscompleted',
            'overallprogress'];

    public $courseracourseid;
    public $contentid;
    public $userid;
    public $externalid;
    public $iscompleted;
    public $overallprogress;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('courseraprogress', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('courseraprogress', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public static function saveprogress($rawprogress) {
        global $DB;

        $progress = new progress(['contentid' => $rawprogress->contentId, 'externalid' => $rawprogress->externalId]);

        if ($progress->iscompleted == $rawprogress->isCompleted && $progress->overallprogress == $rawprogress->overallProgress) {
            return;
        }

        $progress->iscompleted = $rawprogress->isCompleted;
        $progress->overallprogress = $rawprogress->overallProgress;

        if (!isset($progress->id)) {
            $progress->insert();
        } else {
            $progress->update();
        }
    }

    public static function coursecompletions() {
        global $DB;

        $sql = "select cpr.id, cm.course as course, cm.id as cmid, u.id as userid, n.id as instanceid
                from {courseraprogress} cpr
                inner join {coursera} n on n.contentid = cpr.courseracourseid
                inner join {modules} m on m.name = 'coursera'
                inner join {course_modules} cm on cm.instance = n.id and cm.module = m.id
                inner join {user} u on u.id = cpr.userid
                inner join (select userid, courseid from {user_enrolments} ue inner join {enrol} e on e.id = ue.enrolid group by userid, courseid) enrolments on enrolments.userid = u.id and enrolments.courseid = cm.course
                left join {course_modules_completion} cmc on cmc.userid = u.id and cm.id = cmc.coursemoduleid
                where cpr.iscompleted = 1 AND (cmc.completionstate = 0 oR cmc.completionstate is null)";
        $toprocess = $DB->get_records_sql($sql);

        $instances = $DB->get_records('coursera');
        foreach ($instances as $instance) {
            $coursesandcms[$instance->id] = get_course_and_cm_from_instance($instance->id, 'coursera');
        }

        foreach ($toprocess as $record) {
            $courseandcm = $coursesandcms[$record->instanceid];
            $course = $courseandcm[0];
            $cm = $courseandcm[1];

            $completion = new \completion_info($course);
            $completion->update_state($cm, COMPLETION_COMPLETE, $record->userid);
        }
        return true;
    }

    public static function updateusercourselinkages() {
        global $DB;

        $DB->execute("
        update {courseraprogress}
        set userid = coalesce((select id from {user} where idnumber is not null and idnumber <> '' and idnumber = externalid), 0)
        where userid = 0 or userid is null");

        $DB->execute("
        update {courseraprogress}
        set userid = coalesce((select id from {user} where {user}.email = externalid), 0)
        where userid = 0 or userid is null");

        $DB->execute("
        update {courseraprogress}
        set courseracourseid = (select id from {courseracourse} cc where cc.contentid = {courseraprogress}.contentid)");
    }
}