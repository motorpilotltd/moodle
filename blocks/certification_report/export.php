<?php

define('BLOCK_CERTIFICATION_REPORT_EXPORT', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use block_certification_report\certification_report;

$PAGE->set_url(new moodle_url('/blocks/certification_report/export.php'));
$PAGE->set_context(context_system::instance());

/**
 * Verify capabilities
 */
if(!has_capability('block/certification_report:view', context_system::instance())){
    echo get_string('nopermissions', 'block_certification_report');
    exit;
}
// Send the correct headers.
send_headers('text/csv; charset=utf-8;', false);
@header('Content-Disposition: attachment; filename="certification_report.csv"');

$filteroptions = certification_report::get_filter_options();
$filters = certification_report::get_filter_data($filteroptions);

$certificationsreport = certification_report::get_data($filters);

echo certification_report::export_to_csv($filters, $filteroptions, $certificationsreport['data'], $certificationsreport['view']);

