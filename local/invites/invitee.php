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
 * Description of invitee
 *
 * @author paulstanyer
 */
class invitee {
    protected $email;
    protected $realemail;
    protected $name;
    protected $divert;

    public function __construct($email, $name = '') {
        if (is_string($email)) {
            $this->add_plain($email, $name);
        } else {
            $this->add_moodle_user($email);
        }
    }
    private function add_plain($email, $name) {
        global $CFG;
        $this->email = $this->realemail = $email;
        if (email_should_be_diverted($this->email)) {
            $this->email = $CFG->divertallemailsto;
            $this->divert = true;
        }
        $this->name = trim($name);
    }

    private function add_moodle_user($user) {
        $this->add_plain($user->email, fullname($user));
    }

    public function setup_mailer(\moodle_phpmailer $mailer) {
        if (!validate_email($this->email)) {
            // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
            debugging("local_invite: Email (".s($this->email).") is invalid! Not sending.");
            return;
        }
        $mailer->addAddress($this->email, $this->name);
    }

    public function __toString() {
        return "ATTENDEE;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN=\"{$this->name}\":mailto:{$this->realemail}";
    }

    public function __get($name) {
        if (isset($this->{$name})) {
			return $this->{$name};
        }
		return null;
	}
}