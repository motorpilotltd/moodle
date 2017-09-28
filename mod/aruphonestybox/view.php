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
 *
 * @package    mod_aruphonestybox
 * @copyright  2014 Paul Stanyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/aruphonestybox/forms/completion_form.php');
require_once($CFG->dirroot.'/mod/aruphonestybox/lib.php');

 // Course Module ID.
if(!$id = required_param('id', PARAM_INT)) {
    print_error('missingparameter');
}

if (!$cm = get_coursemodule_from_id('aruphonestybox', $id)) {
    print_error('invalidcoursemodule');
}


if(!$course = $DB->get_record('course', array('id' => $cm->course))){
    print_error('coursemisconf');
}

$action = optional_param('action', '', PARAM_ALPHA);
$ahbuserid = optional_param('ahbuserid', 0, PARAM_INT);


$context = context_module::instance($cm->id);

$ahb = $DB->get_record('aruphonestybox',  array('id' => $cm->instance));

require_login($course, false, $cm);

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
$viewurl = new moodle_url('/mod/aruphonestybox/view.php', array('id' => $id));
$approveurl = new moodle_url('/mod/aruphonestybox/approve.php', array('id' => $id));
$outputcache = '';
$params = array();
$params['aruphonestyboxid'] = $cm->instance;

if(!empty($ahbuserid)) {
    $params['id'] = $ahbuserid;
} else {
    $params['userid'] = $USER->id;
}

$ahbuser = $DB->get_record('aruphonestybox_users', $params, '*', IGNORE_MULTIPLE);


$output = $PAGE->get_renderer('mod_aruphonestybox');
$content = '';
$customdata = array(
    'showdate' => (!empty($ahb->showcompletiondate) && $ahb->showcompletiondate ),
    'showfilemanager' => (!empty($ahb->showcertificateupload) && $ahb->showcertificateupload ),
    'aruphonestyboxuser' => $ahbuser,
    'action' => $action,
    'contextid' => $context->id
);

