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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_lynda', new lang_string('pluginname', 'local_lynda')));

    $settings = new admin_settingpage('local_lynda_apisettings', get_string('apisettings', 'local_lynda'));

    $name = 'local_lynda/appkey';
    $title = get_string('setting:appkey','local_lynda');
    $settings->add(new admin_setting_configtext($name, $title, '', '0E15654C2D0046A686AE07888BA4F331'));

    $name = 'local_lynda/secretkey';
    $title = get_string('setting:secretkey','local_lynda');
    $settings->add(new admin_setting_configtext($name, $title, '', '7D84911008F14BB79ECB7F6F7EF82603'));

    $name = 'local_lynda/apiurl';
    $title = get_string('setting:apiurl','local_lynda');
    $settings->add(new admin_setting_configtext($name, $title, '', 'https://api-1.lynda.com'));
    
    $ADMIN->add('local_lynda', $settings);

    $ADMIN->add('local_lynda', new admin_externalpage('managecourses', get_string('managecourses', 'local_lynda'),
            new moodle_url('/local/lynda/manage.php'), 'local/lynda:manage'));
}