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
 * @package    mod_arupevidence
 * @copyright  2014 Paul Stanyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/arupevidence/forms/completion_form.php');
require_once($CFG->dirroot.'/mod/arupevidence/lib.php');

 // Course Module ID.
if(!$id = required_param('id', PARAM_INT)) {
    print_error('missingparameter');
}

if (!$cm = get_coursemodule_from_id('arupevidence', $id)) {
    print_error('invalidcoursemodule');
}


if(!$course = $DB->get_record('course', array('id' => $cm->course))){
    print_error('coursemisconf');
}

$action = optional_param('action', '', PARAM_ALPHA);
$ahbuserid = optional_param('ahbuserid', 0, PARAM_INT);


$context = context_module::instance($cm->id);
$contextcourse = context_course::instance($course->id);

$ahb = $DB->get_record('arupevidence',  array('id' => $cm->instance));

require_login($course, false, $cm);

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
$viewurl = new moodle_url('/mod/arupevidence/view.php', array('id' => $id));
$approveurl = new moodle_url('/mod/arupevidence/approve.php', array('id' => $id));
$outputcache = '';
$params = array();
$params['arupevidenceid'] = $cm->instance;
$params['archived'] = 0;

if(!empty($ahbuserid)) {
    $params['id'] = $ahbuserid;
} else {
    $params['userid'] = $USER->id;
}

$ahbuser = $DB->get_record('arupevidence_users', $params, '*', IGNORE_MULTIPLE);

$output = $PAGE->get_renderer('mod_arupevidence');
$content = '';
$customdata = array(
    'arupevidenceuser' => $ahbuser,
    'action' => $action,
    'contextid' => $context->id,
    'arupevidence' => $ahb
);

