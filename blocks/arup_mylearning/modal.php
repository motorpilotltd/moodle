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

require_once("../../config.php");

try {
    require_login();

    $id = required_param('id', PARAM_INT);
    $instance = required_param('instance', PARAM_INT);

    $PAGE->set_context(context_block::instance($instance));
    $PAGE->set_url('/blocks/arup_mylearning/modal.php');

    if (get_config('local_taps', 'version')) {
        $taps = new \local_taps\taps();

        $enrolment = $DB->get_record('local_taps_enrolment', array('id' => $id, 'staffid' => $USER->idnumber));
        if ($enrolment) {
            $timezone = new DateTimeZone($enrolment->usedtimezone);

            echo html_writer::start_tag('div', array('class' => 'modal-upper-wrapper clearfix'));

            echo html_writer::start_tag('div', array('class' => 'modal-upper'));
            $modalupper = array();
            if ($enrolment->classtype) {
                $classtypegroup = $taps->get_classtype_type($enrolment->classtype);
                if ($classtypegroup != 'cpd' && get_string_manager()->string_exists($classtypegroup, 'block_arup_mylearning')) {
                    $data = get_string($classtypegroup, 'block_arup_mylearning');
                } else {
                    $data = $enrolment->classtype;
                }
                $modalupper[] = html_writer::tag('strong', get_string('modal:classtype', 'block_arup_mylearning').': ') .
                    $data;
            }
            if ($enrolment->classstartdate) {
                $date = new DateTime(null, $timezone);
                $date->setTimestamp($enrolment->classstartdate);
                $data = $date->format('d M Y');
                $modalupper[] = html_writer::tag('strong', get_string('modal:classstartdate', 'block_arup_mylearning').': ') .
                    $data;
            }
            if ($enrolment->provider) {
                $modalupper[] = html_writer::tag('strong', get_string('modal:provider', 'block_arup_mylearning').': ') .
                    $enrolment->provider;
            }
            if ($enrolment->certificateno) {
                $modalupper[] = html_writer::tag('strong', get_string('modal:certificateno', 'block_arup_mylearning').': ') .
                    $enrolment->certificateno;
            }
            if ($enrolment->classcategory) {
                $modalupper[] = html_writer::tag('strong', get_string('modal:classcategory', 'block_arup_mylearning').': ') .
                    $enrolment->classcategory;
            }
            if ($modalupper) {
                echo html_writer::tag('p', implode(html_writer::empty_tag('br'), $modalupper));
            }
            echo html_writer::end_tag('div'); // End div modal-upper.

            echo html_writer::start_tag('div', array('class' => 'modal-upper'));
            $modalupper = array();
            if ($enrolment->duration) {
                $data = (float)$enrolment->duration.' '.$enrolment->durationunits;
                $modalupper[] = html_writer::tag('strong', get_string('modal:duration', 'block_arup_mylearning').': ') .
                    $data;
            }
            if ($enrolment->classcompletiondate) {
                $date = new DateTime(null, $timezone);
                $date->setTimestamp($enrolment->classcompletiondate);
                $data = $date->format('d M Y');
                $modalupper[] = html_writer::tag('strong', get_string('modal:classcompletiondate', 'block_arup_mylearning').': ') .
                    $data;
            }
            if ($enrolment->location) {
                $modalupper[] = html_writer::tag('strong', get_string('modal:location', 'block_arup_mylearning').': ') .
                    $enrolment->location;
            }
            if ($enrolment->expirydate) {
                $date = new DateTime(null, $timezone);
                $date->setTimestamp($enrolment->expirydate);
                $data = $date->format('d M Y');
                $modalupper[] = html_writer::tag('strong', get_string('modal:expirydate', 'block_arup_mylearning').': ') .
                    $data;
            }
            if ($modalupper) {
                echo html_writer::tag('p', implode(html_writer::empty_tag('br'), $modalupper));
            }
            echo html_writer::end_tag('div'); // End div modal-upper.

            echo html_writer::end_tag('div'); // End div modal-upper-wrapper.

            echo html_writer::start_tag('div', array('class' => 'modal-lower'));
            if ($enrolment->learningdesc) {
                $data = html_writer::empty_tag('br') .
                    $enrolment->learningdesc . ' ' . $enrolment->learningdesccont1 . ' ' . $enrolment->learningdesccont2;
                echo html_writer::tag('p', html_writer::tag('strong', get_string('modal:learningdesc', 'block_arup_mylearning').':').$data);
            } else if ($enrolment->courseobjectives) {
                $data = html_writer::empty_tag('br') .
                    $enrolment->courseobjectives;
                echo html_writer::tag('p', html_writer::tag('strong', get_string('modal:courseobjectives', 'block_arup_mylearning').':').$data);
            }
            echo html_writer::end_tag('div'); // End div modal-lower.
        } else {
            echo html_writer::tag('p', get_string('couldnotloadenrolment', 'block_arup_mylearning'));
        }
    } else {
        echo html_writer::tag('p', get_string('couldnotloadenrolment', 'block_arup_mylearning'));
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
