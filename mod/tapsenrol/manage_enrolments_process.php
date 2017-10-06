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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$t = optional_param('t',  0, PARAM_INT);  // TAPS Enrolment Activity ID.
$type = required_param('type', PARAM_ALPHA);
$classid = required_param('classid', PARAM_INT);

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

// Check login and get context.
require_login($tapsenrol->course, false, $tapsenrol->cm);

require_capability('mod/tapsenrol:manageenrolments', $PAGE->context);

$url = new moodle_url('/mod/tapsenrol/manage_enrolments.php', array('id' => $tapsenrol->cm->id));

$PAGE->set_url($url);

$heading = get_string('manageenrolments:heading', 'tapsenrol', $tapsenrol->course->fullname);

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/tapsenrol/js/tapsenrol.js', false);

$output = $PAGE->get_renderer('mod_tapsenrol');

$html = '';

$html .= $OUTPUT->header();

$html .= $OUTPUT->heading($heading, '2');

if (!empty($SESSION->tapsenrol->alert->message)) {
    $html .= $output->alert($SESSION->tapsenrol->alert->message, $SESSION->tapsenrol->alert->type);
    unset($SESSION->tapsenrol->alert);
}

if (!$tapsenrol->tapsenrol->internalworkflowid) {
    $SESSION->tapsenrol->alert = new stdClass();
    $SESSION->tapsenrol->alert->message = get_string('manageenrolments:cannot', 'tapsenrol');
    $SESSION->tapsenrol->alert->type = 'alert-warning';

    redirect($url);
    exit;
}

// Form processing.
$customdata = array();
$customdata['id'] = $id;
$customdata['type'] = $type;

$class = $tapsenrol->taps->get_class_by_id($classid);
if (!$class) {
    $SESSION->tapsenrol->alert = new stdClass();
    $SESSION->tapsenrol->alert->message = get_string('enrol:error:classdoesnotexist', 'tapsenrol');
    $SESSION->tapsenrol->alert->type = 'alert-warning';

    redirect($url);
    exit;
}
$customdata['class'] = $class;

$html .= $OUTPUT->heading(get_string('manageenrolments:header:class', 'tapsenrol', $class->classname), '3');

