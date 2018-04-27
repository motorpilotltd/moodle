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
 * @copyright  2017 Xantico Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
$ahbuserid = optional_param('ahbuserid', 0, PARAM_INT);
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


$context = context_module::instance($cm->id);
$ahb = $DB->get_record('arupevidence',  array('id' => $cm->instance));
$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
$outputcache = '';

require_login($course, false, $cm);

// Redirect to course, if user is not approver
$isuserapprover = arupevidence_isapprover($ahb, $USER);
if (!$isuserapprover || !$ahb->approvalrequired) {
    redirect($courseurl);
}

$title =  get_string('approveahbcompletion', 'mod_arupevidence') . ': '. $ahb->name ;
$PAGE->set_title($title);

$output = $PAGE->get_renderer('mod_arupevidence');

$PAGE->set_url(new moodle_url('/mod/arupevidence/view.php', array('id' => $id)));
$PAGE->requires->css('/mod/arupevidence/styles.css');
$PAGE->requires->js_call_amd('mod_arupevidence/approve', 'init');
echo $OUTPUT->header();

echo html_writer::tag('h2', $title);

$params = array('arupevidenceid' => $cm->instance, 'id' => $ahbuserid, 'archived' => 0);
$ahbuser = $DB->get_record('arupevidence_users', $params, '*', IGNORE_MULTIPLE);

echo $outputcache;

$usernamefields = get_all_user_name_fields(true, 'u');

$ahbusersql = <<<EOS
    SELECT 
        au.*,
        {$usernamefields},
        {$DB->sql_fullname('ur.firstname', 'ur.lastname')} as rejectedby,
        {$DB->sql_fullname('ua.firstname', 'ua.lastname')} as approvedby,
        u.email
    FROM
        {arupevidence_users} au
    JOIN 
        {arupevidence}  a
        ON a.id = au.arupevidenceid
    LEFT JOIN {user} ur 
        ON ur.id = au.rejectedbyid  
    LEFT JOIN {user} ua 
        ON ua.id = au.approverid           
    JOIN 
        {user} u
        ON u.id = au.userid
    WHERE 
        au.arupevidenceid = {$ahb->id}
        AND au.archived <> 1
    ;
EOS;
$ahbuserlists = $DB->get_records_sql($ahbusersql);
//echo ;
$evidencetable = new stdClass();
$rejectedlist = array();
$approvedlist = array();
$approvallist = array();

foreach ($ahbuserlists as $ahbuserlist) {
    if ($ahbuserlist->completion) {
        $approvedlist[] = $ahbuserlist;
    } else if ($ahbuserlist->rejected) {
        $rejectedlist[] = $ahbuserlist;
    } else {
        $approvallist[] = $ahbuserlist;
    }
}

$evidencetable->approvaltable = $output->completion_approval_list($approvallist, $context);
$evidencetable->approvedtable = $output->completion_approval_list($approvedlist, $context);
$evidencetable->rejectedtable = $output->completion_approval_list($rejectedlist, $context, true);

if (!empty($SESSION->arupevidence->alert)) {
    echo $output->alert($SESSION->arupevidence->alert->message, $SESSION->arupevidence->alert->type);
    unset($SESSION->arupevidence->alert);
}

echo $OUTPUT->render_from_template('mod_arupevidence/print_evidenceapproval_tab',$evidencetable);
echo $OUTPUT->render_from_template('mod_arupevidence/evidence_modals', array());
echo $output->return_to_course($course->id);
echo $OUTPUT->footer();