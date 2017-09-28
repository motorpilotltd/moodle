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

class block_arup_mylearning_editcpd_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $taps = $this->_customdata['taps'];

        $mform->addElement('header', 'cpdheader', get_string('cpd:header', 'block_arup_mylearning'));

        $mform->addElement('text', 'classname', get_string('cpd:classname', 'block_arup_mylearning'));
        $mform->setType('classname', PARAM_TEXT);
        $mform->addRule('classname', null, 'required', null, 'client');

        $mform->addElement('select', 'classtype', get_string('cpd:classtype', 'block_arup_mylearning'), $taps->get_classtypes('cpd'));
        $mform->addRule('classtype', null, 'required', null, 'client');

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->addRule('provider', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'classcompletiondate', get_string('cpd:classcompletiondate', 'block_arup_mylearning'), array('timezone' => 0));
        $mform->addRule('classcompletiondate', null, 'required', null, 'client');

        $mform->addElement('text', 'duration', get_string('cpd:duration', 'block_arup_mylearning'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->addRule('duration', null, 'required', null, 'client');
        $mform->addRule('duration', null, 'numeric', null, 'client');

        $mform->addElement('select', 'durationunitscode', get_string('cpd:durationunitscode', 'block_arup_mylearning'), $taps->get_durationunitscode());
        $mform->addRule('durationunitscode', null, 'required', null, 'client');

        $mform->addElement('text', 'location', get_string('cpd:location', 'block_arup_mylearning'));
        $mform->setType('location', PARAM_TEXT);
        $mform->setAdvanced('location');

        $mform->addElement('select', 'classcategory', get_string('cpd:classcategory', 'block_arup_mylearning'), $taps->get_classcategory());

        $mform->addElement('text', 'classcost', get_string('cpd:classcost', 'block_arup_mylearning'));
        $mform->setType('classcost', PARAM_TEXT);
        $mform->addRule('classcost', null, 'numeric', null, 'client');

        $mform->addElement('select', 'classcostcurrency', get_string('cpd:classcostcurrency', 'block_arup_mylearning'), $taps->get_classcostcurrency());

        $mform->addElement(
                'select',
                'healthandsafetycategory',
                get_string('cpd:healthandsafetycategory', 'block_arup_mylearning'),
                $taps->get_healthandsafetycategory()
                );
        $mform->setAdvanced('healthandsafetycategory');

        $mform->addElement('date_selector', 'classstartdate', get_string('cpd:classstartdate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('classstartdate');

        $mform->addElement('text', 'certificateno', get_string('cpd:certificateno', 'block_arup_mylearning'));
        $mform->setType('certificateno', PARAM_TEXT);
        $mform->setAdvanced('certificateno');

        $mform->addElement('date_selector', 'expirydate', get_string('cpd:expirydate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('expirydate');

        $mform->addElement('editor', 'learningdesc', get_string('cpd:learningdesc', 'block_arup_mylearning'));
        $mform->setType('learningdesc', PARAM_RAW);

        $mform->addElement('hidden', 'cpdid');
        $mform->setType('cpdid', PARAM_INT);

        $this->add_action_buttons('true', get_string($this->_customdata['action'].'cpd:save', 'block_arup_mylearning'));
    }
}