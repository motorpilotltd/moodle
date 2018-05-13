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
 * The block_certification_report export reports task.
 *
 * @package    block_certification_report
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_certification_report\task;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use block_certification_report\certification_report;

/**
 * The block_certification_report export reports task class.
 *
 * @package    block_certification_report
 * @since      Moodle 3.0
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class export_reports extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskexportreports', 'block_certification_report');
    }

    /**
     * Run the export reports task.
     */
    public function execute() {
        global $CFG, $USER;

        // Set up for correct view.
        define('BLOCK_CERTIFICATION_REPORT_EXPORT', true);
        $_POST['exportview'] = 'users';

        $filteroptions = certification_report::get_filter_options();
        $data = new stdClass();
        $data->fullname = ''; // A value is normally expected from the filter form.
        $data->georegions = ['UKIMEA'];
        $data->certifications = [1, 2, 3];
        $filters = certification_report::get_filter_data($filteroptions, $data);

        $certificationsreport = certification_report::get_data($filters);

        $csvdata = certification_report::export_to_csv($filters, $filteroptions, $certificationsreport['data'], $certificationsreport['view']);

        make_temp_directory('certificationreport/' . $USER->id);
        $csvfile = $CFG->tempdir . '/certificationreport/' . $USER->id. '/moodle_ticker.csv';
        $csvhandle = fopen($csvfile, 'w+');
        fwrite($csvhandle, $csvdata);
        fclose($csvhandle);

        $user = dummy_user::get_dummy_block_certification_report_user('moodle.ticker@arup.com', 'Moodle', 'Ticker');
        $admin = get_admin();
        email_to_user($user, $admin, "[{$CFG->wwwroot}] Moodle Ticker Report", 'Moodle Ticker Report', '', $csvfile, 'moodle_ticker.csv',
                      true, '', '', 79, [$admin]);

        unlink($csvfile);
    }
}

class dummy_user extends \core_user {
    public static function get_dummy_block_certification_report_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'blockcertificationreportuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}