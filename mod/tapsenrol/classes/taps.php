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
 * The local_taps interface.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// TODO Break up and spread between tapsenrol, local_admin and local_learningrecordstore.
namespace mod_tapsenrol;

defined('MOODLE_INTERNAL') || die();

use coursemetadatafield_arup\arupmetadata;
use stdClass;
use DateTimeZone;

/**
 * The local_taps taps class.
 *
 * @package    local_taps
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class taps {

    /** @var array status groups and their statuses. */
    private $_statuses = array(
        'requested' => array(
            'W:Requested',
            'Requested'
        ),
        'waitlisted' => array(
            'Waiting Listed',
            'Reserve',
            'Wait1',
            'Wait2',
            'Wait3',
            'Wait-Computing',
            'W:Wait Listed',
            'Wait Listed'
        ),
        'placed' => array(
            'Approved Place',
            'Offered Place'
        ),
        'attended' => array(
            'Assessed',
            'Full Attendance',
            'Partial Attendance'
        ),
        'cancelled' => array(
            'Cancelled',
            'Withdrawn',
            'No Place',
            'Dropped Out',
            'Class Postponed',
            'Class No Longer Required',
            'Date Inappropriate',
            'No Response',
            'No Show',
            'Course Full'
        ),
    );

    /** @var array class types. */
    private $_classtypes = array(
        'classroom' => array(
            'Scheduled', 'Classroom'
        ),
        'elearning' => array(
            'Self Paced', 'e-Learning'
        ),
        'cpd' => array(
            '' => null,
            'CE' => 'Competency Evidence',
            'S' => 'Conferences/Seminar',
            'EACPDT1' => 'EA CPD Type I',
            'EACPDT2' => 'EA CPD Type II',
            'EACPDT3' => 'EA CPD Type III',
            'EACPDT4' => 'EA CPD Type IV',
            'EACPDT5' => 'EA CPD Type V',
            'EACPDT6' => 'EA CPD Type VI',
            'EACPDT7' => 'EA CPD Type VII',
            'EACPDT8' => 'EA CPD Type VIII',
            'EC' => 'External Course',
            'HS' => 'Health and Safety',
            'INF' => 'Informal',
            'IM' => 'Institute Meetings',
            'IC' => 'Internal Courses',
            'L' => 'Language',
            'LUNCH_AND_LEARN' => 'Lunch and Learn',
            'OT' => 'Other Training',
            'ILT' => 'Papers',
            'P' => 'Presentations',
            'R' => 'Reading',
            'SELF-LED' => 'Self-led learning',
            'TT' => 'Talks - Technical',
            'WEBINAR' => 'Webinar',
            'OJT' => 'Work Based Learning',
        ),
    );

    /** @var array duration units. */
    private static $_durationunitscode = array(
        '' => null,
        'D' => 'Day(s)',
        'H' => 'Hour(s)',
        'HPM' => 'Hour(s) Per Month',
        'HPW' => 'Hour(s) Per Week',
        'M' => 'Month(s)',
        'MIN' => 'Minute(s)',
        'Q' => 'Quarter Hour(s)',
        'W' => 'Week(s)',
        'Y' => 'Year(s)'
    );

    /** @var array class category. */
    private $_classcategory = array(
        '' => null,
        'AEO' => 'AEO Framework',
        'Engineers Australia CPD Area 1' => 'Engineers Australia CPD Area 1: Risk Management',
        'Engineers Australia CPD Area 2' => 'Engineers Australia CPD Area 2: Business and Management',
        'Engineers Australia CPD Area 3' => 'Engineers Australia CPD Area 3: Area of practise',
        'EMS' => 'Environmental Management System',
        'HS' => 'Health and Safety',
        'Mentorship Program' => 'Mentorship Program',
        'Other' => 'Other',
        'PD' => 'Professional Development',
        'PDC' => 'Professional Development (Certified)',
        'QMS' => 'Quality Management System',
        'RedR' => 'RedR',
        'Sustainability' => 'Sustainability',
        'Europe Region - Non-technical training: Structural' => 'Europe Region - Non-technical training: Structural',
        'Europe Region - Non-technical training: Building Services' => 'Europe Region - Non-technical training: Building Services',
        'Europe Region - Non-technical training: Infrastructures' => 'Europe Region - Non-technical training: Infrastructures',
        'Europe Region - Non-technical training: Consulting / Specialists' => 'Europe Region - Non-technical training: Consulting / Specialists',
        'Europe Region - Non-technical training: Business Services' => 'Europe Region - Non-technical training: Business Services',
        'Europe Region - Technical training: Structural' => 'Europe Region - Technical training: Structural',
        'Europe Region - Technical training: Building Services' => 'Europe Region - Technical training: Building Services',
        'Europe Region - Technical training: Infrastructures' => 'Europe Region - Technical training: Infrastructures',
        'Europe Region - Technical training: Consulting / Specialists' => 'Europe Region - Technical training: Consulting / Specialists',
    );

    /** @var array class cost currency. */
    private $_classcostcurrency = array(
        '' => null,
        'AED' => 'United Arab Emirates Dirham',
        'AUD' => 'AUD	Australia Dollar',
        'BND' => 'Brunei Darussalam Dollar',
        'BWP' => 'Botswana Pula',
        'CAD' => 'Canada Dollar',
        'CNY' => 'China Yuan Renminbi',
        'COP' => 'Colombia Peso',
        'DKK' => 'Denmark Krone',
        'EUR' => 'Euro Member Countries',
        'GBP' => 'United Kingdom Pound',
        'HKD' => 'Hong Kong Dollar',
        'IDR' => 'Indonesia Rupiah',
        'INR' => 'India Rupee',
        'JPY' => 'Japan Yen',
        'KHR' => 'Cambodia Riel',
        'KRW' => 'Korea (South) Won',
        'MUR' => 'Mauritius Rupee',
        'MYR' => 'Malaysia Ringgit',
        'NGN' => 'Nigeria Naira',
        'NZD' => 'New Zealand Dollar',
        'PHP' => 'Philippines Peso',
        'PLN' => 'Poland Zloty',
        'QAR' => 'Qatar Riyal',
        'RSD' => 'Serbia Dinar',
        'RUB' => 'Russia Ruble',
        'SGD' => 'Singapore Dollar',
        'THB' => 'Thailand Baht',
        'TRY' => 'Turkey Lira',
        'USD' => 'US Dollar',
        'VND' => 'Viet Nam Dong',
        'ZAR' => 'South Africa Rand',
        'ZWD' => 'Zimbabwe Dollar',
    );

    /** @var array health and safety category. */
    private $_healthandsafetycategory = array(
        '' => null,
        'AUS 4WD' => 'AUS 4WD',
        'AUS Asbestos' => 'AUS Asbestos',
        'AUS Confined Space' => 'AUS Confined Spaces',
        'AUS Construction Industry Safety (White Card)' => 'AUS Construction Industry Safety (White Card)',
        'AUS First Aid' => 'AUS First Aid',
        'AUS Rail Safety' => 'AUS Rail Safety',
        'AUS Rope Access' => 'AUS Rope Access',
        'Asbestos Awareness Training' => 'Asbestos Awareness Training',
        'Basic First Aid for the Appointed Person (1day)' => 'Basic First Aid for the Appointed Person (1day)',
        'Bi-monthly Rail Safety Briefing' => 'Bi-monthly Rail Safety Briefing',
        'CDM for Designers' => 'CDM for Designers',
        'COSS' => 'COSS',
        'CSCS Training' => 'CSCS Training',
        'Contaminated Land Course' => 'Contaminated Land Course',
        'Core Planner' => 'Core Planner',
        'Core Planner Skills II' => 'Core Planner Skills II',
        'Corporate Manslaughter' => 'Corporate Manslaughter',
        'DSE Assessor Training' => 'DSE Assessor Training',
        'DSE Training' => 'DSE Training',
        'Entry into Confined Spaces with Escape BA' => 'Entry into Confined Spaces with Escape BA',
        'Fire Marshal Training' => 'Fire Marshal Training',
        'Fire Radio Training' => 'Fire Radio Training',
        'First Aid Training' => 'First Aid Training',
        'First Aiders Information Session' => 'First Aiders Information Session',
        'General Site Safety Briefing' => 'General Site Safety Briefing',
        'Health and Safety Office Induction' => 'Health and Safety Office Induction',
        'Health and Safety Training' => 'Health and Safety Training',
        'Health and Safety for Leaders' => 'Health and Safety for Leaders',
        'IOSH Managing Safety in Construction' => 'IOSH Managing Safety in Construction',
        'IPP' => 'IPP',
        'IWA' => 'IWA',
        'Introduction to OHSAS 18001' => 'Introduction to OHSAS 18001',
        'LUL Card' => 'LUL Card',
        'Lead Auditor' => 'Lead Auditor',
        'Lighting Lab - RP Machine' => 'Lighting Lab - RP Machine',
        'Manual Handling' => 'Manual Handling',
        'Manual Handling Training' => 'Manual Handling Training',
        'Model Shop Equipment Induction - Laser Cutter' => 'Model Shop Equipment Induction - Laser Cutter',
        'Model Shop Equipment Induction - RP Machine' => 'Model Shop Equipment Induction - RP Machine',
        'Model Shop Equipment Induction – Saw' => 'Model Shop Equipment Induction – Saw',
        'Model Shop Induction (Basic)' => 'Model Shop Induction (Basic)',
        'NEBOSH National Cert in Construction H&S' => 'NEBOSH National Cert in Construction H&S',
        'PTS' => 'PTS',
        'Rail Safety for Leaders' => 'Rail Safety for Leaders',
        'Re Training' => 'Re Training',
        'Regional Safety Coordinators Meeting' => 'Regional Safety Coordinators Meeting',
        'Safety Coordinator Induction' => 'Safety Coordinator Induction',
        'Safety Coordinator Update' => 'Safety Coordinator Update',
        'Strategic Rail Safety (NR)' => 'Strategic Rail Safety (NR)',
        'Track Visitors Pass' => 'Track Visitors Pass'
    );

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        // Empty.
    }

    /**
     * Get statuses of a type.
     *
     * @param string $type
     * @return mixed
     */
    public function get_statuses($type) {
        return isset($this->_statuses[$type]) ? $this->_statuses[$type] : false;
    }

    /**
     * Is status of a type?.
     *
     * @param string $status
     * @param mixed $types
     * @return bool
     */
    public function is_status($status, $types) {
        if (!is_array($types)) {
            $types = array($types);
        }
        $return = false;
        foreach ($types as $type) {
            $return = $return || (isset($this->_statuses[$type]) && in_array($status, $this->_statuses[$type]));
        }
        return $return;
    }

    /**
     * Get status type.
     *
     * @param string $status
     * @return mixed
     */
    public function get_status_type($status) {
        foreach ($this->_statuses as $type => $statuses) {
            if (in_array($status, $statuses)) {
                return $type;
            }
        }
        return false;
    }

    /**
     * Get classtypes (of a type).
     *
     * @param string $type
     * @return mixed
     */
    public function get_classtypes($type) {
        return isset($this->_classtypes[$type]) ? $this->_classtypes[$type] : false;
    }

    /**
     * Get classtype type.
     *
     * @param string $classtype
     * @return mixed
     */
    public function get_classtype_type($classtype) {
        foreach ($this->_classtypes as $type => $classtypes) {
            if (in_array($classtype, $classtypes)) {
                return $type;
            }
        }
        return false;
    }

    /**
     * Get durationsunitcode.
     *
     * @return array
     */
    public function get_durationunitscode() {
        return self::$_durationunitscode;
    }

    public static function resolvedurationunit($unit) {
        return self::$_durationunitscode[$unit];
    }

    /**
     * Get classcategory(.
     *
     * @return array
     */
    public function get_classcategory() {
        return $this->_classcategory;
    }

    /**
     * Get classcostcurrency.
     *
     * @return array
     */
    public function get_classcostcurrency() {
        return $this->_classcostcurrency;
    }

    /**
     * Get healthandsafetycategory.
     *
     * @return array
     */
    public function get_healthandsafetycategory() {
        return $this->_healthandsafetycategory;
    }


    /**
     * Get course classes.
     *
     * @param int $courseid
     * @return mixed
     */
    public function get_course_classes($courseid, $hidden = false, $archived = false, $fields = '*', $extrawhere = '') {
        global $DB;
        $where = 'courseid = :courseid';
        if (!$hidden) {
            $where .= ' AND (classhidden = 0 OR classhidden IS NULL)';
        }
        if (!$archived) {
            $where .= ' AND (archived = 0 OR archived IS NULL)';
        }
        if ($extrawhere) {
            $where .= " AND ({$extrawhere})";
        }
        return $DB->get_records_select('local_taps_class', $where, array('courseid' => $courseid), '', $fields);
    }

    /**
     * Get employee's enrolled classes.
     *
     * @param int $userid
     * @param mixed $courseid
     * @return mixed
     */
    public function get_enrolments($userid, $courseid = null, $activeonly = false, $archived = false) {
        global $DB;

        $params = array();
        $params['userid'] = $userid;
        $courseidwhere = '';
        if (!is_null($courseid)) {
            $params['courseid'] = $courseid;
            $courseidwhere = ' AND courseid = :courseid';
        }

        $activewhere = $activeonly ? ' AND lte.active = 1' : '';
        $archivedwhere = $archived ? '' : ' AND (ltc.archived = 0 OR ltc.archived IS NULL)';
        $sql = "SELECT lte.* 
                FROM {tapsenrol_class_enrolments} lte 
                INNER JOIN {local_taps_class} ltc ON lte.classid = ltc.id
                WHERE userid = :userid $courseidwhere $activewhere $archivedwhere";
        $enrolments = $DB->get_records_sql($sql, $params);

        return $enrolments;
    }

    /**
     * Get enrolment by ID.
     *
     * @param int $enrolmentid
     * @param string $type
     * @return mixed
     */
    public function get_enrolment_by_id($enrolmentid) {
        global $DB;

        return $DB->get_record('tapsenrol_class_enrolments', array('id' => $enrolmentid));
    }

    /**
     * Get enrolment status.
     *
     * @param int $enrolmentid
     * @return mixed
     */
    public function get_enrolment_status($enrolmentid) {
        global $DB;

        return $DB->get_field('tapsenrol_class_enrolments', 'bookingstatus', array('id' => $enrolmentid));
    }

    /**
     * Enrol employee.
     *
     * @param int $classid
     * @param int $userid
     * @param bool $approved
     * @return mixed
     */
    public function enrol($classid, $userid, $approved = false) {
        global $DB, $CFG;
        $result = new stdClass();

        require_once("$CFG->dirroot/lib/enrollib.php");

        $class = \mod_tapsenrol\enrolclass::fetch(['id' => $classid]);

        $coursecontext = \context_course::instance($class->courseid);
        $user = \core_user::get_user($userid);
        if(!is_enrolled($coursecontext, $user)) {
            $instances = $DB->get_records('enrol', array('enrol'=>'manual', 'courseid' => $class->courseid, 'status' => ENROL_INSTANCE_ENABLED), 'sortorder,id ASC');
            $instance = reset($instances);
            $roleid = $instance ? $instance->roleid : null;

            if (!enrol_try_internal_enrol($class->courseid, $user->id,$roleid)) {
                $result->success = false;
                $result->status = 'MOODLE_ENROLMENT_FAILED';
                return $result;
            }
        }

        // Setup enrolment object.
        $enrolment = new stdClass();
        $enrolment->userid = $userid;
        $enrolment->classid = $classid;
        $existingenrolments = $this->existingenrolments($userid, $classid);

        if (!empty($existingenrolments)) {
            $result->success = false;
            $result->status = 'ALREADY_ENROLLED';
            return $result;
        }

        // Set base (usual) enrolment status.
        switch ($class->classstatus) {
            case 'Planned':
                // Waiting list.
                $enrolment->bookingstatus = ($approved ? 'W:Wait Listed' : 'W:Requested');
                break;
            default :
                $enrolment->bookingstatus = ($approved ? 'Approved Place' : 'W:Requested');
                break;
        }

        // Full class (if being approved).
        if ($approved && $this->get_seats_remaining($enrolment->classid) === 0) {
            // No seats, on to the waiting list.
            $enrolment->bookingstatus = 'W:Wait Listed';
            $result->status = 'CLASS_FULL';
        }

        $enrolment->completiontime = null;
        // Field bookingplaceddate is a new field from migration data.
        $enrolment->timemodified = time();
        $enrolment->id = $DB->insert_record('tapsenrol_class_enrolments', $enrolment);

        $result->success = (bool) $enrolment->id;
        $result->enrolment = $enrolment;
        if (!$result->success) {
            $result->status = 'ENROLMENT_FAILED';
        }
        return $result;
    }

    /**
     * Set booking status.
     *
     * @param int $enrolmentid
     * @param string $status
     * @param string|null $completiontime
     * @return \stdClass
     */
    public function set_status($enrolment, $status, $completiontime = null) {
        global $DB;

        $class = \mod_tapsenrol\enrolclass::fetch(['id' => $enrolment->classid]);

        // Setup result object.
        $result = new \stdClass();
        $result->success = true;
        $result->status = 'UPDATE_SUCCESSFUL';

        // Check status is valid.
        $statustype = $this->get_status_type($status);
        if (!$statustype) {
            $result->success = false;
            $result->status = 'INVALID_STATUS';
            return $result;
        }

        if ($status === $enrolment->bookingstatus) {
            $result->success = false;
            $result->status = 'NO_CHANGE';
            return $result;
        }

        // Lock if 'attended' or 'cancelled'.
        if ($this->is_status($enrolment->bookingstatus, array('attended', 'cancelled'))) {
            $result->success = false;
            $result->status = $this->is_status($enrolment->bookingstatus, 'attended') ? 'IS_ATTENDED' : 'IS_CANCELLED';
            return $result;
        }

        $class = \mod_tapsenrol\enrolclass::fetch(['id' => $enrolment->classid]);
        if (!$class) {
            $result->success = false;
            $result->status = 'INVALID_CLASS';
            return $result;
        }

        // Trying to place on a 'Planned' class.
        if ($this->is_status($status, 'placed') && $class->classstatus === 'Planned') {
            // Should be wait listed.
            $status = 'W:Wait Listed';
        }
        // Check for full class if trying to place.
        if ($this->is_status($status, 'placed') && !$this->is_status($enrolment->bookingstatus, 'placed') && $this->get_seats_remaining($class->id) === 0) {
            // Trying to place and no seats.
            $status = 'W:Wait Listed';
            $result->status = 'CLASS_FULL';
        }

        $enrolment->bookingstatus = $status;
        if ($this->is_status($status, 'attended')) {
            $enrolment->completiontime = (empty($completiontime) ? time() : $completiontime);
            try {
                $usedtimezone = new DateTimeZone($class->usedtimezone);
            } catch (\Exception $e) {
                $usedtimezone = new DateTimeZone('UTC');
            }
            $enrolment->completiontime = usergetmidnight($enrolment->completiontime, $usedtimezone); // Midnight of same day as completion time.
        }

        $enrolment->timemodified = time();

        if (!$DB->update_record('tapsenrol_class_enrolments', $enrolment)) {
            $result->success = false;
            $result->status = 'UPDATE_FAILED';
        }

        return $result;
    }

    public function get_seats_remaining($classid) {
        global $DB;

        $class = \mod_tapsenrol\enrolclass::fetch(['id' => $classid]);

        if (!$class) {
            return false;
        }

        if (!($class->maximumattendees > 0)) {
            return -1;
        }

        // Placed and attended count as taking seats.
        list($in, $inparams) = $DB->get_in_or_equal(
            array_merge($this->get_statuses('placed'), $this->get_statuses('attended')),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('bookingstatus');
        $params = array_merge(
            array('classid' => $classid),
            $inparams
        );
        $enrolments = $DB->count_records_select('tapsenrol_class_enrolments', "classid = :classid AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}", $params);

        return max(array(0, $class->maximumattendees - $enrolments));
    }

    public function get_seats_remaining_by_course($courseid) {
        global $DB;

        $seatsremaining = [];

        $classes = \mod_tapsenrol\enrolclass::fetch_all_visible_by_course($courseid);

        // Placed and attended count as taking seats.
        list($in, $inparams) = $DB->get_in_or_equal(
                array_merge($this->get_statuses('placed'), $this->get_statuses('attended')),
                SQL_PARAMS_NAMED, 'status'
        );
        $params = array_merge(
                array('courseid' => $courseid),
                $inparams
        );
        $sql = "SELECT ltc.id, COUNT(lte.id)
                  FROM {tapsenrol_class_enrolments} lte
                  INNER JOIN {local_taps_class} ltc ON lte.classid = ltc.id
                 WHERE ltc.courseid = :courseid AND (ltc.archived = 0 OR ltc.archived IS NULL) AND bookingstatus {$in}
              GROUP BY ltc.id
              ORDER BY ltc.id ASC";
        $enrolments = $DB->get_records_sql_menu($sql, $params);

        foreach ($classes as $class) {
            if (!($class->maximumattendees > 0)) {
                $seatsremaining[$class->id] = -1;
            } else if (!isset($enrolments[$class->id])) {
                $seatsremaining[$class->id] = $class->maximumattendees;
            } else {
                $seatsremaining[$class->id] = max(array(0, $class->maximumattendees - $enrolments[$class->id]));
            }
        }

        return $seatsremaining;
    }

    /**
     * @param string $information
     * @param \course_modinfo $modinfo
     * @return bool
     */
    public static function is_user_signedup(&$information, $modinfo, $userid) {
        global $USER;

        if ($userid = $USER->id) {
            $user = $USER;
        } else {
            $user = \core_user::get_user($userid);
        }

        $taps = new \mod_tapsenrol\taps();

        if (empty($user)) {
            return false;
        }

        $enrolments = $taps->get_enrolments($user->id, $modinfo->courseid, true, false);

        foreach ($enrolments as $enrolment) {
            $statusstype = $taps->get_status_type($enrolment->bookingstatus);

            if ($statusstype == 'placed' || $statusstype == 'attended') {
                return true;
            }
        }

        $information = get_string('enroltoaccesscourse', 'tapsenrol');
        return false;
    }

    /**
     * @param $userid
     * @param $classid
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function existingenrolments($userid, $classid = null, $courseid = null) {
        global $DB;

        // Does a not cancelled enrolment exist?.
        list($in, $params) = $DB->get_in_or_equal(
                $this->get_statuses('cancelled'),
                SQL_PARAMS_NAMED, 'status', false
        );
        $compare = $DB->sql_compare_text('bookingstatus');
        $params['userid'] = $userid;

        $where = "userid = :userid AND (lte.archived = 0 OR lte.archived IS NULL) AND lte.active = 1 AND {$compare} {$in}";

        if (isset($classid)) {
            $params['classid'] = $classid;
            $where .= ' AND classid = :classid ';
        }

        if (isset($courseid)) {
            $params['courseid'] = $courseid;
            $where .= ' AND courseid = :courseid ';
        }

        $sql = "SELECT lte.* FROM {tapsenrol_class_enrolments} lte
                INNER JOIN {local_taps_class} ltc on lte.classid = ltc.id
                WHERE $where";

        $existingenrolments = $DB->get_records_sql($sql, $params);
        return $existingenrolments;
    }
}