<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/form/filter_form.php');

use block_certification_report\certification_report;

$systemcontext = context_system::instance();
require_login();

// Required CSS and JS.
$PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/certification_report.css'));
$PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/select2.min.css'));
$PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/select2-bootstrap.min.css'));
$PAGE->requires->strings_for_js(
    ['reasonrequired', 'notrequired'], 'block_certification_report'
);
$PAGE->requires->js_call_amd('block_certification_report/enhance', 'initialise');

$stringmanager = get_string_manager();
$strings = $stringmanager->load_component_strings('block_certification_report', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'block_certification_report');

$actualurl = new moodle_url('/blocks/certification_report/report.php');

$PAGE->set_url($actualurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('header', 'block_certification_report'));
$renderer = $PAGE->get_renderer('block_certification_report');

/**
 * Verify capabilities
 */
if(!has_capability('block/certification_report:view', context_system::instance())){
    echo get_string('nopermissions', 'block_certification_report');
    exit;
}

// Get filter options (as needed for active filter display, even if filter not visible).
$filters = new stdClass();
$filteroptions = certification_report::get_filter_options();
$useurlparams = true;
if (has_capability('block/certification_report:filter', context_system::instance())) {
    $reporturl = new moodle_url('/blocks/certification_report/report.php');
    $form = new certification_report_filter_form(
        $reporturl,
        $filteroptions,
        'post',
        '',
        ['id' => 'certification_report_form']
    );
    
    // Has data been submitted/form been cancelled?
    if ($form->is_cancelled()) {
        $useurlparams = false;
    } else {
        $data = $form->get_data();
        if ($data) {
            $filters = certification_report::get_filter_data($filteroptions, $data);
            $useurlparams = false;
        }
    }
}

if ($useurlparams) {
    // No form data, try URL.
    $filters = certification_report::get_filter_data($filteroptions);

    if (!empty($form)) {
        $form->set_data($filters);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('header', 'block_certification_report'));

if (!empty($form)) {
    $form->display();
}

// Get report data.
$certificationsreport = certification_report::get_data($filters);

$currenturl = certification_report::get_base_url($filters, $certificationsreport['view'], 'report');

echo $renderer->show_active_filters($filters, $filteroptions, true, $currenturl);

if (count($certificationsreport['data']) == 0) {
    echo html_writer::div(get_string('nodatafound', 'block_certification_report'));
} else {
    echo $renderer->show_table($filteroptions['certificationsdata'], $certificationsreport['data'], $certificationsreport['view'], $currenturl);
}

echo $renderer->render_from_template('block_certification_report/modal', ['imgsrc' => $OUTPUT->pix_url('loader', 'block_certification_report')]);

echo $OUTPUT->footer();