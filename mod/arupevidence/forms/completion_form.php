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

class mod_arupevidence_completion_form extends moodleform
{
    /*** @var array $_arupevidence */
    protected $_arupevidence;

    /*** @var array $_arupevidenceuser */
    protected $_arupevidenceuser;



    public function definition()
    {
        global $COURSE, $PAGE, $USER;
        $mform = $this->_form;

        $this->_arupevidenceuser = $this->_customdata['arupevidenceuser'] ? $this->_customdata['arupevidenceuser'] : null;
        $this->_arupevidence = $this->_customdata['arupevidence'] ? $this->_customdata['arupevidence'] : null;

        if ($this->_arupevidence->cpdlms == ARUPEVIDENCE_LMS) {
            $defaultenrolment = '';

            $taps = new \local_taps\taps();

            $classchoices = array();
            $hasplacedenrolment = false;
            if ($enrolments = $taps->get_enroled_classes($USER->idnumber, $COURSE->idnumber, true, false)) {
                foreach ($enrolments as $enrolment) {
                    if ($taps->is_status($enrolment->bookingstatus, 'placed')) {
                        $classchoices[$enrolment->enrolmentid] = $enrolment->classname;
                        $hasplacedenrolment = true;
                    }
                }

                if (count($classchoices) > 1) {
                    $classchoices = array(''=>get_string('chooseclass', 'mod_arupevidence')) + $classchoices;
                }

            }

            if (!$hasplacedenrolment) {
                $mform->addElement('html', \html_writer::tag('div', get_string('noenrolments', 'mod_arupevidence'), ['class' => 'alert alert-danger']));
            }
        }

        $mform->addElement('date_selector', 'completiondate',  get_string('completiondate', 'mod_arupevidence'));
        $defaultdate = isset($this->_arupevidenceuser->completiondate) ? $this->_arupevidenceuser->completiondate : time();
        $mform->setDefault('completiondate', $defaultdate);
        if (isset($this->_arupevidence->requireexpirydate) && $this->_arupevidence->requireexpirydate) {
            if ($this->_arupevidence->mustendmonth) {
                $choices = array(
                    '01' => "January",
                    '02' => "February",
                    '03' => "March",
                    '04' => "April",
                    '05' => "May",
                    '06' => "June",
                    '07' => "July",
                    '08' => "August",
                    '09' => "September",
                    '10' => "October",
                    '11' => "November",
                    '12' => "December");

                $choicesyears = array_combine(range(1950,2030), range(1950,2030));

                // Months selection
                $defaultvalue = (isset($this->_arupevidenceuser->expirydate) && $this->_arupevidenceuser->expirydate) ? date('m', $this->_arupevidenceuser->expirydate) : '';
                $mform->addElement(
                    'select',
                    'expirymonth',
                    get_string('label:expirydate', 'mod_arupevidence'),
                    array('' => get_string('selectmonth', 'mod_arupevidence')) + $choices, array('style'=>'width:140px')
                );
                $mform->setDefault('expirymonth', $defaultvalue);

                // Years selection
                $defaultvalue = (isset($this->_arupevidenceuser->expirydate) && $this->_arupevidenceuser->expirydate) ? date('Y', $this->_arupevidenceuser->expirydate) : '';
                $mform->addElement(
                    'select',
                    'expiryyear',
                    '',
                    array('' => get_string('selectyear', 'mod_arupevidence')) + $choicesyears, array('style'=>'width:130px')
                );
                $mform->setDefault('expiryyear', $defaultvalue);
            } else {
                $mform->addElement('date_selector', 'expirydate',  get_string('label:expirydate', 'mod_arupevidence'));
                $mform->setDefault('expirydate', isset($this->_arupevidenceuser->expirydate)? $this->_arupevidenceuser->expirydate : '');

            }

        } else if (isset($this->_arupevidence->requirevalidityperiod) && $this->_arupevidence->requirevalidityperiod) {
            $choices = array(get_string('none'), 1,2,3,4,5,6,7,8,9,10,11,12);
            $mform->addElement('select', 'validityperiod', get_string('validityperiod', 'mod_arupevidence'), $choices);
            $mform->addRule('validityperiod', null, 'required', null, 'client');
            $mform->setDefault('validityperiod', isset($this->_arupevidenceuser->validityperiod)? $this->_arupevidenceuser->validityperiod : '');

            $mform->addElement('select', 'validityperiodunit', '', array('m' => 'Month(s)', 'y' => 'Year(s)', '' => get_string('none')));
            $mform->addRule('validityperiodunit', null, 'required', null, 'client');
            $mform->setDefault('validityperiodunit', isset($this->_arupevidenceuser->validityperiodunit)? $this->_arupevidenceuser->validityperiodunit : '');
        }
        if ($this->_arupevidence->cpdlms == ARUPEVIDENCE_LMS) {
            $mform->addElement(
                'select',
                'enrolmentid',
                get_string('label:enrolment', 'mod_arupevidence'),
                $classchoices, array('style'=>'width:140px')
            );
            $mform->addRule('enrolmentid', null, 'required', null, 'client');
            $mform->setDefault('enrolmentid', $defaultenrolment);
        }

        $fileoptions = array(
            'subdirs' => 0,
            'maxfiles' => 1,
            'maxbytes' => $COURSE->maxbytes
        );
        $mform->addElement('filemanager', 'completioncertificate',
            get_string('uploadcertificate', 'mod_arupevidence'),
            null, $fileoptions);

        $aeuserid = isset($this->_arupevidenceuser->userid) ? $this->_arupevidenceuser->userid : null ;
        $entryid = !empty($this->_arupevidenceuser->itemid) ? $this->_arupevidenceuser->itemid: $aeuserid ;
        $draftitemid = file_get_submitted_draft_itemid("completioncertificate");
        $farea = !empty($this->_arupevidenceuser->itemid)? $this->_arupevidence->cpdlms : null ;
        $filearea = arupevidence_fileareaname($farea);

        file_prepare_draft_area($draftitemid, $this->_customdata['contextid'], 'mod_arupevidence', $filearea, $entryid,
            $fileoptions);
        $mform->setDefault("completioncertificate", $draftitemid);

        if(!empty($this->_arupevidenceuser)) {
            $mform->addElement('hidden', 'timemodified', $this->_arupevidenceuser->timemodified);
            $mform->setType('timemodified', PARAM_INT);

            $mform->addElement('hidden', 'approved', $this->_arupevidenceuser->approved);
            $mform->setType('approved', PARAM_INT);

            $mform->addElement('hidden', 'completion', $this->_arupevidenceuser->completion);
            $mform->setType('completion', PARAM_INT);
        }


        // user was editing existing an arupevidenceuser
        if(isset($this->_customdata['action']) && !empty($this->_customdata['action'])) {
            $mform->addElement('hidden', 'action', $this->_customdata['action']);
            $mform->setType('action', PARAM_ALPHA);
            $mform->addElement('hidden', 'ahbuserid', $this->_arupevidenceuser->id);
            $mform->setType('ahbuserid', PARAM_INT);

        }

        if (isset($this->_arupevidence->cpdlms) && $this->_arupevidence->cpdlms == ARUPEVIDENCE_CPD) {
            $this->add_taps_fields($mform);
        }



        if (!empty($this->_arupevidence->expectedvalidityperiod) && !empty($this->_arupevidence->expectedvalidityperiodunit)) {
            // Validity period confirmation modal.
            $modaldata = new stdClass();
            $modaldata->expectedvalidityperiod = $this->_arupevidence->expectedvalidityperiod . ' ' .
                get_string('validityperiod:'.$this->_arupevidence->expectedvalidityperiodunit, 'mod_arupevidence');
            $renderer = $PAGE->get_renderer('mod_arupevidence');
            $mform->addElement('html', $renderer->render_from_template('mod_arupevidence/modal_warning_validitydate', $modaldata));
        }

        $mform->addElement('hidden', 'mustendmonth', $this->_arupevidence->mustendmonth);
        $mform->setType('mustendmonth', PARAM_INT);

        $mform->addElement('hidden', 'requirevalidityperiod', $this->_arupevidence->requirevalidityperiod);
        $mform->setType('requirevalidityperiod', PARAM_INT);

        $this->add_action_buttons(true, get_string('upload'));
    }

