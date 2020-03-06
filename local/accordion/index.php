<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once("../../config.php");

require_login();
require_capability('local/accordion:view', context_system::instance());

$defaultid = (int) get_config('local_accordion', 'root_category');
define('LOCAL_ACCORDION_ID', optional_param('id', $defaultid, PARAM_INT));

require_once("{$CFG->dirroot}/local/accordion/lib.php");
require_once("{$CFG->dirroot}/course/lib.php");

$site = get_site();
/*
$catalogue = optional_param('catalogue', 'card', PARAM_ALPHA);
if ($catalogue !== 'card') {
    $catalogue = 'accordion';
}
*/
$catalogue = 'accordion'; // Force default temporarily.

if (LOCAL_ACCORDION_ID) {
    $PAGE->set_category_by_id(LOCAL_ACCORDION_ID);
    $category = $PAGE->category;
    if (!$category->visible) {
        require_capability('moodle/category:viewhiddencategories', context_coursecat::instance($category->id));
    }
    $PAGE->set_url(new moodle_url('/course/index.php', array('categoryid' => LOCAL_ACCORDION_ID, 'catalogue' => $catalogue)));
} else {
    $category = null;
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(new moodle_url('/course/index.php', array('catalogue' => $catalogue)));
}

if ($category) {
    $title = $site->shortname . ': ' . $category->name;
} else {
    $title = $site->shortname . ': ' . get_string('title', 'local_accordion');
}
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->blocks->show_only_fake_blocks();

if ($catalogue == 'card') {
    $PAGE->set_pagelayout('frontpage');
    $renderer = $PAGE->get_renderer('theme_arup', 'html');
    echo $OUTPUT->header();
    echo $renderer->availablecategories(LOCAL_ACCORDION_ID);
    echo $OUTPUT->footer();
    exit(0);
}

$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/accordion/js/accordion.js', false);

$renderer = $PAGE->get_renderer('local_accordion');

$filters = array();
$region = '';
$showfilters = (get_config('local_coursemetadata', 'version') && get_config('local_accordion', 'coursemetadata_filter'))
        || (get_config('local_regions', 'version') && get_config('local_accordion', 'regions_filter'));
if ($showfilters) {
    $filterblock = new filter_block();
    // Set a fake instance id so we can hide the block.
    $filterblock->contents->blockinstanceid = -1; // Real instances won't be negative.
    $PAGE->blocks->add_fake_block($filterblock->contents, 'left');
    $filters = $filterblock->get_filters();
    $region = $filterblock->get_filter_region() ? " [{$filterblock->get_filter_region()}]" : '';
}

if (get_config('local_wa_learning_path', 'version')) {
    $regionid = isset($filterblock) ? $filterblock->get_filter_region_id() : null;
    $PAGE->blocks->add_fake_block($renderer->learningpath_block($regionid), 'left');
}

$editcategoryid = empty($category) ? LOCAL_ACCORDION_ID : $category->id;
if (can_edit_in_category($editcategoryid)) {
    $PAGE->blocks->add_fake_block($renderer->editing_block($category), 'left');
}

$buildareaid = get_config('local_accordion', 'build_area');
if (can_edit_in_category($buildareaid)) {
    $PAGE->blocks->add_fake_block($renderer->buildarea_block($buildareaid), 'left');
}

echo $OUTPUT->header();

echo html_writer::start_tag('div', array('id' => 'local_accordion_wrapper'));

if ($category) {
    if (!isset($category->idnumber[0]) || $category->idnumber[0] != '_') {
        echo $OUTPUT->heading($category->name . $region);
    }
} else {
    echo $OUTPUT->heading(get_string('title', 'local_accordion') . $region);
}

echo $OUTPUT->skip_link_target();

if ($category && $category->description) {
    echo $OUTPUT->box_start('generalbox');
    $categoryoptions = new stdClass;
    $categoryoptions->noclean = true;
    $categoryoptions->para = false;
    $categoryoptions->overflowdiv = true;
    if (!isset($category->descriptionformat)) {
        $category->descriptionformat = FORMAT_MOODLE;
    }
    $categorytext = file_rewrite_pluginfile_urls($category->description, 'pluginfile.php', $PAGE->context->id, 'coursecat', 'description', null);
    echo format_text($categorytext, $category->descriptionformat, $categoryoptions);
    echo $OUTPUT->box_end();
} else if (!$category) {
    $introduction = get_string('introduction', 'local_accordion');
    if (!empty($introduction)) {
        echo $OUTPUT->box_start('generalbox');
        echo $introduction;
        echo $OUTPUT->box_end();
    }
}

echo $OUTPUT->box_start('categorybox');

$category = new category($category, $filters, true);
$renderer->render_category($category);

echo $OUTPUT->box_end();

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
