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
 * Strings for component 'block_arup_scormmonitor', language 'en'
 *
 * @package    block_arup_scormmonitor
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['arup_scormmonitor:addinstance'] = 'Add a SCORM monitor block';
$string['arup_scormmonitor:canaccessscorm'] = 'Can access blocked SCORM';

$string['eventscoexited'] = 'SCO exited event';

$string['launch:hide'] = '<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> Sorry about this! Moodle is experiencing high volumes of people trying to take this e-learning. Please come back and try again a little later.</div>';
$string['launch:warn'] = '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle"></i> Moodle is experiencing high volumes of people trying to take this e-learning and performance may be impacted. You may wish to try again a little later.</div>';
$string['link:globalconfig'] = 'Global configuration';

$string['pluginname'] = 'Arup SCORM monitor';

$string['settings:active'] = 'Limit SCO launches';
$string['settings:active:desc'] = 'Should the number of SCO launches be limited?';
$string['settings:limit'] = 'Active SCO limit';
$string['settings:limit:desc'] = 'What the maximum number of active SCOs should be.';
$string['settings:timeout'] = 'Active period (Minutes)';
$string['settings:timeout:desc'] = 'Launches up until the number of minutes specified will be considered potentially active.';
$string['settings:warn'] = 'Warn at (Percentage)';
$string['settings:warn:desc'] = 'At what percentage of the limit the system will present a warning to users.';
