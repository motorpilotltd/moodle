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
 * The local_onlineappraisal appraisal hrleader printed event.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_onlineappraisal appraisal hrleader printed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string type: the type of user viewing.
 *      - string tab: the tab viewed.
 * }
 *
 * @package    local_onlineappraisal
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appraisal_hrleader_printed extends \local_onlineappraisal\event\appraisal_printed {
    // Just used to log as different event.
}
