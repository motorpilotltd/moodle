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

class mod_tapsenrol_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        // Check for arupadvert activity (suite driver) presence.
        $this->_arupadvert_exists();

        // Check enrolment plugins are as required.
        $this->_check_enrolment_plugins();

        // Check groups are setup as required.
        $this->_check_groups();

        // Check in case there are multiple instances.
        $this->_instances_exist();

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'tapsenrol'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->_taps_courses_select();

        $options = array('' => get_string('choosedots'));
        $iws = $DB->get_records_menu('tapsenrol_iw', null, 'name ASC', 'id, name');
        $mform->addElement('select', 'internalworkflowid', get_string('internalworkflow', 'tapsenrol'), $options + $iws);
        $mform->addRule('internalworkflowid', null, 'required', null, 'client');
        $mform->addHelpButton('internalworkflowid', 'internalworkflow', 'tapsenrol');

        $regionoptions =
            array(0 => get_string('global', 'local_regions')) +
            $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
        $size = min(array(count($regionoptions), 10));
        $headerstring = empty($this->_instance) ? get_string('enrolment_region_mapping', 'tapsenrol') : get_string('regions:enrolment', 'tapsenrol');
        $mform->addElement('header', 'enrolment_region_mapping', get_string('regions:enrolment', 'tapsenrol'));
        $mform->setExpanded('enrolment_region_mapping', true, true);
        $region = &$mform->addElement('select', 'region', get_string('regions', 'local_regions'), $regionoptions, array('size' => $size, 'style' => 'min-width:200px'));
        $region->setMultiple(true);
        if (empty($this->_instance)) {
            $hint = html_writer::tag('div', get_string('overrideregions', 'local_taps'), array('class' => 'felement fselect'));
            $mform->addElement('html', html_writer::tag('div', $hint, array('class' => 'fitem')));
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function set_data($data) {
        global $DB;

        if (!empty($this->_instance)) {
            $data->internalworkflowid = $data->internalworkflowid == 0 ? -1 : $data->internalworkflowid;
            $data->region = $DB->get_records_menu('tapsenrol_region', array('tapsenrolid' => $this->_instance), '', 'regionid as id, regionid as id2');
            if (empty($data->region)) {
                $data->region = array(0);
            }
        }

        parent::set_data($data);
    }

    public function definition_after_data() {
        parent::definition_after_data();

        $mform =& $this->_form;

        if ($this->_cm && $mform->exportValue('internalworkflowid') > 0) {
            $url = new moodle_url('/mod/tapsenrol/admin/emails.php', array('cm' => $this->_cm->id, 'course' => $this->_cm->course));
            $editcaps = array(
                'mod/tapsenrol:internalworkflow_edit_activity',
                'mod/tapsenrol:internalworkflow_edit',
                'mod/tapsenrol:internalworkflow'
            );
            $linktext = has_any_capability($editcaps, $this->context) ? get_string('iw:emails:title:cm:edit', 'tapsenrol') : get_string('iw:emails:title:cm:view', 'tapsenrol');
            $link = html_writer::link($url, $linktext, array('class' => 'btn btn-primary'));
            $mform->insertElementBefore($mform->createElement('html', html_writer::tag('p', $link)), 'general');
        }

        $changecaps = array('mod/tapsenrol:internalworkflow_change', 'mod/tapsenrol:internalworkflow_edit', 'mod/tapsenrol:internalworkflow');
        if (!empty($this->_instance) && !has_any_capability($changecaps, $this->context)) {
            $mform->freeze('internalworkflowid');
        }
    }

    public function validation($data, $files) {
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
        if (empty($data->completionenrolment) || !$autocompletion) {
            $data->completionenrolment = 0;
        }
        return $data;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->setDefault('completion', COMPLETION_TRACKING_AUTOMATIC);

        $mform->addElement('checkbox', 'completionenrolment', '', get_string('completionenrolment', 'tapsenrol'));
        $mform->setDefault('completionenrolment', true);

        return array('completionenrolment');
    }

    public function completion_rule_enabled($data) {
        return !empty($data['completionenrolment']);
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
                $this->_trigger_notice(get_string('arupadverttoomany', 'tapsenrol', core_text::strtolower(get_string('course'))));
            } else {
                $arupadvert = array_shift($arupadverts);
                if ($arupadvert->datatype != 'taps') {
                    // Arup advert is not a TAPS one.
                    $this->_trigger_notice(get_string('arupadvertnottaps', 'tapsenrol', core_text::strtolower(get_string('course'))));
                }
            }
        }
    }

    protected function _check_enrolment_plugins() {
        $basemessage = html_writer::empty_tag('br') . get_string('enrolplugins:requirements', 'tapsenrol');

        // Must be manual, self (not auto), guest.
        $self = new stdClass();
        $self->field = 'customchar1';
        $self->logic = 'notequal';
        $self->value = 'y';
        $requiredenrolments = array(
            'manual' => array(),
            'self' => array($self),
            'guest' => array(),
        );

        $enrolinstances = enrol_get_instances($this->current->course, true);
        if (count($enrolinstances) != count($requiredenrolments)) {
            $this->_trigger_notice(get_string('enrolplugins:countmismatch', 'tapsenrol').$basemessage);
        }
        foreach ($requiredenrolments as $enrolplugin => $requirements) {
            $current = array_shift($enrolinstances);
            if ($current->enrol != $enrolplugin) {
                $this->_trigger_notice(get_string('enrolplugins:incorrectorder', 'tapsenrol').$basemessage);
            } else {
                foreach ($requirements as $requirement) {
                    switch ($requirement->logic) {
                        case 'notequal' :
                            if (!($current->{$requirement->field} != $requirement->value)) {
                                $this->_trigger_notice(get_string('enrolplugins:incorrectsettings', 'tapsenrol').$basemessage);
                            }
                            break;
                    }
                }
            }
        }
    }

    protected function _check_groups() {
        global $DB;

        $course = $DB->get_record('course', array('id' => $this->current->course));
        if ($course) {
            if ($course->groupmode != 2 || $course->defaultgroupingid != 0) {
                $this->_trigger_notice(
                    get_string('groups:requirements', 'tapsenrol', core_text::strtolower(get_string('course')))
                );
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
            'modulename' => 'tapsenrol',
            'courseid' => $this->current->course
        );
        $instancecount = $DB->get_field_sql($sql, $params);

        if (!$this->current->instance && $instancecount) {
            $this->_trigger_notice(get_string('alreadyexists:add', 'tapsenrol', core_text::strtolower(get_string('course'))));
        } else if ($instancecount > 1) {
            $this->_trigger_notice(get_string('alreadyexists:edit', 'tapsenrol', core_text::strtolower(get_string('course'))));
        }
    }

    protected function _taps_courses_select() {
        global $DB;
        $mform = $this->_form;

        $arupadvert = $DB->get_record('arupadvert', array('course' => $this->current->course));
        if ($arupadvert && $arupadvert->datatype == 'taps') {
            $tapscourseid = $DB->get_field('arupadvertdatatype_taps', 'tapscourseid', array('arupadvertid' => $arupadvert->id));
        } else if ($this->current->instance) {
            $tapscourseid = $DB->get_field('tapsenrol', 'tapscourse', array('id' => $this->current->instance));
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
            $selectoptions[''] = get_string('noapplicablecourses', 'tapsenrol');
        }

        $mform->addElement('select', 'tapscourse', get_string('tapscourse', 'tapsenrol'), $selectoptions, array('style' => 'max-width:100%'));
        $mform->addHelpButton('tapscourse', 'tapscourse', 'tapsenrol');
    }

    protected function _trigger_notice($message) {
        $url = new moodle_url('/course/view.php', array('id' => $this->current->course));
        notice($message, $url);
        exit;
    }
}