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
 * Provide the basic methods we use in all lal classes
 *
 * @author paulstanyer
 */
class lal_base {
     /* calls method get_'variable', which if it does not exist, will call __call below */
    public function __get($name) {
        return $this->{'get_'.$name}();
    }

    /* Attempts to use the property */
    public function __call($name, $arguments) {
        // Strip name.
        $prop = preg_replace('/^((g|s)et|is)_/', '', $name);

        if (preg_match('/^(is|get)_/', $name)) {
            // Strip name.
            $prop = preg_replace('/^get_/', '', $name);

            if (property_exists($this, $prop)) {
                return $this->{$prop};
            }
        } else if (preg_match('/^set_/', $name)) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $arguments[0];
            }
        }
    }

    public function bind($data) {
        foreach ($data as $prop => $value) {
            $this->{$prop} = $value;
        }
    }

    public function form(moodleform $mform) {
        $mform->set_data(get_object_vars($this));
    }

    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public function load_record($record, $skipid = true) {
        foreach ($record as $prop => $value) {
            if ($skipid && $prop == 'id') {
                continue;
            }
            $this->$prop = $value;
        }
    }
}
