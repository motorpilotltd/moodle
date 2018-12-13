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

        // Should we even be running?
        $tickeractive = get_config('block_certification_report', 'ticker_active');
        if (!$tickeractive) {
            return;
        }

        // Set up for correct view when getting data.
        define('BLOCK_CERTIFICATION_REPORT_EXPORT', true);
        $_POST['exportview'] = 'users';

        $filteroptions = certification_report::get_filter_options();
        $data = new stdClass();
        $data->fullname = ''; // A value is normally expected from the filter form.
        $data->georegions = ['UKIMEA', 'Europe'];
        $data->certifications = [1, 2, 3];
        $filters = certification_report::get_filter_data($filteroptions, $data);

        $certificationsreport = certification_report::get_data($filters);

        $csvdata = $this->export_to_csv($filters, $filteroptions, $certificationsreport['data']);

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

    /**
     * Export data to CSV - ported from main class and forced to 'users' view to protect against changes.
     */
    private function export_to_csv($filters, $filteroptions, $data) {
        global $CFG;

        require_once($CFG->libdir . '/csvlib.class.php');

        $lines = [];

        $usersheader = [];
        $usersheader[] = get_string('staffid', 'block_certification_report');
        $usersheader[] = get_string('username');
        $usersheader[] = get_string('email');
        $usersheader[] = get_string('grade', 'block_certification_report');
        $usersheader[] = get_string('employmentcategory', 'block_certification_report');
        $usersheader[] = get_string('actualregion', 'block_certification_report');
        $usersheader[] = get_string('georegion', 'block_certification_report');
        $usersheader[] = get_string('costcentre', 'block_certification_report');
        $usersheader[] = get_string('groupname', 'block_certification_report');
        $usersheader[] = get_string('locationname', 'block_certification_report');

        $header = [];
        $header[] = get_string('headerusers', 'block_certification_report');

        foreach($filteroptions['certificationsdata'] as $certification){
            if (isset($data['viewtotal']['certifications'][$certification->id]) && $data['viewtotal']['certifications'][$certification->id]['progress'] !== null) {
                $header[] = $certification->shortname
                        . ($data['viewtotal']['certifications'][$certification->id]['exempt'] == 1 ? "\n" . get_string('exempt', 'block_certification_report') : '')
                        . ($data['viewtotal']['certifications'][$certification->id]['optional'] == 1 ? "\n" . get_string('optional', 'block_certification_report') : '')
                        . "\n" . get_string('headercomplete', 'block_certification_report');
                $header[] = $certification->shortname
                        . ($data['viewtotal']['certifications'][$certification->id]['exempt'] == 1 ? "\n" . get_string('exempt', 'block_certification_report') : '')
                        . ($data['viewtotal']['certifications'][$certification->id]['optional'] == 1 ? "\n" . get_string('optional', 'block_certification_report') : '')
                        . "\n" . get_string('headertotal', 'block_certification_report');
                $usersheader[] = $certification->shortname;
            }
        }

        $lines[] = $usersheader;
        $ccs = \block_certification_report\certification_report::get_costcentre_names();

        foreach ($data as $itemname => $item) {
            if ($itemname == 'viewtotal') {
                continue;
            }
            $line = [];
            $line[] = $item['userdata']->idnumber;
            $line[] = $item['userdata']->firstname.' '.$item['userdata']->lastname;
            $line[] = $item['userdata']->email;
            $line[] = $item['userdata']->grade;
            $line[] = $item['userdata']->employmentcategory;
            $line[] = $item['userdata']->actualregion;
            $line[] = $item['userdata']->georegion;
            $costcentre = isset($ccs[$item['userdata']->costcentre]) ? $ccs[$item['userdata']->costcentre] : $item['userdata']->costcentre;
            $line[] = $costcentre == -1 ? '' : $costcentre;
            $line[] = $item['userdata']->groupname;
            $line[] = $item['userdata']->locationname;

            foreach ($item['certifications'] as $certificationid => $certification) {
                if (isset($data['viewtotal']['certifications'][$certificationid]) && $data['viewtotal']['certifications'][$certificationid]['progress'] !== null) {
                    if (isset($certification['exemptionid']) && $certification['exemptionid'] > 0) {
                        $line[] = get_string('notrequired', 'block_certification_report');
                    } elseif ($certification['progress'] === null) {
                        $line[] = get_string('na', 'block_certification_report');
                    } else {
                        $cell = $certification['progress'] . '%';
                        if (isset($certification['completiondate']) && $certification['completiondate'] > 0) {
                            $cell .= ' (' . userdate($certification['completiondate'], get_string('strftimedatefullshort')) . ')';
                        }
                        $line[] = $cell;
                    }
                }
            }
            $lines[] = $line;
        }
        return \csv_export_writer::print_array($lines, 'comma', '"', true);
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