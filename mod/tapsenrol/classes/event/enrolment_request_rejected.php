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
 * The mod_tapsenrol enrolment request rejected event.
 *
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tapsenrol\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapsenrol enrolment request rejected event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int enrolmentid: the TAPS enrolment id.
 *      - int staffid: the user's staff id.
 *      - int classid: the related TAPS class id.
 *      - status: the new status.
 *      - email: the code for email sent.
 * }
 *
 * @package    mod_tapsenrol
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolment_request_rejected extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'tapsenrol_iw_tracking';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventenrolmentrequestrejected', 'mod_tapsenrol');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "ENROLMENT REQUEST REJECTED | Status: {$this->other['status']} | Email: {$this->other['email']} | "
            . "Enrolment ID: {$this->other['enrolmentid']} | Staff ID: {$this->other['staffid']} | "
            . "Class ID: {$this->other['classid']}";
    }

    /**
     * Returns other mapping.
     *
     * @return bool
     */
    public static function get_other_mapping() {
        // Nothing to map.
        return false;
    }
}
