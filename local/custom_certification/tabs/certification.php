<?php
require_once('form/certification.php');
$currenturl = qualified_me();

$detailsform = new local_custom_certification\form\certification_certification_form($currenturl, [
    'certif' => $certif
]);

if ($data = $detailsform->get_data()) {
    $certif->set_recertificationtimeperiod($data->recertificationdatetype, $data->activeperiodtime, $data->activeperiodtimeunit, $data->windowperiodtime, $data->windowperiodtimeunit);

    // update existing recertification user's record
    \local_custom_certification\completion::update_records_completion_status($certif);

    \local_custom_certification\notification::add(get_string('certificationsaved', 'local_custom_certification'), \local_custom_certification\notification::TYPE_SUCCESS);
    redirect(new moodle_url('/local/custom_certification/edit.php', ['action' => 'certification', 'id' => $certif->id]));
}