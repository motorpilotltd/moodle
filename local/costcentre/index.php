<?php
// This file is part of the Arup cost centre local plugin
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
 * Version details
 *
 * @package     local_costcentre
 * @copyright   2016 Motorpilot Ltd
 * @author      Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_costcentre_index');

$title = get_string('title:index', 'local_costcentre');
$PAGE->set_title(get_site()->shortname . ': ' . $title);

$renderer = $PAGE->get_renderer('local_costcentre');

try {
    $costcentre = new \local_costcentre\costcentre('index', $renderer);
    $costcentre->prepare_page();
} catch (Exception $e) {
    // Render error.
    echo $OUTPUT->header();
    $alert = new \local_costcentre\output\alert($e->getMessage(), 'danger', false);
    echo $renderer->render($alert);
    echo $OUTPUT->footer();
    exit;
}

// Required CSS and JS.
$PAGE->requires->css(new moodle_url('/local/costcentre/css/select2.min.css'));
$PAGE->requires->css(new moodle_url('/local/costcentre/css/select2-bootstrap.min.css'));
// String pre-loading.
$PAGE->requires->string_for_js('alert:restrictedaccess:tooltip', 'local_costcentre');
$PAGE->requires->js_call_amd('local_costcentre/enhance', 'initialise');

// Render page.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo $costcentre->output_page();

echo $OUTPUT->footer();
