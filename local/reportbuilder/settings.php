<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Add reportbuilder administration menu settings
 */

$ADMIN->add('reports', new admin_category('local_reportbuilder', get_string('reportbuilder','local_reportbuilder')), 'comments');

// Main report builder settings.
$rb = new admin_settingpage('rbsettings',
                            new lang_string('globalsettings','local_reportbuilder'),
                            array('local/reportbuilder:managereports'));

if ($ADMIN->fulltree) {
    $rb->add(new local_reportbuilder_admin_setting_configexportoptions());

    $rb->add(new admin_setting_configcheckbox('reportbuilder/exporttofilesystem', new lang_string('exporttofilesystem', 'local_reportbuilder'),
        new lang_string('reportbuilderexporttofilesystem_help', 'local_reportbuilder'), false));

    $rb->add(new admin_setting_configdirectory('reportbuilder/exporttofilesystempath', new lang_string('exportfilesystempath', 'local_reportbuilder'),
        new lang_string('exportfilesystempath_help', 'local_reportbuilder'), ''));

    $rb->add(new local_reportbuilder_admin_setting_configdaymonthpicker('reportbuilder/financialyear', new lang_string('financialyear', 'local_reportbuilder'),
        new lang_string('reportbuilderfinancialyear_help', 'local_reportbuilder'), array('d' => 1, 'm' => 7)));

    $rb->add(
        new admin_setting_configcheckbox(
            'local_reportbuilder/allowtotalcount',
            new lang_string('allowtotalcount', 'local_reportbuilder'),
            new lang_string('allowtotalcount_desc', 'local_reportbuilder'),
            0,
            PARAM_INT
        )
    );

    $rb->add(
        new admin_setting_configcheckbox(
            'local_reportbuilder/globalinitialdisplay',
            new lang_string('globalinitialdisplay', 'local_reportbuilder'),
            new lang_string('globalinitialdisplay_desc', 'local_reportbuilder'),
            0
        )
    );

    $rb->add(
        new admin_setting_configcheckbox(
            'enablereportcaching',
            new lang_string('enablereportcaching', 'local_reportbuilder'),
            new lang_string('enablereportcaching', 'local_reportbuilder'),
            0
        )
    );

    // Schedule type options.
    // NOTE: these must be kept in sync with constants in
    // local/reportbuilder/lib/scheduler.php
    $scheduler_options = array(
        'daily' => 1,
        'weekly' => 2,
        'monthly' => 3,
        'hourly' => 4,
        'minutely' => 5,
    );
    $options = array();
    foreach ($scheduler_options as $option => $code) {
        $options[$code] = get_string('schedule' . $option, 'local_reportbuilder');
    }
    $rb->add(
        new admin_setting_configselect(
            'local_reportbuilder/schedulerfrequency',
            new lang_string('scheduledreportfrequency', 'local_reportbuilder'),
            new lang_string('scheduledreportfrequency_desc', 'local_reportbuilder'),
            $scheduler_options['minutely'],
            $options
        )
    );
}

// Add all above settings to the report builder settings node.
$ADMIN->add('local_reportbuilder', $rb);

// Add links to report builder reports.
$ADMIN->add('local_reportbuilder', new admin_externalpage('rbmanagereports', new lang_string('manageuserreports','local_reportbuilder'),
            new moodle_url('/local/reportbuilder/index.php'), array('local/reportbuilder:managereports')));

$ADMIN->add('local_reportbuilder', new admin_externalpage('rbmanageembeddedreports', new lang_string('manageembeddedreports','local_reportbuilder'),
            new moodle_url('/local/reportbuilder/manageembeddedreports.php'), array('local/reportbuilder:manageembeddedreports')));


$ADMIN->add('local_reportbuilder', new admin_externalpage('rbcreatereport', new lang_string('createreport','local_reportbuilder'),
            new moodle_url('/local/reportbuilder/create.php'), array('local/reportbuilder:managereports')));
