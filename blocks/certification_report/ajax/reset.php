<?php

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('block_certification_report');


$action = optional_param('action', null, PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$certifid = optional_param('certifid', null, PARAM_INT);


switch($action){
    case 'resetcertification':
        // Capability check carried out by function.
        \block_certification_report\certification_report::reset_certification($userid, $certifid);
        break;
}
exit;