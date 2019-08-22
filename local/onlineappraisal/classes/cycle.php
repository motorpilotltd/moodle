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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use moodle_exception;

class cycle
{

    private $permittedactions = ['add', 'update'];
    private $cycleurl = '';
    /**
     * Constructor.
     *
     * @param object $admin the full admin object
     */
    public function __construct(\local_onlineappraisal\admin $admin)
    {
        $this->admin = $admin;
        $url = new moodle_url('/local/onlineappraisal/admin.php', ['page' => 'cycle']);
        $this->cycleurl = $url->out(false);
    }

    /**
     * Hook
     *
     * This function is called from the main admin controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_onlineappraisal\admin->add_page();
     *
     * @return void
     */
    public function hook()
    {
        $action = optional_param('action', null, PARAM_ALPHA);

        if (!in_array($action, $this->permittedactions)) {
            return;
        }

        try {
            require_sesskey();
            $id = optional_param('id', 0, PARAM_INT);
            $cyclename = required_param('name', PARAM_ALPHANUM);
            $availablefrom = required_param('availablefrom', PARAM_INT);

            if ($this->isfuturedate($availablefrom)) {
                $result = $this->{$action}($cyclename, $availablefrom, $id);
            } else {
                $result = new stdClass();
                $result->message = get_string('error:appraisalcycle:invalideavailablefrom', 'local_onlineappraisal');
                $result->success = false;

            }
            $alerttype = $result->success? 'success' : 'danger';
            appraisal::set_alert($result->message, $alerttype);
        } catch (moodle_exception $e) {
            appraisal::set_alert($e->getMessage(), 'danger');
        }
        redirect($this->cycleurl);
    }

    /**
     * Save a new entry for local_appraisal_cohorts
     *
     * @param $cyclename
     * @param $availablefrom
     * @param $id
     * @return stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function add($cyclename, $availablefrom, $id) {
        global $DB;

        $cohort = new stdClass();
        $cohort->name = trim($cyclename);
        $cohort->availablefrom = $availablefrom;
        $cohort->timemodified = $cohort->timecreated = time();

        $return = new stdClass();
        $return->success = true;

        if ($DB->count_records('local_appraisal_cohorts', ['name' => $cohort->name])) {
            $return->message = get_string('error:appraisalcycle:nameexist', 'local_onlineappraisal', $cohort->name);
            $return->success = false;
        } else if (empty($cohort->name)) {
            $return->message = get_string('error:appraisalcycle:namenotempty', 'local_onlineappraisal');
            $return->success = false;
        } else {
            $DB->insert_record('local_appraisal_cohorts', $cohort);
            $return->message = get_string('success:appraisalcycle:added', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Modify existing entry of a local_appraisal_cohorts
     *
     * @param $cyclename
     * @param $availablefrom
     * @param $id
     * @return stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function update($cyclename, $availablefrom, $id) {
        global $DB;
        $return = new stdClass();
        $return->success = true;

        $cohort = $DB->get_record('local_appraisal_cohorts', ['id' => $id]);
        $cohort->name = trim($cyclename);
        // Prevent modifying cycle with a past date
        if (!$this->isfuturedate($cohort->availablefrom)) {
            $return->message = get_string('error:appraisalcycle:invalideavailablefrom', 'local_onlineappraisal');
            $return->success = false;
            return $return;
        }

        $cohort->availablefrom = $availablefrom;
        $cohort->timemodified = time();

        $sql = "
            SELECT
                COUNT(*)
            FROM
                {local_appraisal_cohorts}
            WHERE
                name = :cname AND id != :cid
            ";
        $isexist = $DB->count_records_sql($sql, array('cname' => $cohort->name,'cid' => $id));

        if ($isexist) {
            $return->message = get_string('error:appraisalcycle:nameexist', 'local_onlineappraisal', $cohort->name);
            $return->success = false;
        } else if (empty($cohort->name)) {
            $return->message = get_string('error:appraisalcycle:namenotempty', 'local_onlineappraisal');
            $return->success = false;
        } else {
            $DB->update_record('local_appraisal_cohorts', $cohort);
            $return->message = get_string('success:appraisalcycle:updatecycle', 'local_onlineappraisal');
        }

        return $return;
    }

    private function isfuturedate ($datedata) {
        $utctimezone = new \DateTimeZone('UTC');

        $availfromdate = new \DateTime();
        $availfromdate->setTimestamp($datedata);
        $availfromdate->setTimezone($utctimezone);

        $nowdate = new \DateTime('now', $utctimezone);

        $availdiff = $availfromdate->diff($nowdate);

        if ($availdiff->invert == 0 && $availdiff->days > 0) {
            return false;
        }
        return true;
    }
}