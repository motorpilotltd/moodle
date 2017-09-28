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

// Cheeky override for help page.
if (optional_param('page', '', PARAM_ALPHA) === 'help') {
    redirect(new moodle_url('/local/onlineappraisal/index.php', ['page' => 'help']));
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/onlineappraisal/feedback_requests.php'));
$PAGE->set_pagelayout('incourse');

// Setup language.
\local_onlineappraisal\lang_setup();

require_login();

$PAGE->navbar->add(get_string('pluginname', 'local_onlineappraisal'), '/local/onlineappraisal/');
$PAGE->navbar->add(get_string('navbar:contribdashboard', 'local_onlineappraisal'), $PAGE->url);

$PAGE->blocks->show_only_fake_blocks();

$action = optional_param('action', '', PARAM_TEXT);
$requestid = optional_param('id', 0, PARAM_INT);

try {
    \local_onlineappraisal\user::loginas_check();
    $fb = new \local_onlineappraisal\feedback();

    if ($action && $requestid) {
        $fb->request_action($action, $requestid);
    }

    $renderer = $PAGE->get_renderer('local_onlineappraisal', 'dashboard');
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

$PAGE->set_title(get_string('feedbackrequests:heading','local_onlineappraisal'));

$navrenderer = $PAGE->get_renderer('local_onlineappraisal', 'navlist');
$navlist = new \local_onlineappraisal\output\navlist\navlist_feedback($fb);

$regions = $PAGE->blocks->get_regions();
$PAGE->blocks->add_fake_block($navrenderer->render($navlist), reset($regions));

echo $OUTPUT->header();

$feedback = new \local_onlineappraisal\output\dashboard\feedbackrequests($fb);

echo $renderer->render($feedback);

echo $OUTPUT->footer();

$event = \local_onlineappraisal\event\feedback_list_viewed::create();
$event->trigger();