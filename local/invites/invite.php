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

require_once(dirname(__FILE__) . '/invitee.php');
require_once(dirname(__FILE__) . '/organizer.php');

define('RFC5545_EOL', "\r\n");

/**
 * Description of invite
 *
 * @author paulstanyer
 */
class invite {
    private $uid;

    private $organizer;
    /**
     * @var invitee[]
     */
    private $attendees;
    private $location;
    private $subject;
    private $eventurl;
    private $description;
    private $descriptionplain;
    private $status = 'CONFIRMED';
    private $sequence = 0;
    private $diverted = false;

    private $dtstart;
    private $dtend;
    private $dtstamp;

    public function __construct($location = '', $subject='', $description = '', $descriptionplain = '') {
        $this->uid = uniqid();
        $this->dtstamp = date('Ymd\THis\Z');
        $this->location = $location;
        $this->subject = $subject;
        $this->description = $description;
        $this->descriptionplain = $descriptionplain;
    }

    public function set_url($url) {
        $this->eventurl = $url;
    }

    public function set_id($id) {
        $this->uid = $id;
    }

    public function set_as_cancelled() {
        $this->status = 'CANCELLED';
        $this->sequence = 1;
    }

    public function add_organizer(organizer $organizer) {
        $this->organizer = $organizer;
    }

    public function add_recipient(invitee $invitee) {
        $this->attendees[] = $invitee;
    }

    public function set_date(DateTime $datestart, DateInterval $duration) {
        $this->dtstart = $datestart->format('Ymd\THis\Z');
        $datestart->add($duration);
        $this->dtend = $datestart->format('Ymd\THis\Z');
    }

    public function setup_mailer(\moodle_phpmailer $mailer) {
        global $CFG;

        $this->organizer->setup_mailer($mailer);

        foreach ($this->attendees as $attendee) {
            $attendee->setup_mailer($mailer);
            if ($attendee->divert) {
                $this->diverted = true;
            }
        }

        if ($this->diverted) {
            if (count($this->attendees) === 1) {
                $attendeeemail = reset($this->attendees)->realemail;
            } else {
                $attendeeemail = 'MULTIPLE';
            }
            $mailer->Subject = "[DIVERTED {$attendeeemail}] {$this->subject}";
        } else {
            $mailer->Subject = $this->subject;
        }
        $mailer->isHTML(true);
        $mailer->Body = $this->description;
        $mailer->AltBody = $this->descriptionplain;
    }

    private function get_dates_as_string() {
        $datestring = '';

        if (!empty($this->dtstamp)) {
            $datestring = "DTSTART:{$this->dtstart}".RFC5545_EOL;
            $datestring .= "DTEND:{$this->dtend}".RFC5545_EOL;
        }
        $datestring .= "DTSTAMP:{$this->dtstamp}";
        return $datestring;
    }

    public function __toString() {
        $vevent = "BEGIN:VEVENT".RFC5545_EOL;
        $vevent .= "UID:MOO-ARUP-{$this->uid}".RFC5545_EOL;
        $vevent .= $this->get_dates_as_string() . RFC5545_EOL;
        $vevent .= $this->organizer.RFC5545_EOL;
        foreach ($this->attendees as $attendee) {
            $vevent .= $attendee.RFC5545_EOL;
        }
        $vevent .= "DESCRIPTION:".str_replace(';', '\;', $this->eventurl) .RFC5545_EOL;
        $vevent .= "LOCATION:{$this->location}".RFC5545_EOL;
        $vevent .= "SEQUENCE:{$this->sequence}".RFC5545_EOL;
        $vevent .= "STATUS:{$this->status}".RFC5545_EOL;
        $vevent .= "SUMMARY:{$this->subject}".RFC5545_EOL;
        $vevent .= "TRANSP:OPAQUE".RFC5545_EOL;
        $vevent .= "END:VEVENT";
        return $vevent;
    }
}