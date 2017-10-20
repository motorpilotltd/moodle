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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$t = optional_param('t',  0, PARAM_INT);  // TAPS Enrolment Activity ID.

if ($id) {
    $tapsenrol = new tapsenrol($id, 'cm');
} else {
    $tapsenrol = new tapsenrol($t, 'instance');
}

require_login($tapsenrol->course, false, $tapsenrol->cm);

// Trigger module viewed event.
tapsenrol_view($tapsenrol->tapsenrol, $tapsenrol->course, $tapsenrol->cm, context_module::instance($tapsenrol->cm->id));

$PAGE->set_url('/mod/tapsenrol/view.php', array('id' => $tapsenrol->cm->id));

$title = $tapsenrol->course->shortname . ': ' . format_string($tapsenrol->tapsenrol->name);
$PAGE->set_title($title);
$PAGE->set_heading($tapsenrol->course->fullname);

$output = $PAGE->get_renderer('mod_tapsenrol');

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('tapsenrolment', 'tapsenrol'));

if (!$tapsenrol->check_installation()) {
    echo $output->alert(html_writer::tag('p', get_string('installationissue', 'tapsenrol')), 'alert-danger', false);
} else {
    $canview = $canviewclasses = $PAGE->user_is_editing();
    if ($USER->auth == 'saml' && $USER->idnumber != '') {
        $canview = true;

        echo $tapsenrol->enrolment_check($USER->idnumber, true);

        $region = $DB->get_records_menu('tapsenrol_region', array('tapsenrolid' => $tapsenrol->tapsenrol->id), '', 'regionid as id, regionid as id2');
        if (empty($region)) {
            $canviewclasses = true;
        } else {
            local_regions_load_data_user($USER);
            if (isset($USER->regions_field_geotapsregionid) && in_array($USER->regions_field_geotapsregionid, $region)) {
                $canviewclasses = true;
            }
        }
    }

    if ($canview) {
        $classes = $tapsenrol->get_tapsclasses($canviewclasses);
        $enrolments = $tapsenrol->taps->get_enroled_classes($USER->idnumber, $tapsenrol->tapsenrol->tapscourse, false, false);
        $enrolmentoutput = $output->enrolment_history($tapsenrol, $enrolments, $classes, $tapsenrol->cm->id);
    }

    if (!$canview || !$canviewclasses) {
        $a = new stdClass();
        $a->course = core_text::strtolower(get_string('course'));
        $a->reason = '';
        if (!$canviewclasses && !empty($regions)) {
            $a->reason = get_string('cannotenrol:regions', 'tapsenrol', implode(', ', $regions));
        }
        $enrolmentoutput = $output->alert(html_writer::tag('p', get_string('cannotenrol', 'tapsenrol', $a)), 'alert-warning', false);
    }

    if (!empty($SESSION->tapsenrol->alert->message)) {
        $output .= $output->alert($SESSION->tapsenrol->alert->message, $SESSION->tapsenrol->alert->type);
        unset($SESSION->tapsenrol->alert);
    }

    echo $enrolmentoutput;
}

$output->back_to_module($tapsenrol->course->id);

echo $OUTPUT->footer();

