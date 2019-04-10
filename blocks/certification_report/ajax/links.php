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

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

use block_certification_report\certification_report;

require_login();

header('Content-Type: application/json');

$result = new stdClass();
$result->success = false;
$result->message = '';
$result->data = array();

try {
    if (!certification_report::is_admin()) {
        throw new Exception('No permissions.');
    }

    if (empty($SESSION->block_certif_report)) {
        $SESSION->block_certif_report = new stdClass();
    }

    $id = required_param('id', PARAM_INT);
    $action = optional_param('action', '', PARAM_ALPHA);

    switch ($action) {
        case 'delete':

            $SESSION->block_certif_report->alert = new stdClass();

            if ($DB->delete_records('certif_links', ['id' => $id])) {
                $SESSION->block_certif_report->alert->message = get_string('deletelink:success', 'block_certification_report');
                $SESSION->block_certif_report->alert->type = 'alert-success';
                $result->success = true;
            } else {
                $SESSION->block_certif_report->alert->message = get_string('deletelink:error', 'block_certification_report');
                $SESSION->block_certif_report->alert->type = 'alert-error';
            }

            $result->message = $SESSION->block_certif_report->alert->message;
            break;
    }

} catch (Exception $e) {
    $result->message = $e->getMessage();
}

echo json_encode($result);
exit;