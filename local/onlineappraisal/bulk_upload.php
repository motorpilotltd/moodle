<?php
// This file is part of the Arup online appraisal system
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
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once 'locallib.php';
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_onlineappraisal_bulkupload');

// Could be intensive.
core_php_time_limit::raise();
raise_memory_limit(MEMORY_EXTRA);

// To align with plugin CSS.
$PAGE->add_body_class('path-local-onlineappraisal');

$step = optional_param('step', 'one', PARAM_ALPHA);

try {
    $bulkupload = new \local_onlineappraisal\bulkupload($step);
    $bulkupload->setup_page();
} catch (Exception $e) {
    $PAGE->set_title(get_string('error', 'local_onlineappraisal'));
    $PAGE->set_heading(get_string('error', 'local_onlineappraisal'));

    $renderer = $PAGE->get_renderer('local_onlineappraisal');
    $alert = new \local_onlineappraisal\output\alert($e->getMessage(), 'danger', false);

    echo $OUTPUT->header();
    echo $renderer->render($alert);
    echo $OUTPUT->footer();

    exit;
}

// Add params to clone and reset $PAGE->url.
$url = $PAGE->url;
$url->params(array('step' => $bulkupload->step));
$PAGE->set_url($url);
$PAGE->set_title($bulkupload->pagetitle);
$PAGE->set_heading($bulkupload->pageheading);

// JS.
// String pre-loading.
$PAGE->requires->strings_for_js(
        array(
            'admin:processingdots',
        ), 'local_onlineappraisal');
// Add JS.
$arguments = array(
    'dateformat' => get_string('strftimedate'),
);
$PAGE->requires->js_call_amd('local_onlineappraisal/bulkupload', 'init', array($arguments));

echo $OUTPUT->header();

echo $OUTPUT->heading($PAGE->heading);

echo $bulkupload->main_content();

echo $OUTPUT->footer();