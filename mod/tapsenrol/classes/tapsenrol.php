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

defined('MOODLE_INTERNAL') || die();

class tapsenrol {
    public $cm;
    public $course;
    public $context;
    public $tapsenrol;
    public $iw;

    public $taps;

    public $statistics;

    protected $_cfg;

    protected $_completion;
    protected $_cancomplete;
    protected $_is_complete;

    protected $_selfenrolinstance;
    protected $_enrolself = false;
    protected $_isenrolled = array();

    protected $_coursegroups;

    protected $_tapscourse;
    protected $_tapsclasses = array();

    public function __construct($id, $type = 'cm', $courseid = 0) {
        global $SESSION;

        $this->taps = new \local_taps\taps();
        if (!isset($SESSION->tapsenrol)) {
            $SESSION->tapsenrol = new stdClass();
        }
        $this->context = new stdClass();

        $this->statistics = new stdClass();
        $this->statistics->nonmoodleusers = 0;
        $this->statistics->enrolled = 0;
        $this->statistics->notenrolled = 0;

        switch ($type) {
            case 'cm' :
                $this->_set_cm($id, $type, $courseid);
                $this->_set_course($this->cm->course);
                $this->_set_tapsenrol($this->cm->instance);
                break;
            case 'instance' :
                $this->_set_tapsenrol($id);
                $this->_set_course($this->tapsenrol->course);
                $this->_set_cm($this->tapsenrol->id, 'instance', $this->course->id);
                break;
        }

        $this->_cfg = new stdClass();
    }

    protected function _set_cm($id, $type, $courseid) {
        switch ($type) {
            case 'cm' :
                $this->cm = get_coursemodule_from_id('tapsenrol', $id, $courseid);
                break;
            case 'instance' :
                $this->cm = get_coursemodule_from_instance('tapsenrol', $id, $courseid);
                break;
            default :
                $this->cm = false;
                break;
        }

        if (!$this->cm) {
            print_error('invalidcoursemodule');
        }

        $this->context->cm = context_module::instance($this->cm->id);
    }

    protected function _set_course($id) {
        global $DB;
        $this->course = $DB->get_record('course', array('id' => $id));

        if (!$this->course) {
            print_error('coursemisconf');
        }

        $this->context->course = context_course::instance($this->course->id);
    }

    protected function _set_tapsenrol($id) {
        global $DB;
        $this->tapsenrol = $DB->get_record('tapsenrol', array('id' => $id));

        if (!$this->tapsenrol) {
            print_error('invalidcoursemodule');
        }

        if ($this->tapsenrol->internalworkflowid) {
            $this->iw = $DB->get_record('tapsenrol_iw', array('id' => $this->tapsenrol->internalworkflowid));
        }
    }

    public function check_installation() {
        global $DB;

        // Check for arupadvert.

        if (!$DB->get_record('modules', array('name' => 'arupadvert'))) {
            // Arup advert not installed.
            return $this->_mark_broken_instance();
        } else {
            $arupadvert = $DB->get_record('arupadvert', array('course' => $this->course->id));
            if (!$arupadvert) {
                // No Arup advert in this course.
                return $this->_mark_broken_instance();
            } else if ($arupadvert->datatype != 'taps') {
                // Arup advert is not a TAPS one.
                return $this->_mark_broken_instance();
            }
        }

        // Check enrolment plugins.

        // Must be manual, self (not auto), guest.
        $self = new stdClass();
        $self->field = 'customchar1';
        $self->logic = 'notequal';
        $self->value = 'y';
        $requiredenrolments = array(
            'manual' => array(),
            'self' => array($self),
            'guest' => array(),
        );
        $enrolinstances = enrol_get_instances($this->course->id, true);
        if (count($enrolinstances) != count($requiredenrolments)) {
            return $this->_mark_broken_instance();
        }
        foreach ($requiredenrolments as $enrolplugin => $requirements) {
            $current = array_shift($enrolinstances);
            if ($current->enrol != $enrolplugin) {
                return $this->_mark_broken_instance();
            } else {
                foreach ($requirements as $requirement) {
                    switch ($requirement->logic) {
                        case 'notequal' :
                            if (!($current->{$requirement->field} != $requirement->value)) {
                                return $this->_mark_broken_instance();
                            }
                            break;
                    }
                }
            }
        }

        // Check groups.

        $course = $DB->get_record('course', array('id' => $this->course->id));
        if ($course) {
            if ($course->groupmode != 2 || $course->defaultgroupingid != 0) {
                return $this->_mark_broken_instance();
            }
        }

        // Check instances.

        $sql = "SELECT COUNT(*)
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE m.name = :modulename AND cm.course = :courseid
            ";
        $params = array(
            'modulename' => 'tapsenrol',
            'courseid' => $this->course->id
        );
        $instancecount = $DB->get_field_sql($sql, $params);

        if ($instancecount > 1) {
            return $this->_mark_broken_instance();
        }

        return $this->_mark_ok_instance();
    }

    protected function _mark_broken_instance() {
        global $DB;
        $this->tapsenrol->instanceok = false;
        $DB->update_record('tapsenrol', $this->tapsenrol);
        return $this->tapsenrol->instanceok;
    }

    protected function _mark_ok_instance() {
        global $DB;
        $this->tapsenrol->instanceok = true;
        $DB->update_record('tapsenrol', $this->tapsenrol);
        return $this->tapsenrol->instanceok;
    }

    public function get_tapsclasses($canview = true) {
        if (!isset($this->_tapsclasses[$this->tapsenrol->tapscourse]) && $canview) {
            $this->_tapsclasses[$this->tapsenrol->tapscourse] = $this->taps->get_course_classes($this->tapsenrol->tapscourse, false, false);
            $now = time();
            foreach ($this->_tapsclasses[$this->tapsenrol->tapscourse] as $index => $class) {
                $classstartdateok =
                        $class->classstatus == 'Planned'
                        || $this->taps->is_classtype($class->classtype, 'elearning')
                        || $class->classstarttime > $now;
                $enrolmentstartdateok = $class->enrolmentstartdate < $now;
                $enrolmentenddateok = $class->enrolmentenddate > $now || $class->enrolmentenddate == 0;
                if (!$classstartdateok || !$enrolmentenddateok || !$enrolmentstartdateok) {
                    unset($this->_tapsclasses[$this->tapsenrol->tapscourse][$index]);
                }
            }
            // Sort in ascending date ordering with planned classes last.
            usort(
                $this->_tapsclasses[$this->tapsenrol->tapscourse],
                function($a, $b) {
                    if ($a->classstatus == 'Planned') {
                        return +1;
                    } else if ($b->classstatus == 'Planned') {
                        return -1;
                    }
                    if ($a->classstarttime == $b->classstarttime) {
                        return 0;
                    }
                    return ($a->classstarttime > $b->classstarttime) ? +1 : -1;
                }
            );
        }

        return isset($this->_tapsclasses[$this->tapsenrol->tapscourse]) ? $this->_tapsclasses[$this->tapsenrol->tapscourse] : [];
    }

    public function get_tapscourse() {
        if (!isset($this->_tapscourse)) {
            $this->_tapscourse = $this->taps->get_course_by_id($this->tapsenrol->tapscourse);
        }

        return $this->_tapscourse;
    }

