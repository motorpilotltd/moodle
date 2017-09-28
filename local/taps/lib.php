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
 * The local_taps library.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends settings navigation.
 *
 * @param settings_navigation $settingsnav
 * @param context $context
 * @return void
 */
function local_taps_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    if ($context->contextlevel == CONTEXT_COURSE && $context->instanceid != SITEID) {
        $hidefor = array(
            'arupenrol',
            'tapsenrol',
        );
        $hidelinks = false;
        $modinfo = get_fast_modinfo($context->instanceid);
        foreach ($hidefor as $modname) {
            if (isset($modinfo->instances[$modname])) {
                $hidelinks = true;
                break;
            }
        }
        if ($hidelinks) {
            // Remove 'enrolself' link if present.
            $enrolself = $settingsnav->find('enrolself', navigation_node::TYPE_SETTING);
            if ($enrolself) {
                $enrolself->remove();
            }
            // Remove 'unenrolself' link if present.
            $unenrolself = $settingsnav->find('unenrolself', navigation_node::TYPE_SETTING);
            if ($unenrolself) {
                $unenrolself->remove();
            }
        }
    }
}