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
 * @package    mod_aruphonestybox
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_aruphonestybox;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.0
 * @package    mod_aruphonestybox
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via course_completed event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/mod/aruphonestybox/lib.php');

        $user = $DB->get_record('user', array('id' => $event->relateduserid));
        $module = $DB->get_record('modules', array('name' => 'aruphonestybox'));
        $cms = $DB->get_records('course_modules', array('course' => $event->courseid, 'module' => $module->id));
        if (empty($cms)) {
            return; // Silently ignore all courses without an aruphonestybox module.
        }
        $cm = array_shift($cms);// Take first match, should only be one.




        // Check: we only want to do this for auto complete.
        $ahb = $DB->get_record('aruphonestybox', array('id' => $cm->instance));
        if (false === $ahb) {
            // If we don't match an aruphonestybox instance, then do nothing here.
            return;
        }
        // if manual, update completion date based on user's input
        if(!empty($ahb->manualindicate)) {
            $params = array('aruphonestyboxid' => $cm->instance, 'userid' => $user->id);
            $ahbuser = $DB->get_record('aruphonestybox_users', $params, '*', IGNORE_MULTIPLE);
            // Update Moodle course completion date base on users input in AHB view page.
            // Record should exist as we're observing course completion.
            if ($ahbuser->completiondate) {
                $ccompletion = new \completion_completion(array('course' => $event->courseid, 'userid' => $user->id));
                $ccompletion->timecompleted = $ahbuser->completiondate;
                $ccompletion->update();
            }
            return;
        }

        $params = array(
            'context' => \context_module::instance($cm->id),
            'courseid' => $event->courseid,
            'objectid' => $ahb->id,
            'relateduserid' => $event->relateduserid,
            'other' => array(
                'automatic' => true,
            )
        );
        $logevent = \mod_aruphonestybox\event\cpd_request_sent::create($params);
        $logevent->trigger();

        $return = aruphonestybox_process_result(aruphonestybox_sendtotaps($cm->instance, $user));
        if (empty($return->success)) {
            error_log($return->error);
        } else {
            // Insert/update user record.
            $params = array('aruphonestyboxid' => $cm->instance, 'userid' => $user->id);

            $DB->delete_records('aruphonestybox_users', $params);
            $params['completion'] = 1;
            $params['taps'] = 1;
            $DB->insert_record('aruphonestybox_users', $params);

            $completion = new \completion_info(get_course($event->courseid));
            $completion->update_state($cm, COMPLETION_COMPLETE, $user->id);
        }
    }
}
