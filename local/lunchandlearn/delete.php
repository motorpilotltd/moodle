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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/calendar/renderer.php');

$lalid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

$PAGE->set_url('/lunchandlearn/delete.php', array('id' => $lalid));

if (!$site = get_site()) {
    redirect(new moodle_url('/admin/index.php'));
}
$lal = new lunchandlearn($lalid);
$event = $lal->get_event();

require_login($site);

// Check the user has the required capabilities to edit an event.
if (!calendar_edit_event_allowed($event)) {
    print_error('nopermissions');
}

if ($lal->scheduler->is_cancelled()) {
    $confirmbutton = get_string('delete');
    $confirmmessage = get_string('confirmeventdelete', 'local_lunchandlearn');
    $title = get_string('deleteevent', 'calendar');
} else {
    $confirmbutton = get_string('cancelsession', 'local_lunchandlearn');
    $confirmmessage = get_string('confirmeventcancelled', 'local_lunchandlearn');
    $title = get_string('cancelevent', 'local_lunchandlearn');
}

// Is used several times, and sometimes with modification if required.
$viewcalendarurl = new moodle_url(CALENDAR_URL.'view.php', array('view' => 'upcoming'));
$viewcalendarurl->param('cal_y', userdate($event->timestart, '%Y'));
$viewcalendarurl->param('cal_m', userdate($event->timestart, '%m'));

// If confirm is set (PARAM_BOOL) then we have confirmation of intention to delete.
if ($confirm) {
    // Confirm the session key to stop CSRF.
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad');
    }
    // Only if actually submitting (not hitting 'back').
    if (optional_param('submit', '', PARAM_ALPHA) !== '') {
        if ($lal->scheduler->is_cancelled()) {
            $lal->delete();
        } else {
            $lal->scheduler->cancel_session(optional_param('notes', '', PARAM_TEXT));
        }
    }
    // And redirect.
    redirect($viewcalendarurl);
}

// Prepare the page to show the confirmation form.
$strcalendar = get_string('calendar', 'calendar');

$PAGE->navbar->add($strcalendar, $viewcalendarurl);
$PAGE->navbar->add($title);
$PAGE->set_title($site->shortname.': '.$strcalendar.': '.$title);

// Generate button HTML.
$url = new moodle_url('delete.php', array('id' => $lalid, 'confirm' => true));
$submitattributes = array(
                    'type'     => 'submit',
                    'value'    => $confirmbutton,
                    'name'     => 'submit',
                    'id'       => 'id_submitbutton',
                    'class'    => 'm-l-0');
$buttons  = html_writer::empty_tag('input', $submitattributes);
$backattributes = array(
                    'type'     => 'submit',
                    'value'    => get_string('back'),
                    'name'     => 'back',
                    'class'    => 'm-l-10');
$buttons .= html_writer::empty_tag('input', $backattributes);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->box_start('generalbox', 'notice');
echo $OUTPUT->box($confirmmessage);

// Print the event so that people can visually confirm they have the correct event.
$event->time = calendar_format_event_time($event, time(), null, false);
$renderer = $PAGE->get_renderer('core_calendar');
echo $renderer->start_layout();
echo $renderer->event_summary($event, false);
echo $renderer->complete_layout();

if ($lal->get_attendee_count() > 0) {
    echo $OUTPUT->notification(get_string('deletehasattendees', 'local_lunchandlearn'), 'notifyproblem alert alert-warning');
}

$fattributes = array('method' => 'POST',
                    'action' => $url);
echo html_writer::start_tag('form', $fattributes);

$sesskeyattributes = array(
                    'type'     => 'hidden',
                    'value'    => sesskey(),
                    'name'     => 'sesskey');
echo html_writer::empty_tag('input', $sesskeyattributes);

if (false === $lal->scheduler->is_cancelled()) {
    // Add cancellation reason.
    echo html_writer::div(get_string('admincancellationreason', 'local_lunchandlearn'));
    echo html_writer::tag('textarea', '', array('name' => 'notes', 'rows' => '5', 'cols' => '65'));
}

echo $OUTPUT->box($buttons, 'buttons m-t-10');

echo html_writer::end_tag('form');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
