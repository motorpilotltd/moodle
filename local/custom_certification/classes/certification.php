<?php

namespace local_custom_certification;

global $CFG;
require_once($CFG->dirroot . '/cohort/externallib.php');
require_once($CFG->dirroot . '/enrol/certification/lib.php');


class certification
{
    const PERIOD_TIME_DAY = 1;
    const PERIOD_TIME_MONTH = 2;
    const PERIOD_TIME_YEAR = 3;

    const CERTIFICATIONPATH_BASIC = 0;
    const CERTIFICATIONPATH_RECERTIFICATION = 1;

    const ASSIGNMENT_TYPE_AUDIENCE = 1;
    const ASSIGNMENT_TYPE_USER = 2;

    const ASSIGNMENT_SAME_INSTANCE = 0;
    const ASSIGNMENT_OTHER_INSTANCE = 1;

    const COMPLETION_TYPE_ALL = 1;
    const COMPLETION_TYPE_ANY = 2;
    const COMPLETION_TYPE_SOME = 3;

    const NEXTOPERATOR_AND = 1;
    const NEXTOPERATOR_OR = 2;
    const NEXTOPERATOR_THEN = 3;

    const CERTIFICATION_EXPIRY_DATE = 1;
    const CERTIFICATION_COMPLETION_DATE = 2;

    const DUE_DATE_NOT_SET = 0;
    const DUE_DATE_FIXED = 1;
    const DUE_DATE_FROM_FIRST_LOGIN = 2;
    const DUE_DATE_FROM_ENROLMENT = 3;

    const DEFAULT_ACTIVEPERIODTIME = 3;
    const DEFAULT_ACTIVEPERIODTIMEUNIT = self::PERIOD_TIME_YEAR;
    const DEFAULT_WINDOWPERIOD = 1;
    const DEFAULT_WINDOWPERIODUNIT = self::PERIOD_TIME_MONTH;


    public $id;
    public $fullname;
    public $shortname;
    public $category;
    public $timecreated;
    public $timemodified;
    public $usermodified;
    public $activeperiodtime;
    public $activeperiodtimeunit;
    public $windowperiod;
    public $windowperiodunit;
    public $icon;
    public $summary;
    public $endnote;
    public $deleted;
    public $recertificationdatetype;
    public $visible;
    public $linkedtapscourseid;
    public $uservisible;
    public $reportvisible;

    public $certificationcoursesets;
    public $recertificationcoursesets;
    public $assignments;
    public $assignedusers;
    public $assignedcohorts;
    public $messages;


    function __construct($id = 0, $withassignments = true)
    {
        global $DB;

        $ceritification = $DB->get_record('certif', ['id' => $id]);
        if (!$ceritification) {
            return;
        }

        $this->id = $ceritification->id;
        $this->fullname = $ceritification->fullname;
        $this->shortname = $ceritification->shortname;
        $this->category = $ceritification->category;
        $this->timecreated = $ceritification->timecreated;
        $this->timemodified = $ceritification->timemodified;
        $this->usermodified = $ceritification->usermodified;
        $this->activeperiodtime = $ceritification->activeperiodtime;
        $this->activeperiodtimeunit = $ceritification->activeperiodtimeunit;
        $this->windowperiod = $ceritification->windowperiod;
        $this->windowperiodunit = $ceritification->windowperiodunit;
        $this->summary = $ceritification->summary;
        $this->endnote = $ceritification->endnote;
        $this->idnumber = $ceritification->idnumber;
        $this->deleted = $ceritification->deleted;
        $this->visible = $ceritification->visible;
        $this->recertificationdatetype = $ceritification->recertificationdatetype;
        $this->linkedtapscourseid = $ceritification->linkedtapscourseid;
        $this->uservisible = $ceritification->uservisible;
        $this->reportvisible = $ceritification->reportvisible;

        $coursesets = $this->get_coursesets();
        $this->certificationcoursesets = $coursesets['certification'];
        $this->recertificationcoursesets = $coursesets['recertification'];
        
        if($withassignments){
            $this->assignments = $this->get_assignments();
            $this->assignedusers = $this->get_assigned_users();
            $this->assignedcohorts = $this->get_assigned_cohorts();
        }
    }

    /**
     * Get attached files
     *
     * @return mixed
     */
    public function get_files(){
        $fs = get_file_storage();
        $files = $fs->get_area_files(\context_system::instance()->id, 'local_custom_certification', 'attachment', $this->id, 'filename', false);

        return $files;
    }

