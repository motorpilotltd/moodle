<?php
// This file is part of the Arup Reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


if ($hassiteconfig) {
    $settings = new admin_settingpage('local_reports_settings', get_string('settings', 'local_reports'));
    
    $ADMIN->add('localplugins', $settings);
    
    $name = 'local_reports/csv_export_limit';
    $title = get_string('csv_export_limit', 'local_reports');
    $description = get_string('csv_export_limit_desc', 'local_reports');
    $setting = new admin_setting_configtext($name, $title, $description, 20000);
    $settings->add($setting);

    $name = 'local_reports/csvseparator';
    $default = ',';
    $title = get_string('csvdelimitor', 'local_reports');
    $description = get_string('csvdelimitor_desc', 'local_reports');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
}

if (isset($ADMIN)) {
    $ADMIN->add('root', new admin_category('local_reports', get_string('pluginname', 'local_reports')));

    $url = new moodle_url('/local/reports/index.php');
    $ADMIN->add(
        'local_reports',
        new admin_externalpage(
            'local_reports_index',
            get_string('learninghistory', 'local_reports'),
            $url,
            'local/reports:view'
            )
        );
    $url = new moodle_url('/local/reports/index.php', array('page' => 'elearningstatus'));
    $ADMIN->add(
        'local_reports',
        new admin_externalpage(
            'local_reports_elearningstatus',
            get_string('elearningstatus', 'local_reports'),
            $url,
            'local/reports:view'
            )
        );
}

