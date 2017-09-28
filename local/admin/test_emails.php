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

require(dirname(__FILE__).'/../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_admin_testemails');

$output = $PAGE->get_renderer('local_admin');

// Build View.
$echo = '';

$form = new \local_admin\form\test_emails();

$fromform = $form->get_data();
if ($fromform) {
    // Process and send test email...
    $to = \local_admin\dummy_user::get_dummy_local_admin_user($fromform->to);

    $from = get_admin();
    $from->maildisplay = true;

    $cc = array();
    if (!empty($fromform->cc)) {
        $cc[] = \local_admin\dummy_user::get_dummy_local_admin_user($fromform->cc);
    }

    $subject = $fromform->subject;

    if (empty($fromform->html)) {
        $messagetext = $fromform->body;
        $messagehtml = '';
    } else {
        $messagehtml = $fromform->body;
        $messagetext = html_to_text($messagehtml);
    }

    $savedcfg = new stdClass();
    $savedcfg->noemailever = empty($CFG->noemailever) ? null : $CFG->noemailever;
    $CFG->noemailever = null;
    $savedcfg->divertallemailsto = empty($CFG->divertallemailsto) ? null : $CFG->divertallemailsto;
    $CFG->divertallemailsto = null;
    $savedcfg->divertccemailsto = empty($CFG->divertccemailsto) ? null : $CFG->divertccemailsto;
    $CFG->divertccemailsto = null;

    if (empty($fromform->invite) || !get_config('local_invites', 'version')) {
        $emailsent = email_to_user($to, $from, $subject, $messagetext, $messagehtml, '', '', true, '', '', 79, $cc);
        if ($emailsent) {
            $echo .= $output->alert('Email sent', 'alert-success', false);
        } else {
            $echo .= $output->alert('Email not sent', 'alert-danger', false);
        }
    } else {
        require_once($CFG->dirroot . '/local/invites/requester.php');

        $now = time();

        $location = 'Test location';
        $invite = new invite($location, $subject, $messagehtml, $messagetext);
        $invite->set_id('EMAIL-TEST-'.$now);
        $invite->set_url($PAGE->url->out(false));

        $starttime = new DateTime();
        $starttime->setTimestamp($now + (60 * 60));
        $starttime->setTimezone(new DateTimeZone('UTC')); // Send the vcal in UTC.
        $endtime = new DateTime();
        $endtime->setTimestamp($now + (2 * 60 * 60));
        $endtime->setTimezone(new DateTimeZone('UTC'));
        $invite->setDate(
                $starttime,
                $starttime->diff($endtime));

        $invite->add_organizer(new organizer($from));
        $invite->add_recipient(new invitee($to));

        new vcal_requester($invite);

        $echo .= $output->alert('Invite sent', 'alert-success', false);
    }

    $CFG->noemailever = empty($savedcfg->noemailever) ? null : $savedcfg->noemailever;
    $CFG->divertallemailsto = empty($savedcfg->divertallemailsto) ? null : $savedcfg->divertallemailsto;
    $CFG->divertccemailsto = empty($savedcfg->divertccemailsto) ? null : $savedcfg->divertccemailsto;
}

// Output View.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('testemails', 'local_admin'));

echo $echo;

$form->display();

echo $OUTPUT->footer();
