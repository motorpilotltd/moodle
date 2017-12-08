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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

class lyndacourseiterator implements \Iterator {
    private $position;
    private $currentpage;
    private $positionoffset;

    private $api;

    public function __construct($api = null) {
        if ($api == null) {
            $this->api = new lyndaapi();
        } else {
            $this->api = $api;
        }
    }

    public function rewind() {
        $this->position = 0;
        $this->positionoffset = 0;
        $this->currentpage = $this->api->getcourses(0);
    }

    public function current() {
        return $this->currentpage[$this->position - $this->positionoffset];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        if ($this->position > 100) {
            return false;
        }
        if(!isset($this->currentpage[$this->position - $this->positionoffset])) {
            $this->positionoffset = $this->position;
            $this->currentpage = $this->api->getcourses($this->position);
        }

        return isset($this->currentpage[$this->position - $this->positionoffset]);
    }
}