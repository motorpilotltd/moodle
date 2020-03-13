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
 * Contains the class for the My overview block.
 *
 * @package    local_catalogue
 * @copyright  Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_once($CFG->dirroot . '/lib/coursecatlib.php');

require_login();

use local_catalogue\external\catalogue_course_exporter;

$context = context_system::instance();
$title = get_string('pluginname', 'local_catalogue');
$url = new moodle_url('/local/catalogue/index.php');
$catid = (int) get_config('local_accordion', 'root_category');

$showcat = optional_param('catid', $catid, PARAM_INT);
$categories = optional_param('view', 'categories', PARAM_TEXT);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->navbar->add($title, $url);
$PAGE->set_pagelayout('base');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$category = '0';
$fields = 'id,fullname,shortname,idnumber,summary,summaryformat,startdate,enddate';
$offset = '0';
$limit = '12';
$metadata = '[]';
$search = 'Word';

// $DB->set_debug(1);
list($filteredcourses, $processedcount) = \local_catalogue\catalogue_courses::get_filtered_courses($category, $fields, $offset, $limit, $metadata, $search);

$renderer = $PAGE->get_renderer('core');

$formattedcourses = [];

$context = context_system::instance();
foreach ($filteredcourses as $course) {
    $exporter = new catalogue_course_exporter($course, ['context' => $context]);
    $formattedcourses[] = $exporter->export($renderer);
}

echo $OUTPUT->header();

// $coursemetadata = $DB->get_records('coursemetadata_arup');

// echo '<pre>' . print_r($coursemetadata, true) . '<pre>';

//echo '<pre>' . print_r($filteredcourses, true) . '<pre>';

// echo '<pre>' . print_r($formattedcourses, true) . '<pre>';

echo $OUTPUT->footer();