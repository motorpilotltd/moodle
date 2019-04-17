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
 * Observer class containing methods monitoring various events.
 *
 * @package    local_onlineappraisal
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.3
 * @package    local_onlineappraisal
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via \local_costcentre\event\costcentres_added event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function costcentres_added(\local_costcentre\event\costcentres_added $event) {
        global $DB;

        $now = time();
        $costcentres = $event->other['costcentres'];

        if (!is_array($costcentres) || empty($costcentres)) {
            return;
        }

        $email = "*** ADDING APPRAISAL CYCLES ***\n";
        mtrace('Begin starting appraisal cycles...');

        // local_appraisal_cohort_ccs object.
        $lacc = new stdClass();
        $lacc->started = $now;
        $lacc->locked = $now;
        $lacc->closed = null;
        $lacc->duedate = null;

        // Get cycles
        // Reverse array as want two latest, but older cycle first!
        $cycles = array_reverse(
            $DB->get_records_select(
                'local_appraisal_cohorts',
                'availablefrom < :now',
                ['now' => $now],
                'availablefrom DESC',
                'id, name',
                0, 2
            ),
            true
        );

        $count = 0;
        foreach ($costcentres as $costcentre) {
            // Find appraisal cycle to open.
            // Are there any appraisals on previous cycle?
            $countsql = "SELECT lac.id, lac.name, COUNT(laca.id) as appcount
                           FROM {local_appraisal_cohorts} lac
                           JOIN {local_appraisal_cohort_apps} laca ON laca.cohortid = lac.id
                          WHERE laca.appraisalid IN (SELECT laa.id
                                                       FROM {local_appraisal_appraisal} laa
                                                       JOIN {user} u ON u.id = laa.appraisee_userid
                                                       WHERE u.icq = :icq AND u.suspended = 0 AND u.deleted = 0
                                                             AND laa.archived = 0 AND laa.deleted = 0)
                       GROUP BY lac.id, lac.name";
            $appcount = $DB->get_records_sql($countsql, ['icq' => $costcentre]);

            foreach ($cycles as $cycle) {
                if (isset($appcount[$cycle->id]) && $appcount[$cycle->id]->appcount > 0) {
                    break;
                }
            }

            if (isset($cycle)) {
                $count++;
                $lacc->cohortid = $cycle->id;
                $lacc->costcentre = $costcentre;
                $DB->insert_record('local_appraisal_cohort_ccs', $lacc);
                $email .= $string = "{$costcentre} - STARTED/LOCKED APPRAISAL CYCLE: {$cycle->name}\n";
                mtrace($string);
            }
            unset($cycle);
        }

        // Email summary to admin.
        if ($count) {
            email_to_user(get_admin(), get_admin(), 'Newly started appraisal cycles summary', $email);
        }

        mtrace('...end starting new appraisal cycles.');
    }
}