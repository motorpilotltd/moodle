<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Description of delegate_list
 *
 * @author paulstanyer
 * @author Simon Lewis
 */
class delegate_list {
    const BASE_URL = '/local/delegatelist/index.php';
    const CLASSES_IN_LAST = 365;

    private $_taps;

    private $_context;
    private $_course;
    private $_tapsenrolcm;
    private $_tapscourseid;
    private $_classes;
    private $_activeclass;

    private $_hascapability = false; // No capability by default

    private $_filters;

    private $_function;

    private $_statuses = array(
        'requested',
        'waitlisted',
        'placed',
        'attended',
        'cancelled',
    );

    private $_capabilities = array(
        'manager',
        'teacher',
        'student',
    );

    private $_functions = array(
        'display',
        'print',
        'download'
    );
    
    public function __construct($course, $context, $classid = 0, $function = 'display') {
        global $DB;
        
        $this->_course = $course;
        $this->_context = $context;

        $tapsenrol = $DB->get_record('tapsenrol', array('course' => $this->_course->id));
        
        if (empty($tapsenrol)) {
            throw new moodle_exception('error:nottapscourse', 'local_delegatelist');
        }

        $this->_tapsenrolcm = get_coursemodule_from_instance('tapsenrol', $tapsenrol->id);
        $this->_tapscourseid = $tapsenrol->tapscourse;

        $this->_taps = new \local_taps\taps();

        $this->_set_capabilities();

        $this->_set_classes();
        
        if (array_key_exists($classid, $this->_classes)) {
            $this->set_active_class($classid);
        } else {
            $this->set_active_class($this->find_active_class());
        }

        if (in_array($function, $this->_functions)) {
            $this->_function = $function;
        } else {
            $this->_function = 'display';
        }
    }

    public function get_function() {
        return $this->_function;
    }

    protected function _set_capabilities() {
        foreach ($this->_capabilities as $capname) {
            $hascap = has_capability("local/delegatelist:{$capname}view", $this->_context);
            if ($hascap) {
                $this->_hascapability = $capname;
                break;
            }
        }

        if (!$this->_hascapability) {
            throw new moodle_exception('error:nocapability', 'local_delegatelist');
        }
    }

