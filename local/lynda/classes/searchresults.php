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

class searchresults implements \templatable {
    public $results;
    public $count;
    public $regionid;
    public $keyword;
    public $page;
    public $perpage;


    function __construct($keyword, $regionid, $page, $perpage) {
        $this->keyword = $keyword;
        $this->regionid = $regionid;
        $this->page = $page;
        $this->perpage = $perpage;
    }

    public function dosearch() {
        $this->results = \local_lynda\lyndacourse::search($this->keyword, $this->regionid, $this->page, $this->perpage);
        $this->count = \local_lynda\lyndacourse::searchcount($this->keyword, $this->regionid);
    }

    public function export_for_template(\renderer_base $output) {
        $searchresults = new \stdClass();
        $searchresults->resultcount = $this->count;
        $searchresults->results = [];

        if (empty($this->results)) {
            return [];
        }

        foreach ($this->results as $resultobj) {
            $result = new \stdClass();

            $result->uri = new \moodle_url('/local/lynda/launch.php', ['lyndacourseid' => $resultobj->remotecourseid]);
            $result->title = $resultobj->title;
            $result->description = $resultobj->description;
            $result->thumburi = new \moodle_url($resultobj->thumbnail);
            $result->duration = format_time($resultobj->durationinseconds);

            $searchresults->results[] = $result;
        }

        return $searchresults;
    }
}