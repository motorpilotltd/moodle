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

class lyndacoursecompletioniterator implements \Iterator {
    private $position;
    private $currentpage;
    private $positionoffset;

    private $api;

    private $startdate;
    private $enddate;

    public function __construct($startdate, $enddate, $api = null) {
        if ($api == null) {
            $this->api = new lyndaapi();
        } else {
            $this->api = $api;
        }

        $this->startdate = $startdate;
        $this->enddate = $enddate;
    }

    public function rewind() {
        $this->position = 0;
        $this->positionoffset = 0;
        $this->currentpage = $this->api->certficateofcompletion($this->startdate, $this->enddate, 0);

        if (isset($this->currentpage->Status) && $this->currentpage->Status == 'error') {
            print_error('Error communicating with Lynda: ' . $this->currentpage->message);
        }

        if (isset($this->currentpage)) {
            mtrace('Loaded page of course completion data.');
            mtrace(count($this->currentpage) . ' records loaded');
        }
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
        if(!isset($this->currentpage[$this->position - $this->positionoffset])) {
            $this->positionoffset = $this->position;
            $this->currentpage = $this->api->certficateofcompletion($this->startdate, $this->enddate, $this->position);

            if (isset($this->currentpage)) {
                mtrace('Loaded page of course completion data.');
                mtrace(count($this->currentpage) . ' records loaded');
            }
        }

        return isset($this->currentpage[$this->position - $this->positionoffset]);
    }
}