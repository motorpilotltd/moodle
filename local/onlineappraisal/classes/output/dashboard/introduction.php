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

class introduction extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->iscontinuing = $this->appraisal->appraisal->statusid > 1;
        $data->url = new moodle_url('/local/onlineappraisal/view.php', array('page' => 'introduction', 'appraisalaction' => 'start'));
        if ($data->iscontinuing) {
            $data->url->param('page', 'overview');
            $data->url->remove_params('appraisalaction');
        }
        $data->duedate = empty($this->appraisal->appraisal->due_date) ? '-' : userdate($this->appraisal->appraisal->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).

        $data->targetedmessage = $this->targetedmessage();

        return $data;
    }

    /**
     * Returns a targeted message for the overview page.
     *
     * @return string
     */
    private function targetedmessage() {
        // No longer targeted by grade, generally available.
        return get_string('introduction:targetedmessage', 'local_onlineappraisal');
    }
}