$mform = new mod_arupevidence_completion_form($viewurl, $customdata);
if ($mform->is_cancelled() || (!empty($ahbuser) && !has_capability('mod/arupevidence:approvecompletion', $context) && $USER->id != $ahbuser->userid)) {
   redirect($courseurl);
} else if($data = $mform->get_data()) {

    // checks if the data has been updated from the database
    $ismodified = (isset($data->timemodified ) && $data->timemodified != $ahbuser->timemodified);

    if((isset($ahbuser->completion) && $ahbuser->completion) || (isset($data->approved) && $data->approved)) {
        // Already completed or approved
        $outputcache .= $output->alert(get_string('approve:requestapproved', 'mod_arupevidence'), 'alert alert-warning', false);
    } else if ($ismodified) {

        $editahbuserpage_url = get_local_referer(false);

        $content .= $output->alert(get_string('datamodified', 'mod_arupevidence'), 'alert alert-warning', false);
        $html_btn = html_writer::tag('button', get_string('reviewchanges', 'mod_arupevidence'));
        $content .= html_writer::link($editahbuserpage_url, $html_btn);
    } else {
        $itemid = (isset($data->action) && $data->action == 'edit') && isset($data->ahbuserid) ? $ahbuser->userid : $USER->id ;
        file_save_draft_area_files(
            $data->completioncertificate,
            $context->id,
            'mod_arupevidence',
            'certificate',
            $itemid, // set userid as itemid
            array(
                'subdirs' => 0,
                'maxbytes' => $COURSE->maxbytes,
                'maxfiles' => 1
            )
        );

        try {
            // escape/remove special characters
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_arupevidence', 'certificate', $itemid);
            if ($files) {
                foreach ($files as $file) {
                    $pattern = "/[^A-Za-z0-9\_\s\-\.]/";
                    if (($itemid == $file->get_itemid() && $file->get_source() != null && preg_match($pattern, $file->get_filename()))) {
                        $newfilename = core_text::specialtoascii($file->get_filename());
                        $file->rename($file->get_filepath(), $newfilename);
                    }
                }
            }
        } catch (Exception $e) {}

        $arupevidencedata = array(
            'completiondate' => $data->completiondate,
            'expirydate' => !empty($data->expirydate) ? $data->expirydate : 0,
            'validityperiod' => !empty($data->validityperiod) ? $data->validityperiod : '',
            'validityperiodunit' => !empty($data->validityperiodunit) ? $data->validityperiodunit : ''
        );

        if ($ahb->cpdlms == ARUPEVIDENCE_CPD) {
            $cpddetails = array(
                'provider' => $data->provider,
                'duration' => $data->duration,
                'durationunitscode' => $data->durationunitscode,
                'location' => $data->location,
                'classstartdate' => $data->classstartdate,
                'classcost' => !empty($data->classcost) ? $data->classcost : null,
                'classcostcurrency' => $data->classcostcurrency,
                'certificateno' => $data->certificateno);
            $arupevidencedata = array_merge($arupevidencedata, $cpddetails);
        }

        $neworrejected = false;
        if(isset($data->action) && $data->action == 'edit') {
            // Modify existing completion information
            foreach($arupevidencedata as $key => $value) {
                $ahbuser->{$key} = $value;
            }
            $ahbuser->timemodified = time();
            $ahbuser->itemid = !empty($data->enrolmentid) ? $data->enrolmentid : '';

            if (!empty($ahbuser->rejected) && $ahbuser->userid == $USER->id) {
                $neworrejected = true;
            }
            //removing current rejection info
            $ahbuser->rejected = null;
            $ahbuser->rejectedbyid = null;
            $ahbuser->rejectmessage = null;

            $DB->update_record('arupevidence_users', $ahbuser);
        } else {
            // New completion information arupevidence_users
            // Archive any current records.
            $updatesql = "UPDATE {arupevidence_users} SET archived = 1, timemodified = :timemodified WHERE arupevidenceid = :arupevidenceid AND userid = :userid";
            $updateparams = [
                'arupevidenceid' => $cm->instance,
                'userid' => $USER->id,
                'timemodified' => time(),
            ];
            $DB->execute($updatesql, $updateparams);

            $ahbuser = new stdClass;
            $ahbuser->arupevidenceid = $cm->instance;
            $ahbuser->userid = $USER->id;
            $ahbuser->completion = ($ahb->approvalrequired)? 0 : 1 ;
            $ahbuser->itemid = ($ahb->cpdlms == ARUPEVIDENCE_LMS)? $data->enrolmentid : null;
            $ahbuser->approved = null;
            $ahbuser->timemodified = time();

            // Appends input data from user
            foreach($arupevidencedata as $key => $value) {
                $ahbuser->{$key} = $value;
            }

            $DB->insert_record('arupevidence_users', $ahbuser);
            $neworrejected = true;
        }

        // Send mail to the approvers
        if($ahb->approvalrequired && $neworrejected == true) {

            $approverlists = arupevidence_get_user_approvers($ahb, $contextcourse);

            $user = clone($USER);
            foreach ($approverlists as $approverto) {
                $subject = get_string('email:subject', 'mod_arupevidence');
                $messagebody = get_string('email:body', 'mod_arupevidence', array(
                    'approverfirstname' => $approverto->firstname,
                    'approvalurl' => $approveurl->out(),
                    'userfirstname' => $user->firstname,
                    'userlastname' => $user->lastname
                ));
                $sendnotification = arupevidence_send_email($approverto, $user, $subject, $messagebody);
            }
        }

        if (has_capability('mod/arupevidence:approvecompletion', $context)) {
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

        // Completion date
        $label = html_writer::label(get_string('completiondate', 'mod_arupevidence'), 'completiondatedisplay');
        $value = html_writer::div(userdate($ahbuser->completiondate,'%A, %d %B %Y') ,'completiondatedisplay');
        $table->data[] = array($label, $value);

        // Expiry date
        $label = html_writer::label(get_string('label:expirydate', 'mod_arupevidence'), 'expirydatedisplay');
        $value = html_writer::div(userdate($ahbuser->expirydate,'%A, %d %B %Y') ,'expirydatedisplay');
        $table->data[] = array($label, $value);

        // Date Approved date
        $label = html_writer::label(get_string('approve:dateapproved', 'mod_arupevidence'), 'approveddatedisplay');
        $value = html_writer::div(userdate($ahbuser->approved,'%A, %d %B %Y') ,'approveddatedisplay');
        $table->data[] = array($label, $value);

        // Approved By
        $user = $DB->get_record('user', array('id' => $ahbuser->approverid), 'firstname, lastname, email');
        $label = html_writer::label(get_string('approve:approvedby', 'mod_arupevidence'), 'approvedbylabel');
        $fullname = $user->firstname . ' ' . $user->lastname . '(' . $user->email . ')';
        $value = html_writer::div($fullname ,'approvedbylabel');
        $table->data[] = array($label, $value);

        // Show link to the uploaded certificate file
        $certificatelink = $output->format_user_certificatelink($ahbuser, $context);
        $label = html_writer::label(get_string('viewcertificatefile', 'mod_arupevidence'), 'certificatelinkdisplay');
        $linkfile = html_writer::link($certificatelink,'');
        $value = html_writer::div($certificatelink ,'completiondatedisplay');
        $table->data[] = array($label, $value);

        $content .= $output->alert(get_string('approve:requestapproved', 'mod_arupevidence'), 'alert alert-warning', false);

        $content .= html_writer::start_div('container col-md-8 arupevidence-table');
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
$PAGE->requires->css('/mod/arupevidence/styles.css');

$arguments = array(
    'validityperiod' =>  $ahb->expectedvalidityperiod,
    'validityperiodunit' => $ahb->expectedvalidityperiodunit,
);
$PAGE->requires->js_call_amd('mod_arupevidence/view', 'init', $arguments);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('modulename', 'mod_arupevidence') . ': '. $ahb->name);

if ($ahb->intro) {
    echo html_writer::tag('div', format_module_intro('arupevidence', $ahb, $cm->id), array('style' => 'margin-bottom: 15px;'));
}

echo $content;

echo $output->return_to_course($course->id);

echo $OUTPUT->footer();