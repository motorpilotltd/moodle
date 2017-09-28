<?php
// This file is part of the arup theme for Moodle
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
 * Arup Theme Ajax
 *
 * @package     theme_arup
 * @copyright   2016 Arup
 * @author      Bas Brands
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');

$result = new stdClass();
$result->success = false;
$result->message = 'An error occurred';
$result->data = '';

if (!confirm_sesskey()) {
    header('Content-Type: application/json');
    echo json_encode($result);
    echo die();
}

try {
    $value = optional_param('value', '', PARAM_TEXT);
    $action = optional_param('action', '', PARAM_TEXT);

    if (isloggedin() && !isguestuser()) {
        if ($action == 'settimezone') {
            if ($userrecord =$DB->get_record('user', array('id' => $USER->id))) {
                $timezone = core_date::normalise_timezone($value);
                $userrecord->timezone = $timezone;
                $USER->timezone = $timezone;
                $usertimezone = core_date::get_user_timezone_object();
                if ($DB->update_record('user', $userrecord)) {
                    $result->success = true;
                    $result->message = $timezone;

                    $time = new DateTime();
                    $time->setTimezone($usertimezone);
                    $result->data = $time->format('G:i (T)');
                }
            }
        }
    }
} catch (Exception $e) {
    $result->message = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($result);