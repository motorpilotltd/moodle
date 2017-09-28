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
 * Settings file.
 *
 * @package     local_admin
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;

$ADMIN->add('root', new admin_category('local_admin', get_string('pluginname', 'local_admin')));

$systemcontext = context_system::instance();
if ($hassiteconfig
        || has_capability('local/admin:testemails', $systemcontext)) {

    $ADMIN->add(
            'local_admin',
            new admin_externalpage(
                    'local_admin_index',
                    get_string('adminfunctions', 'local_admin'),
                    $CFG->wwwroot . '/local/admin/index.php',
                    'moodle/site:config'
                    )
            );

    $ADMIN->add(
            'local_admin',
            new admin_externalpage(
                    'local_admin_userreport',
                    get_string('userreport', 'local_admin'),
                    $CFG->wwwroot . '/local/admin/user_report.php',
                    'moodle/site:config'
                    )
            );

    $ADMIN->add(
            'local_admin',
            new admin_externalpage(
                    'local_admin_testemails',
                    get_string('testemails', 'local_admin'),
                    $CFG->wwwroot . '/local/admin/test_emails.php',
                    'local/admin:testemails'
                    )
            );

    $ADMIN->add(
            'local_admin',
            new admin_externalpage(
                    'local_admin_enrolmentcheck',
                    get_string('enrolmentcheck', 'local_admin'),
                    $CFG->wwwroot . '/local/admin/enrolment_check.php',
                    'local/admin:enrolmentcheck'
                    )
            );
}