<?php
// This file is part of the Arup Reports system
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
 *
 * @package     local_reports
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

require_login();

$page = optional_param('page', 'learninghistory', PARAM_TEXT);
$filename = required_param('filename', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);
if (!has_capability('block/configurable_reports:managereports', $context)) {
    print_error('noaccess', 'local_reports');
}

$report = new \local_reports\report($page);
$report->download_csv($filename);
