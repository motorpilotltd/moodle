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
 * @copyright  2014 Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once("$CFG->libdir/formslib.php");

class editsubmission_form extends moodleform {

    private $questionstatements;
    private $declarations;


    protected function definition() {
        global $DB;
        $mform =& $this->_form;

        $edit = $this->_customdata['edit'];
        $status = $this->_customdata['status'];
        $userid = $this->_customdata['userid'];
        $submissionid = $this->_customdata['submissionid'];
        $contextid = $this->_customdata['contextid'];
        $referencesubmitted = $this->_customdata['referencesubmitted'];
        $referee_audit = $this->_customdata['referee_audit'];
        $sponsorsubmitted = $this->_customdata['sponsorsubmitted'];
        $sponsor_audit = $this->_customdata['sponsor_audit'];
        $applicationid = $this->_customdata['applicationid'];
        $sponsordeclarationlabel = $this->_customdata['sponsordeclarationlabel'];
        $this->questionstatements = $DB->get_records('arupstatementquestions', array('applicationid'=>$applicationid), 'sortorder');
        $this->declarations = $DB->get_records('arupdeclarations', array('applicationid'=>$applicationid), 'sortorder');

        $disablefields = '';

        if ($edit == 0) {
            $disablefields = ' disabled="disabled"';
        }

        $grades = get_config('arupapplication', 'gradeoptions');
        $grades = explode("\n", $grades);
        $gradeoptions = array();
        $gradeoptions[''] = get_string('choose').'...';
        foreach($grades as $key => $value) {
            $gradeoptions[ltrim(rtrim($value))] = ltrim(rtrim($value));
        }

        $officelocations = get_config('arupapplication', 'officelocationoptions');
        $officelocations = explode("\n", $officelocations);
        $officelocationoptions = array();
        $officelocationoptions[''] = get_string('choose').'...';
        foreach($officelocations as $key => $value) {
            $officelocationoptions[ltrim(rtrim($value))] = ltrim(rtrim($value));
        }
        $officelocationoptions['Other'] = 'Other';

        $mform->addElement('html', '<h2>' . get_string('heading:applicationdetails', 'arupapplication') . '</h2>');

/// PERSONAL DETAILS ///

        $mform->addElement('header', 'personal', get_string('legend:applicantdetails:personal', 'arupapplication'));

        $mform->addElement('text', 'title', get_string('title', 'arupapplication'), $disablefields);
        $mform->setType('title', PARAM_NOTAGS);
        $mform->addHelpButton('title', 'title', 'arupapplication');

        $mform->addElement('text', 'firstname', get_string('firstname', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('firstname', PARAM_NOTAGS);

        $mform->addElement('text', 'lastname', get_string('surname', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('lastname', PARAM_NOTAGS);

        $mform->addElement('text', 'passportname', get_string('passportname', 'arupapplication'), $disablefields);
        $mform->setType('passportname', PARAM_NOTAGS);
        $mform->addHelpButton('passportname', 'passportname', 'arupapplication');

        $mform->addElement('text', 'knownas', get_string('knownas', 'arupapplication'), $disablefields);
        $mform->setType('knownas', PARAM_NOTAGS);
        $mform->addHelpButton('knownas', 'knownas', 'arupapplication');

        $mform->addElement('date_selector', 'dateofbirth', get_string('dateofbirth', 'arupapplication'), array('timezone' => 0, 'startyear'=>date('Y')-70, 'stopyear'=>date('Y')-16), $disablefields);
        $mform->addHelpButton('dateofbirth', 'dateofbirth', 'arupapplication');

        $mform->addElement('text', 'countryofresidence', get_string('countryofresidence', 'arupapplication'), $disablefields);
        $mform->setType('countryofresidence', PARAM_NOTAGS);
        $mform->addHelpButton('countryofresidence', 'countryofresidence', 'arupapplication');

        $mform->addElement('selectyesno', 'requirevisa', get_string('requirevisa', 'arupapplication'), $disablefields);
        $mform->addHelpButton('requirevisa', 'requirevisa', 'arupapplication');

/// ARUP DETAILS ///

        $mform->addElement('header', 'arup', get_string('legend:applicantdetails:arup', 'arupapplication'));

        $mform->addElement('text', 'staffid', get_string('staffid', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('staffid', PARAM_NOTAGS);

        $mform->addElement('select', 'grade', get_string('grade', 'arupapplication'), $gradeoptions, $disablefields);

        $mform->addElement('text', 'jobtitle', get_string('jobtitle', 'arupapplication'), $disablefields);
        $mform->setType('jobtitle', PARAM_NOTAGS);

        $mform->addElement('text', 'discipline', get_string('discipline', 'arupapplication'), $disablefields);
        $mform->setType('discipline', PARAM_NOTAGS);
        $mform->addHelpButton('discipline', 'discipline', 'arupapplication');

        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12,0,0,$i,15,2000), "%B");
        }
        for ($i=date('Y')-50; $i<=date("Y"); $i++) {
            $years[$i] = $i;
        }

        $selectarray=array();
        $selectarray[] =& $mform->createElement('select', 'joiningmonth', get_string('month', 'form'), $months, $disablefields, true);
        $selectarray[] =& $mform->createElement('select', 'joiningyear', get_string('year', 'form'), $years, $disablefields, true);
        $mform->addGroup($selectarray, 'joiningdate', get_string('joiningdate', 'arupapplication'), array(' '), false);
        $mform->addHelpButton('joiningdate', 'joiningdate', 'arupapplication');

        $mform->addElement('text', 'arupgroup', get_string('group', 'arupapplication'), $disablefields);
        $mform->setType('arupgroup', PARAM_NOTAGS);
        $mform->addHelpButton('arupgroup', 'group', 'arupapplication');

        $mform->addElement('text', 'businessarea', get_string('businessarea', 'arupapplication'), $disablefields);
        $mform->setType('businessarea', PARAM_NOTAGS);
        $mform->addHelpButton('businessarea', 'businessarea', 'arupapplication');
        $mform->addRule('businessarea', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);

        $mform->addElement('text', 'region', get_string('region', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('region', PARAM_NOTAGS);
        $mform->setDefault('region', arupapplication_userregion($userid));
        $mform->addHelpButton('region', 'region', 'arupapplication');

        $mform->addElement('select', 'officelocation', get_string('officelocation', 'arupapplication'), $officelocationoptions, $disablefields);

        $mform->addElement('text', 'otherofficelocation', get_string('otherofficelocation', 'arupapplication'), $disablefields);
        $mform->setType('otherofficelocation', PARAM_NOTAGS);
        if ($edit) {
            $mform->disabledIf('otherofficelocation', 'officelocation', 'Neq', 'Other');
        }

/// STATEMENT ///

        $mform->addElement('header', 'statement', get_string('heading:statement', 'arupapplication'));

        foreach($this->questionstatements as $questionstatement) {
            if ($questionstatement->ismandatory) {
                $addtoquestion = get_string('ismandatory', 'arupapplication');
            } else {
                $addtoquestion = '';
            }
            $mform->addElement('textarea', 'qidanswer'. $questionstatement->id, format_string($questionstatement->question), 'wrap="virtual" rows="8" cols="65"' . $disablefields);
            $mform->setType('qidanswer'. $questionstatement->id, PARAM_NOTAGS);
        }

/// QUALIFICATIONS ///

        $mform->addElement('header', 'qualifications', get_string('heading:qualifications', 'arupapplication'));

        $mform->addElement('textarea', 'degree', get_string('degree', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"'. $disablefields);
        $mform->setType('degree', PARAM_NOTAGS);
        $mform->addHelpButton('degree', 'degree', 'arupapplication');
        if ($edit == 0) {
            $fs = get_file_storage();
            $files = $fs->get_area_files($contextid, 'mod_arupapplication', 'submission', $submissionid);
            if ($files) {
                $mform->addElement('static', 'cvfle', get_string('cv', 'arupapplication'));
                $file = array_pop($files);
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                $mform->setDefault('cvfle', html_writer::link($url, $file->get_filename()));
            }
        } else {
            $filemanager_options = array();
            $filemanager_options['return_types'] = 3;
            $filemanager_options['accepted_types'] = array(ARUPAPPLICATION_MAX_FILETYPE);
            $filemanager_options['maxbytes'] = ARUPAPPLICATION_MAX_FILESIZE;
            $filemanager_options['maxfiles'] = ARUPAPPLICATION_MAX_FILES;
            $filemanager_options['mainfile'] = false;

            $mform->addElement('filepicker', 'cv', get_string('cv', 'arupapplication'), null, $filemanager_options);
            $mform->addHelpButton('cv', 'cv', 'arupapplication');
        }

/// DECLARATION ///

        $mform->addElement('header', 'declaration', get_string('heading:declaration', 'arupapplication'));

        foreach($this->declarations as $declaration) {
            $mform->addElement('advcheckbox', 'declarationid'.$declaration->id, '', $declaration->declaration, array('group' => 0, $disablefields), array(0, 1));
        }

/// TECHNICAL REFEREE DETAILS ///

        $mform->addElement('header', 'referee', get_string('heading:referee', 'arupapplication'));

        $mform->addElement('text', 'referee_email', get_string('refereeemail', 'arupapplication'), $disablefields);
        $mform->setType('referee_email', PARAM_EMAIL);
        $mform->addElement('textarea', 'referee_message', get_string('refereemessage', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referee_message', PARAM_NOTAGS);

        if ($edit) {
            if (!$referencesubmitted) {
                if (empty($referee_audit)) {
                    $mform->addElement('submit', 'resendreferenceemail', get_string('button:sendreferenceemail', 'arupapplication'), array('class' => 'btn-primary'));
                } else {
                    $mform->addElement('submit', 'resendreferenceemail', get_string('button:resendreferenceemail', 'arupapplication'), array('class' => 'btn-default'));
                }
            }
        }

        if (!empty($referee_audit)) {
            $mform->addElement('header', 'general', get_string('heading:previousemails', 'arupapplication'));

            $records = explode('$$$', $referee_audit);
            $table = new html_table();
            $table->cellpadding = 4;
            $table->attributes['class'] = 'generaltable boxalignleft';
            $table->head = array(get_string('heading:to', 'arupapplication'), get_string('heading:from', 'arupapplication'), get_string('heading:date', 'arupapplication'), get_string('heading:message', 'arupapplication'));
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }
            $mform->addElement('html', html_writer::table($table));
        }

/// SPONSOR DETAILS ///

        $mform->addElement('header', 'sponsor', get_string('heading:sponsor', 'arupapplication'));

        $mform->addElement('text', 'sponsor_email', get_string('sponsoremail', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('sponsor_email', PARAM_EMAIL);
        $mform->addElement('textarea', 'sponsor_message', get_string('sponsormessage', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"'. $disablefields);
        $mform->setType('sponsor_message', PARAM_NOTAGS);

        if ($edit) {
            if (!$sponsorsubmitted && $status == 6) {
                $mform->addElement('submit', 'resendsponsoremail', get_string('button:resendsponsoremail', 'arupapplication'), array('class' => 'btn-default'));
            }
        }

        if (!empty($sponsor_audit)) {
            $mform->addElement('header', 'general', get_string('heading:previousemails', 'arupapplication'));

            $records = explode('$$$', $sponsor_audit);
            $table = new html_table();
            $table->cellpadding = 4;
            $table->attributes['class'] = 'generaltable boxalignleft';
            $table->head = array(get_string('heading:to', 'arupapplication'), get_string('heading:from', 'arupapplication'), get_string('heading:date', 'arupapplication'), get_string('heading:message', 'arupapplication'));
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }
            $mform->addElement('html', html_writer::table($table));
        }

/// ACTION BUTTONS ///

        if ($edit) {
            $buttonarray=array();

            if ($status < 6) {
                $buttonarray[] = &$mform->createElement('submit', 'submitapplication', get_string('button:submitapplication', 'arupapplication'), array('class' => 'btn-primary'));
                $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
            } else {
                $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
            }
            $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exitnosave', 'arupapplication'), array('class' => 'btn-danger'));

            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        }

/// TECHNICAL REFERENCE ///

        $mform->addElement('header', 'technicalreference', get_string('heading:technicalreference', 'arupapplication'));

        $mform->addElement('text', 'reference_phone', get_string('referencephone', 'arupapplication'), $disablefields);
        $mform->setType('reference_phone', PARAM_NOTAGS);

        $mform->addElement('text', 'referenceposition', get_string('referenceposition', 'arupapplication'), $disablefields);
        $mform->setType('referenceposition', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referenceknown', get_string('referenceknown', 'arupapplication'), 'wrap="virtual" rows="2" cols="65"' . $disablefields);
        $mform->setType('referenceknown', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referenceperformance', get_string('referenceperformance', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referenceperformance', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencetalent', get_string('referencetalent', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referencetalent', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencemotivation', get_string('referencemotivation', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referencemotivation', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referenceknowledge', get_string('referenceknowledge', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referenceknowledge', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencecomments', get_string('referencecomments', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"' . $disablefields);
        $mform->setType('referencecomments', PARAM_NOTAGS);

        if ($edit) {
            $mform->addElement('submit', 'submitreference', get_string('button:submitreference', 'arupapplication'), array('class' => 'btn-primary'));
        }

/// SPONSOR STATEMENT OF SUPPORT ///

        $mform->addElement('header', 'sponsorstatements', get_string('heading:sponsorstatement', 'arupapplication'));

        $mform->addElement('textarea', 'sponsorstatement', get_string('sponsorstatement', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"'. $disablefields);
        $mform->setType('sponsorstatement', PARAM_NOTAGS);
        $mform->addElement('advcheckbox', 'sponsordeclaration', '', $sponsordeclarationlabel, array('group' => 0, $disablefields), array(0, 1));

        if ($edit) {
            $mform->addElement('submit', 'submitsponsor', get_string('button:submitsponsor', 'arupapplication'), array('class' => 'btn-primary'));
        }

/// HIDDEN ELEMENTS ///

        $mform->addElement('hidden', 'editform', $edit);
        $mform->setType('editform', PARAM_INT);
        $mform->addElement('hidden', 'referencesubmitted', $referencesubmitted);
        $mform->setType('referencesubmitted', PARAM_INT);
        $mform->addElement('hidden', 'sponsorsubmitted', $sponsorsubmitted);
        $mform->setType('sponsorsubmitted', PARAM_INT);
        $mform->addElement('hidden', 'applicationid', $applicationid);
        $mform->setType('applicationid', PARAM_INT);
    }

    function definition_after_data() {
        $mform =& $this->_form;

        $fromform = $this->get_data();

        if ($fromform) {
            if (isset($fromform->submitapplication) || isset($fromform->savevalues)) {
                $mform->addRule('title', get_string('error:maxlength', 'arupapplication', $a=10), 'maxlength', 10, 'server', false, false);
                $mform->addRule('countryofresidence', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
                $mform->addRule('jobtitle', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
                $mform->addRule('discipline', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
                $mform->addRule('arupgroup', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
                $mform->addRule('otherofficelocation', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
            }
            if (isset($fromform->submitreference)) {
                $mform->addRule('reference_phone', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
                $mform->addRule('referenceposition', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
            }
        }
    }

    function validation($data, $files) {

        $errors = parent::validation($data, $files);

/// Resend email to technical referee checks ///
        // if technical reference has not been submitted
        // OR
        // on submission if no email sent yet
        if ((isset($data['resendreferenceemail']) && !$this->_customdata['referencesubmitted'])
            || (isset($data['submitapplication'])) && empty($this->_customdata['referee_audit'])) {
            if (! validate_email($data['referee_email'])) {
                $errors['referee_email'] = get_string('invalidemail');
            } else if (!arupapplication_validate_emailaddress($data['referee_email'])) {
                $errors['referee_email'] = get_string('invalidemail');
            } elseif (empty($data['referee_message'])) {
                $errors['referee_message'] = get_string('error:required', 'arupapplication');
            }
        }
/// Resend email to sponsor checks ///
        // if sponsor statement of support has not been submitted
        // AND form has been completed OR on submission
        if (((isset($data['resendsponsoremail']) && $this->_customdata['status'] == 6) || isset($data['submitapplication'])) && !$this->_customdata['sponsorsubmitted']) {
            if (! validate_email($data['sponsor_email'])) {
                $errors['sponsor_email'] = get_string('invalidemail');
            } else if (!arupapplication_validate_emailaddress($data['sponsor_email'])) {
                $errors['sponsor_email'] = get_string('invalidemail');
            } elseif (empty($data['sponsor_message'])) {
                $errors['sponsor_message'] = get_string('error:required', 'arupapplication');
            }
        }

        if (isset($data['submitreference'])) {
            foreach ($data as $key => $value) {
                switch($key) {
                    case 'reference_phone':
                    case 'referenceposition':
                    case 'referenceknown':
                    case 'referencetalent':
                    case 'referenceperformance':
                    case 'referencemotivation':
                    case 'referenceknowledge':
                    case 'referencecomments':
                        if (empty($value)) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                        break;
                }
            }
        }

        if (isset($data['submitsponsor'])) {
            foreach ($data as $key => $value) {
                switch($key) {
                    case 'sponsorstatement':
                    case 'sponsordeclaration':
                        if (empty($value)) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                        break;
                }
            }
        }

        if (isset($data['submitapplication']) || isset($data['savevalues'])) {
            foreach ($data as $key => $value) {
                switch($key) {
                    case 'title':
                    case 'passportname':
                    case 'dateofbirth':
                    case 'countryofresidence':
                    case 'grade':
                    case 'jobtitle':
                    case 'discipline':
                    case 'joiningdate':
                    case 'arupgroup':
                    case 'businessarea':
                    case 'degree':
                    case 'cv':
                        if (empty($value)) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                        break;
                    case 'officelocation':
                    case 'otherofficelocation':
                        if ($key == 'officelocation') {
                            if (empty($value)) {
                                $errors[$key] = get_string('error:required', 'arupapplication');
                            } else if ($value == 'Other' && empty($data['otherofficelocation'])) {
                                $errors['otherofficelocation'] = get_string('error:required', 'arupapplication');
                            }
                        }
                        break;
                }
            }
            // Statements
            foreach($this->questionstatements as $questionstatement) {
                if ($questionstatement->ismandatory && empty($data['qidanswer'. $questionstatement->id])) {
                    $errors['qidanswer'. $questionstatement->id] = get_string('error:required', 'arupapplication');
                }
            }
            // Declarations
            foreach($this->declarations as $declaration) {
                if (empty($data['declarationid'.$declaration->id])) {
                    $errors['declarationid'.$declaration->id] = get_string('error:required', 'arupapplication');
                }
            }
        }
        return $errors;
    }
}