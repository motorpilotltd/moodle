<?php
// This file is part of the Arup Course Management system
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
 * The local_coursemanager class updated event.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_coursemanager class updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int classid: the classid field.
 * }
 *
 * @package     local_coursemanager
 * @since       Moodle 3.0
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class class_updated extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        global $PAGE;
        $this->context = !empty($PAGE->context) ? $PAGE->context : \context_system::instance();
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_taps_class';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventclassupdated', 'local_coursemanager');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "CLASS UPDATED | CLASSID: {$this->other['classid']}";
    }

    /**
     * Returns objectid mapping.
     *
     * @return bool
     */
    public static function get_objectid_mapping() {
        return array('db' => 'local_taps_class', 'restore' => 'local_taps_class');
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
