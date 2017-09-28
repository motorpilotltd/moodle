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
$PAGE->set_url('/local/onlineappraisal/print.php');
$PAGE->set_pagelayout('incourse');

// Setup language.
\local_onlineappraisal\lang_setup(false);

require_login();

// Page can be 'appraisal' or 'feedback'.
$appraisalid = optional_param('appraisalid', 0, PARAM_INT);
$view = optional_param('view', '', PARAM_ALPHA);
$print = optional_param('print', 'appraisal', PARAM_ALPHA);

$PAGE->navbar->add(get_string('pluginname', 'local_onlineappraisal'), '/local/onlineappraisal/');

$PAGE->blocks->show_only_fake_blocks();

try {
    \local_onlineappraisal\user::loginas_check();
    // Load appraisal.
    $appraisal = new \local_onlineappraisal\appraisal($USER, $appraisalid, $view, 'overview', 0);
    // Set up printer.
    $printer = new \local_onlineappraisal\printer($appraisal, $print);

    // Add params to clone and reset $PAGE->url.
    $url = $PAGE->url;
    $url->params(
        array(
            'appraisalid' => $appraisal->appraisal->id,
            'view' => $appraisal->appraisal->viewingas,
            'print' => $printer->print
        )
    );
    $PAGE->set_url($url);

    // This will output PDF and exit on success.
    $printer->pdf();
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