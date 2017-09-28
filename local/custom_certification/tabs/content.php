<?php
require_once('form/content.php');
require_once($CFG->dirroot . '/cache/stores/session/lib.php');

$PAGE->requires->js(new moodle_url('/local/custom_certification/js/content.js'));
$currenturl = qualified_me();

if (!isset($SESSION->certifcontent[$certif->id]->certification) || !isset($SESSION->certifcontent[$certif->id]->recertification)) {
    $SESSION->certifcontent[$certif->id] = new stdClass();
    $SESSION->certifcontent[$certif->id]->changed = false;
    $SESSION->certifcontent[$certif->id]->certification = $certif->certificationcoursesets;
    $SESSION->certifcontent[$certif->id]->recertification = $certif->recertificationcoursesets;
}

$detailsform = new local_custom_certification\form\certification_content_form($currenturl, [
    'certif' => $certif,
    'certifications' => $SESSION->certifcontent[$certif->id]->certification,
    'recertifications' => $SESSION->certifcontent[$certif->id]->recertification
]);
