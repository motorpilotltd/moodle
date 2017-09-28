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
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupmyproxy;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        $ue = (object)$event->other['userenrolment'];

        // Only do this is this is the last enrolment.
        if ($ue->lastenrol) {
            $arupmyproxys = $DB->get_records('arupmyproxy', array('course' => $event->courseid));

            if (empty($arupmyproxys)) {
                return;
            }

            $coursecontext = \context_course::instance($event->courseid);

            foreach ($arupmyproxys as $arupmyproxy) {
                // Load users that were proxying for the unenrolled user (for later).
                $users = $DB->get_records('arupmyproxy_proxies', array('arupmyproxyid' => $arupmyproxy->id, 'proxyuserid' => $ue->userid), '', 'userid as id');

                // Delete proxy records.
                $DB->delete_records('arupmyproxy_proxies', array('arupmyproxyid' => $arupmyproxy->id, 'proxyuserid' => $ue->userid));

                foreach ($users as $user) {
                    // Check if user is still proxying for others in the same activity.
                    $stillproxy = $DB->count_records(
                        'arupmyproxy_proxies',
                        array('userid' => $user->id, 'arupmyproxyid' => $arupmyproxy->id)
                    );
                    if (!$stillproxy) {
                        // Not still a proxy so unassign the special role assignment.
                        role_unassign($arupmyproxy->roleid, $user->id, $coursecontext->id, 'mod_arupmyproxy', $arupmyproxy->id);
                    }
                }
            }
        }
    }
}
