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
 * Arup SCORM monitor block settings
 *
 * @package    block_arup_scormmonitor
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // On/Off.
    $settings->add(new admin_setting_configcheckbox('block_arup_scormmonitor_active', get_string('settings:active', 'block_arup_scormmonitor'),
            get_string('settings:active:desc', 'block_arup_scormmonitor'), 0));

    // Active SCO limit.
    $settings->add(new admin_setting_configtext('block_arup_scormmonitor_limit', get_string('settings:limit', 'block_arup_scormmonitor'),
            get_string('settings:limit:desc', 'block_arup_scormmonitor'), 100, PARAM_INT));

    // Warn percentage.
    $settings->add(new admin_setting_configtext('block_arup_scormmonitor_warn', get_string('settings:warn', 'block_arup_scormmonitor'),
            get_string('settings:warn:desc', 'block_arup_scormmonitor'), 75, PARAM_INT));

    // Active period (timeout).
    $settings->add(new admin_setting_configtext('block_arup_scormmonitor_timeout', get_string('settings:timeout', 'block_arup_scormmonitor'),
            get_string('settings:timeout:desc', 'block_arup_scormmonitor'), 60, PARAM_INT));
}
