<?php
require_once('form/details.php');

$currenturl = qualified_me();


$detailsform = new local_custom_certification\form\certification_details_form($currenturl, [
    'editoroptions' => $TEXTAREA_OPTIONS,
    'overviewfilesoptions' => $overviewfilesoptions,
    'params' => $certif,
    'action' => $action,
    'categories' => \local_custom_certification\certification::get_categories()
]);

$entry = new \stdClass();

$draftitemid = file_get_submitted_draft_itemid('overviewfiles_filemanager');

file_prepare_draft_area($draftitemid, context_system::instance()->id, 'local_custom_certification', 'attachment', $certif->id,
    $overviewfilesoptions);

$entry->overviewfiles_filemanager = $draftitemid;
$detailsform->set_data($entry);

if ($detailsform->is_cancelled()){
    redirect(new moodle_url('/local/custom_certification/index.php'));
}elseif ($data = $detailsform->get_data()) {
    if (!isset($data->visible)) {
        $data->visible = 0;
    }
    $message = get_string('certificationsaved', 'local_custom_certification');
    if($certif->id == null){
        $message = get_string('certificationcreated', 'local_custom_certification');
    }
    $certif->set_details($data->fullname, $data->shortname, $data->category, $data->summary['text'], $data->endnote['text'], $data->idnumber, $data->visible, $data->linkedtapscourseid, $data->uservisible, $data->reportvisible);
    file_save_draft_area_files($data->overviewfiles_filemanager,  context_system::instance()->id, 'local_custom_certification', 'attachment', $certif->id, $overviewfilesoptions);
    \local_custom_certification\notification::add($message, \local_custom_certification\notification::TYPE_SUCCESS);
    redirect(new moodle_url('/local/custom_certification/edit.php', ['action' => 'details', 'id' => $certif->id]));
}

