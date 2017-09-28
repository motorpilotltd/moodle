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

namespace local_onlineappraisal\output\admin;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;

class archived extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->users = array_values($this->admin->get_group_appraisals('archived'));
        $data->usercount = count($data->users);
        foreach ($data->users as $user) {
            $user->progress = $this->get_progress($user->statusid);
            $user->due_date = empty($user->due_date) ? '-' : userdate($user->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
            $user->completed_date = empty($user->completed_date) ? '-' : userdate($user->completed_date, get_string('strftimedate'));
        }
        return $data;
    }
}
