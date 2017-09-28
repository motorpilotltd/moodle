<?php

define('BLOCK_CERTIFICATION_REPORT_EXPORT', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use block_certification_report\certification_report;
use local_costcentre\costcentre;

$filters = new stdClass();
$certificationfilter = ['visible' => true, 'reportvisible' => true];

/**
 * Verify capabilities
 */
if(!has_capability('block/certification_report:view', context_system::instance())){
    echo get_string('nopermissions', 'block_certification_report');
    exit;
}

if (!has_capability('block/certification_report:view_all_regions', context_system::instance())){
    $userregion = $DB->get_field('local_regions_use', 'geotapsregionid', ['userid' => $USER->id]);
    if (!$userregion) {
        echo get_string('noregion', 'block_certification_report');
        exit;

    }
    $filters->regions = [$userregion => $userregion];
}

if (has_capability('block/certification_report:view_all_costcentres', context_system::instance())){
    // Empty filter (may get populated later).
    $filters->costcentres = [];
    // All cost centres.
    $costcentres = $DB->get_records_select_menu('user', "icq != ''", [], 'icq ASC', 'DISTINCT icq as id, icq as value');
} else {
    // Pre-apply a filter if cannot view all cost centres.
    $filters->costcentres = array_keys(costcentre::get_user_cost_centres($USER->id, [
        costcentre::GROUP_LEADER,
        costcentre::HR_LEADER,
        costcentre::HR_ADMIN,
        costcentre::LEARNING_REPORTER,
    ]));
    $costcentres = array_combine($filters->costcentres,  $filters->costcentres);
}
// Send the correct headers.
send_headers('text/csv; charset=utf-8;', false);
@header('Content-Disposition: attachment; filename="certification_report.csv"');

$filters->fullname = optional_param('fullname', '', PARAM_TEXT);

$filters->cohorts = optional_param_array('cohorts', [], PARAM_INT);
$filters->certifications = optional_param_array('certifications', [], PARAM_INT);
$filters->categories = optional_param_array('categories', [], PARAM_INT);

if (!isset($filters->regions)) {
    $filters->regions = optional_param_array('regions', [], PARAM_INT);
}

$filtercostcentres = array_intersect(optional_param_array('costcentres', [], PARAM_ALPHANUMEXT), $costcentres);
if (!empty($filtercostcentres)) {
    $filters->costcentres = $filtercostcentres;
}

if(isset($filters->certifications)){
    $certificationfilter += ['id' => $filters->certifications];
}
if(isset($filters->categories)){
    $certificationfilter += ['category' => $filters->categories];
}

$certificationsreport = certification_report::get_data($filters);

$certifications = \local_custom_certification\certification::get_all($certificationfilter, "ORDER by c.fullname");
echo certification_report::export_to_csv($certifications, $certificationsreport['data'], $certificationsreport['view']);

