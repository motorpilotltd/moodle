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
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure
 *
 * @global \moodle_database $DB
 * @return bool
 */
function xmldb_arupmyproxy_uninstall() {
    global $DB;

    $arupmyproxys = $DB->get_records('arupmyproxy');
    foreach ($arupmyproxys as $arupmyproxy) {
        $coursecontext = context_course::instance($arupmyproxy->course);

        // Remove role assignment from users.
        role_unassign_all(
            array(
                'roleid' => $arupmyproxy->roleid,
                'contextid' => $coursecontext->id,
                'component' => 'mod_arupmyproxy',
                'itemid' => $arupmyproxy->id
            ),
            false,
            false
        );

        // Remove capability from role in course the arupmyproxy was in.
        // Doesn't matter if this is attempted again for another activity later, they all need to go anyway.
        unassign_capability('moodle/course:view', $arupmyproxy->roleid, $coursecontext->id);
        $coursecontext->mark_dirty();
    }

    return true;
}
