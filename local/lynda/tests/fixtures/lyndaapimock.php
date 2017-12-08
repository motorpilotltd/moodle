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

class lyndaapimock extends lyndaapi {
    public function getcourses($start) {
        return array_slice($this->response, $start, 2);
    }

    private $response;
    public function __construct() {
        global $CFG;

        parent::__construct();

        $this->response = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockresponse.json"));
    }

    public function reset() {
        global $CFG;
        
        $this->response = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockresponse.json"));
    }

    public function dropcoursefromresponse() {
        array_shift($this->response);
    }
}