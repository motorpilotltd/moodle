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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('mod_arupapplication_heading', get_string('generalconfig', 'mod_arupapplication'),
                       get_string('globalconfiguration_hint', 'mod_arupapplication')));

    $name = new lang_string('gradeoptions', 'mod_arupapplication');
    $description = new lang_string('gradeoptions_help', 'mod_arupapplication');
    $default = get_string('gradeoptionsdefault', 'mod_arupapplication');
    $settings->add(new admin_setting_configtextarea('arupapplication/gradeoptions',
                                                    $name,
                                                    $description,
                                                    $default));

    $name = new lang_string('officelocationoptions', 'mod_arupapplication');
    $description = new lang_string('officelocationoptions_help', 'mod_arupapplication');
    $default = get_string('officelocationoptionsdefault', 'mod_arupapplication');
    $settings->add(new admin_setting_configtextarea('arupapplication/officelocationoptions',
                                                    $name,
                                                    $description,
                                                    $default));
}