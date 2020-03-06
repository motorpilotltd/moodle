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
require_once(dirname(__FILE__).'/signup_form.php');

require_login($SITE);

require_capability('local/lunchandlearn:view', context_system::instance());

$action = optional_param('action', 'signup', PARAM_ALPHA);

$lal = new lunchandlearn(required_param('id', PARAM_INT));

$url = new moodle_url('/local/lunchandlearn/signup.php', array('id' => $lal->get_id(), 'action' => $action));
$PAGE->set_url($url);
$PAGE->set_title(get_string('confirm'.$action, 'local_lunchandlearn', $lal->get_name()));

$PAGE->requires->jquery(); // For JS used via signup_form.

$mform = new signup_form($url, array('action' => $action, 'lal' => $lal));
$lal->form($mform);

if ($mform->is_cancelled()) {
    redirect($lal->get_cal_url(optional_param('backto', '', PARAM_ALPHA)));
}

if ($data = $mform->get_data()) {
    $redirect = true;
    switch ($action) {
        case 'cancelreason':
            $redirect = false;
        break;

        case 'cancel':
            $lal->attendeemanager->cancel_signup($USER, isset($data->notes)?$data->notes:'');
            lunchandlearn_manager::cancel_meeting_request($lal, $USER);
            $event = \local_lunchandlearn\event\user_signup_cancelled::create(array(
                'objectid' => $lal->get_id(),
            ));
            $event->trigger();
            break;
        default:
            $lal->attendeemanager->signup($USER, $data);
            lunchandlearn_manager::send_meeting_request($lal, $USER);
            $event = \local_lunchandlearn\event\user_signup_completed::create(array(
                'objectid' => $lal->get_id(),
            ));
            $event->trigger();
    }
    if ($redirect) {
        redirect($lal->get_cal_url(optional_param('backto', '', PARAM_ALPHA)));
        exit;
    }
}

print $OUTPUT->header();
print $OUTPUT->heading(get_string('confirm'.$action, 'local_lunchandlearn', $lal->get_name()));
if ($action == 'signup' && $lal->is_fully_subscribed()) {
   print $OUTPUT->box(get_string('notice:overcapacity', 'local_lunchandlearn'), 'generalbox warning');
}
print $OUTPUT->box_start('generalbox');
print $OUTPUT->box(get_string('confirmmsg'.$action, 'local_lunchandlearn'));
print $OUTPUT->box($mform->display());
print $OUTPUT->box_end();

print $OUTPUT->footer();