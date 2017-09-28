<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $regionsinstalled = get_config('local_regions', 'version');
    $coursemetadatainstalled = get_config('local_coursemetadata', 'version');
    $tapsinstalled = get_config('local_taps', 'version');

    $coursemetadataoptions = array();
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

    $settings = new admin_settingpage('local_search_settings', get_string('configuration', 'local_search'));
    $ADMIN->add('localplugins', $settings);

    $possiblepositions = count($coursemetadataoptions) + (bool) $regionsinstalled + (bool) $tapsinstalled;

    if ($tapsinstalled) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_search/duration_info',
                get_string('search_show_duration_info', 'local_search'),
                get_string('search_show_duration_info_desc', 'local_search'),
                0, 1, 0
            )
        );
        $settings->add(
            new admin_setting_configselect(
                'local_search/duration_position',
                get_string('search_show_duration_position', 'local_search'),
                get_string('search_show_duration_position_desc', 'local_search'),
                1,
                array_combine(range(1, $possiblepositions), range(1, $possiblepositions))
            )
        );
    }

    if ($regionsinstalled) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_search/regions_info',
                get_string('search_show_regions_info', 'local_search'),
                get_string('search_show_regions_info_desc', 'local_search'),
                0, 1, 0
            )
        );
        $settings->add(
            new admin_setting_configselect(
                'local_search/regions_position',
                get_string('search_show_regions_position', 'local_search'),
                get_string('search_show_regions_position_desc', 'local_search'),
                (bool) $regionsinstalled + (bool) $tapsinstalled,
                array_combine(range(1, $possiblepositions), range(1, $possiblepositions))
            )
        );
    }

    if ($coursemetadatainstalled) {
        if (empty($coursemetadataoptions)) {
            $coursemetadataoptions[''] = 'No options available';
        }
        $settings->add(
            new admin_setting_configmultiselect(
                'local_search/coursemetadata_info',
                get_string('search_show_coursemetadata_info', 'local_search'),
                get_string('search_show_coursemetadata_info_desc', 'local_search'),
                array(),
                $coursemetadataoptions
            )
        );
    }
}
