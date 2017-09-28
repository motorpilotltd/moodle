<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\lastyear;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;

class lastyear_legacy implements renderable, templatable {
    private $lastyear;
    private $type;

    public function __construct($lastyear, $type) {
        // Clean the $lastyear array coming in from the DB
        $newlastyear = array();
        foreach ($lastyear as $ly) {
            $ly->date = userdate($ly->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (Set by old appraisal system).
            $newlastyear[] = $ly;
        }
        $this->type = $type;
        $this->lastyear = $newlastyear;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $type = $this->type;
        $data->type = $this->type;
        $data->$type = true;
        if (count($this->lastyear) > 0) {
            $data->hasdata = true;
        }
        $data->lastyear = $this->lastyear;
        return $data;
    }
}
