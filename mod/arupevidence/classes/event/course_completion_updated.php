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
 * The mod_arupevidence course completion updated event.
 *
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupevidence\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_arupevidence course completion updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - array old: Old timestamps.
 *      - array new: New timestamps.
 * }
 *
 * @package    mod_arupevidence
 * @since      Moodle 3.0
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion_updated extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'arupevidence';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcoursecompletionupdated', 'mod_arupevidence');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "COURSE COMPLETION UPDATED | "
            . "User: {$this->relateduserid} | "
            . "Course: {$this->courseid} | "
            . "Course Module: {$this->contextinstanceid}";
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
