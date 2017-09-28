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

require(dirname(__FILE__).'/../../../config.php');

$iwid = optional_param('iw', 0, PARAM_INT);
$cmid = optional_param('cm', 0, PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);

$course = false;
$cm = false;
$iw = false;

$context = false;

// If courseid and cmid, trying to edit emails for instance.
// If iwid, trying to edit worfklow emails.
// If none of the above editing global emails.

// If instance, then need workflow, global, default emails.
// If workflow need global, default emails.
// If global need default emails.

if ($courseid && $cmid) {
    // Instance.
    $course = $DB->get_record('course', array('id' => $courseid));
    if (!$course) {
        throw new moodle_exception('invalidcourse', 'error');
    }
    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->get_cm($cmid); // Will throw own exception.
    $tapsenrol = $DB->get_record('tapsenrol', array('id' => $cm->instance));
    if (!$tapsenrol) {
        throw new moodle_exception('invalidmodule', 'error');
    }
    $iw = $DB->get_record('tapsenrol_iw', array('id' => $tapsenrol->internalworkflowid));
    if (!$iw) {
        throw new moodle_exception('invalidworkflow', 'tapsenrol');
    }
    require_login($course, false, $cm);
    $context = context_module::instance($cm->id);
    require_capability('moodle/course:manageactivities', $context);
} else if ($iwid) {
    // Workflow.
    $iw = $DB->get_record('tapsenrol_iw', array('id' => $iwid));
    if (!$iw) {
        throw new moodle_exception('invalidworkflow', 'tapsenrol');
    }
    require_login();
    $context = context_system::instance();
    if (!has_any_capability(array('mod/tapsenrol:internalworkflow_edit', 'mod/tapsenrol:internalworkflow'), $context)) {
        throw new required_capability_exception($context, $capability, 'nopermissions', '');
    }
} else {
    // Global.
    require_login();
    $context = context_system::instance();
    require_capability('mod/tapsenrol:internalworkflow', $context);
}

$viewonly = !$cm && !empty($iw->locked);
if ($cm) {
    $viewonly = !has_any_capability(array('mod/tapsenrol:internalworkflow_edit_activity', 'mod/tapsenrol:internalworkflow_edit', 'mod/tapsenrol:internalworkflow'), $context);
}

// Set some strings and load some data.
$emails = array(
    'default' => $DB->get_records('tapsenrol_iw_email', null, 'sortorder ASC'),
);
if ($cm) {
    $title = $viewonly ? get_string('iw:emails:title:cm:view', 'tapsenrol') : get_string('iw:emails:title:cm:edit', 'tapsenrol');
    $emails['cm'] = $DB->get_records('tapsenrol_iw_email_custom', array('coursemoduleid' => $cm->id, 'internalworkflowid' => null), '', 'emailid, *');
}
if ($iw) {
    if (empty($title)) {
        $title = $viewonly ? get_string('iw:emails:title:iw:view', 'tapsenrol') : get_string('iw:emails:title:iw:edit', 'tapsenrol');
    }
    $emails['iw'] = $DB->get_records('tapsenrol_iw_email_custom', array('coursemoduleid' => null, 'internalworkflowid' => $iw->id), '', 'emailid, *');
}

if (empty($title)) {
    $title = get_string('iw:emails:title:global', 'tapsenrol');
}
$emails['global'] = $DB->get_records('tapsenrol_iw_email_custom', array('coursemoduleid' => null, 'internalworkflowid' => null), '', 'emailid, *');

$PAGE->set_url('/mod/tapsenrol/admin/emails.php');
$PAGE->set_context($context);

$PAGE->set_title($title);
$PAGE->add_body_class('path-mod-tapsenrol-iw');

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/tapsenrol/js/jquery.browser.js', false);
$PAGE->requires->js('/mod/tapsenrol/js/jquery.iframe-auto-height.plugin.1.9.5.min.js', false);
$PAGE->requires->js('/mod/tapsenrol/js/tapsenrol.js', false);

$output = $PAGE->get_renderer('mod_tapsenrol');

// Build View.
$echo = '';

require_once($CFG->dirroot.'/mod/tapsenrol/admin/forms/emails_form.php');

$type = 'global';
$returnurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php');
if (isset($emails['cm'])) {
    $type = 'cm';
    $returnurl = new moodle_url('/course/modedit.php', array('update' => $cm->id));
} else if (isset($emails['iw'])) {
    $type = 'iw';
    $returnurl->param('action', 'edit');
    $returnurl->param('id', $iw->id);
}
if ($viewonly) {
    $returnbutton = html_writer::link($returnurl, get_string('iw:return:view:'.$type, 'tapsenrol'), array('class' => 'btn btn-primary'));
} else {
    $returnbutton = html_writer::link($returnurl, get_string('iw:return:'.$type, 'tapsenrol'), array('class' => 'btn btn-primary'));
}
$customdata = array(
    'emails' => $emails,
    'type' => $type,
    'params' => array('course' => $courseid, 'cm' => $cmid, 'iw' => $iwid),
    'viewonly' => $viewonly
);

$form = new emails_form(null, $customdata);

if ($form->is_cancelled()) {
    if ($viewonly) {
        redirect($returnurl);
    } else {
        redirect($returnurl, get_string('iw:emails:redirect:cancelled', 'tapsenrol'));
    }
    exit;
}

$fromform = $form->get_data();
if ($fromform) {
    if ($viewonly) {
        redirect($returnurl, get_string('iw:emails:redirect:viewonly', 'tapsenrol'));
        exit;
    }
    foreach ($fromform->email as $emailid) {
        $email = new stdClass();
        $email->internalworkflowid = ($iw && !$cm) ? $iw->id : null;
        $email->coursemoduleid = $cm ? $cm->id : null;
        $email->emailid = $emailid;

        $existingemail = $DB->get_record(
            'tapsenrol_iw_email_custom',
            array(
                'internalworkflowid' => $email->internalworkflowid,
                'coursemoduleid' => $email->coursemoduleid,
                'emailid' => $email->emailid,
            )
        );

        if ($fromform->subject[$emailid] && $fromform->body[$emailid]) {
            $email->subject = $fromform->subject[$emailid];
            $email->body = $fromform->body[$emailid];
            $email->html = $fromform->html[$emailid];
            if ($existingemail) {
                $email->id = $existingemail->id;
                $DB->update_record('tapsenrol_iw_email_custom', $email);
            } else {
                $email->id = $DB->insert_record('tapsenrol_iw_email_custom', $email);
            }
        } else if ($existingemail) {
            $DB->delete_records('tapsenrol_iw_email_custom', array('id' => $existingemail->id));
        }
    }
    redirect($returnurl, get_string('iw:emails:redirect:saved', 'tapsenrol'));
    exit;
}

// Output View.
echo $OUTPUT->header();

echo $OUTPUT->heading($title);

echo html_writer::tag('p', $returnbutton);

$form->display();

echo html_writer::tag('p', $returnbutton);

echo $OUTPUT->footer();

// Modal window just before ending body tag.
echo $output->modal('iw-email-modal', get_string('iw:emails:preview', 'tapsenrol'));