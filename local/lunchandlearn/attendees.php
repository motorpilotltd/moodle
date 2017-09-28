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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');
require_once($CFG->dirroot . '/local/lunchandlearn/attendee_form.php');
require_once($CFG->dirroot . '/local/lunchandlearn/signup_form.php');


admin_externalpage_setup('lunchandlearnlist');

$session = new lunchandlearn(required_param('id', PARAM_INT));

if (false === $session->markable()) {
    error(get_string('error:notmarkable', 'local_lunchandlearn'));
}

$url = new moodle_url('/local/lunchandlearn/attendees.php', array(
    'id' => $session->get_id(),
    'action' => optional_param('action', 'list', PARAM_ALPHA)
));

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/attendee.js'));
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/jquery.tablesorter.min.js'));
$PAGE->requires->js_init_code("(function () { //scoped

var whichdata = 'lastname';

$.tablesorter.addParser({
    id: 'firstlast',
    is: function () { return false; },
    format: function (s, table, cell) {
        var jcell = $(cell);
        if (jcell.data(whichdata)) {
            return jcell.data(whichdata);
        }
        return s;
    },
    type: 'text'
});

$('table.attendeelist, table.canxlist').on('click', 'th span', function (ev) {
    var table = $(this).parents('table');
    whichdata = $(this).data('sort');

    table.data('tablesorter').\$headers[0].sortDisabled = false;
    table.data('tablesorter').\$headers[0].sorter = 'firstlast';
    table.trigger('UpdateAll', [true, function () {
        table.find('.c0').trigger('sort');
        table.data('tablesorter').\$headers[0].sortDisabled = true;
    }]);

});

}())");
$PAGE->requires->js_init_code('$("table.attendeelist, table.canxlist").tablesorter({widgets: ["zebra"], widgetOptions : { zebra : [ "normal-row", "alt-row" ] }});');

$action = optional_param('action', '', PARAM_ALPHA);
$errors = array();

