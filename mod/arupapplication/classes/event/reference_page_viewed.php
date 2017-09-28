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
 * The mod_arupapplication reference page viewed event.
 *
 * @package    mod_arupapplication
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupapplication\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_arupapplication reference page viewed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string type: the type of user viewing.
 * }
 *
 * @package    mod_arupapplication
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reference_page_viewed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'arupapplication';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventreferencepageviewed', 'mod_arupapplication');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "APPLICATION REFERENCE PAGE VIEWED | {$this->other['type']} | Application: {$this->objectid}.";
    }

    public static function get_objectid_mapping() {
        return array('db' => 'arupapplication', 'restore' => 'arupapplication');
    }

    public static function get_other_mapping() {
        // Nothing to map.
        return false;
    }
}
