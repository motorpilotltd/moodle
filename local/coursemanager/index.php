<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/coursemanager/index.php');
$PAGE->set_pagelayout('frametop');
$PAGE->navbar->add(get_string('pluginname', 'local_coursemanager'), new moodle_url('/local/coursemanager/view.php'));
$PAGE->navbar->add(get_string('managecourses', 'local_coursemanager'));
$PAGE->blocks->show_only_fake_blocks();

$cmcourse = optional_param('cmcourse', 0, PARAM_INT);
$cmclass = optional_param('cmclass', 0, PARAM_INT);
$page = optional_param('page', 'overview', PARAM_TEXT);
$formid = optional_param('formid', 0, PARAM_INT);

$cm = new \local_coursemanager\coursemanager($cmcourse, $cmclass, $page, $formid);
$cm->prepare_page();

$PAGE->set_title(get_string('pluginname', 'local_coursemanager'));
$PAGE->set_heading(get_string('pluginname', 'local_coursemanager'));

$renderer = $PAGE->get_renderer('local_coursemanager', 'navlist');
$navlist = new \local_coursemanager\output\navlist\navlist($cm);

echo $OUTPUT->header();

echo $renderer->render($navlist);

echo $cm->main_content();

echo $OUTPUT->footer();