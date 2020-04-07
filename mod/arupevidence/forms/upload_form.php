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

class mod_arupevidence_upload_form extends moodleform
{
    /*** @var array $_arupevidence */
    protected $_arupevidence;

    public function definition() {
        global $COURSE, $PAGE;
        $mform = $this->_form;

        // Disable change checker as interferes with validity period modal confirmation.
        $mform->disable_form_change_checker();

        $this->_arupevidence = $this->_customdata['arupevidence'] ? $this->_customdata['arupevidence'] : null;

        if ($this->_arupevidence->cpdlms == ARUPEVIDENCE_LMS) {
            $taps = new \local_taps\taps();
            $classes = $taps->get_course_classes($COURSE->idnumber);
            $classchoices = ['' => get_string('chooseclass', 'mod_arupevidence')];
            foreach ($classes as $class) {
                $classchoices[$class->classid] = $class->classname;
            }
        }

        $mform->addElement(
            'select',
            'ahbuserid',
            get_string('label:uploadforuser', 'mod_arupevidence'),
            array('' => ''),
            array('class' => 'select2-user', 'data-placeholder' => get_string('placeholder:uploadforuser', 'mod_arupevidence'))
        );
        $mform->addRule('ahbuserid', null, 'required', null, 'client');

        if ($this->_arupevidence->cpdlms == ARUPEVIDENCE_LMS) {
            $mform->addElement(
                'select',
                'classid',
                get_string('label:class', 'mod_arupevidence'),
                $classchoices, array('style'=>'width:140px')
            );
            $mform->addRule('classid', null, 'required', null, 'client');
        }

        $mform->addElement('date_selector', 'completiondate',  get_string('completiondate', 'mod_arupevidence'), array('timezone' => 0));

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

                    $choicesyears = array_combine(range(2030, 1950, -1), range(2030, 1950, -1));

                // Months selection
                $mform->addElement(
                    'select',
                    'expirymonth',
                    get_string('label:expirydate', 'mod_arupevidence'),
                    array('' => get_string('selectmonth', 'mod_arupevidence')) + $choices, array('style'=>'width:140px')
                );

                // Years selection
                $mform->addElement(
                    'select',
                    'expiryyear',
                    '',
                    array('' => get_string('selectyear', 'mod_arupevidence')) + $choicesyears, array('style'=>'width:130px')
                );
            } else {
                $mform->addElement('date_selector', 'expirydate',  get_string('label:expirydate', 'mod_arupevidence'), array('timezone' => 0));

            }

        } else if (isset($this->_arupevidence->requirevalidityperiod) && $this->_arupevidence->requirevalidityperiod) {
            $choices = array(get_string('none'), 1,2,3,4,5,6,7,8,9,10,11,12);
            $mform->addElement('select', 'validityperiod', get_string('validityperiod', 'mod_arupevidence'), $choices);
            $mform->addRule('validityperiod', null, 'required', null, 'client');

            $mform->addElement('select', 'validityperiodunit', '', array('m' => 'Month(s)', 'y' => 'Year(s)', '' => get_string('none')));
            $mform->addRule('validityperiodunit', null, 'required', null, 'client');
        }

        if ($this->_arupevidence->requireupload) {
            $fileoptions = array(
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => $COURSE->maxbytes
            );
            $mform->addElement('filemanager', 'completioncertificate',
                get_string('uploadcertificate', 'mod_arupevidence'),
                null, $fileoptions);

            $draftitemid = file_get_submitted_draft_itemid("completioncertificate");
            $filearea = arupevidence_fileareaname(null);
            file_prepare_draft_area($draftitemid, $this->_customdata['contextid'], 'mod_arupevidence', $filearea, null,
                $fileoptions);
            $mform->setDefault("completioncertificate", $draftitemid);
        }

        // Declarations
        if (!empty($this->_customdata['declarations'])) {
            foreach ($this->_customdata['declarations'] as $declaration) {
                $mform->addElement('checkbox', 'declaration-'.$declaration->id, '', $declaration->declaration);
            }
        }

        if (!empty($this->_arupevidence->exemption)) {
            $this->add_exemption_fields($mform);
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

        $mform->addElement('hidden', 'validityexpirydate');
        $mform->setType('validityexpirydate', PARAM_INT);

        $this->add_action_buttons(true, get_string('upload'));
    }

    private function add_exemption_fields(MoodleQuickForm $mform) {
        $mform->addElement('header', 'exemptionsection', get_string('exemptionheader', 'mod_arupevidence'));

        $mform->addElement('advcheckbox', 'exempt', $this->_arupevidence->exemptionquestion);
        $mform->setDefault('exempt', !empty($this->_arupevidenceuser->exempt) ? $this->_arupevidenceuser->exempt : 0);

        if ($this->_arupevidence->exemptioninfo) {
            $mform->addElement('textarea', 'exemptreason', $this->_arupevidence->exemptioninfoquestion);
            $mform->disabledIf('exemptreason', 'exempt', 'notchecked');
            $mform->setDefault('exemptreason', !empty($this->_arupevidenceuser->exemptreason) ? $this->_arupevidenceuser->exemptreason : '');
        }

        // Disable completion date if required.
        if (!empty($this->_arupevidence->exemptioncompletion)) {
            $mform->disabledIf('completiondate', 'exempt', 'checked');
        }
    }

    private function add_taps_fields(MoodleQuickForm $mform) {
        $taps = new \local_taps\taps();

        $defaults = $this->_arupevidence;

        $mform->addElement('header', 'tapstemplate', get_string('cpdformheader', 'mod_arupevidence'));

        $mform->addElement('text', 'provider', get_string('cpd:provider', 'block_arup_mylearning'));
        $mform->setType('provider', PARAM_TEXT);
        $mform->disabledIf('provider', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);
        $mform->setDefault('provider', !empty($defaults->provider) ? $defaults->provider : '');

        $mform->addElement('text', 'duration', get_string('duration', 'local_taps').get_string('durationcode', 'local_taps'), 'size="5"');
        $mform->setType('duration', PARAM_TEXT);
        $mform->addHelpButton('duration', 'duration', 'local_taps');
        $mform->disabledIf('duration', 'cpdlms', 'neq', ARUPEVIDENCE_CPD);
        if (!empty($defaults->durationunitscode) && $defaults->durationunitscode == 'H') {
            $defaults->duration = $taps->duration_hours_display($defaults->duration, '', true);
        }
        $mform->setDefault('duration', !empty($defaults->duration) ? $defaults->duration : '');

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

        if ($this->_arupevidence->mustendmonth && !empty($data->expirymonth) && !empty($data->expiryyear)) {
            // Getting end of month
            $lastday = date('t',strtotime($data->expiryyear . $data->expirymonth . '01'));
            $data->expirydate = strtotime($data->expiryyear . $data->expirymonth . $lastday);
        }

        $data->ahbuserid = optional_param('ahbuserid', null, PARAM_INT);

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
        
        if (!empty($data['duration'])) {
            $time = explode(':', $data['duration']);
            if (count($time) > 2) {
                $errors['duration'] = get_string('validation:durationformatincorrect', 'local_taps').get_string('durationcode', 'local_taps');
            } elseif (isset($time[1]) && ($time[1] < 0 || $time[1] > 59 || !is_numeric($time[1]))) {
                $errors['duration'] = get_string('validation:durationinvalidminutes', 'local_taps').get_string('durationcode', 'local_taps');
            } elseif ((isset($time[0]) && (!is_numeric($time[0]) || $time[0] < 0))) {
                $errors['duration'] = get_string('validation:durationinvalidhours', 'local_taps').get_string('durationcode', 'local_taps');
            }
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

        if (!empty($this->_customdata['declarations'])) {
            foreach ($this->_customdata['declarations'] as $declaration) {
                if (!isset($data['declaration-'.$declaration->id])) {
                    $errors['declaration-'.$declaration->id] = get_string('error:declaration:required', 'mod_arupevidence');
                }
            }
        }

        if ($this->_arupevidence->exemptioninfo && !empty($data['exempt']) && empty($data['exemptreason'])) {
            $errors['exemptreason'] = get_string('required');
        }

        if ($this->_arupevidence->requireupload && empty($data['exempt'])) {
            $draftfiles = file_get_drafarea_files($data['completioncertificate']);
            if (empty($draftfiles->list)) {
                $errors['completioncertificate'] = get_string('required');
            }
        }

        return $errors;
    }

    public function set_data($defaultvalues) {
        global $DB;

        if (!empty($defaultvalues['ahbuserid'])) {
            $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
            $params = array('ahbuserid'=> $defaultvalues['ahbuserid']);
            $where = "id = :ahbuserid";
            $userlist = $DB->get_records_select_menu('user', $where, $params, 'lastname ASC', "id, $usertextconcat");
            $select = $this->_form->getElement('ahbuserid');
            foreach ($userlist as $value => $text) {
                $select->addOption($text, $value, array('selected' => 'selected'));
            }
        }

        parent::set_data($defaultvalues);
    }

    public function render() {
        $this->set_data(['ahbuserid' => optional_param('ahbuserid', null, PARAM_INT)]);

        return parent::render();
    }
}