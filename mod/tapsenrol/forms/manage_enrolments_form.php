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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class mod_tapsenrol_manage_enrolments_form extends moodleform {
    protected $_renderer;

    public function definition() {
        global $PAGE;

        $this->_renderer = $PAGE->get_renderer('mod_tapsenrol');

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $choose = array('' => get_string('choosedots'));
        foreach ($this->_customdata['classes'] as $type => $classes) {
            $mform->addElement('header', "header-{$type}", get_string("manageenrolments:header:{$type}", 'tapsenrol'));
            $mform->setExpanded("header-{$type}", false);
            if (empty($classes)) {
                $mform->addElement('html', $this->_renderer->alert(get_string('manageenrolments:noclasses', 'tapsenrol'), 'alert-warning', false));
            } else {
                $classes = $choose + $classes;
                $mform->addElement('select', "{$type}classid", get_string('manageenrolments:class', 'tapsenrol'), $classes, array('class' => 'class-select'));
                $mform->setDefault("{$type}classid", $this->_customdata['classid']);
                $mform->addElement('html', $this->_get_class_placeholder($this->_customdata['classid']));
                $mform->addElement('submit', "submit-$type", get_string("manageenrolments:button:{$type}", 'tapsenrol'), array('class' => 'btn-primary'));
            }
        }
    }

    protected function _get_class_placeholder($classid) {
        global $DB;
        
        $hide = ' hide';
        
        $class = ($classid ? $DB->get_record('local_taps_class', array('classid' => $classid)) : false);

        if ($class) {
            $hide = '';
            $taps = new \local_taps\taps();
            $class->location = ($class->location ? $class->location : get_string('tbc', 'tapsenrol'));
            if (!$class->classstarttime) {
                $class->date = get_string('waitinglist:classroom', 'tapsenrol');
            } else {
                try {
                    $timezone = new DateTimeZone($class->usedtimezone);
                } catch (Exception $e) {
                    $timezone = new DateTimeZone(date_default_timezone_get());
                }
                $startdatetime = new DateTime(null, $timezone);
                $startdatetime->setTimestamp($class->classstarttime);
                $class->date = $startdatetime->format('d M Y');
                $class->date .= ($class->classstarttime != $class->classstartdate) ? $startdatetime->format(' H:i T') : '';
                // Show UTC as GMT for clarity.
                $class->date = str_replace('UTC', 'GMT', $class->date);
            }
            $class->duration = ($class->classduration) ? (float) $class->classduration . ' ' . $class->classdurationunits : get_string('tbc', 'tapsenrol');
            $class->cost = $class->price ? $class->price.' '.$class->currencycode : '-';
            $tempseatsremaining = $taps->get_seats_remaining($classid);
            $class->seatsremaining = ($tempseatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $tempseatsremaining);

            list($in, $inparams) = $DB->get_in_or_equal(
                $taps->get_statuses('cancelled'),
                SQL_PARAMS_NAMED, 'status', false
            );
            $compare = $DB->sql_compare_text('bookingstatus');
            $params = array_merge(
                array('classid' => $class->classid),
                $inparams
            );
            $class->enrolments = $DB->count_records_select('local_taps_enrolment', "classid = :classid AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}", $params);
        }

        $html = html_writer::start_tag('div', array('class' => "tapsenrol_manage_enrolments_class mform{$hide}"));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', ($class ? $class->classname : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'classname fitem'));

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $location .= html_writer::tag('div', ($class ? $class->location : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'location fitem'));

        $trainingcenterhide = ($class && !$class->trainingcenter ? ' hide' : '');
        $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $trainingcenter .= html_writer::tag('div', ($class ? $class->trainingcenter : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $trainingcenter, array('class' => "trainingcenter fitem{$trainingcenterhide}"));

        $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $date .= html_writer::tag('div', ($class ? $class->date : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $date, array('class' => 'date fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $duration .= html_writer::tag('div', ($class ? $class->duration : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'duration fitem'));

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $cost .= html_writer::tag('div', ($class ? $class->cost : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'cost fitem'));

        $seatsremaining = html_writer::tag('div', get_string('seatsremaining', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $seatsremaining .= html_writer::tag('div', ($class ? $class->seatsremaining : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $seatsremaining, array('class' => 'seatsremaining fitem'));

        $enrolments = html_writer::tag('div', get_string('enrolments', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $enrolments .= html_writer::tag('div', ($class ? $class->enrolments : ''), array('class' => 'felement'));
        $html .= html_writer::tag('div', $enrolments, array('class' => 'enrolments fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_manage_enrolments_class.

        return $html;
    }
}