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
require_once($CFG->dirroot.'/mod/tapsenrol/forms/approve_form.php');

$id = optional_param('id', 0, PARAM_INT); // Enrolment ID.
$action = optional_param('action', '', PARAM_ALPHA);
$direct = optional_param('direct', 0, PARAM_INT);

$url = new moodle_url('/mod/tapsenrol/approve.php', array('id' => $id, 'action' => $action));

// Check login (system level as not course/cm specific).
require_login();

$loaderror = false;
if ($id) {
    $enrolment = $DB->get_record('local_taps_enrolment', array('enrolmentid' => $id));
    $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $id));
    if (!$enrolment || !$iwtrack) {
        $loaderror = 'Enrolment or tracking data not found.';
    }
}
if ($id && !$loaderror) {
    // Now know enrolment is loaded OK.
    $user = $DB->get_record('user', array('idnumber' => $enrolment->staffid));
    $tapsenrol = $DB->get_record('tapsenrol', array('tapscourse' => $enrolment->courseid));
    if (!$user || !$tapsenrol) {
        $loaderror = 'User or activity not found.';
    }
}
if ($id && !$loaderror) {
    $course = $DB->get_record('course', array('id' => $tapsenrol->course));
    $iw = $DB->get_record('tapsenrol_iw', array('id' => $tapsenrol->internalworkflowid));
    if (!$course || !$iw) {
        $loaderror = 'Course or workflow details not found.';
    }
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);

$heading = get_string('approve:title', 'tapsenrol');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$output = $PAGE->get_renderer('mod_tapsenrol');

echo $OUTPUT->header();

$showall = true;
$outputcache = '';
if ($loaderror) {
    $message = get_string('approve:no', 'tapsenrol');
    if (is_siteadmin()) {
        // Extra help to determine cause.
        $message .= '<br />DEBUGGING: '.$loaderror;
    }
    $outputcache .= $output->alert($message, 'alert-danger', false);
} else if ($id) {
    if (strcasecmp($iwtrack->sponsoremail, $USER->email) != 0 && !has_capability('mod/tapsenrol:canapproveanyone', $context)) {
        // Incorrect user.
        $outputcache .= $output->alert(get_string('approve:notsponsor', 'tapsenrol'), 'alert-danger', false);
    } else if (!is_null($iwtrack->approved)) {
        // Already approved/rejected.
        $already = $iwtrack->approved ? get_string('approve:approved', 'tapsenrol') : get_string('approve:rejected', 'tapsenrol');
        $outputcache .= $output->alert(get_string('approve:alreadydone', 'tapsenrol', $already), 'alert-warning', false);
    } else {
        // All OK so far.
        $tapsenrolclass = new tapsenrol($tapsenrol->id, 'instance');
        if ($direct) {
            $_POST['_qf__mod_tapsenrol_approve_form'] = 1;
            $_POST['sesskey'] = sesskey();
            switch ($action) {
                case 'approve' :
                    $_POST['approve'] = true;
                    break;
                case 'reject' :
                    $_POST['reject'] = true;
                    break;
            }
        }
        $form = new mod_tapsenrol_approve_form('', array('action' => $action, 'rejectioncomments' => $iw->rejectioncomments));
        if (!$form->is_cancelled()) {
            $fromform = $form->get_data();
            if ($fromform) {
                $result = $tapsenrolclass->approve_workflow($iwtrack, $enrolment, $user, $fromform);
                $outputcache .= $output->alert($result->message, $result->type);
            } else {
                $form->set_data(array('id' => $id));
                switch ($action) {
                    case 'approve' :
                        $title = get_string('approve:title:approve', 'tapsenrol');
                        $info = $iw->approveinfo ? nl2br($iw->approveinfo) : get_string('approve:info:approve', 'tapsenrol');
                        break;
                    case 'reject' :
                        $title = get_string('approve:title:reject', 'tapsenrol');
                        $info = $iw->rejectinfo ? nl2br($iw->rejectinfo) : get_string('approve:info:reject', 'tapsenrol');
                        break;
                    default :
                        $title = get_string('approve:title:either', 'tapsenrol');
                        $info = $iw->eitherinfo ? nl2br($iw->eitherinfo) : get_string('approve:info:either', 'tapsenrol');
                        break;
                }
                $other = array(
                    'enrolmentid' => $enrolment->enrolmentid,
                    'staffid' => $enrolment->staffid,
                    'classid' => $enrolment->classid,
                    'action' => $action ? $action : 'approve/reject',
                );
                $event = \mod_tapsenrol\event\enrolment_request_viewed::create(array(
                    'objectid' => $iwtrack->id,
                    'context' => $tapsenrolclass->context->cm,
                    'other' => $other,
                ));
                $event->trigger();
                $title .= get_string('separator', 'tapsenrol') . $course->fullname;
                if (!isset($enrolment->trainingcenter)) {
                    // Needed for renderer.
                    $class = $tapsenrolclass->taps->get_class_by_id($enrolment->classid);
                    $enrolment->price = $class ? $class->price : null;
                    $enrolment->currencycode = $class ? $class->currencycode : null;
                    $enrolment->trainingcenter = $class ? $class->trainingcenter : null;
                }
                echo $output->review_approval($title, $info, $iwtrack, $user, $enrolment, $tapsenrolclass->course);
                $form->display();
                $showall = false;
            }
        }
    }
}

if ($showall) {
    echo $OUTPUT->heading($heading, '2');

    echo $outputcache;

    $taps = new \local_taps\taps();
    list($in, $inparams) = $DB->get_in_or_equal(
        $taps->get_statuses('requested'),
        SQL_PARAMS_NAMED, 'status'
    );
    $compare = $DB->sql_compare_text('lte.bookingstatus');
    $usernamefields = get_all_user_name_fields(true, 'u');
    $enrolmentsql = <<<EOS
SELECT
    tit.*,
    {$usernamefields},
    lte.coursename, lte.classname
FROM
    {tapsenrol_iw_tracking} tit
JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = tit.enrolmentid
JOIN
    {tapsenrol} t
    ON t.tapscourse = lte.courseid
JOIN
    {tapsenrol_iw} ti
    ON ti.id = t.internalworkflowid
JOIN
    {user} u
    ON u.idnumber = lte.staffid
WHERE
    tit.approved IS NULL
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
EOS;
    $enrolmentparams = array();
    if (!has_capability('mod/tapsenrol:viewallapprovals', context_system::instance())) {
        $useremail = strtolower($USER->email);
        $enrolmentsql .= ' AND tit.sponsoremail = :sponsoremail ';
        $enrolmentparams['sponsoremail'] = $useremail;
    }
    $enrolmentsql .= <<<EOS

ORDER BY
    tit.timeenrolled ASC
EOS;
    $enrolments = $DB->get_records_sql($enrolmentsql, array_merge($enrolmentparams, $inparams));
    echo $output->enrolments_for_approval($enrolments);

    $output->approval_history();
}

echo $OUTPUT->footer();