    public function add_taps_fields(MoodleQuickForm $mform) {
        $taps = new \local_taps\taps();

        $defaults = !empty($this->_arupevidenceuser)? $this->_arupevidenceuser : $this->_arupevidence ;

        $mform->addElement('header', 'tapstemplate', get_string('cpdformheader', 'mod_arupevidence'));

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->disabledIf('provider', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);
        $mform->setDefault('provider', !empty($defaults->provider) ? $defaults->provider : '');

        $mform->addElement('text', 'duration', get_string('cpd:duration', 'block_arup_mylearning'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->disabledIf('duration', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);
        $mform->setDefault('duration', !empty($defaults->duration) ? $defaults->duration : '');

        $mform->addElement('select', 'durationunitscode', get_string('cpd:durationunitscode', 'block_arup_mylearning'), $taps->get_durationunitscode());
        $mform->disabledIf('durationunitscode', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);
        $mform->setDefault('durationunitscode', !empty($defaults->durationunitscode) ? $defaults->durationunitscode : '');

        $mform->addElement('text', 'location', get_string('cpd:location', 'block_arup_mylearning'));
        $mform->setType('location', PARAM_TEXT);
        $mform->setAdvanced('location');
        $mform->setDefault('location', !empty($defaults->location) ? $defaults->location : '');

        $mform->addElement('date_selector', 'classstartdate', get_string('cpd:classstartdate', 'block_arup_mylearning'), array('optional' => true, 'timezone' => 0));
        $mform->setAdvanced('classstartdate');
        $mform->setDefault('classstartdate', !empty($defaults->classstartdate) ? $defaults->classstartdate : '');


        $mform->addElement('text', 'classcost', get_string('cpd:classcost', 'block_arup_mylearning'));
        $mform->setType('classcost', PARAM_TEXT);
        $mform->setAdvanced('classcost');
        $mform->setDefault('classcost', !empty($defaults->classcost) ? $defaults->classcost : '');

        $mform->addElement('select', 'classcostcurrency', get_string('cpd:classcostcurrency', 'block_arup_mylearning'), $taps->get_classcostcurrency());
        $mform->setAdvanced('classcostcurrency');
        $mform->setDefault('classsclasscostcurrencytartdate', !empty($defaults->classcostcurrency) ? $defaults->classcostcurrency : '');

        $mform->addElement('text', 'certificateno', get_string('cpd:certificateno', 'block_arup_mylearning'));
        $mform->setType('certificateno', PARAM_TEXT);
        $mform->setAdvanced('certificateno');
        $mform->setDefault('certificateno', !empty($defaults->certificateno) ? $defaults->certificateno : '');
    }

    public function get_data()
    {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        $data->expirydate = 0;
        if (!empty($data->expirymonth) && !empty($data->expiryyear)) {
            // Getting end of month
            $lastday = date('t',strtotime($data->expiryyear . $data->expirymonth . '01'));
            $data->expirydate = strtotime($data->expiryyear . $data->expirymonth . $lastday);
        }

    return $data;
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        if (!empty($data['expirymonth']) && empty($data['expiryyear'])) {
            $errors['expiryyear'] = get_string('error:emptyyear', 'mod_arupevidence');
        }

        if (!empty($data['expiryyear']) && empty($data['expirymonth'])) {
            $errors['expirymonth'] = get_string('error:emptymonth', 'mod_arupevidence');
        }

        if ((!empty($data['completiondate']) && !empty($data['expirymonth']) && !empty($data['expiryyear']))
            || !empty($data['expirydate'])) {

            $expirydate = 0;

            if (!empty($data['expirydate'])) {
                $expirydate = $data['expirydate'];
            } else {
                $lastday = date('t',strtotime($data['expiryyear'] . $data['expirymonth'] . '01'));
                $expirydate = strtotime($data['expiryyear'] . $data['expirymonth'] . $lastday);
            }

            $diffmonth = arupevidence_diffdates_bymonth($expirydate, $data['completiondate']);
            if (!$diffmonth) {
                $element = !empty($data['expirydate']) ? 'expirydate' : 'expirymonth';
                $errors[$element] = get_string('error:expirydate', 'mod_arupevidence');
            }
        }

        return $errors;
    }

}