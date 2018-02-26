<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

$result = new stdClass();
$result->success = false;
$result->message = 'An error occurred';
$result->data = '';

try {
    require_once('../../config.php');

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/local/reports/ajax.php');

    require_login();

    require_sesskey();

    $page = optional_param('page', 'learninghistory', PARAM_TEXT);
    $context = context_system::instance();
    $result->success = false;
    if (has_capability('local/reports:export', $context)) {
        $report = new \local_reports\report($page);
        $result->data = $report->export_report();
        $result->success = 'success';
    }
} catch (Exception $e) {
    $result->message = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($result);