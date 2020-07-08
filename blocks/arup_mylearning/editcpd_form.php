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

        $mform->addElement('text', 'originname', get_string('cpd:originname', 'block_arup_mylearning'));
        $mform->setType('originname', PARAM_TEXT);
        $mform->addRule('originname', null, 'required', null, 'client');

        $classtypes = $taps->get_classtypes('cpd');
        // Unset unwanted values.
        unset($classtypes['LE']);
        unset($classtypes['LB']);
        $mform->addElement('select', 'classtype', get_string('cpd:classtype', 'block_arup_mylearning'), $classtypes);
        $mform->addRule('classtype', null, 'required', null, 'client');

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->addRule('provider', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'completiontime', get_string('cpd:completiontime', 'block_arup_mylearning'), array('timezone' => 0));
        $mform->addRule('completiontime', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'starttime', get_string('cpd:starttime', 'block_arup_mylearning'), array('timezone' => 0, 'optional' => 1));

        $mform->addElement('date_selector', 'endtime', get_string('cpd:endtime', 'block_arup_mylearning'), array('timezone' => 0, 'optional' => 1));

        $mform->addElement('text', 'duration', get_string('duration', 'local_taps').get_string('durationcode', 'local_taps'), 'size="5"');
        $mform->setType('duration', PARAM_TEXT);
        $mform->addHelpButton('duration', 'duration', 'local_taps');
        $mform->addRule('duration', null, 'required', null, 'client');

        $mform->addElement('select', 'durationunits', get_string('cpd:durationunits', 'block_arup_mylearning'), $taps->get_durationunitscode());
        $mform->addRule('durationunits', null, 'required', null, 'client');

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

        $mform->addElement('text', 'certificateno', get_string('cpd:certificateno', 'block_arup_mylearning'));
        $mform->setType('certificateno', PARAM_TEXT);
        $mform->setAdvanced('certificateno');

        $mform->addElement('date_selector', 'expirydate', get_string('cpd:expirydate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('expirydate');

        $mform->addElement('editor', 'description', get_string('cpd:description', 'block_arup_mylearning'));
        $mform->setType('description', PARAM_CLEANHTML);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons('true', get_string($this->_customdata['action'].'cpd:save', 'block_arup_mylearning'));
    }

    public function validation($data, $files) {
        $errors = [];
        if (!empty($data['duration'])) {
            $time = explode(':', $data['duration']);
            if (count($time) > 2) {
                $errors['duration'] = get_string('validation:durationformatincorrect', 'local_taps').get_string('durationcode', 'local_taps');
            } elseif (isset($time[1]) && ($time[1] < 0 || $time[1] > 59 || !is_numeric($time[1]))) {
                $errors['duration'] = get_string('validation:durationinvalidminutes', 'local_taps').get_string('durationcode', 'local_taps');
            } elseif ((isset($time[0]) && ((int)$time[0] != $time[0] || $time[0] < 0))) {
                $errors['duration'] = get_string('validation:durationinvalidhours', 'local_taps').get_string('durationcode', 'local_taps');
            }
        }

        return $errors;
    }

}