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
 * The main arupapplication configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_arupapplication
 * @copyright  2014 Epic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_arupapplication_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static', 'label1', '', get_string('instanceconfiguration_hint', 'arupapplication'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('instancename', 'arupapplication'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields
        $this->standard_intro_elements();

        //-------------------------------------------------------------------------------
        // Adding the rest of arupapplication settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic

        $mform->addElement('header', 'arupapplicationfieldset', get_string('arupapplicationfieldset', 'arupapplication'));

        $mform->addElement('advcheckbox', 'technicalreferencereq', get_string('technicalreferencereq', 'arupapplication'), '', array('group' => 0), array(0, 1));
        $mform->setDefault('technicalreferencereq', true);
        $mform->addElement('advcheckbox', 'sponsorstatementreq', get_string('sponsorstatementreq', 'arupapplication'), '', array('group' => 0), array(0, 1));
        $mform->setDefault('sponsorstatementreq', true);

        $mform->addElement('textarea', 'sponsordeclarationlabel', get_string('sponsordeclarationlabel', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('sponsordeclarationlabel', PARAM_RAW);
        $mform->disabledIf('sponsordeclarationlabel', 'sponsorstatementreq', 'notchecked');

        $mform->addElement('textarea', 'refereemessage_hint', get_string('refereemessagehint', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('refereemessage_hint', PARAM_RAW);
        $mform->addHelpButton('refereemessage_hint', 'refereemessagehint', 'arupapplication');
        $mform->disabledIf('refereemessage_hint', 'technicalreferencereq', 'notchecked');

        $mform->addElement('textarea', 'email_referee_footer', get_string('email:referee:footer', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('email_referee_footer', PARAM_RAW);
        $mform->addHelpButton('email_referee_footer', 'email:referee:footer', 'arupapplication');
        $mform->disabledIf('email_referee_footer', 'technicalreferencereq', 'notchecked');

        $mform->addElement('textarea', 'sponsormessage_hint', get_string('sponsormessage_hint', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('sponsormessage_hint', PARAM_RAW);
        $mform->addHelpButton('sponsormessage_hint', 'sponsormessage_hint', 'arupapplication');
        $mform->disabledIf('sponsormessage_hint', 'sponsorstatementreq', 'notchecked');

        $mform->addElement('textarea', 'email_sponsor_footer', get_string('email:sponsor:footer', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('email_sponsor_footer', PARAM_RAW);
        $mform->addHelpButton('email_sponsor_footer', 'email:sponsor:footer', 'arupapplication');
        $mform->disabledIf('email_sponsor_footer', 'sponsorstatementreq', 'notchecked');

        $mform->addElement('textarea', 'submission_hint', get_string('submission_hint', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('submission_hint', PARAM_RAW);
        $mform->addHelpButton('submission_hint', 'submission_hint', 'arupapplication');
        $mform->addRule('submission_hint', get_string('error:required', 'arupapplication'), 'required', null, 'client', false, false);

        $mform->addElement('textarea', 'reference_hint', get_string('reference_hint', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('reference_hint', PARAM_RAW);
        $mform->addHelpButton('reference_hint', 'reference_hint', 'arupapplication');
        $mform->disabledIf('reference_hint', 'technicalreferencereq', 'notchecked');

        $mform->addElement('textarea', 'sponsorstatement_hint', get_string('sponsorstatement_hint', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('sponsorstatement_hint', PARAM_RAW);
        $mform->addHelpButton('sponsorstatement_hint', 'sponsorstatement_hint', 'arupapplication');
        $mform->disabledIf('sponsorstatement_hint', 'sponsorstatementreq', 'notchecked');

        $mform->addElement('textarea', 'footer', get_string('footer', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('footer', PARAM_RAW);
        $mform->addHelpButton('footer', 'footer', 'arupapplication');
        $mform->addRule('footer', get_string('error:required', 'arupapplication'), 'required', null, 'client', false, false);

        $mform->addElement('textarea', 'email_startnotification', get_string('email:applicant:startnotification', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('email_startnotification', PARAM_RAW);
        $mform->addHelpButton('email_startnotification', 'email:applicant:startnotification', 'arupapplication');
        $mform->addRule('email_startnotification', get_string('error:required', 'arupapplication'), 'required', null, 'client', false, false);

        $mform->addElement('textarea', 'email_submissionnotification', get_string('email:applicant:submissionnotification', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('email_submissionnotification', PARAM_RAW);
        $mform->addHelpButton('email_submissionnotification', 'email:applicant:submissionnotification', 'arupapplication');
        $mform->addRule('email_submissionnotification', get_string('error:required', 'arupapplication'), 'required', null, 'client', false, false);

        $mform->addElement('textarea', 'email_completenotification', get_string('email:applicant:completenotification', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('email_completenotification', PARAM_RAW);
        $mform->addHelpButton('email_completenotification', 'email:applicant:completenotification', 'arupapplication');
        $mform->addRule('email_completenotification', get_string('error:required', 'arupapplication'), 'required', null, 'client', false, false);

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox',
                           'completionsubmit',
                           '',
                           get_string('completionsubmit', 'arupapplication'));
        return array('completionsubmit');
    }

    public function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        $autocompletion = !empty($data->completion) AND
                                $data->completion==COMPLETION_TRACKING_AUTOMATIC;
        if (empty($data->completion) || !$autocompletion) {
            $data->completionsubmit=0;
        }
        if (empty($data->completionsubmit)) {
            $data->completionsubmit=0;
        }
        return $data;
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        if ($data['sponsorstatementreq'] == 1) {
            if (empty($data['sponsordeclarationlabel'])) {
                $errors['sponsordeclarationlabel'] = get_string('error:required', 'arupapplication');
            }
            if (empty($data['sponsormessage_hint'])) {
                $errors['sponsormessage_hint'] = get_string('error:required', 'arupapplication');
            }
            if (empty($data['email_sponsor_footer'])) {
                $errors['email_sponsor_footer'] = get_string('error:required', 'arupapplication');
            } elseif (strpos($data['email_sponsor_footer'], '[[link]]') == FALSE && strpos($data['email_sponsor_footer'], '[[linkurl]]') == FALSE) {
                $errors['email_sponsor_footer'] = get_string('error:email', 'arupapplication');
            }
            if (empty($data['sponsorstatement_hint'])) {
               $errors['sponsorstatement_hint'] = get_string('error:required', 'arupapplication');
            }
        }

        if (strpos($data['email_referee_footer'], '[[link]]') == FALSE && strpos($data['email_referee_footer'], '[[linkurl]]') == FALSE) {
            $errors['email_referee_footer'] = get_string('error:email', 'arupapplication');
        }

        if (strpos($data['email_startnotification'], '[[link]]') == FALSE && strpos($data['email_startnotification'], '[[linkurl]]') == FALSE) {
            $errors['email_startnotification'] = get_string('error:email', 'arupapplication');
        }
        if (strpos($data['email_submissionnotification'], '[[link]]') == FALSE && strpos($data['email_submissionnotification'], '[[linkurl]]') == FALSE) {
            $errors['email_submissionnotification'] = get_string('error:email', 'arupapplication');
        }
        if (strpos($data['email_completenotification'], '[[link]]') == FALSE && strpos($data['email_completenotification'], '[[linkurl]]') == FALSE) {
            $errors['email_completenotification'] = get_string('error:email', 'arupapplication');
        }
        return $errors;
    }
}
