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
 * Description of timezone
 *
 * @author paulstanyer
 */
class timezone {
    private $id = 0;
    private $display;
    private $timezone;

    public function __construct($timezone, $display, $id=0) {
        $this->set_timezone($timezone);
        $this->set_display($display);
        $this->set_id($id);
    }

    public static function load($id) {
        global $DB;
        $me = $DB->get_record('local_timezones', array('id' => $id));
        return new self($me->timezone, $me->display, $me->id);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_display() {
        return $this->display;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    public function set_id($id) {
        $this->id = $id;
    }

    public function set_display($display) {
        $this->display = $display;
    }

    public function set_timezone($timezone) {
        $this->timezone = $timezone;
    }

    public function form(moodleform $form) {
        $form->set_data(get_object_vars($this));
    }

    public function save() {
        global $DB;

        $class = (object)get_object_vars($this);

        if (empty($class->id)) {
            $this->set_id($DB->insert_record('local_timezones', $class));
        } else {
            $DB->update_record('local_timezones', $class);
        }
    }

    public function delete() {
        global $DB;

        $DB->delete_records('local_timezones', array('id' => $this->get_id()));
    }

    public function __toString() {
        return $this->get_display() . ' (' . $this->get_timezone() . ')';
    }
}
