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

require_once("{$CFG->libdir}/coursecatlib.php");

if ($hassiteconfig) {
    $regionsinstalled = get_config('local_regions', 'version');
    $coursemetadatainstalled = get_config('local_coursemetadata', 'version');

    if ($coursemetadatainstalled) {
        $coursemetadataoptionssql = <<<EOS
SELECT
    cif.id, cif.name
FROM
    {coursemetadata_info_field} cif
JOIN
    {coursemetadata_info_category} cic
    ON cic.id = cif.categoryid
WHERE
    cif.datatype IN ('menu', 'multiselect', 'iconsingle', 'iconmulti')
ORDER BY
    cic.sortorder ASC, cif.sortorder ASC
EOS;
        $coursemetadataoptions = $DB->get_records_sql_menu($coursemetadataoptionssql);
    }

    $settings = new admin_settingpage('local_accordion_settings', get_string('configuration', 'local_accordion'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('general_heading', get_string('configuration:headings:general', 'local_accordion'), ''));

    $categoryoptions = array(0 => get_string('default', 'local_accordion')) + coursecat::make_categories_list();
    $settings->add(
        new admin_setting_configselect(
            'local_accordion/root_category',
            get_string('accordion_choose_root_category', 'local_accordion'),
            get_string('accordion_choose_root_category_desc', 'local_accordion'),
            0,
            $categoryoptions
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'local_accordion/build_area',
            get_string('accordion_choose_build_area', 'local_accordion', get_string('buildarea', 'local_accordion')),
            get_string('accordion_choose_build_area_desc', 'local_accordion', get_string('buildarea', 'local_accordion')),
            0,
            $categoryoptions
        )
    );

    if ($regionsinstalled) {
        $settings->add(new admin_setting_heading('region_heading', get_string('configuration:headings:region', 'local_accordion'), ''));

        $settings->add(
            new admin_setting_configcheckbox(
                'local_accordion/regions_filter',
                get_string('accordion_show_region_filter', 'local_accordion'),
                get_string('accordion_show_region_filter_desc', 'local_accordion'),
                0, 1, 0
            )
        );
        $settings->add(
            new admin_setting_configcheckbox(
                'local_accordion/regions_info',
                get_string('accordion_show_region_info', 'local_accordion'),
                get_string('accordion_show_region_info_desc', 'local_accordion'),
                0, 1, 0
            )
        );
        if ($coursemetadatainstalled) {
            $possiblepositions = count($coursemetadataoptions) + 1;
            $settings->add(
                new admin_setting_configselect(
                    'local_accordion/regions_position',
                    get_string('accordion_show_region_position', 'local_accordion'),
                    get_string('accordion_show_region_position_desc', 'local_accordion'),
                    1,
                    array_combine(range(1, $possiblepositions), range(1, $possiblepositions))
                )
            );
        }
    }

    if ($coursemetadatainstalled) {
        $settings->add(new admin_setting_heading('coursemetadata_heading', get_string('configuration:headings:coursemetadata', 'local_accordion'), ''));

        $settings->add(
            new admin_setting_configcheckbox(
                'local_accordion/coursemetadata_filter',
                get_string('accordion_show_coursemetadata_filter', 'local_accordion'),
                get_string('accordion_show_coursemetadata_filter_desc', 'local_accordion'),
                0, 1, 0
            )
        );
        if (empty($coursemetadataoptions)) {
            $coursemetadataoptions[''] = 'No options available';
        }
        $settings->add(
            new admin_setting_configmultiselect(
                'local_accordion/coursemetadata_info',
                get_string('accordion_show_coursemetadata_info', 'local_accordion'),
                get_string('accordion_show_coursemetadata_info_desc', 'local_accordion'),
                array(),
                $coursemetadataoptions
            )
        );
    }
}
