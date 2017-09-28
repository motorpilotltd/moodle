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

namespace local_onlineappraisal\output\help;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderable;
use templatable;
use renderer_base;
use popup_action;

class help implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();

        $url = get_config('local_onlineappraisal', 'helpurl');
        if ($url) {
            $data->hasurl = true;
            $action = $OUTPUT->action_link($url, get_string('helppage:helpbtn', 'local_onlineappraisal'), 
                new popup_action('click', $url, 'AppraisalHelp'), array('height' => 600, 'width' => 800, 'class' => 'btn btn-primary'));
            $data->action = $action;
        }
        
        return $data;
    }
}