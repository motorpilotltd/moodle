<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/form/links_form.php');


use block_certification_report\certification_report;
$systemcontext = context_system::instance();
require_login();

$url = new moodle_url('/blocks/certification_report/manage_links.php');


if (empty($SESSION->block_certif_report)) {
    $SESSION->block_certif_report = new stdClass();
}

/**
 * Verify capabilities to manage report links list
 */
if(!certification_report::is_admin()){
    echo get_string('nopermissions', 'block_certification_report');
    exit;
}


$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('headermanagelinks', 'block_certification_report'));
$renderer = $PAGE->get_renderer('block_certification_report');

// Required CSS and JS.
$PAGE->requires->js_call_amd('block_certification_report/enhance', 'initialise');
$PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/certification_report.css'));

$customdata = [];

$content = '';

$customdata['regions'] = certification_report::get_local_regions();

$form = new certification_report_links_form($url, $customdata, 'post', '', ['id' => 'managelink-form']);

$reportlinks = certification_report::get_report_links();
// Alert box
if (!empty($SESSION->block_certif_report->alert)) {
    $content .= $renderer->alert($SESSION->block_certif_report->alert->message, $SESSION->block_certif_report->alert->type);
    unset($SESSION->block_certif_report->alert);
}

$content .= $form->render();

// Has data been submitted/form been cancelled?
if ($form->is_cancelled()) {
    redirect($PAGE->url);
} else if ($data = $form->get_data()) {

    $reportlink = [
        'name'               => $data->linkname,
        'linkurl'            => $data->linkurl,
        'geographicregionid' => $data->geographicregion,
        'actualregionid'     => $data->actualregion,
        'timecreated'        => time()
    ];

    //alert success
    $SESSION->block_certif_report->alert = new stdClass();
    $SESSION->block_certif_report->alert->message = get_string('successaddlink', 'block_certification_report');
    $SESSION->block_certif_report->alert->type = 'alert-success';

    if (!empty($data->id)) {
        $reportlink['id'] = $data->id;
        $DB->update_record('certif_links', $reportlink);
    } else {
        $DB->insert_record('certif_links', $reportlink);
    }


    redirect($PAGE->url);
}

$content .= $renderer->print_reportlink_table(['reportlinks' => array_values($reportlinks)]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('headermanagelinks', 'block_certification_report'));

echo $content;

// Modal
echo $OUTPUT->render_from_template('block_certification_report/delete_confirm_modal', array());

echo $OUTPUT->footer();