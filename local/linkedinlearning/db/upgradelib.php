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
 * Assignment upgrade script.
 *
 * @package   mod_assignment
 * @copyright 2013 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Inform admins about assignments that still need upgrading.
 */
function local_linkedinlearning_addmethodology() {
    global $DB;

    $methodology = $DB->get_record('coursemetadata_info_field', ['shortname' => "Methodology"]);

    if ($methodology) {
        $methodology->param1 = explode("\n", $methodology->param1);
        $methodology->param1[] = 'LinkedIn Learning';
        sort($methodology->param1);
        $methodology->param1 = implode("\n", $methodology->param1);
        $DB->update_record('coursemetadata_info_field', $methodology);
    }
}
