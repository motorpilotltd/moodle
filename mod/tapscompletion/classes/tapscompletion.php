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

namespace mod_tapscompletion;

defined('MOODLE_INTERNAL') || die();

class tapscompletion {
    public $cm;
    public $course;
    public $tapscompletion;

    public $classid = 0;
    public $classes = array();
    public $users = array();

    public $taps;

    protected $_checkinstalls;

    public static $completiontimetypes = [
        'currenttime' => 0,
        'classendtime' => 1,
    ];

    public function __construct() {
        $this->taps = new \local_taps\taps();
    }

    public function set_cm($type, $id, $courseid = 0) {
        switch ($type) {
            case 'id' :
                $this->cm = get_coursemodule_from_id('tapscompletion', $id, $courseid);
                break;
            case 'instance' :
                $this->cm = get_coursemodule_from_instance('tapscompletion', $id, $courseid);
                break;
            default :
                $this->cm = false;
                break;
        }
        return $this->cm;
    }

    public function set_course($id) {
        global $DB;
        $this->course = $DB->get_record('course', array('id' => $id));
        return $this->course;
    }

    public function set_tapscompletion($id) {
        global $DB;
        $this->tapscompletion = $DB->get_record('tapscompletion', array('id' => $id));
        return $this->tapscompletion;
    }

    public function check_installation($fullcheck = false) {
        global $CFG, $DB;

        if (!isset($this->_checkinstalls[$this->course->id])) {
            $arupadvert = $DB->get_record('arupadvert', array('course' => $this->course->id));
            if (!$arupadvert) {
                $this->_checkinstalls[$this->course->id] = false;
            } else if ($arupadvert->datatype != 'taps') {
                $this->_checkinstalls[$this->course->id] = false;
            }

            if (isset($this->_checkinstalls[$this->course->id]) && $this->_checkinstalls[$this->course->id] === false) {
                // Don't check more than necessary.
                return $this->_checkinstalls[$this->course->id];
            }

            $tapsenrols = $DB->get_records('tapsenrol', array('course' => $this->course->id));
            if (count($tapsenrols) != 1) {
                $this->_checkinstalls[$this->course->id] = false;
            } else if ($fullcheck) {
                require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

                $tapsenrol = new \tapsenrol(reset($tapsenrols)->id, 'instance');

                $this->_checkinstalls[$this->course->id] = $tapsenrol->check_installation();
            } else {
                $this->_checkinstalls[$this->course->id] = reset($tapsenrols)->instanceok;
            }
        }

        return $this->_checkinstalls[$this->course->id];
    }

    public function get_classes_and_users($classid = 0) {
        global $DB;

        $users = get_enrolled_users(\context_course::instance($this->cm->course), '', 0, 'u.id', null, 0, 0, true);

        if (empty($users)) {
            return;
        }

        list($userin, $userinparams) = $DB->get_in_or_equal(
            array_keys($users),
            SQL_PARAMS_NAMED, 'user'
        );
        list($statusin, $statusinparams) = $DB->get_in_or_equal(
            $this->taps->get_statuses('placed'),
            SQL_PARAMS_NAMED, 'status'
        );
        $statuscompare = $DB->sql_compare_text('lte.bookingstatus');

        $params = array(
            'tapscourse' => $this->tapscompletion->tapscourse,
            'courseid' => $this->cm->course
        );

        $distinctclassname = $DB->sql_compare_text('lte.classname');
        $classlistsql = "SELECT DISTINCT lte.classid, {$distinctclassname} as classname
            FROM {local_taps_enrolment} lte
            JOIN {user} u ON u.idnumber = lte.staffid
            WHERE
                lte.courseid = :tapscourse
                AND (lte.archived = 0 OR lte.archived IS NULL)
                AND lte.active = 1
                AND {$statuscompare} {$statusin}
                AND u.id {$userin}
            ORDER BY
                lte.classid
            ";
        $this->classes = array(0 => get_string('allclasses', 'tapscompletion')) + $DB->get_records_sql_menu($classlistsql, array_merge($params, $statusinparams, $userinparams));

        $classidwhere = '';
        if ($classid) {
            $this->classid = $classid;
            $classidwhere = 'AND lte.classid = :classid';
            $params['classid'] = $this->classid;
        }

        $sql = "SELECT lte.enrolmentid, lte.classid, lte.classname, u.*
            FROM {local_taps_enrolment} lte
            JOIN {user} u ON u.idnumber = lte.staffid
            WHERE
                lte.courseid = :tapscourse
                AND (lte.archived = 0 OR lte.archived IS NULL)
                AND lte.active = 1
                AND {$statuscompare} {$statusin}
                AND u.id {$userin}
                {$classidwhere}
            ORDER BY
                lte.classid
            ";
        $this->users = $DB->get_records_sql($sql, array_merge($params, $statusinparams, $userinparams));
    }
}