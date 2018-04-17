<?php
// This file is part of the Arup cost centre local plugin
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
 * Version details
 *
 * @package     mod_arupevidence
 * @copyright   2016 Motorpilot Ltd
 * @author      Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/mod/arupevidence/lib.php');

require_login();

$searchterm = optional_param('q', '', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);

$action = optional_param('action', '', PARAM_ALPHA);

$arupevidence_userid = optional_param('ae_userid', '', PARAM_INT);
$id = optional_param('id', '', PARAM_INT);
$reject_message = optional_param('reject_message', '', PARAM_ALPHA);

if (!empty($action)) {
    $result = new stdClass();
    $result->success = false;
    $result->message = '';
    $result->data = array();

    if (!$cm = get_coursemodule_from_id('arupevidence', $id)) {
        print_error('invalidcoursemodule');
    }

    if(!$course = $DB->get_record('course', array('id' => $cm->course))){
        print_error('coursemisconf');
    }

    $context = context_module::instance($cm->id);
    $arupevidence = $DB->get_record('arupevidence',  array('id' => $cm->instance));

    $isuserapprover = arupevidence_isapprover($arupevidence, $USER);

    try {
        $params = array('arupevidenceid' => $cm->instance, 'id' => $arupevidence_userid, 'archived' => 0);
        $ae_user = $DB->get_record('arupevidence_users', $params, '*', IGNORE_MULTIPLE);
        $user = $DB->get_record('user',  array('id' => $ae_user->userid));
        if ($action == 'reject' && $isuserapprover) {
            if(!$ae_user->rejected) {
                $ae_user->rejected = time();
                $ae_user->rejectmessage = $reject_message;

                $DB->update_record('arupevidence_users', $ae_user);

                $editlink = new moodle_url('/mod/arupevidence/view.php', array('id' => $cm->id, 'action' => 'edit', 'ahbuserid' => $ae_user->id));
                $to = clone($USER);

                // send email notification
                $messagecontent = get_string('email:reject:content', 'mod_arupevidence', array(
                    'firstname' => $user->firstname,
                    'evidenceeditlink' => $editlink->out(),
                    'approvercomment' => $reject_message,
                ));
                arupevidence_send_email($user, $to, get_string('email:reject:subject', 'mod_arupevidence'), $messagecontent);

                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('reject:evidencerejected', 'mod_arupevidence');
                $SESSION->arupevidence->alert->type = 'alert-success';

            } else { // already rejected by the other approver
                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('reject:evidencerejectedalready', 'mod_arupevidence');
                $SESSION->arupevidence->alert->type = 'alert-warning';
            }

            $result->success = true;
        } else if ($action == 'approve' && $isuserapprover) {
            if(!$ae_user->approved) {
                // Update arupevidence_user completion status
                $ae_user->approved = time();
                $ae_user->approverid = $USER->id;
                $ae_user->rejected = null;
                $ae_user->completion = 1 ;
                // get uploader fileinfo
                $file = arupevidence_fileinfo($context, $ae_user->userid);

                $itemid = 0;
                $filearea = null;
                if ($arupevidence->cpdlms == ARUPEVIDENCE_CPD) {

                    $user = core_user::get_user($ae_user->userid, '*', MUST_EXIST);
                    $cpd = arupevidence_sendtotaps($cm->instance, $user, $debug);
                    $return = arupevidence_process_result($cpd, $debug);

                    if ($return->success == true) {
                        $ae_user->taps = 1 ;
                        $ae_user->itemid = $cpd;
                        $params = array(
                            'context' => $context,
                            'courseid' => $course->id,
                            'objectid' => $cm->instance,
                            'relateduserid' => $user->id,
                            'other' => array(
                                'automatic' => false,
                            )
                        );

                        $logevent = \mod_arupevidence\event\cpd_request_sent::create($params);
                        $logevent->trigger();

                        $completion = new completion_info($course);

                        if ($completion->is_enabled($cm)) {
                            $completion->update_state($cm, COMPLETION_COMPLETE);
                            $debug[] = 'Updated the completion state';
                        }

                        $itemid = $cpd;
                        $filearea = ARUPEVIDENCE_CPD;
                    }

                } else if ($arupevidence->cpdlms == ARUPEVIDENCE_LMS) {
                    $itemid = $ae_user->itemid;
                    $filearea = ARUPEVIDENCE_LMS;
                }
                $filearea = arupevidence_fileareaname($filearea);
                arupevidence_move_filearea($context, $file, $filearea, $itemid);
                // Update user's record
                $DB->update_record('arupevidence_users', $ae_user);

                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('approve:successapproved', 'mod_arupevidence');
                $SESSION->arupevidence->alert->type = 'alert-success';
            } else { // already approved by the other approver
                $SESSION->arupevidence->alert = new stdClass();
                $SESSION->arupevidence->alert->message = get_string('approve:alreadyapproved', 'mod_arupevidence');
                $SESSION->arupevidence->alert->type = 'alert-warning';
            }
            $result->success = true;
        }
    } catch (Exception $e) {
        $SESSION->arupevidence->alert = new stdClass();
        $SESSION->arupevidence->alert->message = $e->getMessage();
        $SESSION->arupevidence->alert->type = 'alert-warning';
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;

} else if (has_capability('mod/arupevidence:addinstance', context_course::instance($courseid), $USER->id)) {
    $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
    $searchconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' '", 'email', "' '", 'idnumber');
    $searchlike = $DB->sql_like($searchconcat, ":searchterm", false);
    $params = array('searchterm'=> "%$searchterm%");
    $where = "deleted = 0 AND suspended = 0 AND confirmed = 1 AND $searchlike";
    $totalcount = $DB->count_records_select('user', $where, $params);
    $userlist = $DB->get_records_select_menu('user', $where, $params, 'lastname ASC', "id, $usertextconcat", ($page - 1) * 25, $page * 25);

    $json = array('totalcount' => $totalcount, 'items' => array());
    foreach ($userlist as $uid => $usertext) {
        $json['items'][] = array('text' => $usertext, 'id' => $uid);
    }
    echo json_encode($json);
} else {
    $SESSION->arupevidence->alert = new stdClass();
    $SESSION->arupevidence->alert->message = get_string('alert:approveronly', 'mod_arupevidence');
    $SESSION->arupevidence->alert->type = 'alert-warning';
}
exit;