require_once($CFG->dirroot.'/mod/tapsenrol/forms/manage_enrolments_step2_form.php');
$actionurl = new moodle_url('/mod/tapsenrol/manage_enrolments_process.php');
$actionparams = array(
    'id' => $id,
    'classid' => $classid,
    'type' => $type,
);
if ($type == 'future' || $type == 'past') {
    $mform = new mod_tapsenrol_manage_enrolments_enrol_form($actionurl, $customdata);

    if ($mform->is_cancelled()) {
        redirect($url);
        exit;
    }

    $fromform = $mform->get_data();

    $usersearch = optional_param('users_searchbutton', '', PARAM_RAW);
    $usersearchclear = optional_param('users_clearbutton', '', PARAM_RAW);
    if ($fromform && !$usersearch && !$usersearchclear) {
        $a = '';
        // Process form.
        // Pick up users from selector.
        $users = optional_param_array('users', array(), PARAM_INT);
        if (empty($users)) {
            $a .= "<br />No users selected.";
        }
        foreach ($users as $userid) {
            $user = $DB->get_record('user', array('id' => $userid));
            if (!$user) {
                $a .= "<br />FAILED: User (Moodle User ID: {$userid}) not found.";
                continue;
            }

            // Here we need to find and reset any linked certifications.
            $completioncache = \cache::make('core', 'completion');
            $alreadyattended = $tapsenrol->already_attended($user);
            $resetcourses = [];
            foreach ($alreadyattended->completions as $completion) {
                $resetcourses = \local_custom_certification\completion::open_window($completion);
                foreach ($resetcourses as $resetcourseid) {
                    $completioncache->delete("{$user->id}_{$resetcourseid}");
                }
            }
            // If not already done via a linked certification, simply reset the course.
            if (!in_array($tapsenrol->course->id, $resetcourses)) {
                \local_custom_certification\completion::reset_course_for_user($tapsenrol->course->id, $user->id);
                $completioncache->delete("{$user->id}_{$tapsenrol->course->id}");
            }

            $enrolresult = $tapsenrol->enrol_employee($fromform->classid, $user->idnumber);
            $username = fullname($user);
            if ($enrolresult->success) {
                $iwtrack = new stdClass();
                $iwtrack->enrolmentid = $enrolresult->enrolment->enrolmentid;
                $iwtrack->sponsoremail = $USER->email;
                $iwtrack->sponsorfirstname = $USER->firstname;
                $iwtrack->sponsorlastname = $USER->lastname;
                $iwtrack->requestcomments = '';
                $iwtrack->timeenrolled = time();
                $iwtrack->timemodified = $iwtrack->timeenrolled;
                $iwtrack->timecreated = $iwtrack->timeenrolled;
                $iwtrack->id = $DB->insert_record('tapsenrol_iw_tracking', $iwtrack);
                if ($class->classstarttime > time() || $class->classendtime == 0) {
                    $statusresult = $tapsenrol->approve_workflow($iwtrack, $enrolresult->enrolment, $user, new stdClass());
                } else {
                    $iwtrack->approved = 1;
                    $iwtrack->timeapproved = time();
                    $DB->update_record('tapsenrol_iw_tracking', $iwtrack);
                    if ($tapsenrol->taps->is_classtype($class->classtype, 'classroom') && !empty($class->classendtime)) {
                        $completiontime = $class->classendtime;
                    } else {
                        $completiontime = time();
                    }
                    $statusresult = $tapsenrol->taps->set_status($enrolresult->enrolment->enrolmentid, 'Full Attendance', $completiontime);
                    $tapsenrol->enrolment_check($user->idnumber, false);
                }
                $enrolmentstatus = $tapsenrol->taps->get_enrolment_status($enrolresult->enrolment->enrolmentid);
                $a .= "<br />SUCCESS: [{$username}] Enrolled with status '{$enrolmentstatus}'.";
            } else {
                $a .= "<br />FAILED: [{$username}] Could not enrol: {$enrolresult->status}.";
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('manageenrolments:enrol:results', 'tapsenrol', $a);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $actionurl->params($actionparams);
        redirect($actionurl);
        exit;
    }
} else if ($type == 'cancel') {
    $mform = new mod_tapsenrol_manage_enrolments_cancel_form($actionurl, $customdata);

    if ($mform->is_cancelled()) {
        redirect($url);
        exit;
    }

    $fromform = $mform->get_data();

    if ($fromform) {
        $a = '';
        // Process form.
        $enrolments = optional_param_array('enrolmentid', array(), PARAM_INT);

        if (empty($enrolments)) {
            $a .= "<br />No enrolments selected.";
        }

        $status = $fromform->status;

        foreach ($enrolments as $enrolmentid) {
            $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
            if (!$enrolment || $enrolment->archived) {
                $a .= "<br />FAILED: Enrolment ({$enrolmentid}) could not be loaded.";
                continue;
            }
            $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
            if (!$user) {
                $a .= "<br />FAILED: User (Staff ID: {$enrolment->staffid}) not found.";
                continue;
            }
            $username = fullname($user);
            $cancelresult = $tapsenrol->cancel_enrolment($enrolment->enrolmentid, $status);
            if ($cancelresult->success) {
                // Variable $enrolment will have original booking status which is required.
                $email = $enrolment->classstarttime < time() ? null : 'cancellation_admin';
                $tapsenrol->cancel_workflow($enrolment, "ADMIN: {$status}", $email);
                $a .= "<br />SUCCESS: [{$username}] Enrolment cancelled. Status: '{$status}'";
            } else {
                $a .= "<br />FAILED: [{$username}] Could not cancel: {$cancelresult->message}.";
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('manageenrolments:cancel:results', 'tapsenrol', $a);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $actionurl->params($actionparams);
        redirect($actionurl);
        exit;
    }
} else if ($type == 'waitlist') {
    $mform = new mod_tapsenrol_manage_enrolments_waitlist_form($actionurl, $customdata);

    if ($mform->is_cancelled()) {
        redirect($url);
        exit;
    }

    $fromform = $mform->get_data();

    if ($fromform) {
        $a = '';
        // Process form.
        $enrolments = optional_param_array('enrolmentid', array(), PARAM_INT);

        if (empty($enrolments)) {
            $a .= "<br />No applications selected.";
        }

        foreach ($enrolments as $enrolmentid) {
            $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
            $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolmentid));
            if (!$enrolment || $enrolment->archived || !$iwtrack) {
                $a .= "<br />FAILED: Application ({$enrolmentid}) could not be loaded.";
                continue;
            }
            $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
            if (!$user) {
                $a .= "<br />FAILED: User (Staff ID: {$enrolment->staffid}) not found.";
                continue;
            }
            $username = fullname($user);
            $approveresult = $tapsenrol->approve_workflow($iwtrack, $enrolment, $user, new stdClass());
            if ($approveresult->success) {
                $a .= "<br />SUCCESS: [{$username}] Application approved.";
            } else {
                $a .= "<br />FAILED: [{$username}] Could not approve: {$approveresult->message}.";
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('manageenrolments:waitlist:results', 'tapsenrol', $a);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $actionurl->params($actionparams);
        redirect($actionurl);
        exit;
    }

    $jscode = <<<EOJ
$('.tapsenrol-current-enrolments .tapsenrol-checkbox').change(function(){
    var that = $(this);
    var seatsremaining = paresInt($('#tapsenrol-waitlist-seatsremaining').data('seatsremaining'));
    if (seatsremaining === -1) {
        return;
    }
    var seatsremainingalert = seatsremaining.closest('.alert');
    if (seatsremaining === 0 && that.prop('checked') === true) {
        that.prop('checked', false);
        $('html, body').animate({
            scrollTop: seatsremainingalert.offset().top - 5
        }, 1000);
    } else if (that.prop('checked') === true) {
        seatsremaining = seatsremaining - 1;
    } else if (that.prop('checked') === false) {
        seatsremaining = seatsremaining + 1;
    }
    $('#tapsenrol-waitlist-seatsremaining').text(seatsremaining);
    if (seatsremaining === 0) {
        seatsremainingalert.removeClass('alert-info').removeClass('alert-warning').addClass('alert-danger');
    } else if (seatsremaining < 3) {
        seatsremainingalert.removeClass('alert-info').removeClass('alert-danger').addClass('alert-warning');
    } else {
        seatsremainingalert.removeClass('alert-danger').removeClass('alert-warning').addClass('alert-info');
    }
});
$('.tapsenrol-current-enrolments .tapsenrol-checkbox').prop('disabled', false);
EOJ;
    $PAGE->requires->js_init_code($jscode, true);
} else if ($type == 'move') {
    $where = 'courseid = :courseid AND (classstarttime > :now OR classstarttime = 0) AND classid != :classid';
    $params = array(
        'courseid' => $tapsenrol->tapsenrol->tapscourse,
        'now' => time(),
        'classid' => $class->classid,
    );
    $customdata['classes'] = $DB->get_records_select_menu('local_taps_class', $where, $params, '', 'classid, classname');
    $mform = new mod_tapsenrol_manage_enrolments_move_form($actionurl, $customdata);

    if ($mform->is_cancelled()) {
        redirect($url);
        exit;
    }

    $fromform = $mform->get_data();

    if ($fromform) {
        $a = '';
        $process = true;
        
        // Process form.
        $enrolments = optional_param_array('enrolmentid', array(), PARAM_INT);

        if (empty($enrolments)) {
            $a .= "<br />No enrolments selected.";
            $process = false;
        }

        $targetclass = $tapsenrol->taps->get_class_by_id($fromform->moveto);

        if (empty($targetclass)) {
            $a .= "<br />Class to move to not found.";
            $process = false;
        }

        $resendemails = !empty($fromform->resendemails);

        if ($process) {
            foreach ($enrolments as $enrolmentid) {
                $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
                if (!$enrolment || $enrolment->archived) {
                    $a .= "<br />FAILED: Enrolment ({$enrolmentid}) could not be loaded.";
                    continue;
                }
                $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
                if (!$user) {
                    $a .= "<br />FAILED: User (Staff ID: {$enrolment->staffid}) not found.";
                    continue;
                }
                $username = fullname($user);

                list($in, $inparams) = $DB->get_in_or_equal(
                    array_merge($tapsenrol->taps->get_statuses('cancelled'), $tapsenrol->taps->get_statuses('attended')),
                    SQL_PARAMS_NAMED, 'status', false
                );
                $compare = $DB->sql_compare_text('bookingstatus');

                $where .= " AND {$compare} {$in}";
                $sql = "SELECT id FROM {local_taps_enrolment} WHERE staffid = :staffid AND classid = :classid AND {$compare} {$in}";
                $params = array_merge(
                        array('staffid' => $enrolment->staffid, 'classid' => $targetclass->classid),
                        $inparams
                        );
                if ($DB->get_records_sql($sql, $params)) {
                    $a .= "<br />FAILED: [{$username}] Already has an active enrolment on the target class.";
                    continue;
                }

                $success = $tapsenrol->move_workflow($enrolment, $user, $class, $targetclass, $resendemails);

                if ($success) {
                    $a .= "<br />SUCCESS: [{$username}] Enrolment moved.";
                } else {
                    $a .= "<br />FAILED: [{$username}] Could not move enrolment.";
                }
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('manageenrolments:move:results', 'tapsenrol', $a);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $actionurl->params($actionparams);
        redirect($actionurl);
        exit;
    }
} else if ($type == 'update') {
    // @TODO
    $mform = new mod_tapsenrol_manage_enrolments_update_form($actionurl, $customdata);
} else if ($type == 'delete') {
    $mform = new mod_tapsenrol_manage_enrolments_delete_form($actionurl, $customdata);

    if ($mform->is_cancelled()) {
        redirect($url);
        exit;
    }

    $fromform = $mform->get_data();

    if ($fromform) {
        $a = '';
        $process = true;

        // Process form.
        $enrolments = optional_param_array('enrolmentid', array(), PARAM_INT);

        if (empty($enrolments)) {
            $a .= "<br />No enrolments selected.";
            $process = false;
        }

        $sendemails = !empty($fromform->sendemails);

        if ($process) {
            foreach ($enrolments as $enrolmentid) {
                $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $enrolmentid));
                if (!$enrolment || $enrolment->archived) {
                    $a .= "<br />FAILED: Enrolment ({$enrolmentid}) could not be loaded.";
                    continue;
                }
                $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
                if (!$user) {
                    $a .= "<br />FAILED: User (Staff ID: {$enrolment->staffid}) not found.";
                    continue;
                }
                $username = fullname($user);

                $success = $tapsenrol->delete_enrolment($enrolment, $user, $class, $sendemails);

                if ($success) {
                    $a .= "<br />SUCCESS: [{$username}] Enrolment deleted.";
                } else {
                    $a .= "<br />FAILED: [{$username}] Could not delete enrolment.";
                }
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('manageenrolments:delete:results', 'tapsenrol', $a);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $actionurl->params($actionparams);
        redirect($actionurl);
        exit;
    }
}

echo $html;

$mform->display();

echo $output->alert(get_string("manageenrolments:footeralert:{$type}", 'tapsenrol'), 'alert-info', false);

echo html_writer::tag('p', html_writer::link($url, get_string('backtomanageenrolments', 'tapsenrol')));

echo $output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();