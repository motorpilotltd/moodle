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

defined('MOODLE_INTERNAL') || die;

$folder = new admin_category('tapsenrolfolder', new lang_string('pluginname', 'tapsenrol'));
$ADMIN->add('modsettings', $folder);

$settings->visiblename = new lang_string('settings', 'tapsenrol');
$ADMIN->add('tapsenrolfolder', $settings);

$ADMIN->add(
        'tapsenrolfolder',
        new admin_externalpage(
                'tapsenrol/iw_configure',
                get_string('internalworkflow_configure', 'tapsenrol'),
                $CFG->wwwroot . '/mod/tapsenrol/admin/internalworkflow.php',
                ['mod/tapsenrol:internalworkflow_edit',
                        'mod/tapsenrol:internalworkflow',
                        'mod/tapsenrol:internalworkflow_lock']
        )
);

if ($ADMIN->fulltree) {
    $settings->add(
            new admin_setting_configcheckbox(
                    'tapsenrol/forceemailsending',
                    get_string('settings:forceemailsending', 'tapsenrol'),
                    get_string('settings:forceemailsending_desc', 'tapsenrol'),
                    '0',
                    '1',
                    '0'
            )
    );
}

// Prevent Moodle from adding settings block in standard location.
$settings = null;