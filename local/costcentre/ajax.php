<?php
// This file is part of the Arup cost centre local plugin
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
 * @package     local_costcentre
 * @copyright   2016 Motorpilot Ltd
 * @author      Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login();

$searchterm = optional_param('q', '', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT);

$usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
$searchconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' '", 'email', "' '", 'idnumber');
$searchlike = $DB->sql_like($searchconcat, ":searchterm", false);
$params = array('searchterm'=> "%$searchterm%");
$where = "auth = 'saml' AND deleted = 0 AND suspended = 0 AND confirmed = 1 AND $searchlike";

$totalcount = $DB->count_records_select('user', $where, $params);
$userlist = $DB->get_records_select_menu('user', $where, $params, 'lastname ASC', "id, $usertextconcat", ($page - 1) * 25, $page * 25);

$json = array('totalcount' => $totalcount, 'items' => array());
foreach ($userlist as $uid => $usertext) {
    $json['items'][] = array('text' => $usertext, 'id' => $uid);
}
echo json_encode($json);
exit;