    /**
     * Processes employee enrolment.
     *
     * @global \moodle_database $DB
     * @param int $classid
     * @param string $staffid
     * @param bool $approve
     * @return \stdClass
     */
    public function enrol_employee($classid, $staffid, $approved = false) {
        $result = $this->taps->enrol($classid, $staffid, $approved);
        $result->message = '';

        $a = new stdClass();

        if ($result->success) {
            $this->enrolment_check($staffid, false);
            $classtype = $this->taps->get_classtype_type($result->enrolment->classtype);
            $statustype = $this->taps->get_status_type($result->enrolment->bookingstatus);
            if (!$classtype || !get_string_manager()->string_exists('status:'.$classtype.':'.$statustype, 'tapsenrol')) {
                $a->message = get_string('enrol:error:unavailable', 'tapsenrol');
            } else {
                $a->message = get_string('status:'.$classtype.':'.$statustype, 'tapsenrol');
            }
            $a->classname = $result->enrolment->classname;
            $a->coursename = $result->enrolment->coursename;
            $result->message = get_string('enrol:alert:success', 'tapsenrol', $a);
        } else {
            $class = $this->taps->get_class_by_id($classid);
            $a->classname = empty($class->classname) ? '?' : $class->classname;
            $a->coursename = empty($class->coursename) ? '?' : $class->coursename;
            $a->message = $result->status;
            $result->message = get_string('enrol:alert:error', 'tapsenrol', $a);
        }

        return $result;
    }

    public function cancel_enrolment($enrolmentid, $status = 'Cancelled') {
        $allowedstatuses = ['Cancelled', 'No Show'];
        if (!in_array($status, $allowedstatuses)) {
            $status = 'Cancelled';
        }
        $return = new stdClass();
        $return->success = false;
        $return->status = '';
        $return->message = '';

        $enrolment = $this->taps->get_enrolment_by_id($enrolmentid);

        if (!$enrolment) {
            $return->status = 'INVALID_ENROLMENT';
            $return->message = get_string('cancel:error:enrolmentdoesnotexist', 'tapsenrol');
            return $return;
        }

        $result = $this->taps->set_status($enrolment->enrolmentid, $status);
        $result->message = '';

        $a = new stdClass();
        $a->classname = $enrolment->classname;
        $a->coursename = $enrolment->coursename;

        if ($result->success) {
            $this->enrolment_check($enrolment->staffid, false);
            $result->message = get_string('cancel:alert:success', 'tapsenrol', $a);
        } else {
            $a->message = $result->status;
            $result->message = get_string('cancel:alert:error', 'tapsenrol', $a);
        }

        return $result;
    }

    public function enrolment_check($staffid, $redirect = false) {
        global $CFG, $DB, $PAGE, $SESSION, $USER;

        // Cheapest checks first.
        if ($USER->idnumber == $staffid) {
            $user = $USER;
        } else {
            $user = $DB->get_record('user', array('idnumber' => $staffid));
        }

        if (!$user) {
            $this->statistics->nonmoodleusers++;
            return;
        }

        // Now things get more expensive.
        require_once($CFG->dirroot.'/group/lib.php');

        if (empty($this->_completion)) {
            $this->_set_completion_data();
        }

        if (empty($this->_selfenrolinstance)) {
            $this->_set_enrol_data();
        }

        $enrolments = $this->taps->get_enroled_classes($user->idnumber, $this->tapsenrol->tapscourse, true, false);

        $shouldbeenrolled = false;
        $shouldbecomplete = false;
        $isactive = false;
        $isattended = false;
        $iscancelled = false;
        $groupsforuser = array();
        foreach ($enrolments as $enrolment) {
            $enrolmentstatustype = $this->taps->get_status_type($enrolment->bookingstatus);
            switch ($enrolmentstatustype) {
                case 'placed' :
                    $isactive = true;
                    $shouldbeenrolled = true;
                    $shouldbecomplete = true;
                    $groupsforuser[] = trim($enrolment->classname);
                    break;
                case 'requested' :
                    $isactive = true;
                    break;
                case 'waitlisted' :
                    $isactive = true;
                    $shouldbeenrolled = true;
                    $groupsforuser[] = trim($enrolment->classname);
                    break;
                case 'attended' :
                    // Enrolled, activity complete.
                    $isattended = true;
                    $shouldbeenrolled = true;
                    $shouldbecomplete = true;
                    $groupsforuser[] = trim($enrolment->classname);
                    break;
                case 'cancelled' :
                    $iscancelled = true;
                    if (!$isactive && !$isattended) {
                        $shouldbeenrolled = false;
                        $shouldbecomplete = false;
                    }
                    break;
            }
        }

        if ($shouldbeenrolled) {
            $this->statistics->enrolled++;
        } else {
            $this->statistics->notenrolled++;
        }

        if ($shouldbeenrolled && !isset($this->_isenrolled[$user->id])) {
            // Enrol.
            if ($this->_enrolself) {
                $timestart = time();
                if ($this->_selfenrolinstance->enrolperiod) {
                    $timeend = $timestart + $this->_selfenrolinstance->enrolperiod;
                } else {
                    $timeend = 0;
                }
                $this->_enrolself->enrol_user($this->_selfenrolinstance, $user->id, $this->_selfenrolinstance->roleid, $timestart, $timeend);
                $this->_isenrolled[$user->id] = true;
            }
        } else if (!$shouldbeenrolled && isset($this->_isenrolled[$user->id])) {
            // Un-enrol.
            if ($this->_enrolself) {
                $this->_enrolself->unenrol_user($this->_selfenrolinstance, $user->id);
                unset($this->_isenrolled[$user->id]);
            }
        }

        if (isset($this->_isenrolled[$user->id])) {
            role_assign(
                $this->_selfenrolinstance->roleid,
                $user->id,
                $this->context->course
            );
        } else {
            role_unassign(
                $this->_selfenrolinstance->roleid,
                $user->id,
                $this->context->course->id // Error if passing context as you do in role_assign().
            );
        }

        if (empty($this->_coursegroups)) {
            $this->_set_group_data();
        }

        $usergroups = groups_get_all_groups($this->course->id, $user->id);

        foreach ($usergroups as $usergroup) {
            $groupkey = array_search($usergroup->name, $groupsforuser);
            if ($groupkey === false) {
                // Remove member.
                groups_remove_member($usergroup, $user);
            } else {
                // They're in the group, remove it from list to avoid further editing.
                unset($groupsforuser[$groupkey]);
            }
        }

        foreach ($groupsforuser as $groupforuser) {
            // Does group exist?
            $groupkey = array_search($groupforuser, $this->_coursegroups);
            if ($groupkey) {
                $group = groups_get_group($groupkey);
            } else {
                // Create group if it doesn't exist.
                $group = new stdClass();
                $group->courseid = $this->course->id;
                $group->name = $groupforuser;
                $group->description = html_writer::tag('p', "Group for linked course class: $groupforuser");
                $group->descriptionformat = FORMAT_HTML;
                $group->enrolmentkey = '';
                $group->picture = 0;
                $group->hidepicture = 0;
                $group->timecreated = time();
                $group->timemodified = $group->timecreated;
                $group->idnumber = '';
                $group->id = groups_create_group($group);
                if ($group->id) {
                    $this->_coursegroups[$group->id] = $group->name;
                }
            }

            // Add user to group if we have a valid group id.
            if (!empty($group->id)) {
                groups_add_member($group, $user);
            }
        }

        if ($this->_cancomplete) {
            $iscomplete = isset($this->_iscomplete[$user->id]) && $this->_iscomplete[$user->id];
            if ($shouldbecomplete && !$iscomplete) {
                // Complete.
                $this->_set_internal_completion($user->id, true);
                $this->_completion->update_state($this->cm, COMPLETION_COMPLETE, $user->id);
                $redirect = $redirect && true;
            } else if (!$shouldbecomplete && $iscomplete) {
                // Incomplete.
                $this->_set_internal_completion($user->id, false);
                $this->_completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->id);
                $redirect = $redirect && true;
            } else {
                $redirect = false;
            }

            $now = time();
            if ($redirect && (!isset($SESSION->tapsenrol->redirected) || $SESSION->tapsenrol->redirected < $now - 600)) {
                $renderer = $PAGE->get_renderer('mod_tapsenrol');
                $a = new stdClass();
                $a->course = core_text::strtolower(get_string('course'));
                $redirecturl = new moodle_url('/course/view.php', array('id' => $this->course->id));
                $a->link = html_writer::link($redirecturl, get_string('redirectmessage:onupdatelink', 'tapsenrol', $a));
                $SESSION->tapsenrol->redirected = time();
                $PAGE->requires->js_function_call('document.location.replace', array($redirecturl->out(false)), false, 0);
                return $renderer->alert(get_string('redirectmessage:onupdate', 'tapsenrol', $a), 'alert-warning', false);
            }
        }

