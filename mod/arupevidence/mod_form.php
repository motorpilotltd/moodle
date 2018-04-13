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

/**
 * The main arupevidence configuration form
 *
 * @package    mod_arupevidence
 * @copyright  2017 Xantico Ltd 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_arupevidence_mod_form extends moodleform_mod {

    protected $cm = null;

    public function __construct($current, $section, $cm, $course) {
        $this->cm = $cm;
        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Defines forms elements
     */
    public function definition() {

        global $CFG, $PAGE, $COURSE, $DB;

        $mform = $this->_form;

        // Required CSS and JS.
        $PAGE->requires->css(new moodle_url('/mod/arupevidence/css/select2.min.css'));
        $PAGE->requires->css(new moodle_url('/mod/arupevidence/css/select2-bootstrap.min.css'));
        $PAGE->requires->string_for_js('alert:restrictedaccess:tooltip', 'mod_arupevidence');

        $arguments = array(
            'courseid' => $COURSE->id
        );
        $PAGE->requires->js_call_amd('mod_arupevidence/enhance', 'initialise', $arguments);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('instructions', 'mod_arupevidence'));
        $mform->addRule('introeditor', null, 'required', null, 'client');

        $mform->addElement('advcheckbox', 'requireexpirydate', get_string('requireexpirydate', 'mod_arupevidence'));
        $mform->disabledIf('requireexpirydate', 'requirevalidityperiod', 'checked');

        $mform->addElement('advcheckbox', 'mustendmonth', get_string('mustendmonth', 'mod_arupevidence'));
        $mform->disabledIf('mustendmonth', 'requirevalidityperiod', 'checked');
        $mform->disabledIf('mustendmonth', 'requireexpirydate', 'notchecked');

        $mform->addElement('advcheckbox', 'requirevalidityperiod', get_string('requirevalidityperiod', 'mod_arupevidence'));
        $mform->disabledIf('requirevalidityperiod', 'requireexpirydate', 'checked');

        $choices = array(get_string('none'), 1,2,3,4,5,6,7,8,9,10,11,12);
        $mform->addElement('select', 'expectedvalidityperiod', get_string('expectedvalidityperiod', 'mod_arupevidence'), $choices);
        $mform->setDefault('expectedvalidityperiod', '');

        $mform->addElement('select', 'expectedvalidityperiodunit', '', array('m' => 'Month(s)', 'y' => 'Year(s)', '' => get_string('none')));
        $mform->setDefault('expectedvalidityperiodunit', '');

        $mform->addElement('checkbox', 'approvalrequired', get_string('approvalrequired', 'mod_arupevidence'));
        $mform->setDefault('approvalrequired', 0);

        $context = context_course::instance($this->current->course);
        $roles = get_roles_used_in_context($context);
        $userroles = array();
        foreach ($roles as $r) {
            $userroles[$r->id] = $r->shortname;
        }
        $mform->addElement(
            'select',
            'approvalrole',
            get_string('approvalroles','mod_arupevidence'),
            array(''=>get_string('none')) + $userroles
        );
        $mform->disabledIf('approvalrole', 'approvalrequired', 'unchecked');
        $mform->setDefault('approvalrole', '');

        $users = $mform->addElement(
            'select',
            'approvalusers',
            get_string('approvalusers', 'mod_arupevidence'),
            array('' => ''),
            array('class' => 'select2 select2-user', 'data-placeholder' => get_string('chooseusers', 'mod_arupevidence'))
        );
        $mform->disabledIf('approvalrole', 'approvalrequired', 'unchecked');
        $users->setMultiple(true);

        $choices = array(ARUPEVIDENCE_CPD => get_string('arupevidence_cpd', 'mod_arupevidence'), ARUPEVIDENCE_LMS => get_string('arupevidence_lms', 'mod_arupevidence'));
        $mform->addElement(
            'select',
            'cpdlms',
            get_string('cpdorlms', 'mod_arupevidence'),
            $choices + array('' => get_string('none'))
        );
        $mform->addRule('cpdlms', null, 'required', null, 'client');
        $mform->setDefault('cpdlms', '');

        $mform->addElement('checkbox', 'setcoursecompletion', get_string('setcoursecompletion', 'mod_arupevidence'));
        $mform->addHelpButton('setcoursecompletion', 'setcoursecompletion', 'mod_arupevidence');
        $mform->setDefault('setcoursecompletion', 1);

        $mform->addElement('checkbox', 'setcertificationcompletion', get_string('setcertificationcompletion', 'mod_arupevidence'));
        $mform->addHelpButton('setcertificationcompletion', 'setcertificationcompletion', 'mod_arupevidence');
        $mform->setDefault('setcertificationcompletion', 1);

        // Add hidden fields
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
        // FAKE field for completion settings.
        $mform->addElement('static', 'completionfake', '', '');

        $this->add_taps_fields($mform);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function add_taps_fields(MoodleQuickForm $mform) {
        $taps = new \local_taps\taps();

        $mform->addElement('header', 'tapstemplate', get_string('cpdformheader', 'mod_arupevidence'));

        $mform->addElement('text', 'classname', get_string('cpd:classname', 'block_arup_mylearning'));
        $mform->setType('classname', PARAM_TEXT);
        $mform->disabledIf('classname', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->disabledIf('provider', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);

        $mform->addElement('text', 'duration', get_string('cpd:duration', 'block_arup_mylearning'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->disabledIf('duration', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);

        $mform->addElement('select', 'durationunitscode', get_string('cpd:durationunitscode', 'block_arup_mylearning'), $taps->get_durationunitscode());
        $mform->disabledIf('durationunitscode', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);


        $mform->addElement('editor', 'learningdesc', get_string('cpd:learningdesc', 'block_arup_mylearning'));
        $mform->setType('learningdesc', PARAM_RAW);

    }

    public function add_completion_rules() {
        return array('completionfake');
    }

    public function set_data($defaultvalues) {
        global $DB;

        if (!empty($this->cm->instance)) {
            $arupevidence = $DB->get_record('arupevidence',  array('id' => $this->cm->instance));
            $approvalusers = json_decode($arupevidence->approvalusers);
            if (!empty($approvalusers)) {
                $userlists = array();
                list($in, $params) = $DB->get_in_or_equal($approvalusers);

                $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
                $userlists = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,'.$usertextconcat.' AS fullname');

                $select = $this->_form->getElement('approvalusers');
                foreach ($userlists as $value => $text) {
                    $select->addOption($text, $value, array('selected' => 'selected'));
                }
                unset($defaultvalues->{'approvalusers'});
            }
        }


        parent::set_data($defaultvalues);
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if(empty($data->approvalrequired) || empty($_POST['approvalrequired'])) {
            $data->approvalrequired = 0;
        }

        if(empty($data->requireexpirydate) || empty($_POST['requireexpirydate'])) {
            $data->requireexpirydate = 0;
        }

        if(empty($data->mustendmonth) || empty($_POST['mustendmonth'])) {
            $data->mustendmonth = 0;
        }

        if(empty($data->requirevalidityperiod) || empty($_POST['requirevalidityperiod'])) {
            $data->requirevalidityperiod = 0;
        }

        $data->approvalusers = is_array($_POST['approvalusers']) ? optional_param_array('approvalusers', array(), PARAM_INT) : array();
        $data->approvalusers = json_encode($data->approvalusers);
        return $data;
    }

    public function validation($data, $files) {
        global $DB, $CFG, $COURSE;
        $errors = parent::validation($data, $files);
        if (isset($data['cpdlms']) && $data['cpdlms'] == ARUPEVIDENCE_CPD) {
            if (empty($data['classname'])) {
                $errors['classname'] = get_string('error:cpdrequired', 'mod_arupevidence');
            }

            if (empty($data['provider'])) {
                $errors['provider'] = get_string('error:cpdrequired', 'mod_arupevidence');
            }

            if (empty($data['duration'])) {
                $errors['duration'] = get_string('error:cpdrequired', 'mod_arupevidence');
            }

            if (empty($data['durationunitscode'])) {
                $errors['durationunitscode'] = get_string('error:cpdrequired', 'mod_arupevidence');
            }

            if (empty($data['learningdesc']['text'])) {
                $errors['learningdesc'] = get_string('error:cpdrequired', 'mod_arupevidence');
            }

        } else if(isset($data['cpdlms']) && $data['cpdlms'] == ARUPEVIDENCE_LMS) {
            $tapsenrols = $DB->get_records('tapsenrol', array('course' => $COURSE->id));

            $islinkedcourse = false;
            if (count($tapsenrols) != 0) {
                require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');
                $tapsenrol = new \tapsenrol(reset($tapsenrols)->id, 'instance');

                if ($tapsenrol->check_installation()) {
                    $islinkedcourse = true;
                }
            }

            if (!$islinkedcourse) {
                $errors['cpdlms'] = get_string('error:mustlinkedcourse', 'mod_arupevidence');
            }
        }
        return $errors;
    }

    public function completion_rule_enabled($data) {
        return (true);
    }

    public function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $default_values['learningdesc'] = ['text' => $default_values['learningdesc'], 'format' => FORMAT_HTML];
        }
    }
}
