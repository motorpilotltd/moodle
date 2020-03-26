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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

class courseprogressiterator implements \Iterator {
    private $position;
    private $currentpage;
    private $positionoffset;
    private $since;

    private $api;

    public function __construct($api = null, $since) {
        $this->since = $since;
        if ($api == null) {
            $this->api = new api();
        } else {
            $this->api = $api;
        }
    }

    private $totalinresultset = null;
    private function loaddata() {
        if ($this->position === $this->totalinresultset) {
            return [];
        }

        $response = $this->api->getcourseprogress($this->position, $this->since);

        if (isset($response->elements)) {
            $this->totalinresultset = $response->paging->total;
            return $response->elements;
        }

        return [];
    }

    public function rewind() {
        $this->position = 0;
        $this->positionoffset = 0;
        $this->totalinresultset = null;
        $this->currentpage = $this->loaddata();

        if (!empty($this->currentpage)) {
            mtrace('Loaded page of course progress.');
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
            $this->currentpage = $this->loaddata();

            if (!empty($this->currentpage)) {
                mtrace('Loaded page of course progress.');
                mtrace(count($this->currentpage) . ' records loaded');
            }
        }

        return isset($this->currentpage[$this->position - $this->positionoffset]);
    }
}