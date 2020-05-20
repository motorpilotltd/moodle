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

class programmember extends \data_object {
    public $table = 'courseraprogrammember';
    public $required_fields = ['id', 'programid', 'externalid', 'userid', 'datejoined', 'dateleft'];

    public $programid;
    public $userid;
    public $externalid;
    public $datejoined;
    public $dateleft;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('courseraprogrammember', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('courseraprogrammember', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public static function saveprogrammember($rawprogrammember) {
        global $DB;

        $params = ['programid'  => $rawprogrammember->programId, 'externalid' => $rawprogrammember->externalId,
                   'datejoined' => $rawprogrammember->joinedAt];

        $data = $DB->get_record_select(
                'courseraprogrammember',
                'programid = :programid and externalid = :externalid and (datejoined = 0 or datejoined = :datejoined)',
                $params
        );

        if ($data) {
            $programmember = new programmember();
            self::set_properties($programmember, $data);
            $programmember->datejoined = $rawprogrammember->joinedAt;
            $programmember->dateleft = 0;
            $programmember->update();
        } else {
            $programmember =
                    new programmember($params);
            $programmember->dateleft = 0;
            $programmember->userid = 0;
            $programmember->insert();
        }
    }

    public static function updateuserprogrammemberlinkages() {
        global $DB;

        $DB->execute("
        update {courseraprogrammember}
        set userid = coalesce((select id from {user} where idnumber is not null and idnumber <> '' and idnumber = externalid), 0)
        where userid = 0 or userid is null");

        $DB->execute("
        update {courseraprogrammember}
        set userid = coalesce((select id from {user} where {user}.email = externalid), 0)
        where userid = 0 or userid is null");
    }

    public static function iscurrentlymember($userid) {
        global $DB;

        $user = \core_user::get_user($userid);
        return $DB->record_exists('courseraprogrammember', ['dateleft' => 0, 'externalid' => $user->idnumber]);
    }

    public static function getallprogrammembershipidnumbers() {
        global $DB;

        // also look at moodle and taps enrolment expiry
        $sql = "select externalid
from {courseraprogrammember} cpm
where cpm.dateleft = 0";

        $res = $DB->get_records_sql_menu($sql);
        return array_keys($res);
    }

    public static function getmoodleactivelearners() {
        global $DB;

        // also look at moodle and taps enrolment expiry
        $sql = "select u.idnumber, u.idnumber
from {coursera} i
         inner join {enrol} me on i.course = me.courseid
         inner join {course} c on c.id = me.courseid and c.visible = 1
         inner join {user_enrolments} ue on ue.enrolid = me.id and ue.timestart > :now and ue.timeend < :now2
         inner join {user} u on u.id = ue.userid and u.suspended = 0 and u.deleted = 0
         left join {courseraprogress} mc on mc.userid = u.id and mc.courseracourseid = i.contentid
where mc.iscompleted is null or mc.iscompleted = 0";

        $params = ['now' => time()];
        $params['now2'] = $params['now'];
        $res = $DB->get_records_sql_menu($sql, $params);
        return array_keys($res);
    }
}