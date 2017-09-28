<?php

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('local_custom_certification');

$action = optional_param('action', null, PARAM_RAW);
$messagename = optional_param('messagename', null, PARAM_RAW);
$messagetype = optional_param('messagetype', null, PARAM_INT);
$messageid = optional_param('messageid', null, PARAM_INT);
$certifid = optional_param('certifid', null, PARAM_INT);

if (isset($_POST['messages'])) {
    $messages = $_POST['messages'];
}
switch ($action) {
    case 'displaybox':
        echo $renderer->display_message_box(0, $messagename, $messagetype);

        break;
    case 'save':
        if(isset($messages) && is_array($messages)){
            foreach ($messages as $message) {

                $message['triggertime'] > 0 ? $message['triggertime'] = strtotime($message['triggertime'] . ' days', 0) : $message['triggertime'] = 0;
                $message['recipient'] == 'true' ? $message['recipient'] = 1 : $message['recipient'] = 0;

                if ($message['subject'] != '') {
                    \local_custom_certification\certification::set_message_details($message['messageid'], $certifid, $message['messagetype'], $message['recipient'], $message['recipientemail'], $message['subject'], $message['body'], $message['triggertime']);
                }
            }
        }

        \local_custom_certification\notification::add(get_string('certificationsaved', 'local_custom_certification'), \local_custom_certification\notification::TYPE_SUCCESS);
        echo json_encode('success');
        break;
    case 'delete':
        \local_custom_certification\certification::delete_message($messageid, $certifid);
        break;
}