$mform = new mod_aruphonestybox_completion_form($viewurl, $customdata);
if ($mform->is_cancelled() || (!empty($ahbuser) && !has_capability('mod/aruphonestybox:approvecompletion', $context) && $USER->id != $ahbuser->userid)) {
   redirect($courseurl);
} else if($data = $mform->get_data()) {

    // checks if the data has been updated from the database
    $ismodified = (isset($data->timemodified ) && $data->timemodified != $ahbuser->timemodified);

    if((isset($ahbuser->completion) && $ahbuser->completion) || (isset($data->approved) && $data->approved)) {
        // Already completed or approved
        $outputcache .= $output->alert(get_string('approve:requestapproved', 'mod_aruphonestybox'), 'alert alert-warning', false);
    } else if ($ismodified) {

        $editahbuserpage_url = get_local_referer(false);

        $content .= $output->alert(get_string('datamodified', 'mod_aruphonestybox'), 'alert alert-warning', false);
        $html_btn = html_writer::tag('button', get_string('reviewchanges', 'mod_aruphonestybox'));
        $content .= html_writer::link($editahbuserpage_url, $html_btn);
    } else {
        $itemid = (isset($data->action) && $data->action == 'edit') && isset($data->ahbuserid) ? $ahbuser->userid : $USER->id ;
        file_save_draft_area_files(
            $data->completioncertificate,
            $context->id,
            'mod_aruphonestybox',
            'certificate',
            $itemid, // set userid as itemid
            array(
                'subdirs' => 0,
                'maxbytes' => $COURSE->maxbytes,
                'maxfiles' => 1
            )
        );

        if(isset($data->action) && $data->action == 'edit') {
            // Modify existing completion information
            $ahbuser->completiondate = $data->completiondate;
            $ahbuser->timemodified = time();
            $DB->update_record('aruphonestybox_users', $ahbuser);
        } else {
            // New completion information aruphonestybox_users

            // Remove any current records.
            $DB->delete_records('aruphonestybox_users', array(
                'aruphonestyboxid' => $cm->instance,
                'userid' => $USER->id
            ));

            $ahbuser = new stdClass;
            $ahbuser->aruphonestyboxid = $cm->instance;
            $ahbuser->userid = $USER->id;
            $ahbuser->completion = ($ahb->approvalrequired)? 0 : 1 ;
            $ahbuser->taps = ($ahb->approvalrequired)? 0 : 1 ;
            $ahbuser->approved = null;
            $ahbuser->timemodified = time();
            $ahbuser->completiondate = $data->completiondate;
            $DB->insert_record('aruphonestybox_users', $ahbuser);

            if($ahb->approvalrequired && !empty($ahb->email)) {
                $user = clone($USER);
                $to = aruphonestybox_user::get_dummy_aruphonestybox_user($ahb->email, $ahb->firstname, $ahb->lastname);
                $subject = get_string('email:subject', 'mod_aruphonestybox');
                $messagebody = get_string('email:body', 'mod_aruphonestybox', array(
                    'approverfirstname' => $to->firstname,
                    'approvalurl' => $approveurl->out(),
                    'userfirstname' => $user->firstname,
                    'userlastname' => $user->lastname
                ));
                $sendnotification = aruphonestybox_send_email($to, $user, $subject, $messagebody);
            }
        }

        // do adding cpd only when approval is off
        if(!$ahb->approvalrequired) {
            $result = aruphonestybox_sendtotaps($cm->instance, $USER, $debug);
            $return = aruphonestybox_process_result($result, $debug);

            if ($return->success == true) {
                $params = array(
                    'context' => context_module::instance($cm->id),
                    'courseid' => $course->id,
                    'objectid' => $cm->instance,
                    'relateduserid' => $USER->id,
                    'other' => array(
                        'automatic' => false,
                    )
                );

                $logevent = \mod_aruphonestybox\event\cpd_request_sent::create($params);
                $logevent->trigger();
            }

            $completion = new completion_info($course);

            if ($completion->is_enabled($cm)) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
                $debug[] = 'Updated the completion state';
            }
        }

        if (has_capability('mod/aruphonestybox:approvecompletion', $context)) {
            redirect($approveurl);
        }
        redirect($courseurl);
    }

    if(!empty($outputcache)) {
        $content .= $outputcache;
        $content .= $OUTPUT->continue_button('/course/view.php?id='.$course->id);  // Back to course page
    }

} else {

    if($ahbuser && $ahbuser->completion) {

        $table = new html_table();
        $table->data = array();

        if($ahbuser->completiondate) {
            $label = html_writer::label(get_string('completiondate', 'mod_aruphonestybox'), 'completiondatedisplay');
            $value = html_writer::div(userdate($ahbuser->completiondate,'%A, %d %B %Y') ,'completiondatedisplay');
            $table->data[] = array($label, $value);
        }

        // Show link to the uploaded certificate file
        $certificatelink = $output->format_user_certificatelink($context, $USER->id);
        $label = html_writer::label(get_string('viewcertificatefile', 'mod_aruphonestybox'), 'certificatelinkdisplay');
        $linkfile = html_writer::link($certificatelink,'');
        $value = html_writer::div($certificatelink ,'completiondatedisplay');
        $table->data[] = array($label, $value);

        $content .= $output->alert(get_string('approve:requestapproved', 'mod_aruphonestybox'), 'alert alert-warning', false);

        $content .= html_writer::start_div('container col-md-8 aruphonesty-table');
        $content .= html_writer::table($table);
        $content .= $OUTPUT->continue_button('/course/view.php?id='.$course->id);  // Back to course page
        $content .= html_writer::end_div();

    } else {

        $content .= $mform->render();
    }

}

$title = $course->fullname . ': ' . $ahb->name;
$PAGE->set_title($title);
$PAGE->set_url($viewurl);
$PAGE->requires->css('/mod/aruphonestybox/styles.css');
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('modulename', 'mod_aruphonestybox') . ': '. $ahb->name);

echo $content;

echo $OUTPUT->footer();