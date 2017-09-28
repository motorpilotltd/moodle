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
 * @copyright  2017 Aleks Daloso
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
$action = optional_param('action', '', PARAM_ALPHA);
$ahbuserid = optional_param('ahbuserid', 0, PARAM_INT);
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


$context = context_module::instance($cm->id);
$ahb = $DB->get_record('aruphonestybox',  array('id' => $cm->instance));
$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
$outputcache = '';

require_login($course, false, $cm);

// Redirect to course, if user has no capability
if (!has_capability('mod/aruphonestybox:approvecompletion', $context) || !$ahb->approvalrequired) {
    redirect($courseurl);
}



$title =  get_string('approveahbcompletion', 'mod_aruphonestybox') . ': '. $ahb->name ;
$PAGE->set_title($title);

$output = $PAGE->get_renderer('mod_aruphonestybox');

$PAGE->set_url(new moodle_url('/mod/aruphonestybox/view.php', array('id' => $id)));
$PAGE->requires->css('/mod/aruphonestybox/styles.css');
echo $OUTPUT->header();

echo html_writer::tag('h2', $title);


if(!empty($action) && $action == 'approve') {
    $params = array('aruphonestyboxid' => $cm->instance, 'id' => $ahbuserid);

    if($ahbuser = $DB->get_record('aruphonestybox_users', $params, '*', IGNORE_MULTIPLE)) {
        if ($ahbuser->completion) {
            // completion already approved
            $outputcache .= $output->alert(get_string('approve:alreadyapproved', 'mod_aruphonestybox'), 'alert-warning', true);
        } else if($ahbuser->userid == $USER->id) {
            // approver can't approve their own request
            $outputcache .= $output->alert(get_string('approve:cannotapproveown', 'mod_aruphonestybox'), 'alert-warning', true);
        } else {
            $user = core_user::get_user($ahbuser->userid, '*', MUST_EXIST);
            $result = aruphonestybox_sendtotaps($cm->instance, $user, $debug);
            $return = aruphonestybox_process_result($result, $debug);

            if ($return->success == true) {
                $params = array(
                    'context' => context_module::instance($cm->id),
                    'courseid' => $course->id,
                    'objectid' => $cm->instance,
                    'relateduserid' => $user->id,
                    'other' => array(
                        'automatic' => false,
                    )
                );

                $logevent = \mod_aruphonestybox\event\cpd_request_sent::create($params);
                $logevent->trigger();


                // Update aruphonestybox_user completion status
                $ahbuser->approved = time();
                $ahbuser->approverid = $USER->id;
                $ahbuser->completion = 1 ;
                $ahbuser->taps = 1 ;
                $DB->update_record('aruphonestybox_users', $ahbuser);
            }

            $completion = new completion_info($course);

            if ($completion->is_enabled($cm)) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
                $debug[] = 'Updated the completion state';
            }

            // completion already approved
            $outputcache .= $output->alert(get_string('approve:successapproved', 'mod_aruphonestybox'), 'alert-success', true);
        }
    }
}

echo $outputcache;

$usernamefields = get_all_user_name_fields(true, 'u');
$ahbusersql = <<<EOS
    SELECT 
        au.*,
        {$usernamefields},
        u.email
    FROM
        {aruphonestybox_users} au
    JOIN 
        {aruphonestybox}  a
        ON a.id = au.aruphonestyboxid
    JOIN 
        {user} u
        ON u.id = au.userid
    WHERE 
        au.aruphonestyboxid = {$ahb->id}
    ;
EOS;
$ahbuserlists = $DB->get_records_sql($ahbusersql);

echo $output->completion_approval_list($ahbuserlists, $context, $id);

echo $OUTPUT->footer();