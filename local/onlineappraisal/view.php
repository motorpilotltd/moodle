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

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/onlineappraisal/view.php');
$PAGE->set_pagelayout('incourse');

// Setup language.
\local_onlineappraisal\lang_setup();

$PAGE->navbar->add(get_string('pluginname', 'local_onlineappraisal'), '/local/onlineappraisal/');

$PAGE->blocks->show_only_fake_blocks();

$appraisalid = optional_param('appraisalid', 0, PARAM_INT);
$view = optional_param('view', '', PARAM_ALPHA);
$page = optional_param('page', '', PARAM_ALPHA);
$formid = optional_param('formid', 0, PARAM_INT);

try {
    \local_onlineappraisal\user::loginas_check();
    $oa = new \local_onlineappraisal\appraisal($USER, $appraisalid, $view, $page, $formid);
    $oa->setup_page($page);
    // This must be done before output is send to the page and after the page context has been set.
    $oa->prepare_page();
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
$url->params(
    array(
        'appraisalid' => $oa->appraisal->id,
        'view' => $oa->appraisal->viewingas,
        'page' => $oa->page,
        'formid' => $oa->formid,
    )
);
$PAGE->set_url($url);
$PAGE->set_title($oa->pagetitle);
$PAGE->set_heading($oa->pageheading);

// String pre-loading.
$PAGE->requires->strings_for_js(
    array(
        'error:request',
        'error:sessioncheck',
        'form:userinfo:refresh',
        'form:userinfo:refresh:tooltip',
        'form:development:leadershiproles:placeholder',
        'form:development:leadershiproles:answer:generic',
        'form:development:leadershipattributes:error:toomany',
        'error'
    ), 'local_onlineappraisal');
// Add JS.
$arguments = array(
    'appraisalid' => $oa->appraisal->id,
    'view' => $oa->appraisal->viewingas,
    'page' => $oa->page,
    'statusid' => $oa->appraisal->statusid,
);
$PAGE->requires->js_call_amd('local_onlineappraisal/view', 'init', $arguments);

// Add vendor CSS.
$PAGE->requires->css(new moodle_url('/local/onlineappraisal/css/select2.min.css'));
$PAGE->requires->css(new moodle_url('/local/onlineappraisal/css/select2-bootstrap.min.css'));

$renderer = $PAGE->get_renderer('local_onlineappraisal', 'navlist');
$navlist = new \local_onlineappraisal\output\navlist\navlist($oa);

$regions = $PAGE->blocks->get_regions();
$PAGE->blocks->add_fake_block($renderer->render($navlist), reset($regions));

$quicklinks = new \local_onlineappraisal\output\quicklinks(current_language(), $oa->page);
if ($quicklinks->has_content()) {
    $PAGE->blocks->add_fake_block($PAGE->get_renderer('local_onlineappraisal')->render($quicklinks), reset($regions));
}

$PAGE->navbar->add(get_string($oa->page, 'local_onlineappraisal'), $PAGE->url);

echo $OUTPUT->header();

echo $oa->main_content();

echo $OUTPUT->footer();

$class = "\\local_onlineappraisal\\event\\appraisal_{$oa->appraisal->viewingas}_viewed";
if (!class_exists($class)) {
    $class = "\\local_onlineappraisal\\event\\appraisal_viewed";
}
$eventdata = array(
    'objectid' => $oa->appraisal->id,
    'other' => array(
        'type' => core_text::strtoupper($oa->appraisal->viewingas),
        'page' => $page,
    )
);
$event = call_user_func(array($class, 'create'), $eventdata);
if ($event) {
    $event->trigger();
}