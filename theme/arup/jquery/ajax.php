<?php
// This file is part of the Arup theme
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
 * @package    theme
 * @subpackage arup
 * @copyright  2016 Arup
 * @author     Bas Brands, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once('../../../config.php');

$alertid = optional_param('alertid', 0, PARAM_INT);
$sesskey = required_param('sesskey', PARAM_RAW);

if (!isloggedin()) {
    die;
}
if ($alertid) {
    set_user_preference('alert' . $alertid, 1, $USER);
}