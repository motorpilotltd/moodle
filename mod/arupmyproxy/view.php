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
require_once($CFG->dirroot.'/mod/arupmyproxy/classes/arupmyproxy.php');

$id = optional_param('id', 0, PARAM_INT);
$a  = optional_param('a', 0, PARAM_INT);

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

require_course_login($course, true, $cm);

arupmyproxy_view($arupmyproxy, $course, $cm, $PAGE->context);

$PAGE->set_url('/mod/arupmyproxy/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($arupmyproxy->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_arupmyproxy');

echo $OUTPUT->header();

if ($arupmyproxy->intro) {
    echo $OUTPUT->box(format_module_intro('arupmyproxy', $arupmyproxy, $cm->id), 'generalbox mod_introbox', 'arupmyproxyintro');
}

// Check if already proxying.
if (\core\session\manager::is_loggedinas()) {
    $realuser = \core\session\manager::get_realuser();
    $a = new stdClass();
    $a->realfullname = fullname($realuser);
    $a->fullname = fullname($USER);
    $alert = html_writer::start_tag('p');
    $alert .= get_string('currentlyloggedinas', 'arupmyproxy', $a);
    $logouturl = new moodle_url('/mod/arupmyproxy/logout.php', array('id' => $cm->id));
    $alert .= html_writer::empty_tag('br');
    $alert .= html_writer::link($logouturl, get_string('logout', 'arupmyproxy'), array('class' => 'btn btn-danger'));
    $alert .= html_writer::end_tag('p');
    echo $renderer->alert($alert, 'alert-warning', false);
} else {
    $arupmyproxyclass = new arupmyproxy($arupmyproxy);

    if (isset($SESSION->arupmyproxy[$cm->id]->alert)) {
        echo $renderer->alert($SESSION->arupmyproxy[$cm->id]->alert->message, $SESSION->arupmyproxy[$cm->id]->alert->type);
        unset($SESSION->arupmyproxy[$cm->id]->alert);
    }

    // Choose user to login as (already had proxy accepted).
    echo $renderer->proxy_loginas($arupmyproxyclass->get_loginas_users(), $cm->id);

    // Refused requests.
    echo $renderer->proxy_refused($arupmyproxyclass->get_refused_users(), $cm->id);

    // Show pending requests.
    echo $renderer->proxy_pending($arupmyproxyclass->get_pending_users(), $cm->id);

    // Request to be proxy.
    echo $renderer->proxy_request($arupmyproxyclass->get_request_users(), $cm->id);
}

echo $renderer->back_to_module($course->id);

// Finish the page.
echo $OUTPUT->footer();