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

/**
 * A Catalogue of Courses for Arup.
 *
 * @package    local_catalogue
 * @copyright  Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
$title = get_string('pluginname', 'local_catalogue');
$url = new moodle_url('/local/catalogue/index.php');
$catid = (int) get_config('local_accordion', 'root_category');
$page = optional_param('page', 'categories', PARAM_TEXT);
$showcatid = optional_param('showcatid', 0, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_INT);
$search = optional_param('search', 0, PARAM_TEXT);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->navbar->add($title, $url);

if ($PAGE->user_allowed_editing()) {
    if ($edit != -1) {
        $USER->editing = $edit;
    }
}

if ($showcatid) {
    if ($subcat = $DB->get_record('course_categories', ['id' => $showcatid])) {
        $url = new \moodle_url('/local/catalogue/index.php', ['showcat' => $showcatid]);
        $PAGE->navbar->add($subcat->name, $url);
        $PAGE->set_title($subcat->name);
        $PAGE->set_heading($subcat->name);
    }
} else {
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
}

$viewpreference = get_user_preferences('local_catalogue_user_view_preference');
$paging = get_user_preferences('local_catalogue_user_paging_preference');

if ($page == 'categories') {
    $PAGE->set_pagelayout('catalogue');
    $renderable = new \local_catalogue\output\categories($catid);
} else {
    $PAGE->set_pagelayout('base');
    $renderable = new \local_catalogue\output\main($viewpreference, $paging, $catid);
}

$renderer = $PAGE->get_renderer('local_catalogue');

echo $OUTPUT->header();

echo $renderer->render($renderable);

echo $OUTPUT->footer();