    /**
     * Add assignment record for individual or cohort assignment.
     * If assignment type is individual add immediately user to user_assignments table
     *
     * @param $certifid Certification ID
     * @param $assignmenttype Assignment type (individual - check const ASSIGNMENT_TYPE_USER,
     *                        or cohort - check consnt ASSIGNMENT_TYPE_CAUDIENCE)
     * @param $assignmenttypeid ID of assigned user or cohort
     * @return bool|int ID of certif_assignments table record
     */
    public static function add_assignment($certifid, $assignmenttype, $assignmenttypeid)
    {
        global $DB;
        $assignment = new \stdClass();
        $assignment->certifid = $certifid;
        $assignment->assignmenttype = $assignmenttype;
        $assignment->assignmenttypeid = $assignmenttypeid;
        $assignment->timecreated = time();
        $assignment->duedatetype = self::DUE_DATE_FROM_ENROLMENT;
        $assignment->duedateperiod = 0;
        $assignment->duedateunit = self::PERIOD_TIME_DAY;

        $certifassignmentid = $DB->insert_record(
            'certif_assignments', $assignment, true
        );

        if ($assignment->assignmenttype == self::ASSIGNMENT_TYPE_USER) {
            $users = self::get_users_from_assignment($certifassignmentid);
            foreach($users as $userid){
                self::add_user_to_assignment($userid, $certifid, $certifassignmentid);
            }
        }

        return $certifassignmentid;
    }


    /**
     * Add user to certification basing on assignment
     *
     * @param $userid User ID
     * @param $certifid Certification ID
     * @param $assignmentid Assignment ID
     * @return bool|int certif_user_assignment table record ID
     */
    public static function add_user_to_assignment($userid, $certifid, $assignmentid)
    {
        global $DB;

        $alreadyexists = $DB->get_record('certif_user_assignments', ['userid' => $userid, 'certifid' => $certifid]);
        if($alreadyexists){
            if(!$exists = $DB->get_record('certif_assignments', ['id' => $alreadyexists->assignmentid])){
                $alreadyexists->assignmentid = $assignmentid;
                $DB->update_record('certif_user_assignments', $alreadyexists);
                self::set_user_assignment_due_date($userid, $certifid);
            }
            $userassignmentid = $alreadyexists->id;
        }else{
            $record = new \stdClass();
            $record->userid = $userid;
            $record->certifid = $certifid;
            $record->assignmentid = $assignmentid;
            $record->timecreated = time();
            $userassignmentid = $DB->insert_record(
                'certif_user_assignments', $record, true
            );

            completion::check_completion($certifid, $userid);
            self::add_user_assignment_pair($userid, $assignmentid, $certifid);
            self::set_user_assignment_due_date($userid, $certifid);

            $context = \context_system::instance();
            $params = [
                'context' => $context,
                'objectid' => $userassignmentid,
                'other' => [
                    'userid' => $userid,
                    'certifid' => $certifid
                ]
            ];
            $event = \local_custom_certification\event\user_assignment_created::create($params);
            $event->trigger();

        }
        return $userassignmentid;
    }

    /**
     * Delete user-assignment pair from pivot table
     *
     * @param $userid
     * @param $assignmentid
     */
    public static function delete_user_assignment_pair($userid, $assignmentid){
        global $DB;
        $DB->delete_records('certif_assignments_users', ['userid' => $userid, 'assignmentid' => $assignmentid]);
    }

    /**
     * Add user-assignment pair to pivot table
     *
     * @param $userid
     * @param $assignmentid
     * @param $certifid
     */
    public static function add_user_assignment_pair($userid, $assignmentid, $certifid){
        global $DB;
        if(!$exists = $DB->get_record('certif_assignments_users', ['userid' => $userid, 'assignmentid' => $assignmentid, 'certifid' => $certifid])){
            $record = new \stdClass();
            $record->userid = $userid;
            $record->assignmentid = $assignmentid;
            $record->certifid = $certifid;
            $DB->insert_record('certif_assignments_users', $record);
            self::set_user_assignment_due_date($userid, $certifid);
        }
    }

    /**
     * Get all users basing on assignment.
     * If individual get signle user, if cohort get all users from cohort
     *
     * @param $assignmentid Assignment ID
     * @return array User's ID
     */
    public static function get_users_from_assignment($assignmentid)
    {
        global $DB;

        $assignment = $DB->get_record('certif_assignments', ['id' => $assignmentid]);

        if (!$assignment){
            return [];
        }
        switch ($assignment->assignmenttype) {
            case self::ASSIGNMENT_TYPE_AUDIENCE:
                $cohortmembersresult = \core_cohort_external::get_cohort_members([$assignment->assignmenttypeid]);
                $cohortmembers = array_shift($cohortmembersresult);
                $users = $cohortmembers['userids'];
                break;
            case self::ASSIGNMENT_TYPE_USER:
                $users[] = $assignment->assignmenttypeid;

                break;
        }
        return $users;
    }