    public function has_capability() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if ($this->_hascapability == $arg) {
                return true;
            }
        }
        return false;
    }

    protected function _set_classes() {
        global $DB, $USER;

        $classesinlast = get_config('local_delegatelist', 'classesinlast');
        if ($classesinlast === false) {
            $classesinlast = self::CLASSES_IN_LAST;
        }

        $where = 'courseid = :courseid';
        $params = array('courseid' => $this->_tapscourseid);
        if ($classesinlast && !$this->has_capability('manager')) {
            $where .= ' AND (classendtime > :classendtime OR classendtime = 0)';
            $params['classendtime'] = strtotime("{$classesinlast} days ago");
        }

        if ($this->has_capability('student')) {
            $where .= ' AND staffid = :staffid';
            $params['staffid'] = $USER->idnumber;

            $statuses = array_merge($this->_taps->get_statuses('placed'), $this->_taps->get_statuses('attended'));
            if (!empty($statuses)) {
                list($in, $inparams) = $DB->get_in_or_equal(
                    $statuses,
                    SQL_PARAMS_NAMED, 'status'
                );
                $compare = $DB->sql_compare_text('bookingstatus');
                $where .= " AND {$compare} {$in}";
                $params = array_merge($params, $inparams);
            }
            
            $activeclasses = array();
        } else {
            $activewhere = "{$where} AND (classhidden = 0 OR classhidden IS NULL) AND (archived = 0 OR archived IS NULL)";
            $activeclasses = $DB->get_records_select('local_taps_class', $activewhere, $params, '', 'classid, classname, classendtime');
        }

        $classname = $DB->sql_compare_text('classname', 32);
        $classtype = $DB->sql_compare_text('classtype', 32);
        // This may not be DB agnostic... Tested against MSSQL
        $sql = <<<EOS
SELECT
    classid, MAX({$classname}) as classname, MAX(classendtime) as classendtime, MAX({$classtype}) as classtype
FROM
    {local_taps_enrolment}
WHERE
    {$where}
    AND active = 1
    AND (archived = 0 OR archived IS NULL)
GROUP BY
    classid
EOS;
        $extraclasses = $DB->get_records_sql($sql, $params);

        $classes = $activeclasses + $extraclasses;

        usort($classes, function($a, $b) {
            if ($a->classendtime == $b->classendtime) {
                return 0;
            } elseif ($a->classendtime == 0) {
                return -1;
            } elseif ($b->classendtime == 0) {
                return 1;
            } elseif ($a->classendtime > $b->classendtime) {
                return -1;
            } else {
                return 1;
            }
        });

        foreach ($classes as $class) {
            if ($this->has_capability('student') && $this->_taps->is_classtype($class->classtype, 'elearning')) {
                continue;
            }
            $this->_classes[$class->classid] = new stdClass();
            $this->_classes[$class->classid]->classname = $class->classname;
            $this->_classes[$class->classid]->classendtime = $class->classendtime;
        }

        if (empty($this->_classes)) {
            throw new moodle_exception('error:noclasses', 'local_delegatelist');
        }
    }
    
    public function find_active_class() {
        global $USER, $DB;

        if ($this->has_capability('student')) {
            $staffid = $USER->idnumber;
        }

        list($in, $params) = $DB->get_in_or_equal(
            array_keys($this->_classes),
            SQL_PARAMS_NAMED, 'classid'
        );
        $compare = $DB->sql_compare_text('classid');

        $where = "active = 1 AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}";

        if (isset($staffid)) {
            $where .= ' AND staffid = :staffid';
            $params['staffid'] = $staffid;
        }

        $enrolments = $DB->get_records_select(
            'local_taps_enrolment',
            $where,
            $params,
            '',
            'DISTINCT classid'
        );

        if (empty($enrolments)) {
            return null;
        }

        $now = time();
        $return = null;
        foreach ($this->_classes as $classid => $class) {
            if (array_key_exists($classid, $enrolments) && ($class->classendtime > $now || $class->classendtime == 0)) {
                $return = $classid;
            }
        }
        
        return $return;
    }
    
    public function set_active_class($classid) {
        global $DB;

        if (empty($classid)) {
            return;
        }

        $this->_activeclass = $this->_taps->get_class_by_id($classid);

        if (!$this->_activeclass) {
            $this->_activeclass = new stdClass();
            $this->_activeclass->classid = $classid;
            $this->_activeclass->classname = isset($this->_classes[$classid]) ? $this->_classes[$classid]->classname : '';
            $this->_activeclass->classduration = 0;
            $this->_activeclass->classdurationunits = '';
        }
    }

    public function get_active_class() {
        return $this->_activeclass;
    }
    
    public function get_course_name() {
        return $this->_course->fullname;
    }
    
    public function get_class_menu() {
        $options = array();
        foreach ($this->_classes as $classid => $class) {
            $options[$classid] = $class->classname;
        }
        return $options;
    }
    
    public function get_class_dates() {
        if (!isset($this->_activeclass) || !isset($this->_activeclass->classstarttime)) {
            return;
        }
        
        if (empty($this->_activeclass->classstarttime)) {
            return get_string('tbc', 'local_delegatelist');
        }

        try {
            $timezone = new DateTimeZone($this->_activeclass->usedtimezone);
        } catch (Exception $e){
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $startdatetime = new DateTime(null, $timezone);
        $startdatetime->setTimestamp($this->_activeclass->classstarttime);
        $enddatetime = new DateTime(null, $timezone);
        $enddatetime->setTimestamp($this->_activeclass->classendtime);

        $startformat = '';
        $endformat = '';
        $starttz = false;

        if ($this->_activeclass->classstartdate == $this->_activeclass->classstarttime) {
            $startformat = 'd M Y';
        } else {
            $startformat = 'd M Y H:i';
            $starttz = true;
        }

        if ($enddatetime->format('Ymd') > $startdatetime->format('Ymd')) {
            if ($this->_activeclass->classenddate == $this->_activeclass->classendtime) {
                $endformat = 'd M Y';
            } else {
                $endformat = 'd M Y H:i T';
                $starttz = false;
            }
        } elseif ($this->_activeclass->classenddate != $this->_activeclass->classendtime) {
            $endformat = 'H:i T';
            $starttz = false;
        }

        if ($starttz) {
            $startformat .= ' T';
        }
        $date = $startdatetime->format($startformat);
        if ($endformat) {
            $date .= ' ' . get_string('to', 'local_delegatelist') . ' ';
            $date .= $enddatetime->format($endformat);
        }
        // Show UTC as GMT for clarity
        return str_replace('UTC', 'GMT', $date);
    }
    
    public function get_class_duration() {
        if (!isset($this->_activeclass) || !isset($this->_activeclass->classduration)) {
            return;
        }

        if (!empty($this->_activeclass->classduration)) {
            return (float) $this->_activeclass->classduration . ' ' . $this->_activeclass->classdurationunits;
        } else {
            return '-';
        }
    }

    public function get_class_location() {
        return !empty($this->_activeclass->location)? $this->_activeclass->location : '';
    }

    public function get_class_trainingcenter() {
        return !empty($this->_activeclass->trainingcenter)? $this->_activeclass->trainingcenter : '';
    }

    /**
     * Returns a list for select
     */
    public function get_statuses_list() {
        $options = array();
        foreach ($this->_statuses as $type) {
            $options[$type] = get_string('filter:'.$type, 'local_delegatelist');
        }
        return $options;
    }
    
    public function get_url($ignore='') {
        $params = array('contextid'=>$this->_context->id);
        if (!empty($this->_activeclass) && $ignore !== 'classid') {
            $params['classid'] = $this->_activeclass->classid;
        }
        foreach (optional_param_array('filters', array(), PARAM_ALPHA) as $filter => $value) {
            if ($filter===$ignore) {
                continue;
            }
            $params["filters[$filter]"] = $value;
        }
        $baseurl = $this->_function === 'print' ? str_replace('index.php', 'print.php', self::BASE_URL) : self::BASE_URL;
        return new moodle_url($baseurl, $params);
    }
    
    public function get_list_table() {
        if (empty($this->_activeclass)) {
            return;
        }
        
        require_once dirname(__FILE__) . '/delegatetable.php';
        
        $table = new delegatetable('delegate_list', $this, $this->_function);
        $usernamefields = get_all_user_name_fields(true, 'u');
        $fields = "lte.*, u.id as userid, {$usernamefields}, u.picture,"
                . 'u.idnumber as imagealt, u.phone1, u.department, u.icq, u.address,'
                . 'u.email, tit.sponsorfirstname, tit.sponsorlastname, tit.sponsoremail, tit.timeenrolled, ua.timeaccess';

        $from = <<<EOF
    {local_taps_enrolment} lte
JOIN
    {user} u
    ON u.idnumber = lte.staffid
LEFT JOIN
    {user_lastaccess} ua
    ON ua.userid = u.id
    AND ua.courseid = :courseid
LEFT JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
EOF;
        $where = 'lte.classid = :classid AND (lte.archived = 0 OR lte.archived IS NULL)';

        $params = array(
            'courseid' => $this->_course->id,
            'classid' => $this->_activeclass->classid
        );

        $this->_process_filters($where, $params);

        $table->set_sql($fields, $from, $where, $params);
        $table->text_sorting('lastname');
        $table->initialise();
        
        $table->sortable(true, 'lastname', SORT_ASC);

        $table->is_collapsible = false;

        $table->useridfield = 'userid';

        if ($this->_function == 'display') {
            $table->is_downloadable(true);
            $table->show_download_buttons_at(array(TABLE_P_BOTTOM));
        }

        return $table;

    }

    public function get_filters() {
        if(!isset($this->_filters)) {
            $this->_filters = optional_param_array('filters', array(), PARAM_ALPHANUMEXT);
            if ($this->has_capability('student') || $this->_function == 'print') {
                $this->_filters['bookingstatus'] = 'placed_attended';
            }
        }
        return $this->_filters;
    }
    
    protected function _process_filters(&$where, &$params) {
        global $DB;

        foreach ($this->get_filters() as $filter => $value) {
            if ($filter == 'bookingstatus') {
                $actualvalue = array();
                foreach (explode('_', $value) as $type) {
                    $statuses = $this->_taps->get_statuses($type);
                    if (!empty($statuses)) {
                        $actualvalue = array_merge($actualvalue, $statuses);
                    }
                }
            } else {
                $actualvalue = $value;
            }
            
            if (empty($actualvalue)) {
                continue;
            }

            list($in, $inparams) = $DB->get_in_or_equal(
                $actualvalue,
                SQL_PARAMS_NAMED, $filter
            );
            $compare = $DB->sql_compare_text($filter);

            $where .= " AND {$compare} {$in}";
            $params = array_merge($params, $inparams);
        }
    }

    public function get_taps_completion_mod() {
        $tapscomp = get_coursemodules_in_course('tapscompletion', $this->_course->id);
        if (!empty($tapscomp)) {
            return array_shift($tapscomp);
        }
        return array();
    }
    
    public function get_completion_url() {
        return new moodle_url('/report/progress/index.php', array('course'=>$this->_course->id));
    }
    
    public function get_attendance_url($tapscompletion) {
        return new moodle_url('/mod/tapscompletion/view.php', array('id' => $tapscompletion->id));
    }

    public function get_print_url() {
        return $this->get_url('bookingstatus');
    }

    public function get_status_type_string($status) {
        $return = new stdClass();
        $return->type = $this->_taps->get_status_type($status);
        if (!$return->type) {
            $return->type = 'unknown';
        }
        $return->string = get_string_manager()->string_exists("type:{$return->type}", 'local_delegatelist') ?
            get_string("type:{$return->type}", 'local_delegatelist', $status) : get_string('type:unknown', 'local_delegatelist');
        return $return;
    }

    public function get_manage_enrolments_url() {
        if (!has_capability('mod/tapsenrol:manageenrolments', context_module::instance($this->_tapsenrolcm->id))) {
            return false;
        }
        return new moodle_url(
                '/mod/tapsenrol/manage_enrolments.php',
                ['id' => $this->_tapsenrolcm->id, 'classid' => $this->_activeclass->classid]);
    }
}
