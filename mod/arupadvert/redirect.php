<?php

require_once(dirname(__FILE__) . '/../../config.php');

$shortname = optional_param('shortname', null, PARAM_TEXT);

if (empty($shortname)) {
    redirect(new moodle_url('/'));
}

$course = $DB->get_record('course', ['shortname' => $shortname]);

if (empty($course)) {
    redirect(new moodle_url('/'));
}

$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_course($course);
$PAGE->set_url('/course/view.php', ['id' => $course->id]);

$modinfo = get_fast_modinfo($course->id);
$advertcms = $modinfo->get_instances_of('arupadvert');

if (empty($advertcms)) {
    return '';
}

$advertcm = reset($advertcms);
$advert = $DB->get_record('arupadvert', array('id' => $advertcm->instance));

$info = \mod_arupadvert\arupadvertdatatype::factory($advert->datatype, $advert);
$info->get_advert_block();

$title = format_string($info->name ? $info->name : $advert->name);
$ogdata = [
        'title'       => $title,
        'type'        => 'website',
        'url'         => new moodle_url('/course/view.php', ['id' => $course->id]),
        'image'       => $info->imgurl . "?og=1",
        'description' => format_text($course->summary, $course->summaryformat),
        'site_name'   => format_string($SITE->fullname),
];

foreach ($ogdata as $ogkey => $ogvalue) {
    $CFG->additionalhtmlhead .= '<meta property="og:' . $ogkey . '" content="' . $ogvalue . '"/>' . "\n";
}

$PAGE->set_title($ogdata['title']);

$url = new moodle_url('/course/view.php', ['id' => $course->id]);
$url = $url->out(false);
$url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);
$url = str_replace('"', '%22', $url);
$encodedurl = preg_replace("/\&(?![a-zA-Z0-9#]{1,8};)/", "&amp;", $url);
$encodedurl = preg_replace('/^.*href="([^"]*)".*$/', "\\1", clean_text('<a href="' . $encodedurl . '" />', FORMAT_HTML));

echo $OUTPUT->redirect_message($encodedurl, get_string('redirect_message', 'arupadvert', $title), 1,
        false);