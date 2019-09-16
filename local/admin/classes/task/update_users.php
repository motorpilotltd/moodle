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
 * The local_admin update users task.
 *
 * @package    local_admin
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_admin\task;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use local_admin\user_update;

/**
 * The local_admin update users task class.
 *
 * @package    local_admin
 * @since      Moodle 3.0
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_users extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskupdateusers', 'local_admin');
    }

    /**
     * Run the update users task.
     */
    public function execute() {
        global $CFG, $DB;

        // Allow extended usernames.
        // Grab existing.
        $extendedusernamechars = $CFG->extendedusernamechars;
        // Allow any characters.
        $CFG->extendedusernamechars = true;

        // Open second connection as we need no prefix.
        $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = array();
        }
        // Pretend it's external to remove prefix injection.
        $DB2 = \moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary, true);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, false, $cfg->dboptions);

        // Don't run if we can't find the HUB table.
        if (!$DB2->get_manager()->table_exists('ARUP_ALL_STAFF_V')) {
            mtrace('View SQLHUB.ARUP_ALL_STAFF_V does not exist. Aborting...');
            return;
        }

        $actions = ['add', 'unsuspend', 'suspend', 'update'];

        $email = '';

        $userupdate = new user_update();

        mtrace('Begin user update Hub <-> Moodle...');

        foreach ($actions as $action) {
            $method = "{$action}_users";
            if (method_exists($userupdate, $method) && is_callable([$userupdate, $method])) {
                $results = call_user_func([$userupdate, $method]);
                $title = '***' . strtoupper($action) . ' users***';
                $email .= $title . "\n";
                mtrace($title);
                $successes = '  Success: ' . $results['success'];
                $email .= $successes . "\n";
                mtrace($successes);
                $errors = '  Error: ' . $results['error'];
                $email .= $errors . "\n";
                mtrace($errors);

                mtrace('');
                $email .= "\n";
            }
        }

        // Ensure all added users have policyagreed flagged as true.
        $DB->set_field('user', 'policyagreed', 1, ['auth' => 'saml', 'policyagreed' => 0]);

        $url = new moodle_url('/local/admin/user_report.php');
        $urltext = 'See ' . $url->out() . ' for full logs.';
        mtrace($urltext);
        $email .= $urltext . "\n";

        mtrace('');
        $email .= "\n";

        email_to_user(get_admin(), get_admin(), 'Nightly user update summary', $email);

        mtrace('...end user update Hub <-> Moodle.');

        // Reset.
        $CFG->extendedusernamechars = $extendedusernamechars;
    }
}