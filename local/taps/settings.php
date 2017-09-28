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
 * The local_taps settings configuration.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('local_taps', get_string('pluginname', 'local_taps')));

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_taps_settings', get_string('configuration', 'local_taps'));
    $ADMIN->add('local_taps', $settings);

    $settings->add(new admin_setting_heading('taps_add_course_heading', get_string('taps_add_course_heading', 'local_taps'), ''));

    $enrolmentroles = array('' => get_string('choosedots')) + get_default_enrol_roles(context_course::instance(SITEID));
    $settings->add(new admin_setting_configselect('local_taps/taps_enrolment_role', get_string('enrolmentrole', 'local_taps'),
        '', null, $enrolmentroles));
}

$localtapsbaseurl = $CFG->wwwroot . '/local/taps/';
$ADMIN->add('local_taps',
        new admin_externalpage('local_taps_addcourse', get_string('addcourse', 'local_taps'), $localtapsbaseurl . 'addcourse.php', 'local/taps:addcourse'));
$ADMIN->add('local_taps',
        new admin_externalpage('local_taps_listcourses', get_string('listcourses', 'local_taps'), $localtapsbaseurl . 'listcourses.php', 'local/taps:listcourses'));

if (get_capability_info('mod/tapsenrol:internalworkflow_edit')) { // Only need to check one (new one) exists.
    $caparray = array(
        'mod/tapsenrol:internalworkflow_edit',
        'mod/tapsenrol:internalworkflow',
        'mod/tapsenrol:internalworkflow_lock'
    );
    if (has_any_capability($caparray, context_system::instance())) {
        $ADMIN->add(
                'local_taps',
                new admin_externalpage(
                        'mod_tapsenrol_iw_configure',
                        get_string('internalworkflow_configure', 'tapsenrol'),
                        $CFG->wwwroot . '/mod/tapsenrol/admin/internalworkflow.php',
                        $caparray
                        )
                );
    }
}