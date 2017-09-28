<?php
/**
 * Settings File
 *
 * @package    local
 * @subpackage learningpath
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/coursecatlib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_learningpath_settings', get_string('settings', 'local_learningpath'));
    $ADMIN->add('localplugins', $settings);
    // Root category for learning paths
    $categories = array(0 => get_string('settings:anycategory', 'local_learningpath'))
        + coursecat::make_categories_list();
    $settings->add(
        new admin_setting_configselect(
            'local_learningpath/category',
            get_string('setting:category', 'local_learningpath'),
            get_string('setting:category_desc', 'local_learningpath', core_text::strtolower(get_string('courses'))),
            0,
            $categories
        )
    );

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

    if ($regionsinstalled) {
        $settings->add(
            new admin_setting_configcheckbox(
                'local_learningpath/regions_filter',
                get_string('setting:show_region_filter', 'local_learningpath'),
                get_string('setting:show_region_filter_desc', 'local_learningpath'),
                0, 1, 0
            )
        );
        $settings->add(
            new admin_setting_configcheckbox(
                'local_learningpath/regions_info',
                get_string('setting:show_region_info', 'local_learningpath'),
                get_string('setting:show_region_info_desc', 'local_learningpath', core_text::strtolower(get_string('course'))),
                0, 1, 0
            )
        );
        if ($coursemetadatainstalled) {
            $possiblepositions = count($coursemetadataoptions) + 1;
            $settings->add(
                new admin_setting_configselect(
                    'local_learningpath/regions_position',
                    get_string('setting:show_region_position', 'local_learningpath'),
                    get_string('setting:show_region_position_desc', 'local_learningpath'),
                    1,
                    array_combine(range(1, $possiblepositions), range(1, $possiblepositions))
                )
            );
        }
    }

    if ($coursemetadatainstalled) {
        if (empty($coursemetadataoptions)) {
            $coursemetadataoptions[''] = get_string('settings:nooptions', 'local_learningpath');
        }
        $settings->add(
            new admin_setting_configmultiselect(
                'local_learningpath/coursemetadata_info',
                get_string('setting:show_coursemetadata_info', 'local_learningpath'),
                get_string('setting:show_coursemetadata_info_desc', 'local_learningpath', core_text::strtolower(get_string('course'))),
                array(),
                $coursemetadataoptions
            )
        );
    }
}