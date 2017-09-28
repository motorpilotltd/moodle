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

require_once(dirname(__FILE__) . '/invite.php');

/**
 * Description of requester
 *
 * @author paulstanyer
 */
class vcal_requester {

    private function wrap_vevent($vevent, $method='REQUEST') {
        $wrap = "BEGIN:VCALENDAR".RFC5545_EOL
                . "VERSION:2.0".RFC5545_EOL
                . "PRODID:-//Arup//NONSGML Moodle Invites//EN".RFC5545_EOL
                . "CALSCALE:GREGORIAN".RFC5545_EOL
                . "METHOD:{$method}".RFC5545_EOL
                . "$vevent".RFC5545_EOL
                . "END:VCALENDAR";
        return $wrap;
    }

    public function __construct(invite $invite, $method='REQUEST') {
        global $CFG;

        $mail = get_mailer();
        $invite->setup_mailer($mail);
        $mail->Ical = ($this->wrap_vevent($invite, $method));
        $mail->IcalMethod = $method;

        if (!empty($CFG->noemailever)) {
            // Hidden setting for development sites, set in config.php if needed.
            $noemail = 'Not sending email due to noemailever config setting';
            error_log($noemail);
            if (CLI_SCRIPT) {
                mtrace('Error: lib/moodlelib.php email_to_user(): '.$noemail);
            }
            return true;
        }

        if (!$mail->Send()) {
            error_log($mail->ErrorInfo);
        }
    }
}
