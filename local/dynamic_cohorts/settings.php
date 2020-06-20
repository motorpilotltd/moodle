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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_dynamic_cohorts_settings', get_string('configuration', 'local_dynamic_cohorts'));
    $ADMIN->add('localplugins', $settings);

    // TODO: Add max actions setting (need lang string for heading).
    // $settings->add(new admin_setting_heading('general_heading', get_string('configuration:headings:general', 'local_dynamic_cohorts'), ''));

    $settings->add(new admin_setting_heading('aad_heading', get_string('configuration:headings:aad', 'local_dynamic_cohorts'), ''));

    $settings->add(
        new admin_setting_configtext(
            'local_dynamic_cohorts/aad_tenant_id',
            get_string('aad_tenant_id', 'local_dynamic_cohorts'),
            get_string('aad_tenant_id_desc', 'local_dynamic_cohorts'),
            '',
            PARAM_ALPHANUMEXT,
            64
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_dynamic_cohorts/aad_client_id',
            get_string('aad_client_id', 'local_dynamic_cohorts'),
            get_string('aad_client_id_desc', 'local_dynamic_cohorts'),
            '',
            PARAM_ALPHANUMEXT,
            64
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_dynamic_cohorts/aad_client_secret',
            get_string('aad_client_secret', 'local_dynamic_cohorts'),
            get_string('aad_client_secret_desc', 'local_dynamic_cohorts'),
            '',
            PARAM_RAW,
            64
        )
    );
}
