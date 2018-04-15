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

namespace local_onlineappraisal\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use renderer_base;

class feedbackrequests extends base {
    private $feedback;
    public function __construct(\local_onlineappraisal\feedback $feedback) {
        $this->feedback = $feedback;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = $this->feedback->request_data();

        if (!empty($data->filter)) {
            $url = new \moodle_url('/local/onlineappraisal/feedback_requests.php');
            $s = new \single_select($url, 'filter', $data->filter, $data->filterselected, null);
            $s->label = get_string('feedbackrequests:filter:label', 'local_onlineappraisal');
            $s->labelattributes = ['class' => 'm-t-5 m-r-5'];
            $s->class = 'pull-right';
            $data->filterselect = $output->render($s);
        }

        return $data;
    }
}
