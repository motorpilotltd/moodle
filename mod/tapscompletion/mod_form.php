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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_tapscompletion_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Check edge case of being called from defaults editing form...
        // In this case we don't need to worry about what does/doesn't exsit.
        $runchecks = true;
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 0);
        foreach ($frames as $frame) {
            if ($frame['class'] === 'core_completion_defaultedit_form') {
                $runchecks = false;
                break;
            }
        }
        if ($runchecks) {
            // Check for arupadvert activity (suite driver) presence.
            $this->_arupadvert_exists();

            // Check for tapsenrol activity presence.
            $this->_tapsenrol_exists();

            // Check for multiple instances.
            $this->_instances_exist();
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'tapscompletion'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->_taps_courses_select();

        $mform->addElement('checkbox', 'autocompletion', get_string('autocompletion', 'tapscompletion'));
        $mform->setDefault('autocompletion', 0);
        $mform->addElement('static', 'autocompletionhint', '', get_string('autocompletionhint', 'tapscompletion'));

        $options = [];
        foreach (\mod_tapscompletion\tapscompletion::$completiontimetypes as $name => $value) {
            $options[$value] = get_string("completiontimetype:{$name}", 'tapscompletion');
        }
        $mform->addElement('select', 'completiontimetype', get_string('completiontimetype', 'tapscompletion'), $options);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $DB;

        $mform =& $this->_form;

        $canupdateautocompletion = has_capability('mod/tapscompletion:setautocompletion', $this->get_context());

        if (!$canupdateautocompletion) {
            $mform->hardFreeze('autocompletion');
        } else {
            $mform->removeElement('autocompletionhint');
        }

        if (!$canupdateautocompletion && $mform->getElementValue('update')) {
            $instance = $DB->get_record('tapscompletion', array('id' => $mform->getElementValue('instance')));
            if ($instance) {
                $mform->setConstant('autocompletion', $instance->autocompletion);
            }
        }
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Internal Moodle validation will ensure tapscourse is existing choice or from arupadvert activity.

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        $autocompletion = !empty($data->completion) &&
                $data->completion == COMPLETION_TRACKING_AUTOMATIC;
        if (empty($data->completionattended) || !$autocompletion) {
            $data->completionattended = 0;
        }
        return $data;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->setDefault('completion', COMPLETION_TRACKING_AUTOMATIC);

        $mform->addElement('checkbox', 'completionattended', '', get_string('completionattended', 'tapscompletion'));
        $mform->setDefault('completionattended', true);

        return array('completionattended');
    }

    public function completion_rule_enabled($data) {
        return !empty($data['completionattended']);
    }

    protected function _arupadvert_exists() {
        global $DB;

        if (!$DB->get_record('modules', array('name' => 'arupadvert'))) {
            // Arup advert not installed.
            $this->_trigger_notice(get_string('arupadvertnotinstalled', 'tapsenrol', core_text::strtolower(get_string('course'))));
        } else {
            $sql = "SELECT cm.id, a.datatype
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                JOIN {arupadvert} a ON a.id = cm.instance
                WHERE m.name = :modulename AND cm.course = :courseid
                ";
            $params = array(
                'modulename' => 'arupadvert',
                'courseid' => $this->current->course
            );
            $arupadverts = $DB->get_records_sql($sql, $params);
            if (!$arupadverts) {
                // No Arup advert in this course.
                $this->_trigger_notice(get_string('arupadvertmissing', 'tapsenrol', core_text::strtolower(get_string('course'))));
            } else if (count($arupadverts) > 1) {
                $this->_trigger_notice(get_string('arupadverttoomany', 'tapscompletion', core_text::strtolower(get_string('course'))));
            } else {
                $arupadvert = array_shift($arupadverts);
                if ($arupadvert->datatype != 'taps') {
                    // Arup advert is not a TAPS one.
                    $this->_trigger_notice(get_string('arupadvertnottaps', 'tapsenrol', core_text::strtolower(get_string('course'))));
                }
            }
        }
    }

    protected function _tapsenrol_exists() {
        global $DB;

        if (!$DB->get_record('modules', array('name' => 'tapsenrol'))) {
            // Activity tapsenrol not installed.
            $this->_trigger_notice(get_string('tapsenrolnotinstalled', 'tapscompletion', core_text::strtolower(get_string('course'))));
        } else {
            $sql = "SELECT COUNT(*)
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                WHERE m.name = :modulename AND cm.course = :courseid
                ";
            $params = array(
                'modulename' => 'tapsenrol',
                'courseid' => $this->current->course
            );
            $tapsenrols = $DB->get_field_sql($sql, $params);
            if (!$tapsenrols) {
                // No tapsenrol in this course.
                $this->_trigger_notice(get_string('tapsenrolmissing', 'tapscompletion', core_text::strtolower(get_string('course'))));
            } else if ($tapsenrols > 1) {
                // Too many tapsenrols in this course.
                $this->_trigger_notice(get_string('tapsenroltoomany', 'tapscompletion', core_text::strtolower(get_string('course'))));
            }
        }
    }

    protected function _instances_exist() {
        global $DB;

        $sql = "SELECT COUNT(*)
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE m.name = :modulename AND cm.course = :courseid
            ";
        $params = array(
            'modulename' => 'tapscompletion',
            'courseid' => $this->current->course
        );
        $instancecount = $DB->get_field_sql($sql, $params);

        if (!$this->current->instance && $instancecount) {
            $this->_trigger_notice(get_string('alreadyexists:add', 'tapscompletion', core_text::strtolower(get_string('course'))));
        } else if ($instancecount > 1) {
            $this->_trigger_notice(get_string('alreadyexists:edit', 'tapscompletion', core_text::strtolower(get_string('course'))));
        }
    }

    protected function _taps_courses_select() {
        global $DB;
        $mform = $this->_form;

        $arupadvert = $DB->get_record('arupadvert', array('course' => $this->current->course));
        if ($arupadvert && $arupadvert->datatype == 'taps') {
            $tapscourseid = $DB->get_field('arupadvertdatatype_taps', 'tapscourseid', array('arupadvertid' => $arupadvert->id));
        } else if ($this->current->instance) {
            $tapscourseid = $DB->get_field('tapscompletion', 'tapscourse', array('id' => $this->current->instance));
        }

        $selectoptions = array();
        if ($tapscourseid) {
            $tapscoursesin = $DB->get_records_select(
                    'local_taps_course',
                    'courseid = :courseid',
                    array('courseid' => $tapscourseid),
                    '',
                    'courseid, courseregion, coursename, coursecode'
                    );
            $tapscourses = array();
            foreach ($tapscoursesin as $tapscoursein) {
                $coursecode = $tapscoursein->coursecode ? ' ['.$tapscoursein->coursecode.']' : '';
                $tapscourses[$tapscoursein->courseid] = $tapscoursein->courseregion .
                    ' - ' .
                    $tapscoursein->coursename .
                    $coursecode;
            }
            asort($tapscourses);
            $selectoptions = $selectoptions + $tapscourses;
        }
        if (empty($selectoptions)) {
            $selectoptions[''] = get_string('noapplicablecourses', 'tapscompletion');
        }

        $mform->addElement('select', 'tapscourse', get_string('tapscourse', 'tapscompletion'), $selectoptions, array('style' => 'max-width:100%'));
        $mform->addHelpButton('tapscourse', 'tapscourse', 'tapscompletion');
    }

    protected function _trigger_notice($message) {
        $url = new moodle_url('/course/view.php', array('id' => $this->current->course));
        notice($message, $url);
        exit;
    }
}