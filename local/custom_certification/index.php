<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('form/certification_filter.php');

$url = new moodle_url('/local/custom_certification/index.php', []);

$context = context_system::instance();

$currenturl = qualified_me();
$PAGE->set_url($url);
$PAGE->requires->css(new moodle_url('/local/custom_certification/styles/custom_certification.css'));
$PAGE->requires->jquery();
$PAGE->set_pagelayout('admin');

$PAGE->set_context($context);
$PAGE->set_title(get_string('certifications', 'local_custom_certification'));
require_capability('local/custom_certification:view', $context);

$renderer = $PAGE->get_renderer('local_custom_certification');
$copy = optional_param('copy', 0, PARAM_INT);
if($copy > 0){
    $copiedcertification = \local_custom_certification\certification::copy($copy);
    redirect(new moodle_url('/local/custom_certification/edit.php', ['action' => 'details', 'id' => $copiedcertification->id]));
}
$delete = optional_param('delete', 0, PARAM_INT);
if($delete > 0){
    $certification = new \local_custom_certification\certification($delete, false);
    $certification->delete();
    redirect(new moodle_url('/local/custom_certification/index.php'));
}


$filterform = new \local_custom_certification\form\certification_certification_filter_form($currenturl, [
    'categories' => \local_custom_certification\certification::get_categories()
]);
$filters = [];
if ($data = $filterform->get_data()) {
    $filters['fullname'] = $data->fullname;
    if(isset($data->visible)){
        $filters['visible'] = $data->visible;
    }
    if(isset($data->category)){
        $filters['category'] = $data->category;
    }
}
$certifications = \local_custom_certification\certification::get_all($filters);

echo $OUTPUT->header();


$filterform->display();
echo $renderer->display_certifications($certifications);

echo $OUTPUT->footer();