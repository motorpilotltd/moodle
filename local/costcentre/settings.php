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

defined('MOODLE_INTERNAL') || die();

if (get_config('local_costcentre', 'version')) {
    global $USER;

    $ADMIN->add('root', new admin_category('local_costcentre', get_string('pluginname', 'local_costcentre')));

    $settings = new admin_settingpage('local_costcentre_settings', get_string('settings', 'local_costcentre'));
    $ADMIN->add('local_costcentre', $settings);

    $name = 'local_costcentre/help_courseid';
    $title = get_string('setting:help_courseid','local_costcentre', \core_text::strtolower(get_string('course')));
    $description = get_string('setting:help_courseid_desc', 'local_costcentre', \core_text::strtolower(get_string('course')));
    $settings->add(new admin_setting_configtext($name, $title, $description, '', PARAM_INT));

    $fakecap = 'local/costcentre:administer';
    if (\local_costcentre\costcentre::is_user($USER->id, \local_costcentre\costcentre::BUSINESS_ADMINISTRATOR)) {
        $fakecap = 'moodle/block:view';
    }
    $adminurl = new moodle_url('/local/costcentre/index.php');
    $ADMIN->add('local_costcentre', new admin_externalpage('local_costcentre_index', get_string('menu:index', 'local_costcentre'), $adminurl, $fakecap));
}