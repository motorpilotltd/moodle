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
 * @copyright   2019 Xantico Ltd
 * @author      aleks@xanti.co
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\admin;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;

class cycle extends base
{
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output)
    {
        global $DB;
        $data = new stdClass();
        $cohorts = $DB->get_records('local_appraisal_cohorts');

        $utctimezone = new \DateTimeZone('UTC');

        foreach ($cohorts as &$cohort) {
            $cohort->availablefromdate = userdate($cohort->availablefrom, get_string('strftimedate', 'core_langconfig'), $utctimezone);

            // For disabling editing of passed dates in days counting
            $availfromdate = new \DateTime();
            $availfromdate->setTimestamp($cohort->availablefrom);
            $availfromdate->setTimezone($utctimezone);

            $nowdate = new \DateTime('now', $utctimezone);

            $availdiff = $availfromdate->diff($nowdate);

            if ($availdiff->invert == 0 && $availdiff->days > 0) {
                $cohort->notavailable = true;
            }

        }

        $data->cohorts = array_values($cohorts);

        $data->form = new stdClass();
        $data->form->id = 'oa-form-appraisalcycle';
        $data->form->availablefrom = time();
        $data->form->hiddeninputs = [
            ['name' => 'id', 'value' => ''],
            ['name' => 'page', 'value' => 'cycle'],
            ['name' => 'sesskey', 'value' => sesskey()],
            ['name' => 'action', 'value' => 'add']
        ];

        return $data;
    }
}