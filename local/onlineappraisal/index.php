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
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once 'locallib.php';

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/onlineappraisal/index.php');
$PAGE->set_pagelayout('incourse');

// Setup language.
\local_onlineappraisal\lang_setup();

require_login();

$PAGE->navbar->add(get_string('pluginname', 'local_onlineappraisal'), '/local/onlineappraisal/');

$PAGE->blocks->show_only_fake_blocks();

$page = optional_param('page', '', PARAM_ALPHA);

try {
    \local_onlineappraisal\user::loginas_check();
    $index = new \local_onlineappraisal\index($page);
    $index->setup_page();
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

$PAGE->url->param('page', $index->page);
$PAGE->set_title($index->pagetitle);
$PAGE->set_heading($index->pageheading);

// String pre-loading.
$PAGE->requires->strings_for_js(
    array(
        'admin:processingdots',
        'admin:savingdots',
        'error:f2fdate',
        'error:request',
    ), 'local_onlineappraisal');
// Add JS.
$arguments = array(
    'dateformat' => get_string('strftimedate'),
);
$PAGE->requires->js_call_amd('local_onlineappraisal/index', 'init', array($arguments));

$renderer = $PAGE->get_renderer('local_onlineappraisal', 'navlist');
$navlist = new \local_onlineappraisal\output\navlist\navlist_index($index);

$regions = $PAGE->blocks->get_regions();
$PAGE->blocks->add_fake_block($renderer->render($navlist), reset($regions));

$PAGE->navbar->add(get_string('index', 'local_onlineappraisal'));
$PAGE->navbar->add(get_string("index:{$index->page}", 'local_onlineappraisal'), $PAGE->url);

echo $OUTPUT->header();

echo $index->main_content();

echo $OUTPUT->footer();

$event = \local_onlineappraisal\event\appraisal_dashboard_viewed::create(array(
    'other' => array(
        'page' => $index->page,
    ),
));
$event->trigger();