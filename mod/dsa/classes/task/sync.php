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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

namespace mod_dsa\task;

use core\task\scheduled_task;
use mod_dsa\apiclient;
use mod_dsa\event\user_dsa_assessments_updated;

defined('MOODLE_INTERNAL') || die();

class sync extends scheduled_task {
    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('sync', 'mod_dsa');
    }

    private $apiclient;
    private $synclimit = 100;

    public function __construct() {
        $this->apiclient = apiclient::getapiclient();
    }

    public function execute() {
        global $DB, $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        mtrace("Checking for active users");

        $now = time();

        // On first run just get last 24hrs then let the rest come in piece-meal.
        if ($this->get_last_run_time() == 0) {
            $this->set_last_run_time($now - DAYSECS);
        }

        $newrecords = $this->apiclient->checkforupdate($now, $this->get_last_run_time());

        mtrace("Loaded records: " . count($newrecords));

        foreach ($newrecords as $newrecord) {
            $user = $DB->get_record('user', ['idnumber' => str_pad($newrecord->StaffNumber, 6, '0', STR_PAD_LEFT)]);

            if (!$user) {
                mtrace('Unable to find staff number: ' . $newrecord->StaffNumber);
                continue;
            }

            $this->apiclient->sync_user($user);

        }
        mtrace("Checking for users that have never been synced.");

        $countsql = "select count(u.id)
                from {user} u
                       left join {logstore_standard_log} l on l.relateduserid = u.id and component = 'mod_dsa' and action = 'updated'
                       left join {dsa_assessment} da on da.userid = u.id
                where da.id is null
                  and l.id is null
                  and u.idnumber <> ''
                  and u.deleted = 0";

        $neversyncedcount = $DB->count_records_sql($countsql);
        mtrace("Found $neversyncedcount that have never been synced. Will sync the first $this->synclimit");

        $sql = "select u.*
                from {user} u
                       left join {logstore_standard_log} l on l.relateduserid = u.id and component = 'mod_dsa' and action = 'updated'
                       left join {dsa_assessment} da on da.userid = u.id
                where da.id is null
                  and l.id is null
                  and u.idnumber <> ''
                  and u.deleted = 0";
        $neversynced = $DB->get_records_sql($sql, [], 0, $this->synclimit);

        foreach ($neversynced as $user) {
            $this->apiclient->sync_user($user);
        }

        mtrace("Done");

        $this->set_last_run_time($now);
        return true;
    }
}
