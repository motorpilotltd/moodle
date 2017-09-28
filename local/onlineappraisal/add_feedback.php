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

// To stop session dying after 3 minutess for non-logged in users so they can submit form.
$USER->ignoresesskey = true;

$id = required_param('id', PARAM_INT);
$pw = required_param('pw', PARAM_RAW);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/onlineappraisal/add_feedback.php'), array('id' => $id, 'pw' => $pw));
$PAGE->set_pagelayout('base');

// Setup language.
\local_onlineappraisal\lang_setup();

try {
    \local_onlineappraisal\user::loginas_check();
    $guest = $DB->get_record('user', array('username' => 'guest'));
    $oa = new \local_onlineappraisal\appraisal($guest, $id, 'guest', 'addfeedback', 0);
} catch (Exception $e) {
    $PAGE->set_title(get_string('error', 'local_onlineappraisal'));
    $PAGE->set_heading(get_string('error', 'local_onlineappraisal'));

    $renderer = $PAGE->get_renderer('local_onlineappraisal');
    $alert = new \local_onlineappraisal\output\alert($e->getMessage(), 'danger', false);

    if ($request = $DB->get_record('local_appraisal_feedback', array('id' => $id))) {
        if ($appraisal = $DB->get_record('local_appraisal_appraisal', array('id' => $request->appraisalid))) {
            if (!\local_onlineappraisal\permissions::is_allowed('addfeedback:view',
                $appraisal->permissionsid, 'guest', $appraisal->archived, $appraisal->legacy)) {
                $alert = new \local_onlineappraisal\output\alert(get_string('form:addfeedback:closed','local_onlineappraisal'), 'danger', false);
            }
        }
    }

    echo $OUTPUT->header();
    echo $renderer->render($alert);
    echo $OUTPUT->footer();

    // Reset.
    $USER->ignoresesskey = false;

    exit;
}

$PAGE->set_title(get_string('add_feedback_title','local_onlineappraisal'));

// Do this first as may redirect during form processing.
$content = $oa->main_content();

// String pre-loading.
$PAGE->requires->strings_for_js(
    array(
        'form:addfeedback:confirm',
    ), 'local_onlineappraisal');
// Add JS.
$PAGE->requires->js_call_amd('local_onlineappraisal/add_feedback', 'init');

echo $OUTPUT->header();

echo $content;

echo $OUTPUT->footer();

// Reset.
$USER->ignoresesskey = false;