        // Only unsets if not redirected to avoid a loop.
        unset($SESSION->tapsenrol->redirected);
        return '';
    }

    public function trigger_workflow($enrolmentid, $formdata) {
        global $DB;

        list($in, $inparams) = $DB->get_in_or_equal(
            $this->taps->get_statuses('requested'),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');
        $params = array(
            'enrolmentid' => $enrolmentid,
        );
        $sql = <<<EOS
SELECT
    lte.*
FROM
    {local_taps_enrolment} lte
LEFT JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
WHERE
    lte.enrolmentid = :enrolmentid
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
    AND tit.id IS NULL
EOS;
        $enrolment = $DB->get_record_sql(
            $sql,
            array_merge($params, $inparams)
        );

        if ($enrolment) {
            $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
            if ($user) {
                // Setup tracking.
                $iwtrack = new stdClass();
                $iwtrack->enrolmentid = $enrolment->enrolmentid;
                $iwtrack->sponsoremail = strtolower($formdata->sponsoremail);
                $iwtrack->sponsorfirstname = $formdata->sponsorfirstname;
                $iwtrack->sponsorlastname = $formdata->sponsorlastname;
                $iwtrack->requestcomments = $formdata->comments;
                $iwtrack->timeenrolled = time();
                $iwtrack->timemodified = $iwtrack->timeenrolled;
                $iwtrack->timecreated = $iwtrack->timeenrolled;
                $iwtrack->id = $DB->insert_record('tapsenrol_iw_tracking', $iwtrack);

                // Send confirmation to applicant.
                $data = array(
                    'applicant' => $this->_applicant_data($user),
                    'sponsor' => $this->_sponsor_data($iwtrack),
                    'class' => $this->_class_data($enrolment),
                    'cancelurl' => $this->_cancel_url($enrolment),
                );
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('awaiting_approval', $data, $this->iw->approvalrequired);
                $this->_send_email($user, $from, $subject, $emailhtml, $emailtext);

                // Send request to approver.
                $approver = $DB->get_record('user', array('email' => $iwtrack->sponsoremail));
                if (!$approver) {
                    $approver = $this->_get_sponsor($iwtrack);
                }
                try {
                    $approvertimezone = new DateTimeZone($approver->timezone);
                } catch (Exception $e) {
                    $approvertimezone = new DateTimeZone(date_default_timezone_get());
                }
                unset($data['cancelurl']);
                $data['approveurls'] = $this->_approve_urls($enrolment);
                $data['comments:applicant'] = $iwtrack->requestcomments;
                $approvebytimestamp = $iwtrack->timeenrolled + $this->iw->cancelafter;
                if ($this->taps->is_classtype($enrolment->classtype, 'classroom')) {
                    // Need approval before auto cancellation prior to class start.
                    $classstarttime = $enrolment->classstarttime - $this->iw->cancelbefore;
                    if ($approvebytimestamp > $classstarttime && $enrolment->classstarttime != 0) {
                        $approvebytimestamp = $classstarttime;
                    }
                } else {
                    // Need approval before class ends.
                    if ($approvebytimestamp > $enrolment->classendtime && $enrolment->classendtime != 0) {
                        $approvebytimestamp = $enrolment->classendtime;
                    }
                }
                $approvebytime = new DateTime(null, $approvertimezone);
                $approvebytime->setTimestamp($approvebytimestamp);
                $data['approvebydate'] = $approvebytime->format('H:i T, d M Y');
                // Show UTC as GMT for clarity.
                $data['approvebydate'] = str_replace('UTC', 'GMT', $data['approvebydate']);

                list($from2, $subject2, $emailhtml2, $emailtext2) = $this->_get_email('approval_request', $data, $this->iw->approvalrequired);
                $this->_send_email($approver, $from2, $subject2, $emailhtml2, $emailtext2);

                unset($data['approveurls']);
                unset($data['comments:applicant']);
                unset($data['approvebydate']);
                $this->_check_region_mismatch($user, $data);
            } else {
                // User not found.
                error_log("tapsenrol::trigger_workflow()\n\tUser with staff ID {$enrolment->staffid} not found.\n\tEnrolment ID: {$enrolmentid}\n"
                        . "\tSponsor: {$formdata->sponsorfirstname} {$formdata->sponsorlastname} ({$formdata->sponsoremail})");
            }
        } else {
            // Enrolment not found/already tracking.
            error_log("tapsenrol::trigger_workflow()\n\tEnrolment ID {$enrolmentid} not found/wrong status/already tracking.\n"
                    . "\tUser: {$enrolment->staffid}\n\tSponsor: {$formdata->sponsorfirstname} {$formdata->sponsorlastname} ({$formdata->sponsoremail})");
        }
    }

    public function trigger_workflow_no_approval($enrolmentid, $formdata) {
        global $DB;

        $message = null;

        list($in, $inparams) = $DB->get_in_or_equal(
            $this->taps->get_statuses('requested'),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');
        $params = array(
            'enrolmentid' => $enrolmentid,
        );
        $sql = <<<EOS
SELECT
    lte.*
FROM
    {local_taps_enrolment} lte
LEFT JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
WHERE
    lte.enrolmentid = :enrolmentid
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
    AND tit.id IS NULL
EOS;
        $enrolment = $DB->get_record_sql(
            $sql,
            array_merge($params, $inparams)
        );

        if ($enrolment) {
            $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
            if ($user) {
                // Setup tracking.
                $iwtrack = new stdClass();
                $iwtrack->enrolmentid = $enrolment->enrolmentid;
                $iwtrack->sponsoremail = strtolower($formdata->sponsoremail);
                $iwtrack->sponsorfirstname = $formdata->sponsorfirstname;
                $iwtrack->sponsorlastname = $formdata->sponsorlastname;
                $iwtrack->requestcomments = $formdata->comments;
                $iwtrack->timeenrolled = time();
                $iwtrack->timemodified = $iwtrack->timeenrolled;
                $iwtrack->timecreated = $iwtrack->timeenrolled;
                $iwtrack->id = $DB->insert_record('tapsenrol_iw_tracking', $iwtrack);

                $this->approve_workflow($iwtrack, $enrolment, $user, new stdClass());

                // Email data.
                $data = array(
                    'applicant' => $this->_applicant_data($user),
                    'sponsor' => $this->_sponsor_data($iwtrack),
                    'class' => $this->_class_data($enrolment)
                );

                $this->_check_region_mismatch($user, $data);

                $a = new stdClass();
                $a->classname = $enrolment->classname;
                $a->coursename = $enrolment->coursename;
                $classtype = $this->taps->get_classtype_type($enrolment->classtype);
                $statustype = $this->taps->get_status_type($this->taps->get_enrolment_status($enrolment->enrolmentid));
                if (!$classtype || !$statustype || !get_string_manager()->string_exists('status:'.$classtype.':'.$statustype, 'tapsenrol')) {
                    $a->message = get_string('enrol:error:unavailable', 'tapsenrol');
                } else {
                    $a->message = get_string('status:'.$classtype.':'.$statustype, 'tapsenrol');
                }
                $message = get_string('enrol:alert:success', 'tapsenrol', $a);
            } else {
                // User not found.
                error_log("tapsenrol::trigger_workflow_no_approval()\n\tUser with staff ID {$enrolment->staffid} not found.\n"
                        . "\tEnrolment ID: {$enrolmentid}\n\tSponsor: {$formdata->sponsorfirstname} {$formdata->sponsorlastname} ({$formdata->sponsoremail})");
            }
        } else {
            // Enrolment not found/already tracking.
            error_log("tapsenrol::trigger_workflow_no_approval()\n\tEnrolment ID {$enrolmentid} not found/wrong status/already tracking.\n"
                    . "\tUser: {$enrolment->staffid}\n\tSponsor: {$formdata->sponsorfirstname} {$formdata->sponsorlastname} ({$formdata->sponsoremail})");
        }

        return $message;
    }

    protected function _check_region_mismatch($user, $data) {
        global $CFG, $DB;

        // Load user region data.
        require_once($CFG->dirroot.'/local/regions/lib.php');
        local_regions_load_data_user($user);

        // Check for region mismatch.
        if (!empty($user->regions_field_geotapsregionid)) {
            $userregion = $DB->get_field('local_regions_reg', 'name', array('id' => $user->regions_field_geotapsregionid));
            if ($userregion && $user->regions_field_geotapsregionid != $this->iw->regionid) {
                list($unusedfrom, $subject3, $emailhtml3, $emailtext3) = $this->_get_email('region_mismatch', $data, $this->iw->approvalrequired);
                unset($unusedfrom);
                $to = tapsenrol_user::get_dummy_tapsenrol_user('moodle@arup.com', 'Moodle');
                $from = tapsenrol_user::get_dummy_tapsenrol_user('moodle_notifications@arup.com', 'Moodle', 'Notifications');
                $this->_send_email($to, $from, "[[R:{$userregion}]] [[W:{$this->iw->name}]] {$subject3}", $emailhtml3, $emailtext3);
            }
        }

    }

    public function approve_workflow($iwtrack, $enrolment, $user, $formdata) {
        global $DB;

       // Data array for populating with email text replacements.
        $data = array();

        // Users to CC.
        $cc = array();

        // Are we approving or rejecting?
        $approve = isset($formdata->reject) ? false : true;

        if ($approve) {
            // Grab class status.
            $class = $this->taps->get_class_by_id($enrolment->classid);

            if (empty($iwtrack->approved) && $this->iw->enroltype == 'apply') {
                $status = 'W:Wait Listed';
                $email = 'approved_waitlist_apply';
            } else if ($class && $class->classstatus == 'Planned') {
                $status = 'W:Wait Listed';
                $email = 'approved_waitlist_planned';
            } else {
                $status = 'Approved Place';
                $email = 'approved';
            }

            $iwtrack->approved = 1;
            $a = get_string('approve:approved', 'tapsenrol');
            $data['cancelurl'] = $this->_cancel_url($enrolment);
        } else {
            $status = 'Cancelled';
            $email = 'rejected';

            $iwtrack->approved = 0;
            $a = get_string('approve:rejected', 'tapsenrol');
            if (isset($formdata->comments)) {
                $data['comments:approver'] = $iwtrack->approvecomments = $formdata->comments;
            } else {
                $data['comments:approver'] = '-';
            }
        }

        $result = $this->taps->set_status($enrolment->enrolmentid, $status);

        if (!$result->success) {
            $result->message = 'FAILED: '.$result->status;
            $result->type = 'alert-danger';
            return $result;
        } else if ($result->status === 'CLASS_FULL') {
            $email = 'class_full';
        }

        $iwtrack->timeapproved = time();
        $iwtrack->timemodified = $iwtrack->timeapproved;
        $DB->update_record('tapsenrol_iw_tracking', $iwtrack);

        $this->enrolment_check($enrolment->staffid, false);

        // Email.
        if ($this->iw->approvalrequired && ($email == 'class_full' || $email == 'rejected')) {
            $cc[] = $this->_get_sponsor($iwtrack);
        }
        $data['class'] = $this->_class_data($enrolment);
        $data['applicant'] = $this->_applicant_data($user);
        $data['sponsor'] = $this->_sponsor_data($iwtrack);
        list($from, $subject, $emailhtml, $emailtext) = $this->_get_email($email, $data, $this->iw->approvalrequired);
        $this->_send_email($user, $from, $subject, $emailhtml, $emailtext, $cc);

        if ($email == 'approved' && $status == 'Approved Place' && $this->taps->is_classtype($enrolment->classtype, 'classroom')) {
            // Only send invite for 'Approved Place' statuses on classroom based class, i.e. not to users on waiting lists/elearning.
            $data['update:extrainfo'] = ''; // Not updating.
            list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('approved_invite', $data, $this->iw->approvalrequired);
            $enrolment->trainingcenter = empty($enrolment->trainingcenter) ? $data['class']['classtrainingcenter'] : $enrolment->trainingcenter;
            $this->_send_invite($user, $from, $enrolment, $subject, $emailtext, $emailhtml);
        }

        $other = array(
            'enrolmentid' => $enrolment->enrolmentid,
            'staffid' => $enrolment->staffid,
            'classid' => $enrolment->classid,
            'status' => $status,
            'email' => $email,
        );
        if ($approve) {
            $event = \mod_tapsenrol\event\enrolment_request_approved::create(array(
                'objectid' => $iwtrack->id,
                'other' => $other,
                'context' => $this->context->cm,
            ));
        } else {
            $event = \mod_tapsenrol\event\enrolment_request_rejected::create(array(
                'objectid' => $iwtrack->id,
                'other' => $other,
                'context' => $this->context->cm,
            ));
        }
        $event->trigger();

        $result->message = get_string('approve:thankyou', 'tapsenrol', $a);
        $result->type = 'alert-success';
        return $result;
    }

    public function cancel_workflow($enrolment, $comments, $email = 'cancellation') {
        global $DB;

        $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
        $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolment->enrolmentid));
        if ($user && $iwtrack) {
            $iwtrack->cancelcomments = $comments;
            $iwtrack->timecancelled = time();
            $iwtrack->timemodified = $iwtrack->timecancelled;
            $DB->update_record('tapsenrol_iw_tracking', $iwtrack);

            if ($email) {
                // Email.
                $cc = array();
                if ($this->iw->approvalrequired) {
                    $cc[] = $this->_get_sponsor($iwtrack);
                }
                $data = array(
                    'applicant' => $this->_applicant_data($user),
                    'sponsor' => $this->_sponsor_data($iwtrack),
                    'class' => $this->_class_data($enrolment),
                );
                switch ($email) {
                    case 'cancellation':
                        $data['comments:cancellation'] = $comments ? $comments : '-';
                        break;
                    case 'cancellation_admin':
                        $data['admin'] = $this->_admin_user_data();
                        break;
                    case 'cancelled':
                        $data['time:cancelledafter'] = $this->iw->cancelafter / (24 * 60 * 60) . ' days';
                        break;
                    case 'cancelled_classstart':
                        $data['time:cancelledbefore'] = $this->iw->cancelbefore / (60 * 60) . ' hours';
                        break;
                }
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email($email, $data, $this->iw->approvalrequired);
                $this->_send_email($user, $from, $subject, $emailhtml, $emailtext, $cc);
                // Need to also send a cancellation if an invite was sent.
                // Also necessary to be separate as copied email to sponsor.
                if (0 === strpos($email, 'cancellation')
                        && $this->taps->is_status($enrolment->bookingstatus, 'placed')
                        && $this->taps->is_classtype($enrolment->classtype, 'classroom')) {
                    // Only send if original bookingstatus was 'placed' and a classroom based class, i.e. not on a waiting list/elearning.
                    list($from, $subject, $emailhtml, $emailtext) = $this->_get_email($email.'_invite', $data, $this->iw->approvalrequired);
                    $enrolment->trainingcenter = empty($enrolment->trainingcenter) ? $data['class']['classtrainingcenter'] : $enrolment->trainingcenter;
                    $this->_send_invite($user, $from, $enrolment, $subject, $emailtext, $emailhtml, 'CANCEL');
                }
            }
        }
    }

    public function move_workflow($enrolment, $user, $class, $targetclass, $resendemail) {
        global $DB, $USER;
        
        $time = time();

        // Load tracking record, if exists.
        $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolment->enrolmentid));

        // Move enrolment to new class (update relevant fields).
        // Clone new enrolment as need old info too.
        $enrolmentnew = clone($enrolment);
        // Update new enrolment.
        // Only need to do class fields as not moving between courses.
        $enrolmentnew->classid = $targetclass->classid;
        $enrolmentnew->classname = $targetclass->classname;
        $enrolmentnew->location = $targetclass->location;
        $enrolmentnew->trainingcenter = $targetclass->trainingcenter;
        $enrolmentnew->classtype = $targetclass->classtype;
        // classcategory : Don't have at present.
        $enrolmentnew->classstartdate = $targetclass->classstartdate;
        $enrolmentnew->classstarttime = $targetclass->classstarttime;
        $enrolmentnew->classenddate = $targetclass->classenddate;
        $enrolmentnew->classendtime = $targetclass->classendtime;
        $enrolmentnew->duration = $targetclass->classduration;
        $enrolmentnew->durationunits = $targetclass->classdurationunits;
        $enrolmentnew->durationunitscode = $targetclass->classdurationunitscode;
        $enrolmentnew->provider = $targetclass->classsuppliername;
        $enrolmentnew->classcost = $targetclass->classcost;
        $enrolmentnew->classcostcurrency = $targetclass->classcostcurrency;
        $enrolmentnew->price = $targetclass->price;
        $enrolmentnew->pricebasis = $targetclass->pricebasis;
        $enrolmentnew->currencycode = $targetclass->currencycode;
        $enrolmentnew->timezone = $targetclass->timezone;
        $enrolmentnew->usedtimezone = $targetclass->usedtimezone;
        $enrolmentnew->timemodified = $time;

        // Update booking status depending on status of old/new class.
        if ($class->classstatus == 'Normal' && $targetclass->classstatus == 'Planned'
            && $this->taps->get_status_type($enrolment->bookingstatus) == 'placed') {
            // Need to move to waitlisted.
            $enrolmentnew->bookingstatus = 'W:Wait Listed';
        }
        if ($class->classstatus == 'Planned' && $targetclass->classstatus == 'Normal'
            && $this->taps->get_status_type($enrolment->bookingstatus) == 'waitlisted') {
            // Need to move to placed.
            $enrolmentnew->bookingstatus = 'Approved Place';
        }
        if (!$iwtrack && $this->taps->get_status_type($enrolment->bookingstatus) == 'requested') {
            // If requested and not tracked update booking status as we don't have legitimate approver.
            $enrolmentnew->bookingstatus = ($targetclass->classstatus == 'Normal' ? 'Approved Place' : 'W:Wait Listed');
        }

        $DB->update_record('local_taps_enrolment', $enrolmentnew);

        // If approved cancel original invite (if resend emails on).
        // Send relevant emails (if resend emails on), i.e. approval required or invite

        $approvalrequest = false;
        $notificationemail = false;
        $cancelinvite = false;
        $enrolment->moved = 0;
        $enrolmentnew->moved = 0;
        // Update/insert tracking.
        if ($iwtrack) {
            // Tracking record indicates emails will have been sent.
            // Beeds time updates, indication of movement as enrolment carried over to new class and remindersent update.
            $enrolment->moved = $iwtrack->moved;
            $enrolmentnew->moved = ++$iwtrack->moved;
            $iwtrack->timeenrolled = $time; // Indicates when moved, useful for approval requests.
            $iwtrack->timemodified = $time;
            if ($iwtrack->remindersent > 1) {
                $iwtrack->remindersent = 1;
            }
            $DB->update_record('tapsenrol_iw_tracking', $iwtrack);

            // Do we need to send a new approval request?
            if ($this->iw->approvalrequired &&$this->taps->is_status($enrolmentnew->bookingstatus, 'requested')) {
                $approvalrequest = true;
            }

            // Do we need a notifcation email?
            if ($this->iw->approvalrequired ||
                        !($this->taps->is_status($enrolmentnew->bookingstatus, 'placed')
                            && $this->taps->is_classtype($enrolmentnew->classtype, 'classroom'))) {
                $notificationemail = true;
            }

            // Do we need to cancel an old invite?
            if ($this->taps->is_status($enrolment->bookingstatus, 'placed')
                    && $this->taps->is_classtype($enrolment->classtype, 'classroom')) {
                $cancelinvite = true;
            }
        } else {
            // No tracking, need to set it up!
            // Sponsor will be down as (current) admin user if approval required.
            // Setup tracking.
            $iwtrack = new stdClass();
            $iwtrack->enrolmentid = $enrolment->enrolmentid;
            $iwtrack->sponsoremail = $this->iw->approvalrequired ? $USER->email : '';
            $iwtrack->sponsorfirstname = $this->iw->approvalrequired ? $USER->firstname : '';
            $iwtrack->sponsorlastname = $this->iw->approvalrequired ? $USER->lastname : '';
            $iwtrack->requestcomments = '';
            $iwtrack->moved = 0;
            $iwtrack->timeenrolled = $time;
            $iwtrack->timemodified = $time;
            $iwtrack->timecreated = $time;
            // If requested then automatically approve (as we don't have legitimate sponsor details).
            if ($this->taps->get_status_type($enrolment->bookingstatus) == 'requested') {
                $iwtrack->approved = 1;
                $iwtrack->timeapproved = $time;
                // New enrolment record already updated.
            }

            $iwtrack->id = $DB->insert_record('tapsenrol_iw_tracking', $iwtrack);
        }

        // Send emails if requested to.
        if ($resendemail) {
            $cc = array();
            if ($this->iw->approvalrequired) {
                $cc[] = $this->_get_sponsor($iwtrack);
            }
            $data = array(
                'applicant' => $this->_applicant_data($user),
                'sponsor' => $this->_sponsor_data($iwtrack),
                'class:old' => $this->_class_data($enrolment, true),
                'class' => $this->_class_data($enrolmentnew),
                'admin' => $this->_admin_user_data(),
            );

            // Send notification if required.
            if ($notificationemail) {
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('moved', $data, $this->iw->approvalrequired);
                $this->_send_email($user, $from, $subject, $emailhtml, $emailtext, $cc);
            }

            // Cancel old invite if needed.
            if ($cancelinvite) {
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('moved_cancel_invite', $data, $this->iw->approvalrequired);
                $enrolment->trainingcenter = empty($enrolment->trainingcenter) ? $data['class:old']['classtrainingcenter:old'] : $enrolment->trainingcenter;
                $this->_send_invite($user, $from, $enrolment, $subject, $emailtext, $emailhtml, 'CANCEL');
            }

            // Send new invite if needed.
            if ($this->taps->is_status($enrolmentnew->bookingstatus, 'placed')
                    && $this->taps->is_classtype($enrolmentnew->classtype, 'classroom')) {
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('moved_new_invite', $data, $this->iw->approvalrequired);
                $this->_send_invite($user, $from, $enrolmentnew, $subject, $emailtext, $emailhtml);
            }

            // Send a new approval request if required.
            // Do this last due to changing $data array.
            if ($approvalrequest) {
                // Send email to user.
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('awaiting_approval', $data, $this->iw->approvalrequired);
                $this->_send_email($user, $from, $subject, $emailhtml, $emailtext);

                // Send request to approver.
                $approver = $DB->get_record('user', array('email' => $iwtrack->sponsoremail));
                if (!$approver) {
                    $approver = $this->_get_sponsor($iwtrack);
                }
                try {
                    $approvertimezone = new DateTimeZone($approver->timezone);
                } catch (Exception $e) {
                    $approvertimezone = new DateTimeZone(date_default_timezone_get());
                }
                unset($data['cancelurl']);
                $data['approveurls'] = $this->_approve_urls($enrolmentnew);
                $data['comments:applicant'] = $iwtrack->requestcomments;
                $approvebytimestamp = $iwtrack->timeenrolled + $this->iw->cancelafter;
                if ($this->taps->is_classtype($enrolmentnew->classtype, 'classroom')) {
                    // Need approval before auto cancellation prior to class start.
                    $classstarttime = $enrolmentnew->classstarttime - $this->iw->cancelbefore;
                    if ($approvebytimestamp > $classstarttime && $enrolmentnew->classstarttime != 0) {
                        $approvebytimestamp = $classstarttime;
                    }
                } else {
                    // Need approval before class ends.
                    if ($approvebytimestamp > $enrolmentnew->classendtime && $enrolmentnew->classendtime != 0) {
                        $approvebytimestamp = $enrolmentnew->classendtime;
                    }
                }
                $approvebytime = new DateTime(null, $approvertimezone);
                $approvebytime->setTimestamp($approvebytimestamp);
                $data['approvebydate'] = $approvebytime->format('H:i T, d M Y');
                // Show UTC as GMT for clarity.
                $data['approvebydate'] = str_replace('UTC', 'GMT', $data['approvebydate']);

                list($from2, $subject2, $emailhtml2, $emailtext2) = $this->_get_email('approval_request', $data, $this->iw->approvalrequired);
                $this->_send_email($approver, $from2, $subject2, $emailhtml2, $emailtext2);
            }
        }

        // Run an enrolment check to update groups.
        $this->enrolment_check($enrolmentnew->staffid, false);
        
        return true;
    }

    public function delete_enrolment($enrolment, $user, $class, $sendemail) {
        global $DB;

        $time = time();

        // Set up required vars.
        $statustype = $this->taps->get_status_type($enrolment->bookingstatus);
        $classtype = $this->taps->get_classtype_type($class->classtype);
        if ($class->classstarttime > $time) {
            $datetype = 'future';
        } else if ($class->classendtime > $time || $class->classendtime == 0) {
            $datetype = 'current';
        } else {
            $datetype = 'past';
        }

        // Double check it's a valid deletion.
        // Classroom/Elearning, Planned, All - requested/waitlisted/cancelled.
        // Classroom/Elearning, Normal, Future - not attended (should never be this anyway).
        // Others any - but attended mod/tapsenrol:deleteattendedenrolments cap only.
        if ($class->classstatus == 'Planned' && !in_array($statustype, ['requested', 'waitlisted', 'cancelled'])) {
            return false;
        }
        if ($statustype == 'attended' && !has_capability('mod/tapsenrol:deleteattendedenrolments', $this->context->cm)) {
            return false;
        }

        // Load tracking record, if exists.
        $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolment->enrolmentid));

        $enrolment->archived = 1;
        $enrolment->timemodified = $time;
        $DB->update_record('local_taps_enrolment', $enrolment);

        $notificationemail = false;
        $cancelinvite = false;
        // Update/insert tracking.
        if ($iwtrack) {
            // Tracking record indicates emails will have been sent.
            $iwtrack->timemodified = $time;
            $DB->update_record('tapsenrol_iw_tracking', $iwtrack);

            $data = array(
                'applicant' => $this->_applicant_data($user),
                'sponsor' => $this->_sponsor_data($iwtrack),
                'class' => $this->_class_data($enrolment),
                'admin' => $this->_admin_user_data(),
            );

            // Do we need a notifcation email?
            // Planned Current/Future [requested/waitlisted/cancelled, approver if requested]
            // Elearning/Normal Current [requested/waitlisted/cancelled - approver if requested]
            // Classroom/Normal Future [requested/waitlisted, approver if requested]
            // Elearning/Normal Future [requested/waitlisted/cancelled, approver if requested]
            if ($sendemail && in_array($datetype, ['current', 'future'])) {
                switch ($statustype) {
                    case 'requested':
                    case 'waitlisted':
                        $notificationemail = true;
                        break;
                    case 'cancelled':
                        $notificationemail = !($class->classstatus == 'Normal' && $classtype == 'classroom' && $datetype == 'future');
                        break;
                }
                $notificationemail = true;
            }

            $cc = array();
            // Send notification if required.
            if ($notificationemail) {
                if ($this->iw->approvalrequired && $statustype == 'requested') {
                    $cc[] = $this->_get_sponsor($iwtrack);
                }
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('deleted', $data, $this->iw->approvalrequired);
                $this->_send_email($user, $from, $subject, $emailhtml, $emailtext, $cc);
            }

            // Do we need to cancel an invite?
            // Classroom/Normal Current [placed]
            // Elearning/Normal Current [placed]
            // Classroom/Normal Future [placed]
            // Elearning/Normal Future [placed]
            if ($statustype == 'placed' && in_array($datetype, ['current', 'future'])) {
                $cancelinvite = true;
            }

            // Cancel old invite if needed.
            if ($cancelinvite) {
                list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('deleted_cancel_invite', $data, $this->iw->approvalrequired);
                $enrolment->trainingcenter = empty($enrolment->trainingcenter) ? $data['class']['classtrainingcenter'] : $enrolment->trainingcenter;
                $this->_send_invite($user, $from, $enrolment, $subject, $emailtext, $emailhtml, 'CANCEL');
            }
        }

        // Run an enrolment check to update groups.
        $this->enrolment_check($enrolment->staffid, false);

        return true;
    }

    protected function _set_internal_completion($userid, $complete) {
        global $DB;

        $record = $DB->get_record('tapsenrol_completion', array('tapsenrolid' => $this->tapsenrol->id, 'userid' => $userid));

        if ($record) {
            $record->completed = $complete;
            $record->timemodified = time();
            $DB->update_record('tapsenrol_completion', $record);
        } else {
            $record = new stdClass();
            $record->tapsenrolid = $this->tapsenrol->id;
            $record->userid = $userid;
            $record->completed = $complete;
            $record->timemodified = time();
            $DB->insert_record('tapsenrol_completion', $record);
        }
    }

    protected function _set_completion_data() {
        global $CFG, $DB;

        require_once($CFG->libdir.'/completionlib.php');

        $this->_completion = new completion_info($this->course);
        $this->_cancomplete = $this->_completion->is_enabled($this->cm) == COMPLETION_TRACKING_AUTOMATIC && $this->tapsenrol->completionenrolment;
        $this->_iscomplete = $DB->get_records_menu('course_modules_completion', array('coursemoduleid' => $this->cm->id), '', 'userid, completionstate');
    }

    protected function _set_enrol_data() {
        global $DB;

        $enrolinstances = enrol_get_instances($this->course->id, true);
        $selfenrolinstances = array_filter($enrolinstances, create_function('$a', 'return $a->enrol == \'self\';'));
        $this->_selfenrolinstance = array_shift($selfenrolinstances);
        if ($this->_selfenrolinstance) {
            $this->_enrolself = enrol_get_plugin('self'); // Used later.
            $this->_isenrolled = $DB->get_records_menu('user_enrolments', array('enrolid' => $this->_selfenrolinstance->id), '', 'userid, id');
        }
    }

    protected function _set_group_data() {
        global $DB;
        $this->_coursegroups = $DB->get_records_menu('groups', array('courseid' => $this->course->id), '', 'id, name');
    }

    protected function _applicant_data($user) {
        return array(
            'applicant:firstname' => $user->firstname,
            'applicant:lastname' => $user->lastname,
            'applicant:email' => $user->email,
        );
    }

    protected function _admin_user_data() {
        global $USER;
        return array(
            'admin:firstname' => $USER->firstname,
            'admin:lastname' => $USER->lastname,
            'admin:email' => $USER->email,
        );
    }

    protected function _sponsor_data($iwtrack) {
        return array(
            'approver:firstname' => $iwtrack->sponsorfirstname,
            'approver:lastname' => $iwtrack->sponsorlastname,
            'approver:email' => $iwtrack->sponsoremail,
        );
    }

    protected function _class_data($enrolment, $old = false) {
        if (!isset($enrolment->trainingcenter)) {
            // Merge in data from class record (if needed/available).
            $class = $this->taps->get_class_by_id($enrolment->classid);
            $enrolment->price = $class ? $class->price : null;
            $enrolment->currencycode = $class ? $class->currencycode : null;
            $enrolment->trainingcenter = $class ? $class->trainingcenter : null;
        }

        try {
            $timezone = new DateTimeZone($enrolment->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $courseurl = new moodle_url('/course/view.php', array('id' => $this->course->id));

        $classarray = array(
            'coursename' => $this->course->fullname,
            'courseurl' => $courseurl->out(false),
            'classname' => $enrolment->classname,
            'classlocation' => $enrolment->location ? $enrolment->location : get_string('tbc', 'tapsenrol'),
            'classtrainingcenter' => $enrolment->trainingcenter,
        );

        if ($enrolment->classstarttime == 0) {
            $classarray['classdate'] = get_string('tbc', 'tapsenrol');
        } else {
            $startdatetime = new DateTime(null, $timezone);
            $startdatetime->setTimestamp($enrolment->classstarttime);
            $enddatetime = new DateTime(null, $timezone);
            $enddatetime->setTimestamp($enrolment->classendtime);

            $startformat = '';
            $endformat = '';
            $starttz = false;

            if ($enrolment->classstartdate == $enrolment->classstarttime) {
                $startformat = 'd M Y';
            } else {
                $startformat = 'd M Y H:i';
                $starttz = true;
            }

            if ($enddatetime->format('Ymd') > $startdatetime->format('Ymd')) {
                if ($enrolment->classenddate == $enrolment->classendtime) {
                    $endformat = 'd M Y';
                } else {
                    $endformat = 'd M Y H:i T';
                    $starttz = false;
                }
            } else if ($enrolment->classenddate != $enrolment->classendtime) {
                $endformat = 'H:i T';
                $starttz = false;
            }

            if ($starttz) {
                $startformat .= ' T';
            }
            $classarray['classdate'] = $startdatetime->format($startformat);
            if ($endformat) {
                $classarray['classdate'] .= ' ' . get_string('to', 'tapsenrol') . ' ';
                $classarray['classdate'] .= $enddatetime->format($endformat);
            }
            // Show UTC as GMT for clarity.
            $classarray['classdate'] = str_replace('UTC', 'GMT', $classarray['classdate']);
        }

        if (!empty($enrolment->duration)) {
            $classarray['classduration'] = (float) $enrolment->duration . ' ' . $enrolment->durationunits;
        } else {
            $classarray['classduration'] = '-';
        }

        if (!empty($enrolment->price)) {
            $classarray['classcost'] = $enrolment->price . ' ' . $enrolment->currencycode;
        } else {
            $classarray['classcost'] = '-';
        }

        if ($old) {
            // Suffix ':old' on keys.
            $keys = array_keys($classarray);
            array_walk($keys, function(&$value, $key, $suffix) {
                $value .= $suffix;
            }, ':old');
            $classarray = array_combine($keys, $classarray);
        }

        return $classarray;
    }

    protected function _approve_urls($enrolment) {
        $approveurl = new moodle_url('/mod/tapsenrol/approve.php', array('id' => $enrolment->enrolmentid, 'action' => 'approve'));
        $rejecturl = new moodle_url('/mod/tapsenrol/approve.php', array('id' => $enrolment->enrolmentid, 'action' => 'reject'));

        $urls = array(
            'approveurl' => $approveurl->out(false),
            'rejecturl' => $rejecturl->out(false),
        );

        $approveurl->param('direct', 1);
        $rejecturl->param('direct', 1);

        $urls += array(
            'directapproveurl' => $approveurl->out(false),
            'directrejecturl' => $rejecturl->out(false),
        );

        return $urls;
    }

    protected function _cancel_url($enrolment) {
        $cancelurl = new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $this->cm->id, 'enrolmentid' => $enrolment->enrolmentid));
        return $cancelurl->out(false);
    }

    protected function _get_email($email, $data, $approvalrequired = false) {
        global $DB;

        $sql = <<<EOS
SELECT
    def.*,
    global.id as globalid,
    global.subject as globalsubject,
    global.body as globalbody,
    global.html as globalhtml,
    iw.id as iwid,
    iw.subject as iwsubject,
    iw.body as iwbody,
    iw.html as iwhtml,
    cm.id as cmid,
    cm.subject as cmsubject,
    cm.body as cmbody,
    cm.html as cmhtml
FROM
    {tapsenrol_iw_email} def
LEFT JOIN
    {tapsenrol_iw_email_custom} global
    ON global.emailid = def.id AND global.internalworkflowid IS NULL AND global.coursemoduleid IS NULL
LEFT JOIN
    {tapsenrol_iw_email_custom} iw
    ON iw.emailid = def.id AND iw.internalworkflowid = :iwid AND iw.coursemoduleid IS NULL
LEFT JOIN
    {tapsenrol_iw_email_custom} cm
    ON cm.emailid = def.id AND cm.internalworkflowid IS NULL AND cm.coursemoduleid = :cmid
WHERE
    def.email = :email
EOS;
        $params = array(
            'email' => $email,
            'iwid' => $this->iw->id,
            'cmid' => $this->cm->id,
        );
        $emailrecord = $DB->get_record_sql($sql, $params);

        if (!$emailrecord) {
            return false;
        }

        // Get appropriate one!
        if (!is_null($emailrecord->cmid)) {
            $subject = $emailrecord->cmsubject;
            $body = $emailrecord->cmbody;
            $html = $emailrecord->cmhtml;
        } else if (!is_null($emailrecord->iwid)) {
            $subject = $emailrecord->iwsubject;
            $body = $emailrecord->iwbody;
            $html = $emailrecord->iwhtml;
        } else if (!is_null($emailrecord->globalid)) {
            $subject = $emailrecord->globalsubject;
            $body = $emailrecord->globalbody;
            $html = $emailrecord->globalhtml;
        } else {
            $subject = $emailrecord->subject;
            $body = $emailrecord->body;
            $html = $emailrecord->html;
        }

        $this->_email_replacements($subject, $body, $data);

        if (!$approvalrequired) {
            // Strip out references to approver wrapped in {{approver}}{{/approver}}.
            $body = preg_replace('#\{\{approver\}\}(.*)?\{\{/approver\}\}#ims', '', $body);
        } else {
            // Strip out approver wrappers {{approver}}{{/approver}}.
            $body = preg_replace('#\{\{approver\}\}(.*)?\{\{/approver\}\}#ims', '$1', $body);
        }

        if ($html) {
            $emailtext = html_to_text($body);
            $emailhtml = $body;
        } else {
            $emailtext = $body;
            $emailhtml = '';
        }

        // Load 'from' email.
        $from = $DB->get_record(
            'tapsenrol_iw',
            array('id' => $this->tapsenrol->internalworkflowid),
            'id, fromfirstname as firstname, fromlastname as lastname, fromemail as email'
        );
        if (!$from || empty($from->email)) {
            $from = get_admin();
        } else {
            $from = tapsenrol_user::get_dummy_tapsenrol_user($from->email, $from->firstname, $from->lastname);
        }

        return array($from, $subject, $emailhtml, $emailtext);
    }

    protected function _send_email($to, $from, $subject, $emailhtml, $emailtext, array $cc=array()) {
        // Are we sending emails?
        if (!empty($this->iw->emailsoff)) {
            return true;
        }
        // Force HTML...
        $to->mailformat = 1;
        // Force maildisplay...
        $from->maildisplay = true;

        $this->_force_email_sending('save');
        $result = email_to_user($to, $from, $subject, $emailtext, $emailhtml, '', '', true, '', '', 79, $cc);
        $this->_force_email_sending('reset');
        return $result;
    }

    protected function _send_invite($to, $from, $enrolment, $subject, $emailtext, $emailhtml, $method = 'REQUEST') {
        global $CFG;

        // Are we sending emails?
        if (!empty($this->iw->emailsoff)) {
            return true;
        }

        $this->_force_email_sending('save');

        require_once($CFG->dirroot . '/local/invites/requester.php');

        $room = empty($enrolment->trainingcenter) ? '' : " | {$enrolment->trainingcenter}";
        $location = $enrolment->location ? $enrolment->location.$room : get_string('tbc', 'tapsenrol');
        $courseurl = new moodle_url('/course/view.php', array('id' => $this->course->id));

        if (empty($emailhtml)) {
            $emailhtml = $emailtext;
        }
        $invite = new invite($location, $subject, $emailhtml, $emailtext);
        if (!empty($enrolment->moved)) {
            $invite->set_id('ORACLE-ENROL-'.$enrolment->enrolmentid.'-'.$enrolment->moved);
        } else {
            $invite->set_id('ORACLE-ENROL-'.$enrolment->enrolmentid);
        }
        $invite->set_url($courseurl->out(false));

        $starttime = new DateTime();
        $starttime->setTimestamp($enrolment->classstarttime);
        $starttime->setTimezone(new DateTimeZone('UTC')); // Send the vcal in UTC.
        $endtime = new DateTime();
        $endtime->setTimestamp($enrolment->classendtime);
        $endtime->setTimezone(new DateTimeZone('UTC'));
        $invite->set_date(
                $starttime,
                $starttime->diff($endtime));

        $invite->add_organizer(new organizer($from));
        $invite->add_recipient(new invitee($to));

        if ($method == 'CANCEL') {
            $invite->set_as_cancelled();
        }

        new vcal_requester($invite, $method);

        $this->_force_email_sending('reset');
    }

    protected function _email_replacements(&$subject, &$body, $data) {
        foreach ($data as $index => $value) {
            if (is_array($value)) {
                $this->_email_replacements($subject, $body, $value);
            } else {
                $subject = str_ireplace('[['.$index.']]', $value, $subject);
                $body = str_ireplace('[['.$index.']]', $value, $body);
            }
        }
    }

    protected function _get_sponsor($iwtrack) {
        return tapsenrol_user::get_dummy_tapsenrol_user($iwtrack->sponsoremail, $iwtrack->sponsorfirstname, $iwtrack->sponsorlastname);
    }

    public function send_applicant_reminder($enrolmentid, $email) {
        global $DB;

        $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
        $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));

        if (!$user) {
            return false;
        }

        // Email.
        $data = array(
            'applicant' => $this->_applicant_data($user),
            'class' => $this->_class_data($enrolment),
            'cancelurl' => $this->_cancel_url($enrolment),
        );
        list($from, $subject, $emailhtml, $emailtext) = $this->_get_email($email, $data, $this->iw->approvalrequired);
        return $this->_send_email($user, $from, $subject, $emailhtml, $emailtext);
    }

    public function send_sponsor_reminder($enrolmentid, $email) {
        global $DB;

        $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
        $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolmentid));

        if (!$enrolment || !$iwtrack) {
            return false;
        }

        $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));

        if (!$user) {
            return false;
        }

        // Email.
        $to = $DB->get_record('user', array('email' => $iwtrack->sponsoremail));
        if (!$to) {
            $to = $this->_get_sponsor($iwtrack);
        }
        try {
            $totimezone = new DateTimeZone($to->timezone);
        } catch (Exception $e) {
            $totimezone = new DateTimeZone(date_default_timezone_get());
        }
        $approvebytimestamp = $iwtrack->timeenrolled + $this->iw->cancelafter;
        if ($this->taps->is_classtype($enrolment->classtype, 'classroom')) {
            // Need approval before auto cancellation prior to class start.
            $classstarttime = $enrolment->classstarttime - $this->iw->cancelbefore;
            if ($approvebytimestamp > $classstarttime && $enrolment->classstarttime != 0) {
                $approvebytimestamp = $classstarttime;
            }
        } else {
            // Need approval before class ends.
            if ($approvebytimestamp > $enrolment->classendtime && $enrolment->classendtime != 0) {
                $approvebytimestamp = $enrolment->classendtime;
            }
        }
        $approvebytime = new DateTime(null, $totimezone);
        $approvebytime->setTimestamp($approvebytimestamp);
        $data = array(
            'applicant' => $this->_applicant_data($user),
            'sponsor' => $this->_sponsor_data($iwtrack),
            'class' => $this->_class_data($enrolment),
            'approveurls' => $this->_approve_urls($enrolment),
            'comments:applicant' => $iwtrack->requestcomments,
            'approvebydate' => $approvebytime->format('H:i T, d M Y'),
        );
        // Show UTC as GMT for clarity.
        $data['approvebydate'] = str_replace('UTC', 'GMT', $data['approvebydate']);
        list($from, $subject, $emailhtml, $emailtext) = $this->_get_email($email, $data, $this->iw->approvalrequired);
        return $this->_send_email($to, $from, $subject, $emailhtml, $emailtext);
    }

    public function resend_invite($enrolmentid, $class, $extrainfo) {
        global $DB;

        $userenrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
        if (!$userenrolment) {
            return false;
        }

        $user = $DB->get_record('user', array('idnumber' => $userenrolment->staffid));
        if (!$user) {
            return false;
        }

        $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolmentid));
        if (!$iwtrack) {
            return false;
        }

        // Merge class data with enrolment data to ensure up-to-date.
        $enrolment = (object)array_merge((array)$userenrolment, (array)$class);
        $enrolment->duration = $enrolment->classduration;
        $enrolment->durationunits = $enrolment->classdurationunits;
        $enrolment->durationunitscode = $enrolment->classdurationunitscode;

        $data = array();
        $data['update:extrainfo'] = nl2br($extrainfo);
        $data['class'] = $this->_class_data($enrolment);
        $data['applicant'] = $this->_applicant_data($user);
        $data['sponsor'] = $this->_sponsor_data($iwtrack);
        $data['cancelurl'] = $this->_cancel_url($enrolment);
        list($from, $subject, $emailhtml, $emailtext) = $this->_get_email('approved_invite', $data, $this->iw->approvalrequired);
        $this->_send_invite($user, $from, $enrolment, $subject, $emailtext, $emailhtml);
        return true;
    }

    protected function _force_email_sending($action = 'save') {
        global $CFG;

        if (!isset($this->_cfg->forceemailsending)) {
            $this->_cfg->forceemailsending = get_config('tapsenrol', 'forceemailsending');
        }

        if ($this->_cfg->forceemailsending) {
            switch ($action) {
                case 'save' :
                    $this->_cfg->noemailever = empty($CFG->noemailever) ? null : $CFG->noemailever;
                    $CFG->noemailever = null;
                    $this->_cfg->divertallemailsto = empty($CFG->divertallemailsto) ? null : $CFG->divertallemailsto;
                    $CFG->divertallemailsto = null;
                    $this->_cfg->divertccemailsto = empty($CFG->divertccemailsto) ? null : $CFG->divertccemailsto;
                    $CFG->divertccemailsto = null;
                    break;
                case 'reset' :
                    $CFG->noemailever = empty($this->_cfg->noemailever) ? null : $this->_cfg->noemailever;
                    $CFG->divertallemailsto = empty($this->_cfg->divertallemailsto) ? null : $this->_cfg->divertallemailsto;
                    $CFG->divertccemailsto = empty($this->_cfg->divertccemailsto) ? null : $this->_cfg->divertccemailsto;
                    break;
            }
        }
    }
}

class tapsenrol_user extends \core_user {
    public static function get_dummy_tapsenrol_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'tapsenroluser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}