    /**
     * Get all assignment for current certification
     *
     * @return array Records from assignments table
     */
    private function get_assignments()
    {
        global $DB;
        
        $assignments = $DB->get_records(
            'certif_assignments', ['certifid' => $this->id]
        );

        return $assignments;
    }

    /**
     * Return user assignment or false if not exists
     * @param $userid
     * @return bool|object
     */
    public function get_user_assignments($userid){
        global $DB;
        
        $sql = "
            SELECT
              cau.id, 
              cua.assignmentid, 
              ca.assignmenttypeid,
              ca.duedatetype, 
			  cua.timecreated as assignmenttime,
			  c.name as cohortname
            FROM {certif_user_assignments} cua
            JOIN {certif_assignments_users} cau ON cau.userid = cua.userid AND cau.certifid = cua.certifid
            JOIN {certif_assignments} ca ON ca.id = cau.assignmentid
            LEFT JOIN {cohort} c ON c.id = ca.assignmenttypeid AND ca.assignmenttype = :assignmenttype
            WHERE cua.userid = :userid
            AND cua.certifid = :certifid
            ORDER by ca.timecreated
        
        ";

        $params = [];
        $params['userid'] = $userid;
        $params['certifid'] = $this->id;
        $params['assignmenttype'] = self::ASSIGNMENT_TYPE_AUDIENCE;

        return $DB->get_records_sql($sql, $params);
       
    }

    /**
     * Get all users which are already added to certif_user_assignments table
     * for current certification
     *
     * @return array
     */
    private function get_assigned_users()
    {
        global $DB;
        $picturefields = \user_picture::fields('u');
        $query = "
			SELECT 
				DISTINCT 
				".$picturefields.",
				".$DB->sql_fullname('u.firstname', 'u.lastname')." as fullname,
				cua.assignmentid,
				cua.timecreated as assignmenttime,
				cua.duedate as duedate
			FROM {certif_user_assignments} cua 
			JOIN {user} u ON u.id = cua.userid
			WHERE cua.certifid = :certifid
		";

        $params['certifid'] = $this->id;
        $users = $DB->get_records_sql($query, $params);
        return $users;
    }


    /**
     * Get all coursesets (with courses) for currect assignments, divided into seperate array key basing on certification path
     *
     * @return array 'certification' key contains all coursesets for certificaiton path
     *               'recertification' key contains all coursesets for recertification path
     */
    private function get_coursesets()
    {
        global $DB;
        $certifications = [];
        $recertifications = [];

        $coursesets = $DB->get_records('certif_coursesets', ['certifid' => $this->id], 'sortorder ASC');
        $coursessql = "
            SELECT 
                ccc.id as id,
                ccc.courseid as courseid,
                ccc.coursesetid as coursesetid,
                ccc.certifid as certifid,
                ccc.sortorder as sortorder,
                c.fullname as fullname
            FROM {certif_courseset_courses} ccc
            LEFT JOIN {course} c ON c.id = ccc.courseid
            WHERE ccc.certifid=:certifid
            ORDER BY ccc.sortorder ASC
        ";
        $params['certifid'] = $this->id;
        $courses = $DB->get_records_sql($coursessql, $params);

        foreach ($coursesets as $courseset) {
            $courseset->courses = [];
            foreach ($courses as $course) {
                if ($course->coursesetid == $courseset->id) {
                    $courseset->courses[$course->courseid] = $course;
                }
            }
        }
        foreach ($coursesets as $courseset) {
            $courseset->certifpath == self::CERTIFICATIONPATH_RECERTIFICATION ?
                $recertifications[$courseset->id] = $courseset
                : $certifications[$courseset->id] = $courseset;
        }

        return (['certification' => $certifications,
            'recertification' => $recertifications]);
    }


