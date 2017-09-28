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
$classid = optional_param('classid', 0, PARAM_INT);

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

// Check login and get context.
require_login($tapsenrol->course, false, $tapsenrol->cm);

require_capability('mod/tapsenrol:resendinvites', $PAGE->context);

$url = new moodle_url('/mod/tapsenrol/resend_invites.php', array('id' => $tapsenrol->cm->id));
if ($classid) {
    $url->param('classid', $classid);
}

$PAGE->set_url($url);

$heading = get_string('resendinvites:heading', 'tapsenrol', $tapsenrol->course->fullname);

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$output = $PAGE->get_renderer('mod_tapsenrol');

$html = '';
$displayform = false;

$html .= $OUTPUT->header();

$html .= $OUTPUT->heading($heading, '2');

if (!empty($SESSION->tapsenrol->alert->message)) {
    $html .= $output->alert($SESSION->tapsenrol->alert->message, $SESSION->tapsenrol->alert->type);
    unset($SESSION->tapsenrol->alert);
}

if (!$tapsenrol->tapsenrol->internalworkflowid) {
    $html .= $output->alert(get_string('resendinvites:cannot', 'tapsenrol'), 'alert-danger', false);
} else if (!$classid) {
    // This is via enrolment records as may not be considered active classes anymore.
    list($in, $inparams) = $DB->get_in_or_equal(
        $tapsenrol->taps->get_statuses('placed'),
        SQL_PARAMS_NAMED, 'status'
    );
    $compare = $DB->sql_compare_text('lte.bookingstatus');

    $sql = <<<EOS
SELECT
    lte.classid,
    COUNT(lte.id) AS enrolments
FROM
    {local_taps_enrolment} lte
JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
WHERE
    lte.courseid = :courseid
    AND lte.classstarttime > :now
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
GROUP BY
    lte.classid
EOS;
    $params = array(
        'courseid' => $tapsenrol->tapsenrol->tapscourse,
        'now' => time(),
    );
    $enrolments = $DB->get_records_sql($sql, array_merge($params, $inparams));
    $classes = array();
    foreach ($enrolments as $enrolment) {
        $classes[$enrolment->classid] = $tapsenrol->taps->get_class_by_id($enrolment->classid);
        $classes[$enrolment->classid]->enrolments = $enrolment->enrolments;
    }
    $html .= $output->resend_invites_classes($tapsenrol, $classes);
} else {
    require_once($CFG->dirroot.'/mod/tapsenrol/forms/resend_invites_form.php');

    list($in, $inparams) = $DB->get_in_or_equal(
        $tapsenrol->taps->get_statuses('placed'),
        SQL_PARAMS_NAMED, 'status'
    );
    $compare = $DB->sql_compare_text('lte.bookingstatus');
    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = <<<EOS
SELECT
    lte.*,
    {$usernamefields}
FROM
    {local_taps_enrolment} lte
JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
JOIN
    {user} u
    ON u.idnumber = lte.staffid
WHERE
    classid = :classid
    AND classstarttime > :now
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
EOS;
    $params = array(
        'classid' => $classid,
        'now' => time()
    );
    $customdata = array();
    $customdata['id'] = $id;
    $customdata['classid'] = $classid;
    $customdata['enrolments'] = $DB->get_records_sql($sql, array_merge($params, $inparams));
    $mform = new mod_tapsenrol_resend_invites_form(null, $customdata);

    if ($mform->is_cancelled()) {

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('resendinvites:resendingcancelled', 'tapsenrol');
        $SESSION->tapsenrol->alert->type = 'alert-warning';

        $url->remove_params('classid');
        redirect($url);
        exit;
    }

    $class = $tapsenrol->taps->get_class_by_id($classid);

    if (!$class) {
        $class = new stdClass();
    }

    $fromform = $mform->get_data();

    if ($fromform) {
        $count = 0;
        foreach ($fromform->enrolment as $enrolmentid => $state) {
            if ($state) {
                if ($tapsenrol->resend_invite($enrolmentid, $class, $fromform->extrainfo)) {
                    $count++;
                }
            }
        }

        $SESSION->tapsenrol->alert = new stdClass();
        $SESSION->tapsenrol->alert->message = get_string('resendinvites:invitesresent', 'tapsenrol', $count);
        $SESSION->tapsenrol->alert->type = 'alert-success';

        $url->remove_params('classid');
        redirect($url);
        exit;
    }

    // Add Class info.
    $html .= $output->resend_invites_class_info($class);

    $displayform = true;
}

echo $html;

if ($displayform) {
    $mform->display();
}

echo $output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();