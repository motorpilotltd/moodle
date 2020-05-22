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

class courseramoduleaccess extends \data_object {
    public $table = 'courseramoduleaccess';
    public $required_fields = ['id', 'courseraid', 'userid', 'timestart', 'timeend'];

    public $courseraid;
    public $userid;
    public $timestart;
    public $timeend;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('courseramoduleaccess', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('courseramoduleaccess', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public static function endofcourseramoduleaccess($userid, $courseraid) {
        global $DB, $CFG;

        $cma = self::fetch(['userid' => $userid, 'courseraid' => $courseraid]);
        if (!empty($cma)) {
            return $cma->timeend;
        }

        $coursera = $DB->get_record('coursera', ['id' => $courseraid]);
        $context = \context_course::instance($coursera->course);

        $gradebookroles = explode(",", $CFG->gradebookroles);
        $userroles = get_user_roles($context, $userid);
        $userroleids = [];
        foreach ($userroles as $role) {
            $userroleids[] = $role->roleid;
        }

        if (!array_intersect($gradebookroles, $userroleids)) {
            return false;
        }

        $maxenrolmentstart = $DB->get_field_sql(
                'SELECT max(timestart) from {user_enrolments} ue inner join {enrol} e on e.id = ue.enrolid where userid = :userid and courseid = :courseid',
                ['userid' => $userid, 'courseid' => $coursera->course]
        );

        if ($maxenrolmentstart) {
            return $maxenrolmentstart + $coursera->moduleaccessperiod;
        }

        return false;
    }
}