    /**
     * Get all cohorts assigned to certification with number of members
     * 
     * @return array
     */
    private function get_assigned_cohorts()
    {
        global $DB;
        $sql
            = '
            SELECT 
                c.id,
                c.name,
                ca.id as assignmentid,
                (SELECT 
                  COUNT(1) 
                FROM {cohort_members} cm
                WHERE cm.cohortid = c.id
                ) as memberscount
            FROM {certif_assignments} ca
            LEFT JOIN {cohort} c ON c.id = ca.assignmenttypeid 
            WHERE 
              ca.assignmenttype = :typecohort AND
              ca.certifid = :certifid
            GROUP BY 
              c.id,
              c.name,
              ca.id
        ';

        $params['typecohort'] = self::ASSIGNMENT_TYPE_AUDIENCE;
        $params['certifid'] = $this->id;

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Set details of certification - add new if not exists
     * 
     * @param string $fullname Certification fullname
     * @param string $shortname Certification shortname
     * @param int $category Category ID - taken from course categories
     * @param string $summary Summary description
     * @param string $endnote Endnote description
     * @param string $idnumber 
     * @param int $visible Determine if certificaito is visible (1) or hidden (0)
     */
    public function set_details($fullname, $shortname, $category, $summary, $endnote, $idnumber, $visible, $linkedtapscourseid, $uservisible, $reportvisible)
    {
        global $DB, $USER;

        $this->fullname = $fullname;
        $this->shortname = $shortname;
        $this->category = $category;
        $this->summary = $summary;
        $this->endnote = $endnote;
        $this->idnumber = $idnumber;
        $this->timemodified = time();
        $this->usermodified = $USER->id;
        $this->visible = $visible;
        $this->deleted = 0;
        $this->linkedtapscourseid = $linkedtapscourseid;
        $this->uservisible = $uservisible;
        $this->reportvisible = $reportvisible;

        if ($this->id > 0) {
            $DB->update_record('certif', $this);
        } else {
            $this->timecreated = time();
            $this->activeperiodtime = self::DEFAULT_ACTIVEPERIODTIME;
            $this->activeperiodtimeunit = self::DEFAULT_ACTIVEPERIODTIMEUNIT;
            $this->windowperiod = self::DEFAULT_WINDOWPERIOD;
            $this->windowperiodunit = self::DEFAULT_WINDOWPERIODUNIT;
            $this->id = $DB->insert_record('certif', $this, true);
        }
    }

    /**
     * Set recertification expire time and when window opens
     * 
     * @param $recertificationdatetype How expire date will be calucalutaed:
     *                                  const CERTIFICATION_EXPIRY_DATE - basing on last expire date, or completion date if this is certificatio path
     *                                  const CERTIFICATION_COMPLETION_DATE - basing on completion date
     * @param $activeperiodtime number of days/months/years to calculate when certificaiton expires
     * @param $activeperiodtimeunit unit (days/months/years) (const: PERIOD_TIME_DAY, PERIOD_TIME_MONTH, PERIOD_TIME_YEAR)
     * @param $windowperiod number of days/months/years before time expire
     * @param $windowperiodunit unit (days/months/years) (const: PERIOD_TIME_DAY, PERIOD_TIME_MONTH, PERIOD_TIME_YEAR)
     */
    public function set_recertificationtimeperiod($recertificationdatetype, $activeperiodtime, $activeperiodtimeunit, $windowperiod, $windowperiodunit)
    {
        global $DB;

        $this->recertificationdatetype = $recertificationdatetype;
        $this->activeperiodtime = $activeperiodtime;
        $this->activeperiodtimeunit = $activeperiodtimeunit;
        $this->windowperiod = $windowperiod;
        $this->windowperiodunit = $windowperiodunit;

        $DB->update_record('certif', $this);
    }

    /**
     * TODO - refactor
     * Remove assignment record, all users who belongs to this assignment will be removed by cron task
     *
     * @param $certifid Certificatio ID
     * @param $assignmenttype Assignemnt type (const: ASSIGNMENT_TYPE_USER or ASSIGNMENT_TYPE_AUDIENCE)
     * @param $assignmenttypeid ID of assignment type (user id or cohort id
     * @return bool
     */
    public static function delete_assignment($certifid, $assignmenttype, $assignmenttypeid)
    {
        global $DB;
        $sql
            = "
            SELECT 
                ca.id,
                ca.assignmenttype
            FROM {certif_assignments} ca 
            WHERE 
                ca.certifid=:certifid AND 
                ca.assignmenttype = :assignmenttype AND 
                ca.assignmenttypeid = :assignmenttypeid;
        ";
        $params['certifid'] = $certifid;
        $params['assignmenttype'] = $assignmenttype;
        $params['assignmenttypeid'] = $assignmenttypeid;

        $certifassignment = $DB->get_record_sql($sql, $params);

        if (empty($certifassignment)) {
            return false;
        }

        $DB->delete_records(
            'certif_assignments', ['id' => $certifassignment->id]
        );
    }

    /**
     * Removes single course from courseset
     *
     * @param $coursesetid Courseset id
     * @param $courseid Course id
     */
    public static function delete_course_from_courseset($coursesetid, $courseid)
    {
        global $DB;

        $params['courseid'] = $courseid;
        $params['coursesetid'] = $coursesetid;

        $DB->delete_records('certif_courseset_courses', $params);
    }

    /**
     * Update courseset information
     *
     * @param $certifid Certification ID
     * @param $id
     * @param string $label
     * @param int $completiontype
     * @param int $mincourses
     * @param int $nextoperator
     */
    public static function update_courseset($certifid, $id, $label = 'Course Set', $completiontype = 0, $mincourses = 0, $nextoperator = 0)
    {
        global $DB;
        $record = new \stdClass();
        $record->id = $id;
        $record->label = $label;
        $record->certifid = $certifid;
        $record->completiontype = $completiontype;
        $record->mincourses = $mincourses;
        $record->nextoperator = $nextoperator;
        $DB->update_record('certif_coursesets', $record);
    }

    /**
     * Get all certifications
     *
     * @param array $filters array of filters (fullname, category, visible)
     * @return array certification list
     */
    public static function get_all($filters = [], $orderby = '')
    {
        global $DB;
        $params = [];
        $params['deleted'] = 0;
        $query = "SELECT 
              c.*,
              cc.name as categoryname
            FROM {certif} c
            JOIN {course_categories} cc ON cc.id = c.category
            WHERE c.deleted = :deleted
        ";

        if (isset($filters['fullname'])) {
            $query .= "AND ".$DB->sql_like('c.fullname', ":fullname", false, false)." ";
            $params['fullname'] = '%' . $filters['fullname'] . '%';
        }

        if (isset($filters['category']) && count($filters['category']) > 0) {
            list($inoreqaalquery, $inorequalparams) = $DB->get_in_or_equal($filters['category'], SQL_PARAMS_NAMED);
            $query .= "AND c.category " . $inoreqaalquery;
            $params += $inorequalparams;
        }
        
        if (isset($filters['id']) && count($filters['id']) > 0) {
            list($inoreqaalquery, $inorequalparams) = $DB->get_in_or_equal($filters['id'], SQL_PARAMS_NAMED);
            $query .= "AND c.id " . $inoreqaalquery;
            $params += $inorequalparams;
        }

        if (isset($filters['visible'])) {
            $query .= "AND c.visible = :visible ";
            $params['visible'] = $filters['visible'];
        }

        if (isset($filters['uservisible'])) {
            $query .= "AND c.uservisible = :uservisible ";
            $params['uservisible'] = $filters['uservisible'];
        }

        if (isset($filters['reportvisible'])) {
            $query .= "AND c.reportvisible = :reportvisible ";
            $params['reportvisible'] = $filters['reportvisible'];
        }
        
        $query .= $orderby;
        
        return $DB->get_records_sql($query, $params);
    }

    /**
     * Remove courseset with courses
     *
     * @param $coursesetid Coursesetid
     */
    public static function delete_courseset($coursesetid)
    {
        global $DB;

        $DB->delete_records('certif_courseset_courses', ['coursesetid' => $coursesetid]);
        $DB->delete_records('certif_coursesets', ['id' => $coursesetid]);
    }

    /**
     * Remove users from assignment
     *
     * @param $assignmentid Assignemnt ID
     * @param null $userid User id
     */
    public function delete_users_from_assignment($assignmentid, $userid = null)
    {
        global $DB;
        $params = [];
        $params['assignmentid'] = $assignmentid;
        if ($userid > 0) {
            $params['userid'] = $userid;
        }

        $users = $DB->get_records('certif_user_assignments', $params);
        $DB->delete_records('certif_user_assignments', $params);
        foreach ($users as $user) {
            self::delete_user_assignment_pair($user->userid, $assignmentid);
            $context = \context_system::instance();
            $params = [
                'context' => $context,
                'objectid' => null,
                'other' => [
                    'userid' => $user->userid,
                    'certifid' => $user->certifid
                ]
            ];
            $event = \local_custom_certification\event\user_assignment_deleted::create($params);
            $event->trigger();
        }
    }


    /**
     * Mark certification as deleted
     */
    public function delete()
    {
        global $DB;
        $this->deleted = 1;
        $DB->update_record('certif', $this);
    }

    /**
     * Add or update single message
     *
     * @param $id message type
     * @param $certifid Certification ID
     * @param $messagetype Message type (see types from message class)
     * @param $recipient - determine if message should be send to 3rd party
     * @param $recipientemail - 3rd party recipients (divided by ; )
     * @param $subject - message subject
     * @param $body - body
     * @param $triggertime - number of seconds before action, when message should be send (i.e. X days before duedate)
     */
    public static function set_message_details($id, $certifid, $messagetype, $recipient, $recipientemail, $subject, $body, $triggertime)
    {
        global $DB;
        $message = new \stdClass();
        $message->certifid = $certifid;
        $message->messagetype = $messagetype;
        $message->subject = $subject;
        $message->body = $body;
        $message->recipient = $recipient;
        $message->recipientemail = $recipientemail;
        $message->triggertime = $triggertime;
        if ($id > 0) {
            $message->id = $id;
            $DB->update_record('certif_messages', $message);
        } else {
            $DB->insert_record('certif_messages', $message);
        }
    }


    /**
     * Add or update courseset information
     *
     * @param $id Courseset ID - if this is update
     * @param $label Label
     * @param $certifid Certificatio ID
     * @param $sortorder Sortorder
     * @param $certifpath Determine if this is certfication path (const CERTIFICATIONPATH_BASIC) or recertificaiton (CERTIFICATIONPATH_RECERTIFICATION)
     * @param $completiontype Determine how many courses need to be completed to complete whole courseset
     *                      const COMPLETION_TYPE_ALL  - all courses
     *                      const COMPLETION_TYPE_ANY  - one course
     *                      const COMPLETION_TYPE_SOME - value passed by mincourses var
     * @param $mincourses number of courses which need to by completed if completiontype is COMPLETION_TYPE_SOME
     * @param $nextoperator Operator between coursesets
     *                      const NEXTOPERATOR_AND - this courseset and next one need to be completed to complete certificatoin
     *                      const NEXTOPERATOR_OR - this courseset or next one need to be completed to complete certificatoin
     *                      const NEXTOPERATOR_THEN - this and next courseset need to be completed, by first courseset need to be completed to start next one
     * @return bool|int ID of courseset
     */
    public static function set_courseset_details($id, $label, $certifid, $sortorder, $certifpath, $completiontype, $mincourses, $nextoperator)
    {
        global $DB;
        $courseset = $DB->get_record('certif_coursesets', ['id' => $id]);

        $record = new \stdClass();
        $record->label = $label;
        $record->certifid = $certifid;
        $record->sortorder = $sortorder;
        $record->certifpath = $certifpath;
        $record->completiontype = $completiontype;
        $record->mincourses = $mincourses;
        $record->nextoperator = $nextoperator;

        if (!empty($courseset)) {
            $record->id = $id;
            $DB->update_record('certif_coursesets', $record);
        } else {
            $id = $DB->insert_record('certif_coursesets', $record, true);
        }
        return $id;
    }

    /**
     * Add/update - course into courseset
     *
     * @param $courseid Course id
     * @param $coursesetid Courseset id
     * @param $certifid Certification ID
     * @param $sortorder Course order in courseset
     */
    public static function set_courseset_course_details($courseid, $coursesetid, $certifid, $sortorder)
    {
        global $DB;
        $params['courseid'] = $courseid;
        $params['coursesetid'] = $coursesetid;
        $params['certifid'] = $certifid;
        $course = $DB->get_record('certif_courseset_courses', $params);

        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->coursesetid = $coursesetid;
        $record->certifid = $certifid;
        $record->sortorder = $sortorder;

        if (!empty($course)) {
            $record->id = $course->id;
            $DB->update_record('certif_courseset_courses', $record);
        } else {
            $DB->insert_record('certif_courseset_courses', $record);
            //add enrol instance when course was added to courseset
            self::add_instance_for_certification($courseid, $certifid);
        }
    }

    /**
     * Adds enrolment instance of certification enrol plugin for course.
     * customint8 keeps certification id
     * @param $courseid
     * @param $certifid
     *
     * @throws \coding_exception
     */
    public static function add_instance_for_certification($courseid, $certifid)
    {
        global $DB;
        if (!$checkinstance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'certification', 'customint8' => $certifid], '*')) {
            $enrol = new \enrol_certification_plugin();
            $course = (object)['id' => $courseid];
            $fields = $enrol->get_instance_defaults();
            $fields['name'] = get_string('certificationenrolname', 'local_custom_certification', $certifid);
            $fields['customint8'] = $certifid;
            $enrol->add_instance($course, $fields);
        }
    }

    /**
     * Check if certification has recertification path
     *
     * @return bool
     */
    public function has_recertification(){
        if(count($this->recertificationcoursesets)){
            return true;
        }
        return false;
    }

    /**
     * Get all messages
     *
     * @return array
     */
    public function get_messages()
    {
        global $DB;
        return $DB->get_records('certif_messages', ['certifid' => $this->id]);
    }

    /**
     * Delete message
     *
     * @param $messageid
     */
    public static function delete_message($messageid)
    {
        global $DB;
        $DB->delete_records('certif_messages', ['id' => $messageid]);
    }

    /**
     * Create certication object for new certification
     *
     * @return static
     */
    public static function create()
    {
        $certification = new static();
        return $certification;
    }

    /**
     * Set due date for assignment and all users from assignment
     *
     * @param $assignmentid Assignment ID
     * @param $duedatetype How due date should be calucated
     *                   const DUE_DATE_NOT_SET
     *                   const DUE_DATE_FIXED
     *                   const DUE_DATE_FROM_FIRST_LOGIN ( X days from first login)
     *                   const DUE_DATE_FROM_ENROLMENT ( X days from enrolment day)
     * @param $dateperiod numbers of days/months/years to calucalte duedate
     * @param $duedateunit (const: PERIOD_TIME_DAY, PERIOD_TIME_MONTH, PERIOD_TIME_YEAR)
     * @return bool
     */
    public static function set_due_date_details($assignmentid, $duedatetype, $dateperiod, $duedateunit)
    {
        global $DB;
        $assignment = $DB->get_record('certif_assignments', ['id' => $assignmentid]);
        if (empty($assignment)) {
            return false;
        }

        $assignment->duedatetype = $duedatetype;
        $assignment->duedateperiod = $dateperiod;
        $assignment->duedateunit = $duedateunit;
        $DB->update_record('certif_assignments', $assignment);

        if ($assignment->assignmenttype == self::ASSIGNMENT_TYPE_USER) {
            self::set_user_assignment_due_date($assignment->assignmenttypeid, $assignment->certifid);
        } elseif ($assignment->assignmenttype == self::ASSIGNMENT_TYPE_AUDIENCE) {
            $cohortusers = self::get_assigned_cohort_members_details($assignment->certifid, $assignment->id);
            foreach ($cohortusers as $cohortuser) {
                if ($cohortuser->exist == self::ASSIGNMENT_SAME_INSTANCE) {
                    self::set_user_assignment_due_date($cohortuser->id, $assignment->certifid);
                }
            }
        }
    }

    /**
     * Get time periods days/months/years
     *
     * @return array
     */
    public static function get_time_periods()
    {
        $timeperiods = [];
        $timeperiods[self::PERIOD_TIME_DAY] = get_string('timeperiodday', 'local_custom_certification');
        $timeperiods[self::PERIOD_TIME_MONTH] = get_string('timeperiodmonth', 'local_custom_certification');
        $timeperiods[self::PERIOD_TIME_YEAR] = get_string('timeperiodyear', 'local_custom_certification');
        return $timeperiods;
    }

    /**
     * Get time period (days, moths, yers) in accordance with DateInterval
     *
     * @param $timeunit (const: PERIOD_TIME_DAY, PERIOD_TIME_MONTH, PERIOD_TIME_YEAR)
     * @return string
     */
    public static function get_time_period_for_interval($timeunit)
    {
        switch ($timeunit) {
            case self::PERIOD_TIME_DAY:
                return 'D';
                break;
            case self::PERIOD_TIME_MONTH:
                return 'M';
                break;
            case self::PERIOD_TIME_YEAR:
                return 'Y';
                break;
            default:
                return 'D';
                break;
        }
    }

    /**
     * Get all categories from course categories
     *
     * @return array
     */
    public static function get_categories($parent = 0)
    {
        global $DB;
        $params = ['visible' => 1];
        if (!$parent) {
            $where = '1=1';
        } else {
            $where = $DB->sql_like('path', ':path');
            $params['path'] = "%/{$parent}/%";
        }
        return $DB->get_records_select_menu('course_categories', $where, $params, 'sortorder ASC', 'id, name');
    }


    /**
     * Create new certification basing on other, set visible to false
     *
     * @param $certifid Certification ID - certificaiton which will be used as template
     * @return certification new certification
     */
    public static function copy($certifid)
    {
        $certification = new self($certifid);
        $new = self::create();
        $new->set_details($certification->fullname, $certification->shortname, $certification->category,
            $certification->summary, $certification->endnote, $certification->idnumber, 0, $certification->linkedtapscourseid, $certification->uservisible, $certification->reportvisible);
        $new->set_recertificationtimeperiod($certification->recertificationdatetype, $certification->activeperiodtime, $certification->activeperiodtimeunit,
            $certification->windowperiod, $certification->windowperiodunit);
        $coursesets = $certification->certificationcoursesets;
        $coursesets += $certification->recertificationcoursesets;
        foreach ($coursesets as $courseset) {
            $coursesetid = self::set_courseset_details(
                0,
                $courseset->label,
                $new->id,
                $courseset->sortorder,
                $courseset->certifpath,
                $courseset->completiontype,
                $courseset->mincourses,
                $courseset->nextoperator
            );

            foreach ($courseset->courses as $course) {
                self::set_courseset_course_details(
                    $course->courseid,
                    $coursesetid,
                    $new->id,
                    $course->sortorder
                );
            }
        }

        foreach ($certification->assignments as $assignment){
            self::add_assignment($new->id, $assignment->assignmenttype, $assignment->assignmenttypeid);
        }

        foreach ($certification->get_messages() as $message) {
            $new->set_message_details(
                0,
                $new->id,
                $message->messagetype,
                $message->recipient,
                $message->recipientemail,
                $message->subject,
                $message->body,
                $message->triggertime
            );
        }

        return $new;
    }

    /**
     * Get lists (with details) of users assigned by cohort
     *
     * @param $certifid Certification ID
     * @param $assignmentid Assignment ID
     * @return array
     */
    public static function get_assigned_cohort_members_details($certifid, $assignmentid)
    {
        global $DB;

        $assignment = $DB->get_record(
            'certif_assignments', ['id' => $assignmentid]
        );

        $fields = get_all_user_name_fields(true, 'u');

        $sql = "
                SELECT
                    u.id,
                    cua.id as userassignmentid,
                    cua.assignmentid as assignmentid,
                    cua.duedate as duedate,
                    u.firstname,
                    u.lastname,
                    ".$fields."
                FROM {cohort_members} cm
                JOIN {user} u ON u.id = cm.userid 
                LEFT JOIN {certif_user_assignments} cua ON cua.userid = cm.userid AND cua.certifid = :certifid
                WHERE cm.cohortid = :cohortid
                ";


        $params = [];
        $params['certifid'] = $certifid;
        $params['cohortid'] = $assignment->assignmenttypeid;

        $users = $DB->get_records_sql($sql, $params);

        foreach ($users as $user) {
            if ($user->assignmentid != $assignmentid) {
                $user->exist = self::ASSIGNMENT_OTHER_INSTANCE;
            } else {
                $user->exist = self::ASSIGNMENT_SAME_INSTANCE;
            }
        }

        return $users;
    }

    /**
     * Calculate due date for single user
     *
     * @param $userid Userid
     * @param $certifid Certificaiton ID
     */
    public static function set_user_assignment_due_date($userid, $certifid)
    {
        global $DB;
        $sql = "
            SELECT
                cau.id, 
                cua.id as userassignmentid,
                ca.id as assignmentid,
                ca.duedatetype,
                ca.duedateperiod,
                ca.duedateunit,
                ca.timecreated
            FROM {certif_user_assignments} cua
            LEFT JOIN {certif_assignments_users} cau ON cau.userid = cua.userid AND cau.certifid = cua.certifid
            LEFT JOIN {certif_assignments} ca ON ca.id = cau.assignmentid AND ca.certifid = cua.certifid 
            WHERE 
                cua.userid = :userid AND 
                cua.certifid = :certifid
            ORDER by ca.timecreated
        ";
        $params['certifid'] = $certifid;
        $params['userid'] = $userid;

        $assignments = $DB->get_records_sql($sql, $params);
        $duration = self::get_time_periods();
        $duedate = 0;
        $optional = 1;
        $assignmentid = 0;
        foreach($assignments as $assignment){
            $duedatetmp = 0;
            if ($assignment->duedatetype == self::DUE_DATE_FIXED) {
                $duedatetmp = $assignment->duedateperiod;
                $optional = 0;
            } elseif ($assignment->duedatetype == self::DUE_DATE_FROM_FIRST_LOGIN) {
                $userfirstaccess = $DB->get_field('user', 'firstaccess', ['id' => $userid]);
                if ($userfirstaccess > 0) {
                    $date = new \DateTime(date('m/d/Y H:i:s', $userfirstaccess));
                    $date->modify('+' . $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit]);
                    $duedatetmp = $date->getTimestamp();
                }
                $optional = 0;
            } elseif ($assignment->duedatetype == self::DUE_DATE_FROM_ENROLMENT) {
                $date = new \DateTime(date('m/d/Y H:i:s', $assignment->timecreated));
                $date->modify('+' . $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit]);
                $duedatetmp = $date->getTimestamp();
                $optional = 0;
            }
            if($assignmentid == 0){
                $assignmentid = $assignment->assignmentid;
            }
            if($duedatetmp != 0){
                if($duedate == 0){
                    $duedate = $duedatetmp;
                    $assignmentid = $assignment->assignmentid;
                }elseif($duedatetmp < $duedate){
                    $assignmentid = $assignment->assignmentid;
                    $duedate = $duedatetmp;
                }
            }
        }
        $userassignment = new \stdClass();
        $userassignment->id = array_values($assignments)[0]->userassignmentid;
        $userassignment->assignmentid = $assignmentid;
        $userassignment->duedate = $duedate;
        $userassignment->optional = $optional;
        $DB->update_record('certif_user_assignments', $userassignment);
        completion::calculate_duedate($certifid, $userid);
    }
}