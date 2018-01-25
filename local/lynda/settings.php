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

    $name = 'local_lynda/apiurl';
    $title = get_string('setting:apiurl','local_lynda');
    $settings->add(new admin_setting_configtext($name, $title, '', 'https://api-1.lynda.com'));

    $regions = $DB->get_records_menu('local_regions_reg', ['userselectable' => true]);
    $settings->add(
            new admin_setting_configmultiselect(
                    'local_lynda/enabledregions',
                    get_string('enabledregions', 'local_lynda'),
                    get_string('enabledregions', 'local_lynda'),
                    array(),
                    $regions
            )
    );

    foreach ($regions as $id => $name) {
        $settings->add(new admin_setting_heading('heading_' . $id, $name, ''));

        $name = 'local_lynda/appkey_' . $id;
        $title = get_string('setting:appkey','local_lynda');
        $settings->add(new admin_setting_configtext($name, $title, '', ''));

        $name = 'local_lynda/secretkey_' . $id;
        $title = get_string('setting:secretkey','local_lynda');
        $settings->add(new admin_setting_configtext($name, $title, '', ''));

        $name = 'local_lynda/ltikey_' . $id;
        $title = get_string('setting:ltikey','local_lynda');
        $settings->add(new admin_setting_configtext($name, $title, '', ''));

        $name = 'local_lynda/ltisecret_' . $id;
        $title = get_string('setting:ltisecret','local_lynda');
        $settings->add(new admin_setting_configtext($name, $title, '', ''));
    }
    
    $ADMIN->add('local_lynda', $settings);

    $ADMIN->add('local_lynda', new admin_externalpage('managecourses', get_string('managecourses', 'local_lynda'),
            new moodle_url('/local/lynda/manage.php'), 'local/lynda:manage'));
}