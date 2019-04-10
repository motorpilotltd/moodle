<?php
require_once('form/messages.php');

defined('MOODLE_INTERNAL') || die();

// IE11 polyfill for URL api.
$PAGE->requires->js(new moodle_url('https://cdn.polyfill.io/v2/polyfill.min.js', ['features' => 'URL']));
$PAGE->requires->js(new moodle_url('/local/custom_certification/js/messages.js'));

$currenturl = qualified_me();

$messagetypes = \local_custom_certification\message::get_types();

$messages = $certif->get_messages();
$detailsform = new local_custom_certification\form\certification_messages_form($currenturl, [
    'certif' => $certif,
    'messagetypes' => $messagetypes,
    'messages' => $messages,
    'canmanage' => $canmanage
]);
