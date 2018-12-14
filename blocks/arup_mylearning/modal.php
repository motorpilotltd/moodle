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

    $taps = new \mod_tapsenrol\taps();

    $lrsentry = \local_learningrecordstore\lrsentry::fetch(['id' => $id, 'staffid' => $USER->idnumber]);
    if ($lrsentry) {
        echo html_writer::start_tag('div', array('class' => 'modal-upper-wrapper clearfix'));

        echo html_writer::start_tag('div', array('class' => 'modal-upper'));
        $modalupper = array();
        if (!empty($lrsentry->classtype)) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:classtype', 'block_arup_mylearning').': ') .
                    $lrsentry->classtype;
        }
        if ($lrsentry->provider) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:provider', 'block_arup_mylearning').': ') .
                $lrsentry->provider;
        }
        if ($lrsentry->certificateno) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:certificateno', 'block_arup_mylearning').': ') .
                $lrsentry->certificateno;
        }
        if ($lrsentry->classcategory) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:classcategory', 'block_arup_mylearning').': ') .
                $lrsentry->classcategory;
        }
        if ($modalupper) {
            echo html_writer::tag('p', implode(html_writer::empty_tag('br'), $modalupper));
        }
        echo html_writer::end_tag('div'); // End div modal-upper.

        echo html_writer::start_tag('div', array('class' => 'modal-upper'));
        $modalupper = array();
        if ($class->duration) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:duration', 'block_arup_mylearning').': ') .
                $lrsentry->formatduration();
        }
        if ($lrsentry->completiontime) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:completiontime', 'block_arup_mylearning').': ') .
                userdate($lrsentry->completiontime, get_string('strftimedate', 'langconfig'));
        }
        if ($class->location) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:location', 'block_arup_mylearning').': ') .
                    $class->location;
        }
        if ($lrsentry->expirydate) {
            $modalupper[] = html_writer::tag('strong', get_string('modal:expirydate', 'block_arup_mylearning').': ') .
                    userdate($lrsentry->expirydate, get_string('strftimedate', 'langconfig'));
        }
        if ($modalupper) {
            echo html_writer::tag('p', implode(html_writer::empty_tag('br'), $modalupper));
        }
        echo html_writer::end_tag('div'); // End div modal-upper.

        echo html_writer::end_tag('div'); // End div modal-upper-wrapper.

        echo html_writer::start_tag('div', array('class' => 'modal-lower'));
        if ($lrsentry->description) {
            echo html_writer::tag('p', html_writer::tag('strong', get_string('modal:learningdesc', 'block_arup_mylearning') . ':') .
                    html_writer::empty_tag('br') .
                    $lrsentry->description);
        }
        echo html_writer::end_tag('div'); // End div modal-lower.
    } else {
        echo html_writer::tag('p', get_string('couldnotloadenrolment', 'block_arup_mylearning'));
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
