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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/lib/formslib.php');

class signup_form extends moodleform {
    /**
     * The form definition
     */
    function definition () {
        global $USER, $OUTPUT;

        $mform = $this->_form;

        $lal = $this->_customdata['lal'];


        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('action', $USER->id);

        $mform->addElement('hidden', 'backto');
        $mform->setType('backto', PARAM_ALPHA);


        $action = $this->_customdata['action'];
        $this->set_data(array('action' => $action, 'backto' => optional_param('backto', '', PARAM_ALPHA)));

        if ($action == 'signup' || $action == 'edit') {

            $sessioninfo = $lal->sessioninfo;

            if (!empty($sessioninfo)) {
                $mform->addElement('static', 'sessioninfo', '', $sessioninfo);
                $mform->setType('sessioninfo', PARAM_RAW);
            }

            $mform->addElement('textarea', 'requirements', get_string('eventrequirements','local_lunchandlearn'), array('cols' => 40, 'rows' => 5));
            $mform->setType('requirements', PARAM_RAW);
            $mform->addHelpButton('requirements', 'eventrequirements', 'local_lunchandlearn');

            $radioarray=array();
            if ($lal->attendeemanager->availableinperson) {
                $nocapacity = '';
                $attrs = array();
                $icon = '-full';
                if (false === $lal->attendeemanager->has_inperson_capacity()) {
                    $attrs['disabled'] = 'disabled';
                    $nocapacity = get_string('nocapacity', 'local_lunchandlearn');
                    $icon = '-full';

                    // add warning
                    $mform->addElement('static', 'warningonline', '', html_writer::div(get_string('warn:onlineonly', 'local_lunchandlearn'), 'alert alert-warning', array('role' => 'alert')));
                    $mform->setType('warningonline', PARAM_RAW);
                }
                $radioarray[] =& $mform->createElement('radio', 'inperson', '', $lal->get_fa_icon(lunchandlearn::ICON_INPERSON) . $nocapacity, 1, $attrs);
            }
            if ($lal->attendeemanager->availableonline) {
                $nocapacity = '';
                $attrs = array();
                $icon = '-full';
                if (false === $lal->attendeemanager->has_online_capacity()) {
                    $attrs['disabled'] = 'disabled';
                    $nocapacity = get_string('nocapacity', 'local_lunchandlearn');
                    $icon = '-full';

                     // add warning
                    $mform->addElement('static', 'warninginperson', '', html_writer::div(get_string('warn:inpersononly', 'local_lunchandlearn'), 'alert alert-warning', array('role' => 'alert')));
                    $mform->setType('warningonline', PARAM_RAW);
                }
                $radioarray[] =& $mform->createElement('radio', 'inperson', '', $lal->get_fa_icon(lunchandlearn::ICON_ONLINE) . $nocapacity, 0, $attrs);
            }
            $mform->addGroup($radioarray, 'radioar', get_string('eventinperson','local_lunchandlearn'), array(' '), false);
            $mform->setType('inperson', PARAM_INT);
            $mform->setDefault('inperson', $lal->attendeemanager->has_inperson_capacity() ? 1 : 0);

        } else if ($action == 'cancel' && $USER->id != $lal->userid) {
            $mform->addElement('textarea', 'notes', get_string('eventnotescancel','local_lunchandlearn'), array('cols' => 40, 'rows' => 5));
            $mform->setType('notes', PARAM_RAW);
            $mform->addHelpButton('notes', 'eventnotescancel', 'local_lunchandlearn');
        }

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('confirmevent'.$action, 'local_lunchandlearn'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('donotconfirmevent'.$action, 'local_lunchandlearn'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

    }
}