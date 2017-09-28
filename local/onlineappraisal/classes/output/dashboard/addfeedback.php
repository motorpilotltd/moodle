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

use stdClass;
use renderer_base;
use moodle_url;

class addfeedback extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        // Show Form
        $this->appraisal->prepare_page();

        $data->form = $this->appraisal->form->render();
        
        $data->appraiseename = fullname($this->appraisal->appraisal->appraisee);
        $data->appraisername = fullname($this->appraisal->appraisal->appraiser);
        $a = new stdClass();
        $a->appraisee_fullname = $data->appraiseename;
        $a->appraiser_fullname = $data->appraisername;
        $day = userdate($this->appraisal->appraisal->held_date, '%d');
        $month = substr(userdate($this->appraisal->appraisal->held_date, '%B'), 0, 3);
        $year = userdate($this->appraisal->appraisal->held_date, '%Y');
        $a->facetofacedate = "$day/$month/$year";
        $data->feedbackheader = get_string('feedback_header', 'local_onlineappraisal', $a);

        return $data;
    }
}
