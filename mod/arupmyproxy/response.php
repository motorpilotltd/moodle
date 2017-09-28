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

$uniquehash = required_param('r', PARAM_ALPHANUM);
$allow = optional_param('y', 0, PARAM_INT);
$disallow = optional_param('n', 0, PARAM_INT);

$url = new moodle_url('/mod/arupmyproxy/response.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

try {
    $proxyrecord = $DB->get_record_select('arupmyproxy_proxies', 'uniquehash = :uniquehash AND uniquehash IS NOT NULL', array('uniquehash' => $uniquehash), '*', MUST_EXIST);
    $requestinguser = $DB->get_record('user', array('id' => $proxyrecord->userid), '*', MUST_EXIST);
    $arupmyproxy = $DB->get_record('arupmyproxy', array('id' => $proxyrecord->arupmyproxyid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $arupmyproxy->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('arupmyproxy', $arupmyproxy->id, $course->id, false, MUST_EXIST);
} catch (Exception $e) {
    $event = \mod_arupmyproxy\event\proxy_request_response_failed::create(
        array(
            'context' => context_system::instance(),
            'other' => array(
                'hash' => $uniquehash,
                'allow' => $allow,
                'disallow' => $disallow,
                'error' => $e->getMessage,
            ),
        )
    );
    $event->trigger();

    $PAGE->set_title(get_string('error'));
    $renderer = $PAGE->get_renderer('mod_arupmyproxy');
    echo $OUTPUT->header();
    $message = get_string('alert:confirmproxy:norecord', 'arupmyproxy');
    $type = 'alert-danger';
    echo $renderer->alert($message, $type, false, true);
    echo $OUTPUT->footer();
    exit;
}

$a = new stdClass();
$a->name = fullname($requestinguser);
$a->email = $requestinguser->email;
$a->coursename = $course->fullname;

$PAGE->set_title(format_string($arupmyproxy->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_arupmyproxy');

echo $OUTPUT->header();

if ($allow || $disallow) {
    $proxyrecord->response = $allow ? 1 : 0;
    $proxyrecord->responsetime = time();
    $proxyrecord->uniquehash = null;
    $DB->update_record('arupmyproxy_proxies', $proxyrecord);
    $coursecontext = context_course::instance($course->id);
    if ($proxyrecord->response == 1) {
        role_assign($arupmyproxy->roleid, $proxyrecord->userid, $coursecontext->id, 'mod_arupmyproxy', $arupmyproxy->id);
    }

    $event = \mod_arupmyproxy\event\proxy_request_response_completed::create(
        array(
            'objectid' => $proxyrecord->id,
            'context' => context_module::instance($cm->id),
            'relateduserid' => $proxyrecord->userid,
            'other' => array(
                'proxyuserid' => $proxyrecord->proxyuserid,
                'allow' => $allow,
            ),
        )
    );
    $event->trigger();

    $message = $proxyrecord->response ? get_string('alert:confirmproxy:yes', 'arupmyproxy', $a) : get_string('alert:confirmproxy:no', 'arupmyproxy', $a);
    $type = $proxyrecord->response ? 'alert-success' : 'alert-warning';
    echo $renderer->alert($message, $type, false, true);
} else {
    $actionurl = new moodle_url('/mod/arupmyproxy/response.php');
    echo html_writer::start_tag('form', array('id' => 'form-proxy-response', 'class' => 'arupmyproxy-form-proxy text-center', 'action' => $actionurl, 'method' => 'POST'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'r', 'value' => $proxyrecord->uniquehash));
    echo html_writer::tag('p', get_string('confirmproxy:question', 'arupmyproxy', $a));
    echo html_writer::tag('button', get_string('yes'), array('class' => 'btn btn-success', 'type' => 'submit', 'name' => 'y', 'value' => 1));
    echo html_writer::tag('button', get_string('no'), array('class' => 'btn btn-danger', 'type' => 'submit', 'name' => 'n', 'value' => 1));
    echo html_writer::end_tag('form');
}

// Finish the page.
echo $OUTPUT->footer();