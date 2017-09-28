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
 * The local_sitemessaging check countdown task.
 *
 * @package    local_sitemessaging
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sitemessaging\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_sitemessaging check countdown task class.
 *
 * @package    local_sitemessaging
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_countdown extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskcheckcountdown', 'local_sitemessaging');
    }

    /**
     * Run check countdown task.
     */
    public function execute() {
        global $CFG;

        $config = get_config('local_sitemessaging');

        $now = time();

        $notrunning = empty($config->active) || empty($config->countdown_active) || empty($config->countdown_stop_login);
        $expirednotstarted = empty($config->countdown_inprogress) && $config->countdown_until < $now;

        if ($notrunning || $expirednotstarted) {
            $configtoreset = !empty($config->countdown_inprogress) || !empty($config->login_stopped) || !empty($config->sessiontimeout);
            if ($configtoreset) {
                require_once($CFG->dirroot.'/local/sitemessaging/lib.php');
                $config->sessiontimeout = empty($config->sessiontimeout) ? null : $config->sessiontimeout;
                local_sitemessaging_reset_auto_config($config->sessiontimeout);
            }
            return;
        }


        if (empty($config->countdown_inprogress)) {
            $config->countdown_inprogress = true;
            set_config('countdown_inprogress', $config->countdown_inprogress, 'local_sitemessaging');
        }

        $stoptime = $config->countdown_stop_login_time * 60;
        if (empty($config->login_stopped) && $now + $stoptime >= $config->countdown_until) {
            $config->login_stopped = true;
            set_config('login_stopped', $config->login_stopped, 'local_sitemessaging');
            set_config('sessiontimeout', $CFG->sessiontimeout, 'local_sitemessaging');
            set_config('sessiontimeout', max(600, $stoptime));
        }
    }
}