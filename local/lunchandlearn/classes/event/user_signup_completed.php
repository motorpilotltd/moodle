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
 * The local_lunchandlearn user signup completed event.
 *
 * @package    local_lunchandlearn
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lunchandlearn\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_lunchandlearn user signup completed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 * }
 *
 * @package    local_lunchandlearn
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_signup_completed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        global $PAGE;
        $this->context = !empty($PAGE->context) ? $PAGE->context : \context_system::instance();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_lunchandlearn';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventusersignupcompleted', 'local_lunchandlearn');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        if (!empty($this->data['relateduserid'])) {
            $user = "{$this->data['relateduserid']} (By: {$this->data['userid']})";
        } else {
            $user = $this->data['userid'];
        }
        return "USER SIGNUP | Session ID: {$this->data['objectid']} | User: {$user}";
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
