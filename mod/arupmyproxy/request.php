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
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/classes/arupmyproxy.php');

$id = optional_param('id', 0, PARAM_INT);
$a  = optional_param('a', 0, PARAM_INT);
$proxy = required_param('p', PARAM_INT);
require_sesskey();

if ($id) {
    $cm = get_coursemodule_from_id('arupmyproxy', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $arupmyproxy = $DB->get_record('arupmyproxy', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($a) {
    $arupmyproxy = $DB->get_record('arupmyproxy', array('id' => $a), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $arupmyproxy->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('arupmyproxy', $arupmyproxy->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingparameter');
}

if (!isset($SESSION->arupmyproxy[$cm->id])) {
    $SESSION->arupmyproxy[$cm->id] = new stdClass();
}

$url = new moodle_url('/course/view.php', array('id' => $course->id));
$PAGE->set_url($url); // For require_course_login().

if (!$proxy) {
    $SESSION->arupmyproxy[$cm->id]->errors = new stdClass();
    $SESSION->arupmyproxy[$cm->id]->errors->request = new stdClass();
    $SESSION->arupmyproxy[$cm->id]->errors->request->message = get_string('error:nouser', 'arupmyproxy');
    $SESSION->arupmyproxy[$cm->id]->errors->request->type = 'alert-danger';
    redirect($url);
}

require_course_login($course, true, $cm);

$alreadyloggedinas = \core\session\manager::is_loggedinas();
$alreadyrequested = $DB->get_record('arupmyproxy_proxies', array('arupmyproxyid' => $arupmyproxy->id, 'userid' => $USER->id, 'proxyuserid' => $proxy));
$proxyuser = $DB->get_record('user', array('id' => $proxy));
$user = clone($USER);
if ($alreadyloggedinas || $alreadyrequested || !$proxyuser || $proxyuser->id == $USER->id) {
    redirect($url);
}

$arupmyproxyclass = new arupmyproxy($arupmyproxy);

// Process request.
$proxyrecord = new stdClass();
$proxyrecord->arupmyproxyid = $arupmyproxy->id;
$proxyrecord->userid = $user->id;
$proxyrecord->proxyuserid = $proxy;
$proxyrecord->requesttime = time();
$proxyrecord->uniquehash = $arupmyproxyclass->generate_uniquehash($proxyuser->email);
$proxyrecord->id = $DB->insert_record('arupmyproxy_proxies', $proxyrecord);

$responseurl = new moodle_url('/mod/arupmyproxy/response.php', array('r' => $proxyrecord->uniquehash));
$responseallowurl = clone($responseurl);
$responseallowurl->param('y', 1);
$responsedisallowurl = clone($responseurl);
$responsedisallowurl->param('n', 1);

$reps = new stdClass();
$reps->proxyname = fullname($proxyuser);
$reps->requestername = fullname($user);
$reps->allowurl = $responseallowurl->out();
$reps->disallowurl = $responsedisallowurl->out();
$reps->generalurl = $responseurl->out();
$reps->coursename = $course->fullname;

$emailsubject = get_string('email:request:subject', 'arupmyproxy');
$emailhtml = get_string('email:request:body', 'arupmyproxy', $reps);
$emailtext = html_to_text($emailhtml);

// Force HTML...
$proxyuser->mailformat = 1;
// Force maildisplay...
$user->maildisplay = true;
email_to_user($proxyuser, $user, $emailsubject, $emailtext, $emailhtml);

$event = \mod_arupmyproxy\event\proxy_request_created::create(
    array(
        'objectid' => $proxyrecord->id,
        'context' => context_module::instance($cm->id),
        'relateduserid' => $proxyuser->id,
    )
);
$event->trigger();

$SESSION->arupmyproxy[$cm->id]->alert = new stdClass();
$SESSION->arupmyproxy[$cm->id]->alert->message = get_string('alert:request:success', 'arupmyproxy', fullname($proxyuser));
$SESSION->arupmyproxy[$cm->id]->alert->type = 'alert-success';

redirect($url);