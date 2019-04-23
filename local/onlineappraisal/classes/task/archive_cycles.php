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
 * The local_onlineappraisal archive cycles task.
 *
 * @package    local_onlineappraisal
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\task;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * The local_onlineappraisal archive cycles task class.
 *
 * @package    local_onlineappraisal
 * @since      Moodle 3.3
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archive_cycles extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskarchivecycles', 'local_onlineappraisal');
    }

    /**
     * Run the archive cycles task.
     *
     * @global \moodle_database $DB
     */
    public function execute() {
        global $DB;

        $email = "*** ARCHIVING OLD APPRAISAL CYCLE APPRAISALS ***\n";

        mtrace('Begin archiving old appraisal cycle appraisals...');

        // Want cycle prior to current and previous.
        $cycles = $DB->get_records_select(
            'local_appraisal_cohorts',
            'availablefrom < :now',
            ['now' => time()],
            'availablefrom DESC',
            'id, name',
            2, 0
        );

        $count = 0;
        foreach ($cycles as $cycle) {
            $email .= $string = "\nARCHIVING FROM CYCLE: {$cycle->name}\n";
            mtrace($string);
            // Any non-archive appraisals on it?
            $appsql = "SELECT laa.*
                         FROM {local_appraisal_appraisal} laa
                         JOIN {local_appraisal_cohort_apps} laca ON laca.appraisalid = laa.id
                        WHERE laca.cohortid = :cycle AND laa.archived = 0 AND laa.deleted = 0";
            $appraisals = $DB->get_records_sql($appsql, ['cycle' => $cycle->id]);
            foreach ($appraisals as $appraisal) {
                $count++;
                if ($appraisal->statusid == 1) {
                    $appraisal->deleted = 1;
                } else {
                    $appraisal->archived = 1;
                }
                $DB->update_record('local_appraisal_appraisal', $appraisal);
                $email .= $string = "    ARCHIVED APPRAISAL ID: {$appraisal->id}\n";
                mtrace($string);
            }
        }

        // Email summary to admin.
        if ($count) {
            email_to_user(get_admin(), get_admin(), 'Archiving old appraisal cycle appraisals summary', $email);
        }

        mtrace('...end archiving old appraisal cycle appraisals.');
    }
}