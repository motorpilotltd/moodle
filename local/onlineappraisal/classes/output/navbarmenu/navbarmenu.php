<?php
// This file is part of the Arup online navbarmenu system
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

namespace local_onlineappraisal\output\navbarmenu;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use moodle_url;
use stdClass;

class navbarmenu implements renderable, templatable {

    public function __construct(\local_onlineappraisal\navbarmenu $navbarmenu) {
        $this->navbarmenu = $navbarmenu;
    }
    
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $data = $this->navbarmenu->get_navdata();
        if (!$data) {
            $data = new stdClass();
        }
        $data->moodleurl = new moodle_url('/');
        $data->appraisalurl = new moodle_url('/local/onlineappraisal/index.php');
        $url = get_config('local_onlineappraisal', 'helpurl');
        if ($url) {
            $data->helpurl = $url;
        } else {
            $data->helpurl = $PAGE->url;
            $data->helpurl->params(['page' => 'help']);
        }
        if ($data && isset($this->navbarmenu->inappraisal)) {
            $data->inappraisal = $this->navbarmenu->inappraisal;
        }
        return $data;
    }
}
