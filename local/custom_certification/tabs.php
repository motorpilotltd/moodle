<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$toprow = [];
$activated = [];
$inactive = [];

if (!isset($currenttab)) {
    $currenttab = 'details';
}
$activated[] = $currenttab;

$toprow[] = new tabobject('details', new moodle_url('/local/custom_certification/edit.php', ['action' => 'details', 'id' => $certif->id]), get_string('details', 'local_custom_certification'));
$toprow[] = new tabobject('content', new moodle_url('/local/custom_certification/edit.php', ['action' => 'content', 'id' => $certif->id]), get_string('content', 'local_custom_certification'));
$toprow[] = new tabobject('assignments', new moodle_url('/local/custom_certification/edit.php', ['action' => 'assignments', 'id' => $certif->id]), get_string('assignments', 'local_custom_certification'));
$toprow[] = new tabobject('messages', new moodle_url('/local/custom_certification/edit.php', ['action' => 'messages', 'id' => $certif->id]), get_string('messages', 'local_custom_certification'));
$certificationtext = html_writer::span(get_string('certification', 'local_custom_certification'), ($certif->recertificationdatetype === null && $certif->has_recertification() ? 'text-warning' : ''));
$toprow[] = new tabobject('certification', new moodle_url('/local/custom_certification/edit.php', ['action' => 'certification', 'id' => $certif->id]), $certificationtext, get_string('certification', 'local_custom_certification'));

if ($certif->id == 0) {
    $inactive += ['content', 'assignments', 'messages', 'certification'];
} elseif (empty($certif->recertificationcoursesets)) {
    $inactive += ['certification'];
}

/**
 * CLear session data if content was closed without saving
 */
if ($currenttab != 'content' && isset($SESSION->certifcontent[$certif->id]->changed) && $SESSION->certifcontent[$certif->id]->changed == true) {
    unset($SESSION->certifcontent[$certifid]->certification);
    unset($SESSION->certifcontent[$certifid]->recertification);
    unset($SESSION->certifcontent[$certifid]->changed);
}

$tabs = [$toprow];
print_tabs($tabs, $currenttab, $inactive, $activated);

echo html_writer::div(html_writer::div('','loader'), 'loader-wrapper');
