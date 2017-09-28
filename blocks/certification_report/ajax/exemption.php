<?php

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/form/exemption.php');

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('block_certification_report');


$action = optional_param('action', null, PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$certifid = optional_param('certifid', null, PARAM_INT);
$reason = optional_param('reason', null, PARAM_TEXT);
$timeexpires = optional_param('timeexpires', null, PARAM_TEXT);


switch($action){
    case 'getexemptionform':
        
        $formurl = new moodle_url('/local/custom_certification/index.php');
        $user = $DB->get_record('user', ['id' => $userid]);

        $certification = $DB->get_record('certif', ['id' => $certifid]);
        
        $exemption = \block_certification_report\certification_report::get_exemption($userid, $certifid);

        $exemptionform = new block_certification_report\form\certification_report_exemption_form($formurl, [
            'exemption' => $exemption,
            'userid' => $userid,
            'certifid' => $certifid
        ]);

        echo $renderer->get_modal($exemptionform->render(), fullname($user).' ('.$certification->fullname.')');
        
        break;
    case 'saveexemption':
        if(has_capability('block/certification_report:set_exemption', context_system::instance())){
            \block_certification_report\certification_report::save_exemption($userid, $certifid, $reason, $timeexpires);
        }
        break;
    case 'deleteexmption':
        if(has_capability('block/certification_report:set_exemption', context_system::instance())) {
            \block_certification_report\certification_report::save_exemption($userid, $certifid, 'deleted', 0, 1);
        }
        break;
}


exit;