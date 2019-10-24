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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();

if ($hassiteconfig || has_capability('local/linkedinlearning:manage', $context)) {
    require_once($CFG->dirroot.'/cohort/lib.php');

    $ADMIN->add('root', new admin_category('local_linkedinlearning', new lang_string('pluginname', 'local_linkedinlearning')));

    $settings = new admin_settingpage('local_linkedinlearning_settings', get_string('settings', 'local_linkedinlearning'));

    $name = 'local_linkedinlearning/client_id';
    $title = get_string('setting:client_id', 'local_linkedinlearning');
    $settings->add(new admin_setting_configtext($name, $title, '', ''));

    $name = 'local_linkedinlearning/client_secret';
    $title = get_string('setting:client_secret', 'local_linkedinlearning');
    $settings->add(new admin_setting_configtext($name, $title, '', ''));

    $name = 'local_linkedinlearning/category_idnumber';
    $title = get_string('setting:category_id', 'local_linkedinlearning');
    $settings->add(new admin_setting_configtext($name, $title, '', ''));

    $name = 'local_linkedinlearning/cohorts';
    $title = get_string('setting:cohorts', 'local_linkedinlearning');
    $systemcohorts = cohort_get_cohorts($context->id, 0, 0);
    $cohorts = [];
    foreach ($systemcohorts['cohorts'] as $cohort) {
        $cohorts[$cohort->id] = $cohort->name;
    }
    $setting = new admin_setting_configmultiselect($name, $title, '', [], $cohorts);
    $setting->set_updatedcallback(
        create_function('',
            '\local_linkedinlearning\lib::cohorts_updated();'
        )
    );
    $settings->add($setting);

    $ADMIN->add('local_linkedinlearning', new admin_externalpage('local_linkedinlearning/managecourses', get_string('managecourses', 'local_linkedinlearning'),
            new moodle_url('/local/linkedinlearning/manage.php'), 'local/linkedinlearning:manage'));

    $ADMIN->add('local_linkedinlearning', $settings);
}