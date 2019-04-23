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
 * The local_costcentre costcentre added event.
 *
 * @package    local_costcentre
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_costcentre costcentre added event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string costcentre: the costcenre (code) added.
 * }
 *
 * @package    local_costcentre
 * @since      Moodle 3.3
 * @copyright  2018 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class costcentres_added extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcostcentresadded', 'local_costcentre');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "NEW COSTCENTRES ADDED.";
    }
}
