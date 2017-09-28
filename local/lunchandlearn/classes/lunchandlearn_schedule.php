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
 * Handle the date/time and location of lal
 *
 * @author paulstanyer
 */
class lunchandlearn_schedule extends lal_base {
    protected $lunchandlearn;

    protected $office;
    protected $room;
    protected $regionid;
    protected $regionname;

    protected $date;
    protected $timezone;
    protected $duration = 3600; // In seconds at base unit.

    protected $cancelled = 0;

    public function __construct(lunchandlearn $lal, $scheduledata = null) {

        $this->lunchandlearn = $lal;
        if (empty($scheduledata)) {
            $this->set_defaults();
            return;
        }

        $this->set_office($scheduledata->office);
        $this->set_room($scheduledata->room);
        $this->set_date($lal->event->timestart, $scheduledata->timezone);
        $this->set_duration($lal->event->timeduration);

        $this->set_regionid($scheduledata->regionid);
        $this->set_cancelled($scheduledata->cancelled);
    }

    protected function set_defaults() {
        global $USER;
        $this->set_date(time(), lunchandlearn_get_moodle_user_timezone($USER)->getName());
    }

    public function get_regionid() {
        return $this->regionid;
    }

    public function get_region_name() {
        if (empty($this->regionname)) {
            global $DB;
            $region = $DB->get_record('local_regions_reg', array('id' => $this->regionid));
            if (!empty($region)) {
                $this->regionname = $region->name;
            }
        }
        return $this->regionname;
    }

    public function set_regionid($regionid) {
        $this->regionid = $regionid;
    }

    public function get_date() {
        return $this->date;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    /*
     * Returns the session date as a timezone aware DateTime
     */
    public function get_DateTime() {
        $date = new DateTime();
        $date->setTimestamp($this->date);
        if (!empty($this->timezone)) {
            $tz = new DateTimeZone($this->timezone);
            $date->setTimezone($tz);
        }
        return $date;
    }

    /**
     * Returns the date as a formatted string - attempts to be timezone aware
     */
    public function get_date_string($formatdate='dS F Y', $formattime=' G:i T', $includeorigintz=true) {
        global $USER;
        $date = $this->get_DateTime();

        if ($includeorigintz & !empty($this->timezone) && !empty($USER->id)) {
            $tz = new DateTimeZone($this->timezone);
            $usertimezone = lunchandlearn_get_moodle_user_timezone($USER);
            if ($usertimezone->getOffset($date)-$tz->getOffset($date) != 0) {
                $origtz = $date->format("$formatdate$formattime");
                $date->setTimezone($usertimezone);
                $ds = $date->format("$formatdate$formattime") . " ($origtz)";
            }
        }
        if (!isset($ds)) {
            $ds = $date->format("$formatdate$formattime");
        }
        return $ds;
    }

    public function get_duration() {
        if (empty($this->duration)) {
            return 0;
        }
        return (int)$this->duration / MINSECS;
    }

    public function set_date($date, $timezone) {
        $this->date = $date;
        $this->timezone = $timezone;
    }

    public function set_duration($duration) {
        $this->duration = $duration;
    }

    public function get_office() {
        return $this->office;
    }

    public function get_room() {
        return $this->room;
    }

    public function set_office($office) {
        $this->office = $office;
    }

    public function set_room($room) {
        $this->room = $room;
    }

    public function has_past() {
        $existingid = $this->lunchandlearn->id;
        return !empty($existingid) && ($this->get_date() < time());
    }

    public function cancel_session($notes = '') {
        global $DB;

        if ($this->lunchandlearn->attendeemanager->get_attendee_count() > 0) {
            foreach ($this->lunchandlearn->attendeemanager->get_attendees() as $attendee) {
                $user = $attendee->get_user();
                $this->lunchandlearn->attendeemanager->cancel_signup($user, $notes);
                if (false === $this->has_past()) {
                    lunchandlearn_manager::admin_bulk_cancel_meeting_request($this->lunchandlearn, $user);
                }
            }
        }
        $DB->set_field('local_lunchandlearn', 'cancelled', '1', array('id' => $this->lunchandlearn->id));
    }

    public function is_cancelled() {
        return !empty($this->cancelled);
    }

    public function set_cancelled($cancelled) {
        $this->cancelled = $cancelled;
    }

    public function form(moodleform $mform) {
        $mform->set_data(get_object_vars($this));
        // Set date, timezone and duration as these are different.
        $mform->set_data(array(
            'timestart' => array(
                'timestart' => $this->get_date(),
                'timezone' => $this->get_timezone()
            ),
            'timedurationminutes' => $this->get_duration()
        ));
    }
}
