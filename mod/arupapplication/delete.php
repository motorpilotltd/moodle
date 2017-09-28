<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);   // course module
$submissionid = required_param('submissionid', PARAM_INT);   // submission id
$confirm = optional_param('confirm', 0 ,PARAM_INT);

if (!$cm = get_coursemodule_from_id('arupapplication', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (!$arupapplication = $DB->get_record("arupapplication", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

if (!$context) {
    print_error('badcontext');
}

if (!has_capability('mod/arupapplication:deleteapplication', $context)) {
    notice(get_string('cannotdeletesubmission', 'arupapplication'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

$PAGE->set_url('/mod/arupapplication/delete.php', array('id' => $id, 'submissionid' => $submissionid));

$submissiondetails = arupapplication_submissionsdetails($submissionid);

$confirmurl = new moodle_url('/mod/arupapplication/delete.php', array('id' => $id, 'submissionid' => $submissionid, 'confirm' => 1));
$redirecturl = new moodle_url('/mod/arupapplication/view.php', array('id' => $id));

if (!$submissiondetails) {
    notice(get_string('cannotdeletesubmission', 'arupapplication'), new moodle_url('/course/view.php', array('id' => $course->id)));
} elseif ($confirm) {
    // Delete submission data
    $DB->delete_records('arupdeclarationanswers', array('userid' => $submissiondetails->userid, 'applicationid' => $submissiondetails->applicationid));
    $DB->delete_records('arupstatementanswers', array('userid' => $submissiondetails->userid, 'applicationid' => $submissiondetails->applicationid));
    $DB->delete_records('arupapplication_tracking', array('userid' => $submissiondetails->userid, 'applicationid' => $submissiondetails->applicationid));
    $DB->delete_records('arupsubmissions', array('id' => $submissiondetails->id));
    //  Delete area files
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_arupapplication', 'submission', $submissiondetails->id);
    //  Reset up completion
    $completion = new completion_info($course);
    $completion->update_state($cm, COMPLETION_INCOMPLETE);
    // Log
    $event = \mod_arupapplication\event\submission_deleted::create(array(
        'context' => $context,
        'objectid' => $submissionid,
    ));
    $event->trigger();
    // Redirect
    redirect($redirecturl, get_string('deletesuccess', 'arupapplication'));
    exit;
}

$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($arupapplication->name));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('heading:deletesubmission', 'arupapplication'));

$continue = html_writer::link($confirmurl, get_string('continue'), array('class' => 'btn btn-primary'));
$cancel = html_writer::link($redirecturl, get_string('cancel'), array('class' => 'btn btn-default m-l-10'));

echo html_writer::tag('p', get_string('confirmdeletesubmission', 'arupapplication', fullname($submissiondetails)));
echo html_writer::tag('div', $continue . $cancel, array('class' => 'buttons'));

echo $OUTPUT->footer();