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

namespace local_onlineappraisal\output\index;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;

class groupleader extends base {
    /**
     * Constructor.
     * @param \local_onlineappraisal\index $index
     */
    public function __construct(\local_onlineappraisal\index $index) {
        $this->set_type('groupleader');
        parent::__construct($index);
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        parent::export_for_template($output);

        $this->data->heading = get_string('index:groupleader', 'local_onlineappraisal');
        $this->data->toptext = get_string('index:toptext:groupleader', 'local_onlineappraisal');
        if ($this->index->groupid || $this->searching) {
            $this->get_appraisals();
        }

        return $this->data;
    }
}
