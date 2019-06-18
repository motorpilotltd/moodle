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

$cmid = required_param('cmid', PARAM_INT);
$sortcolumn = optional_param('tsort', 'classstarttime', PARAM_ALPHA);
$showall = optional_param('showall', false, PARAM_BOOL);
$resendinvitesclassid = optional_param('resendinvitesclassid', 0, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'tapsenrol');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$baseurl = new moodle_url('/mod/tapsenrol/classoverview.php', ['cmid' => $cmid]);

$PAGE->set_context($context);
$PAGE->set_url($baseurl);

$PAGE->set_title(get_string('manageclasses', 'tapsenrol'));
$PAGE->set_heading(get_string('manageclasses', 'tapsenrol'));

echo $OUTPUT->header();

if ($resendinvitesclassid) {
    $resendinviteslink = new moodle_url(
            '/mod/tapsenrol/resend_invites.php',
            ['id' => $cm->id, 'classid' => $resendinvitesclassid]
    );
    echo html_writer::div(get_string('resendinvites', 'tapsenrol', $resendinviteslink), 'alert alert-warning');
}

$showallpagesize = 5000;
$defaultpagesize = 50;
if ($showall) {
    $pagesize = $showallpagesize;
} else {
    $pagesize = $defaultpagesize;
}

if (has_capability('mod/tapsenrol:createclass', $context)) {
    echo $OUTPUT->single_button(new moodle_url('/mod/tapsenrol/editclass.php', array('cmid' => $cmid)),
            get_string("addnewclass", 'tapsenrol'));
}

$table = new \mod_tapsenrol\classoverview_table($sortcolumn, $cm);
$table->define_baseurl($baseurl);
$table->out($pagesize, true);

$output = $PAGE->get_renderer('mod_tapsenrol');
echo $output->back_to_coursemanager($cm->course);

echo $OUTPUT->footer();