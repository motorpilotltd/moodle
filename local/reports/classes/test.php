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

define('CLI_SCRIPT', true);

require_once '../../../config.php';

$start = 1;
$limit = 20;
$params = array();

$sql = "SELECT  lte.*, staff.*
                  FROM SQLHUB.ARUP_ALL_STAFF_V as staff
             LEFT JOIN {local_taps_enrolment} as lte
             ON staff.EMPLOYEE_NUMBER = lte.staffid
                WHERE EMPLOYEE_NUMBER = 32417";


// $sql = "SELECT * from {local_taps_enrolment} where staffid = 32417";

$all = $DB->get_records_sql($sql, $params, $start * $limit, $limit);


foreach ($all as $a) {
    echo "RUN LINE";
    print_r($a);
    echo "\n";
}