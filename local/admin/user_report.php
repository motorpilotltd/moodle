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
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_admin_userreport');

$title = get_string('userreport', 'local_admin');
$PAGE->set_title(get_site()->shortname . ': ' . $title);

// Render page.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Content.
echo '<p>This page will display two different reports:<br />'
. 'A table of users who cannot be added (i.e. are not even attempted).<br />'
. 'A table of log entries showing all the additions/(un)suspensions/updates that have been carried out.';

echo $OUTPUT->footer();
