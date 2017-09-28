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

class internalworkflow_form extends moodleform {

    public function definition() {
        global $DB;

        $mform =& $this->_form;

        if ($this->_customdata['locked']) {
            global $PAGE;
            $renderer = $PAGE->get_renderer('mod_tapsenrol');
            $mform->addElement('html', $renderer->alert(get_string('iw:editing:locked', 'tapsenrol'), 'alert-warning', false));
        }

        // GENERAL.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('iw:name', 'tapsenrol'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'emailsoff', get_string('iw:emailsoff', 'tapsenrol'));
        $mform->setDefault('emailsoff', 1);

        $regionoptions =
            array(0 => get_string('global', 'local_regions')) +
            $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
        $mform->addElement('select', 'regionid', get_string('region', 'local_regions'), $regionoptions);

        $enroltypeoptions = array(
            'enrol' => get_string('iw:enroltype:enrol', 'tapsenrol'),
            'apply' => get_string('iw:enroltype:apply', 'tapsenrol'),
        );
        $mform->addElement('select', 'enroltype', get_string('iw:enroltype', 'tapsenrol'), $enroltypeoptions);
        $mform->setDefault('enroltype', 'enrol');
        $mform->addHelpButton('enroltype', 'iw:enroltype', 'tapsenrol');

        $mform->addElement('checkbox', 'approvalrequired', get_string('iw:approvalrequired', 'tapsenrol'));
        $mform->setDefault('approvalrequired', 1);

        $mform->addElement('textarea', 'sponsors', get_string('iw:sponsors', 'tapsenrol'));
        $mform->setType('sponsors', PARAM_TEXT);
        $mform->disabledIf('sponsors', 'approvalrequired', 'notchecked');
        $mform->addHelpButton('sponsors', 'iw:sponsors', 'tapsenrol');

        $mform->addElement('checkbox', 'rejectioncomments', get_string('iw:rejectioncomments', 'tapsenrol'));
        $mform->setDefault('rejectioncomments', false);
        $mform->disabledIf('rejectioncomments', 'approvalrequired', 'notchecked');

        $mform->addElement('text', 'approvalreminder', get_string('iw:approvalreminder', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('approvalreminder', PARAM_TEXT);
        $mform->setDefault('approvalreminder', 7); // Days.
        $mform->disabledIf('approvalreminder', 'approvalrequired', 'notchecked');
        $mform->addHelpButton('approvalreminder', 'iw:approvalreminder', 'tapsenrol');

        $mform->addElement('text', 'cancelafter', get_string('iw:cancelafter', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('cancelafter', PARAM_TEXT);
        $mform->setDefault('cancelafter', 14); // Days.
        $mform->disabledIf('cancelafter', 'approvalrequired', 'notchecked');
        $mform->addHelpButton('cancelafter', 'iw:cancelafter', 'tapsenrol');

        $mform->addElement('text', 'cancelbefore', get_string('iw:cancelbefore', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('cancelbefore', PARAM_TEXT);
        $mform->setDefault('cancelbefore', 24); // Hours.
        $mform->disabledIf('cancelbefore', 'approvalrequired', 'notchecked');
        $mform->addHelpButton('cancelbefore', 'iw:cancelbefore', 'tapsenrol');

        $mform->addElement('text', 'closeenrolment', get_string('iw:closeenrolment', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('closeenrolment', PARAM_TEXT);
        $mform->setDefault('closeenrolment', 48); // Hours.
        $mform->addHelpButton('closeenrolment', 'iw:closeenrolment', 'tapsenrol');

        $mform->addElement('text', 'firstreminder', get_string('iw:firstreminder', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('firstreminder', PARAM_TEXT);
        $mform->setDefault('firstreminder', 14); // Days.
        $mform->addHelpButton('firstreminder', 'iw:firstreminder', 'tapsenrol');

        $mform->addElement('text', 'secondreminder', get_string('iw:secondreminder', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('secondreminder', PARAM_TEXT);
        $mform->setDefault('secondreminder', 3); // Days.
        $mform->addHelpButton('secondreminder', 'iw:secondreminder', 'tapsenrol');

        $mform->addElement('text', 'noreminder', get_string('iw:noreminder', 'tapsenrol'), array('class' => 'tapsenrol-iw-numberfield'));
        $mform->setType('noreminder', PARAM_TEXT);
        $mform->setDefault('noreminder', 48); // Hours.
        $mform->addHelpButton('noreminder', 'iw:noreminder', 'tapsenrol');

        // Alternate 'from' details.
        $mform->addElement('header', 'from', get_string('iw:from', 'tapsenrol'));
        $mform->setExpanded('from', true);

        $a = get_admin();
        $a->fullname = fullname($a);
        $mform->addElement('html', html_writer::tag('p', get_string('iw:fromhint', 'tapsenrol', $a)));

        $mform->addElement('text', 'fromfirstname', get_string('iw:fromfirstname', 'tapsenrol'));
        $mform->setType('fromfirstname', PARAM_TEXT);

        $mform->addElement('text', 'fromlastname', get_string('iw:fromlastname', 'tapsenrol'));
        $mform->setType('fromlastname', PARAM_TEXT);

        $mform->addElement('text', 'fromemail', get_string('iw:fromemail', 'tapsenrol'));
        $mform->setType('fromemail', PARAM_EMAIL);

        // DECLARATIONS.
        $mform->addElement('header', 'declarations', get_string('iw:declarations', 'tapsenrol'));

        $mform->addElement('html', html_writer::tag('p', get_string('iw:declarationshint', 'tapsenrol')));

        $declaration = $mform->createElement('textarea', 'declaration', get_string('iw:declaration', 'tapsenrol'));

        $this->repeat_elements(
            array($declaration),
            $this->_customdata['declarationcount'],
            array(
                'declaration' => array(
                    'type' => PARAM_TEXT
                )
            ),
            'declarationrepeats',
            'declarationadds',
            1,
            get_string('iw:declaration:add', 'tapsenrol'),
            true
        );

        // ENROLMENT PAGE INFO.
        $mform->addElement('header', 'enrolment', get_string('iw:enrolmentinfo', 'tapsenrol'));

        $mform->addElement('html', html_writer::tag('p', get_string('iw:enrolmentinfohint', 'tapsenrol')));

        $mform->addElement('textarea', 'enrolinfo', get_string('iw:enrolinfo', 'tapsenrol'));
        $mform->setType('enrolinfo', PARAM_RAW);
        $mform->addHelpButton('enrolinfo', 'iw:enrolinfo', 'tapsenrol');
        $mform->addElement('static', 'enrolinfohint', get_string('default').':', html_writer::tag('i', get_string('emptysettingvalue', 'admin')));

        // APPROVAL PAGE INFO.
        $mform->addElement('header', 'approval', get_string('iw:approvalinfo', 'tapsenrol'));

        $mform->addElement('html', html_writer::tag('p', get_string('iw:approvalinfohint', 'tapsenrol')));

        $mform->addElement('textarea', 'approveinfo', get_string('iw:approveinfo', 'tapsenrol'));
        $mform->setType('approveinfo', PARAM_RAW);
        $mform->addHelpButton('approveinfo', 'iw:approveinfo', 'tapsenrol');
        $approveinfohint = html_writer::tag('p', nl2br(get_string('approve:info:approve', 'tapsenrol')));
        $mform->addElement('static', 'approveinfohint', get_string('default').':', $approveinfohint);

        $mform->addElement('textarea', 'rejectinfo', get_string('iw:rejectinfo', 'tapsenrol'));
        $mform->setType('rejectinfo', PARAM_RAW);
        $mform->addHelpButton('rejectinfo', 'iw:rejectinfo', 'tapsenrol');
        $rejectinfohint = html_writer::tag('p', nl2br(get_string('approve:info:reject', 'tapsenrol')));
        $mform->addElement('static', 'rejectinfohint', get_string('default').':', $rejectinfohint);

        $mform->addElement('textarea', 'eitherinfo', get_string('iw:eitherinfo', 'tapsenrol'));
        $mform->setType('eitherinfo', PARAM_RAW);
        $mform->addHelpButton('eitherinfo', 'iw:eitherinfo', 'tapsenrol');
        $eitherinfohint = html_writer::tag('p', nl2br(get_string('approve:info:either', 'tapsenrol')));
        $mform->addElement('static', 'eitherinfohint', get_string('default').':', $eitherinfohint);

        // CANCELLATION PAGE INFO.
        $mform->addElement('header', 'cancellation', get_string('iw:cancellationinfo', 'tapsenrol'));

        $mform->addElement('html', html_writer::tag('p', get_string('iw:cancellationinfohint', 'tapsenrol')));

        $mform->addElement('textarea', 'cancelinfo', get_string('iw:cancelinfo', 'tapsenrol'));
        $mform->setType('cancelinfo', PARAM_RAW);
        $mform->addHelpButton('cancelinfo', 'iw:cancelinfo', 'tapsenrol');
        $mform->addElement('static', 'cancelinfohint', get_string('default').':', html_writer::tag('i', get_string('emptysettingvalue', 'admin')));

        $mform->addElement('checkbox', 'cancelcomments', get_string('iw:cancelcomments', 'tapsenrol'));
        $mform->setDefault('cancelcomments', false);

        // HIDDEN ELEMENTS.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ALPHA);

        // SUBMIT BUTTONS.
        if ($this->_customdata['locked']) {
            $mform->addElement('cancel', 'cancel', get_string('exit:locked', 'tapsenrol'));
            $mform->closeHeaderBefore('cancel');
        } else {
            $this->add_action_buttons();
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $numberfields = array(
            'approvalreminder', 'cancelafter', 'cancelbefore', 'closeenrolment',
            'firstreminder', 'secondreminder', 'noreminder'
        );

        $filteroptions = array(
            'options' => array( 'min_range' => 0)
        );
        foreach ($numberfields as $numberfield) {
            if (filter_var($data[$numberfield], FILTER_VALIDATE_INT, $filteroptions) === false) {
                $errors[$numberfield] = get_string('iw:form:error:intorzero', 'tapsenrol');
            }
        }

        if (!empty($data['approvalrequired']) && $data['approvalreminder'] != 0 && $data['cancelafter'] != 0 && $data['approvalreminder'] >= $data['cancelafter']) {
            $this->_add_error($errors, 'approvalreminder', get_string('iw:form:error:lessthan', 'tapsenrol', get_string('iw:cancelafter', 'tapsenrol')));
            $this->_add_error($errors, 'cancelafter', get_string('iw:form:error:greaterthan', 'tapsenrol', get_string('iw:approvalreminder', 'tapsenrol')));
        }

        if (!empty($data['approvalrequired']) && $data['closeenrolment'] != 0 && $data['cancelbefore'] != 0 && $data['closeenrolment'] <= $data['cancelbefore']) {
            $this->_add_error($errors, 'closeenrolment', get_string('iw:form:error:greaterthan', 'tapsenrol', get_string('iw:cancelbefore', 'tapsenrol')));
            $this->_add_error($errors, 'cancelbefore', get_string('iw:form:error:lessthan', 'tapsenrol', get_string('iw:closeenrolment', 'tapsenrol')));
        }

        if ($data['firstreminder'] != 0 && $data['secondreminder'] != 0 && $data['secondreminder'] >= $data['firstreminder']) {
            $this->_add_error($errors, 'firstreminder', get_string('iw:form:error:greaterthan', 'tapsenrol', get_string('iw:secondreminder', 'tapsenrol')));
            $this->_add_error($errors, 'secondreminder', get_string('iw:form:error:lessthan', 'tapsenrol', get_string('iw:firstreminder', 'tapsenrol')));
        }

        // Tidy up sponsors emails prior to validation.
        if (empty($data['sponsors'])) {
            $data['sponsors'] = '';
        } else {
            $data['sponsors'] = trim(
                preg_replace(
                    "/\n+/",
                    "\n",
                    preg_replace("/\r\n|\r/", "\n", $data['sponsors'])
                )
            );
        }
        if (!empty($data['sponsors'])) {
            $errors['sponsors'] = '';
            if (!is_enabled_auth('saml')) {
                $errors['sponsors'] = get_string('iw:sponsors:error:noldap', 'tapsenrol');
            } else {
                $sponsors = explode("\n", $data['sponsors']);
                foreach ($sponsors as $sponsor) {
                    if (!filter_var($sponsor, FILTER_VALIDATE_EMAIL)) {
                        $errors['sponsors'] .= get_string('iw:sponsors:error:invalid', 'tapsenrol', $sponsor);
                    } else {
                        $samlauth = get_auth_plugin('saml');
                        $users = $samlauth->ldap_get_userlist('(mail='.$sponsor.')');
                        if (empty($users)) {
                            $errors['sponsors'] .= get_string('iw:sponsors:error:notfound', 'tapsenrol', $sponsor);
                        }
                    }
                }
            }

            if (empty($errors['sponsors'])) {
                unset($errors['sponsors']);
            }
        }

        return $errors;
    }

    protected function _add_error(&$errors, $errorfield, $error) {
        $errors[$errorfield] = !empty($errors[$errorfield]) ? $errors[$errorfield].html_writer::empty_tag('br') : '';
        $errors[$errorfield] .= $error;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if (empty($data->emailsoff)) {
            $data->emailsoff = 0;
        }
        if (empty($data->approvalrequired)) {
            $data->approvalrequired = 0;
        }
        if (empty($data->sponsors)) {
            $data->sponsors = '';
        }
        if (empty($data->rejectioncomments)) {
            $data->rejectioncomments = 0;
        }
        if (empty($data->cancelcomments)) {
            $data->cancelcomments = 0;
        }

        $daystosecs = array('approvalreminder', 'cancelafter', 'firstreminder', 'secondreminder');
        $hourstosecs = array('cancelbefore', 'closeenrolment', 'noreminder');

        foreach ($daystosecs as $field) {
            if (isset($data->{$field})) {
                $data->{$field} = $data->{$field} * (24 * 60 * 60);
            }
        }

        foreach ($hourstosecs as $field) {
            if (isset($data->{$field})) {
                $data->{$field} = $data->{$field} * (60 * 60);
            }
        }
        return $data;
    }

    public function set_data($defaultvalues) {
        $todays = array('approvalreminder', 'cancelafter', 'firstreminder', 'secondreminder');
        $tohours = array('cancelbefore', 'closeenrolment', 'noreminder');

        foreach ($todays as $field) {
            if (isset($defaultvalues->{$field})) {
                $defaultvalues->{$field} = $defaultvalues->{$field} / (24 * 60 * 60);
            }
        }

        foreach ($tohours as $field) {
            if (isset($defaultvalues->{$field})) {
                $defaultvalues->{$field} = $defaultvalues->{$field} / (60 * 60);
            }
        }

        parent::set_data($defaultvalues);
    }
}