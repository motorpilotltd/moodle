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
 * The main aruphonestybox configuration form
 *
 * @package    mod_aruphonestybox
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_aruphonestybox_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('advcheckbox', 'manualindicate', 'User must manually indicate completion');
        $mform->setDefault('manualindicate', true);

        $mform->addElement('checkbox', 'showcompletiondate', get_string('showcompletiondate', 'mod_aruphonestybox'));
        $mform->setDefault('showcompletiondate', 0);
        $mform->disabledIf('showcompletiondate', 'manualindicate', 'notchecked');

        $mform->addElement('checkbox', 'showcertificateupload', get_string('showcertificateupload', 'mod_aruphonestybox'));
        $mform->setDefault('showcertificateupload', 0);
        $mform->disabledIf('showcertificateupload', 'manualindicate', 'notchecked');

        $mform->addElement('checkbox', 'approvalrequired', get_string('approvalrequired', 'mod_aruphonestybox'));
        $mform->setDefault('approvalrequired', 0);
        $mform->disabledIf('approvalrequired', 'manualindicate', 'notchecked');

        $mform->addElement('text', 'firstname', get_string('firstname'), array('size' => '64'));
        $mform->setType('firstname', PARAM_TEXT);


        $mform->addElement('text', 'lastname', get_string('lastname'), array('size' => '64'));
        $mform->setType('lastname', PARAM_TEXT);


        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_EMAIL);

        // FAKE field for completion settings.
        $mform->addElement('static', 'completionfake', '', '');

        $this->standard_intro_elements();

        $this->add_taps_fields($mform);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function add_taps_fields(MoodleQuickForm $mform) {
        $taps = new \local_taps\taps();

        $mform->addElement('header', 'tapstemplate', get_string('cpdformheader', 'mod_aruphonestybox'));

        $mform->addElement('text', 'classname', get_string('cpd:classname', 'block_arup_mylearning'));
        $mform->setType('classname', PARAM_TEXT);
        $mform->addRule('classname', null, 'required', null, 'client');

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->addRule('provider', null, 'required', null, 'client');

        $mform->addElement('text', 'duration', get_string('cpd:duration', 'block_arup_mylearning'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->addRule('duration', null, 'required', null, 'client');
        $mform->addElement('select', 'durationunitscode', get_string('cpd:durationunitscode', 'block_arup_mylearning'), $taps->get_durationunitscode());
        $mform->addRule('durationunitscode', null, 'required', null, 'client');

        $mform->addElement('editor', 'learningdesc', get_string('cpd:learningdesc', 'block_arup_mylearning'));
        $mform->setType('learningdesc', PARAM_RAW);

        $mform->addElement('text', 'location', get_string('cpd:location', 'block_arup_mylearning'));
        $mform->setType('location', PARAM_TEXT);
        $mform->setAdvanced('location');

        $mform->addElement('select', 'classtype', get_string('cpd:classtype', 'block_arup_mylearning'), $taps->get_classtypes('cpd'));
        $mform->setAdvanced('classtype');

        $mform->addElement('select', 'classcategory', get_string('cpd:classcategory', 'block_arup_mylearning'), $taps->get_classcategory());
        $mform->setAdvanced('classcategory');

        $mform->addElement('select', 'healthandsafetycategory', get_string('cpd:healthandsafetycategory', 'block_arup_mylearning'), $taps->get_healthandsafetycategory());
        $mform->setAdvanced('healthandsafetycategory');

        $mform->addElement('text', 'classcost', get_string('cpd:classcost', 'block_arup_mylearning'));
        $mform->setType('classcost', PARAM_TEXT);
        $mform->setAdvanced('classcost');

        $mform->addElement('select', 'classcostcurrency', get_string('cpd:classcostcurrency', 'block_arup_mylearning'), $taps->get_classcostcurrency());
        $mform->setAdvanced('classcostcurrency');

        $mform->addElement('date_selector', 'classstartdate', get_string('cpd:classstartdate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('classstartdate');

        $mform->addElement('text', 'certificateno', get_string('cpd:certificateno', 'block_arup_mylearning'));
        $mform->setType('certificateno', PARAM_TEXT);
        $mform->setAdvanced('certificateno');

        $mform->addElement('date_selector', 'expirydate', get_string('cpd:expirydate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('expirydate');
    }

    public function add_completion_rules() {
        return array('completionfake');
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        if(empty($data->showcompletiondate)) {
            $data->showcompletiondate = 0;
        }

        if(empty($data->showcertificateupload)) {
            $data->showcertificateupload = 0;
        }

        if(empty($data->approvalrequired)) {
            $data->approvalrequired = 0;
        }

        return $data;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if(!empty($data['approvalrequired'])) {
            if (empty($data['email'])) {
                // Email will be validated by PARAM_EMAIL usage in form (which will return empty string if not valid.
                $errors['email'] = get_string('invalidemail');
            }
            if(empty($data['firstname'])) {
                $errors['firstname'] = get_string('error:required', 'mod_aruphonestybox');
            }

            if(empty($data['lastname'])) {
                $errors['lastname'] = get_string('error:required', 'mod_aruphonestybox');
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
