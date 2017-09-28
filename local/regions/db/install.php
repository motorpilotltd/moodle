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
 * Installation code for local_regions.
 *
 * @package     local_regions
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install local_regions plugin.
 *
 * @param   int $oldversion The old version of the local_regions plugin
 * @return  bool
 */
function xmldb_local_regions_install() {
    global $DB;

    // Set up base regions.
    $regions = array(
        array(
            'name' => 'TAP Partnerships',
            'tapsname' => 'TAP Partnerships',
            'userselectable' => 0
        ),
        array(
            'name' => 'Corporate Services',
            'tapsname' => 'Corporate Services',
            'userselectable' => 0
        ),
        array(
            'name' => 'Americas',
            'tapsname' => 'Americas Region',
            'userselectable' => 1
        ),
        array(
            'name' => 'Australasia',
            'tapsname' => 'Australasia Region',
            'userselectable' => 1
        ),
        array(
            'name' => 'East Asia',
            'tapsname' => 'East Asia Region',
            'userselectable' => 1
        ),
        array(
            'name' => 'Europe',
            'tapsname' => 'Europe Region',
            'userselectable' => 1
        ),
        array(
            'name' => 'UKMEA',
            'tapsname' => 'UK-MEA Region',
            'userselectable' => 1
        ),
    );
    foreach ($regions as $region) {
        $DB->insert_record('local_regions_reg', (object) $region);
    }
}