switch ($action) {
    case 'attendance':
        $mform = new attendee_form($url, $session);
        if ($mform->is_cancelled()) {
            $url->param('action', 'list');
            redirect($url);
            exit;
        }
        $mform->set_data(array('action' => 'attendance', 'id' => $session->get_id(), 'p_learning_desc' => ['text' => $session->get_summary(), 'format' => FORMAT_HTML]));
        if ($extra = $mform->get_data()) {
            $users = optional_param_array('attendance', array(), PARAM_INT);

            try {
                lunchandlearn_manager::take_attendance($session->get_id(), $users, $extra, $session->is_locked());
            } catch (Exception $ex) {
                $SESSION->tapserror = get_string('tapserror', 'local_lunchandlearn');
            }

            $other = array(
                'users' => $users,
            );
            $event = \local_lunchandlearn\event\attendance_taken::create(array(
                'objectid' => $session->get_id(),
                'other' => $other,
            ));
            $event->trigger();

            $url->param('action', 'list');
            redirect($url);
            exit;
        } else {
            $PAGE->set_title(get_string('submitattendancetitle', 'local_lunchandlearn'));

            // Output the confim form.
            print $OUTPUT->header();
            print $OUTPUT->heading(get_string('submitattendancetitle', 'local_lunchandlearn'));
            print html_writer::tag('p', get_string('pleasecheck', 'local_lunchandlearn', count(optional_param_array('attendance', array(), PARAM_INT))));
            print html_writer::start_div('attendee_form');
            $mform->display();
            print html_writer::end_div();
            print $OUTPUT->footer();
            exit;
        }

    case 'edit':
        $userid = required_param('userid', PARAM_INT);
        $user = $DB->get_record('user', array('id' => $userid));
        $editform = new signup_form($url, array('action' => $action, 'lal' => $session));
        $attendance = $session->attendeemanager->get_by_user($userid);
        $attendance->form($editform);
        $editform->set_data(array('id' => $session->id, 'userid' => $userid));

        if ($editform->is_cancelled()) {
            $url->param('action', 'list');
            redirect($url);
            exit;
        }
        if ($data = $editform->get_data()) {
            // Special case for checkbox 'inperson'.
            $data->inperson = empty($data->inperson) ? 0 : 1;
            $attendance->bind($data);
            $attendance->save();
            $url->param('action', 'list');
            redirect($url);
            exit;
        }

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('signupedit', 'local_lunchandlearn', array('fullname' => fullname($user), 'eventname' => $session->get_name())));
        echo html_writer::start_div('signup_form');
        echo $editform->display();
        echo html_writer::end_div() . $OUTPUT->footer();
        exit;

    case 'signup':
        global $DB;
        $userids = required_param('userid', PARAM_TEXT);
        if (empty($userids)) {
            $errors['user'] = get_string('error:missinguser', 'local_lunchandlearn');
            break;
        }
        $data = new stdClass();
        $data->inperson = optional_param('inperson', null, PARAM_INT);
        if (is_null($data->inperson)) {
            $errors['inperson'] = get_string('error:selectattendancetype', 'local_lunchandlearn');
            break;
        }
        $data->requirements = optional_param('requirements', '', PARAM_TEXT);
        foreach (explode(',', $userids) as $userid) {
            $user = $DB->get_record('user', array('id' => $userid));
            if (empty($user)) {
                $errors['user'.$userid] = get_string('error:missinguser:id', 'local_lunchandlearn', $userid);
                continue;
            }
            try {
                $session->attendeemanager->signup($user, $data);
            } catch (Exception $ex) {
                $errors['signupfailed'] = $ex->getMessage();
                break;
            }
            if (false === $session->scheduler->has_past()) {
                lunchandlearn_manager::send_meeting_request($session, $user);
            }
            $event = \local_lunchandlearn\event\user_signup_completed::create(array(
                'objectid' => $session->get_id(),
                'relateduserid' => $user->id,
            ));
            $event->trigger();
        }
        if (!empty($errors)) {
            break;
        }
        $url->param('action', 'list');
        redirect($url);
        exit;

    case 'cancel':
        // Show cancel form first...
        $userid = required_param('userid', PARAM_INT);
        $user = $DB->get_record('user', array('id' => $userid));
        $cancelform = new signup_form($url, array('action' => $action, 'lal' => $session));
        $cancelform->set_data(array('id' => $session->id, 'userid' => $userid));
        if ($cancelform->is_cancelled()) {
            $url->param('action', 'list');
            redirect($url);
            exit;
        }
        if ($reason = $cancelform->get_data()) {
            // Then cancel.
            $session->attendeemanager->cancel_signup($userid, optional_param('notes', '', PARAM_TEXT));
            if (false === $session->scheduler->has_past()) {
                lunchandlearn_manager::admin_cancel_meeting_request($session, $user);
            }

            $event = \local_lunchandlearn\event\user_signup_cancelled::create(array(
                'objectid' => $session->get_id(),
                'relateduserid' => $user->id,
            ));
            $event->trigger();

            $url->param('action', 'list');
            redirect($url);
            exit;
        }

        // Output the confim form.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('adminconfirm'.$action, 'local_lunchandlearn', array('fullname' => fullname($user), 'eventname' => $session->get_name())));
        echo html_writer::start_div('signup_form');
        echo $cancelform->display();
        echo html_writer::end_div(). $OUTPUT->footer();
        exit;
}

$renderer = $PAGE->get_renderer('local_lunchandlearn');

$renderer->errors = $errors;

$PAGE->requires->css(new moodle_url('/local/lunchandlearn/css/select2.css'));
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/select2.min.js'));

$potentials = $session->attendeemanager->get_potential_attendees();
$potentialstoencode = array();
foreach ($potentials as $id => $text) {
    $potentialstoencode[] = array('text' => $text, 'id' => $id);
}
$potentialsjson = json_encode($potentialstoencode);

$PAGE->requires->js_init_code("
potentialusers = $potentialsjson;
$('input[name=userid]').select2({
    width: '300px',
    minimumInputLength: 2,
    multiple: true,
    data: { results: potentialusers }
});
", true);
$PAGE->requires->js_init_code("
$('#lal-attendee-selectall').on('change', function(){
    var that = $(this);
    that.closest('form').find('input[type=checkbox]:not([disabled])').prop('checked', that.prop('checked') === true);
});
", true
);

$print = 'javascript:print()';

lunchandlearn_add_page_navigation($PAGE, $url);
$PAGE->navbar->ignore_active();

print $OUTPUT->header();
print $OUTPUT->heading(get_string('lunchandlearnattendeelist', 'local_lunchandlearn'));
if (!empty($SESSION->tapserror)) {
    $close = '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
    print html_writer::div($close . $SESSION->tapserror, 'alert alert-danger alert-dismissible alert-taps');
    unset($SESSION->tapserror);
}
print $renderer->summary_session($session);
print $renderer->list_session_attendees($session);
print $OUTPUT->footer();