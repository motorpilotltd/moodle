<?php
// This file is part of the Arup online appraisal system
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
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('root', new admin_category('local_onlineappraisal', get_string('pluginname', 'local_onlineappraisal')));

    $settings = new admin_settingpage('local_onlineappraisal_settings', get_string('settings', 'local_onlineappraisal'));
    $ADMIN->add('local_onlineappraisal', $settings);

    $name = 'local_onlineappraisal/logo';
    $title = get_string('setting:logo','local_onlineappraisal');
    $description = get_string('setting:logo_desc', 'local_onlineappraisal');
    $settings->add(new admin_setting_configstoredfile($name, $title, $description, 'logo'));

    $name = 'local_onlineappraisal/helpurl';
    $title = get_string('setting:helpurl','local_onlineappraisal');
    $description = get_string('setting:helpurl_desc', 'local_onlineappraisal');
    $settings->add(new admin_setting_configtext($name, $title, $description, ''));

    $name = 'local_onlineappraisal/quicklinks';
    $title = get_string('setting:quicklinks','local_onlineappraisal');
    $description = get_string('setting:quicklinks_desc', 'local_onlineappraisal');
    $settings->add(new admin_setting_configtextarea($name, $title, $description, ''));

    $name = 'local_onlineappraisal/activateleadershipattributes';
    $title = get_string('setting:activateleadershipattributes','local_onlineappraisal');
    $description = get_string('setting:activateleadershipattributes_desc', 'local_onlineappraisal');
    $settings->add(new admin_setting_configtext($name, $title, $description, '', PARAM_INT));

    $html = '';
    if (optional_param('rebuild_permissions', false, PARAM_BOOL)) {
        \local_onlineappraisal\permissions::rebuild_permissions();
        $html = html_writer::div('Permissions cache has been rebuilt', 'alert alert-success');
    }

    $url = new moodle_url(qualified_me());
    $url->param('rebuild_permissions', true);
    $html .= html_writer::link($url, 'Rebuild Permissions Cache', array('class' => 'btn btn-primary'));

    $adminsetting = new admin_setting_heading(
            'local_onlineappraisal_actions',
            'Actions',
            $html
            );
    $settings->add($adminsetting);

    $ADMIN->add(
            'local_onlineappraisal',
            new admin_externalpage(
                    'local_onlineappraisal_bulkupload',
                    'Bulk Upload',
                    $CFG->wwwroot . '/local/onlineappraisal/bulk_upload.php'
                    )
            );
}