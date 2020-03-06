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
require_once('lib.php');
require_once('invite_form.php');

require_login($SITE);

$lal = new lunchandlearn(required_param('id', PARAM_INT));

$url = new moodle_url('/local/lunchandlearn/invite.php');

$mform = new lunchandlearn_invite_form('', $lal);
$mform->set_data(array('id' => $lal->get_id()));

$PAGE->set_url($url);

require_capability('local/lunchandlearn:edit', $PAGE->context);

if (optional_param('action', '', PARAM_ALPHA) === 'send') {
    $messagehtml = required_param('body', PARAM_TEXT);
    $messagetext = strip_tags($messagehtml);
    foreach (required_param_array('to', PARAM_INT) as $to) {
        $user = $DB->get_record('user', array('id' => $to));
        email_to_user($user, get_admin(), get_string('invitesubject', 'local_lunchandlearn', $lal->get_name()),
            $messagetext, $messagehtml);
    }
    redirect(new moodle_url('/calendar/view.php', array('view' => 'event', 'id' => $lal->get_id())), get_string('messagessent', 'local_lunchandlearn'), 2);
    exit;
}

if ($mform->is_submitted()) {
    $data = $mform->get_data();

    if (!empty($data)) {
        // Render a preview.
        $renderer = $PAGE->get_renderer('local_lunchandlearn');
        print $OUTPUT->header();
        echo $renderer->show_invite_preview($lal, $data);
        print $OUTPUT->footer();
        exit;
    }
}

$PAGE->requires->jquery();
$PAGE->requires->css(new moodle_url('/local/lunchandlearn/chosen.css'));
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/chosen.jquery.min.js'));
$PAGE->requires->js_init_code("$('select#id_to').chosen({width: '74%', no_results_text: 'Oops, nothing found!'})", true);

print $OUTPUT->header();
print $OUTPUT->heading(get_string('inviteheader', 'local_lunchandlearn'));

print $OUTPUT->box_start('generalbox');
print $OUTPUT->box($mform->display());
print $OUTPUT->box_end();

print $OUTPUT->footer();
