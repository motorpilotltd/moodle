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

require(dirname(__FILE__).'/../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

admin_externalpage_setup('local_admin_enrolmentcheck');

$output = $PAGE->get_renderer('local_admin');

$form = new \local_admin\form\enrolment_check();

$processingresults = [];
$fromform = $form->get_data();
if ($fromform) {
    // Process and check enrolments...
    $tapsenrol = new tapsenrol($fromform->cm);
    $staffids = explode("\n", $fromform->staffids);
    foreach ($staffids as $origstaffid) {
        $staffid = str_pad(trim($origstaffid), 6, '0', STR_PAD_LEFT);
        $tapsenrol->enrolment_check($staffid, false);
        $processingresults[] = get_string('enrolmentcheck:processed', 'local_admin', $staffid);
    }
    $processingresults[] = '';
    $a = new stdClass();
    $a->stat = $tapsenrol->statistics->nonmoodleusers;
    $a->s = ($a->stat != 1 ? 's' : '');
    $processingresults[] = get_string('enrolmentcheck:stats:nonmoodle', 'local_admin', $a);
    $a->stat = $tapsenrol->statistics->enrolled;
    $a->s = ($a->stat != 1 ? 's' : '');
    $processingresults[] = get_string('enrolmentcheck:stats:enrolled', 'local_admin', $a);
    $a->stat = $tapsenrol->statistics->notenrolled;
    $a->s = ($a->stat != 1 ? 's' : '');
    $processingresults[] = get_string('enrolmentcheck:stats:notenrolled', 'local_admin', $a);
}

// Output View.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('enrolmentcheck', 'local_admin'));

if (!empty($processingresults)) {
    echo $output->alert(implode('<br>', $processingresults), 'alert-success');
}

$form->display();

echo $OUTPUT->footer();
