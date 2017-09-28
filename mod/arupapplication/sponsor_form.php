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

class sponsor_form extends moodleform {
    public function definition() {
        global $DB;

        $mform =& $this->_form;

        $contextid = $this->_customdata['contextid'];
        $cmid = $this->_customdata['cmid'];
        $userid = $this->_customdata['userid'];
        $applicationid = $this->_customdata['applicationid'];
        $submissionid = $this->_customdata['submissionid'];
        $sponsorsubmitted = $this->_customdata['sponsorsubmitted'];
        $sponsorstatement_hint = $this->_customdata['sponsorstatement_hint'];
        $sponsordeclarationlabel = $this->_customdata['sponsordeclarationlabel'];
        $footermessage = $this->_customdata['footermessage'];

        $submissiondetails = $DB->get_record('arupsubmissions', array('id'=>$submissionid, 'applicationid'=>$applicationid, 'userid'=>$userid));

        $user = $DB->get_record('user', array('id' => $userid));

        //Statement question answers
        $sql = "SELECT sqs.id as questionid, sqs.question, ap.id as applicationid, san.answer, san.id
            FROM   {arupapplication} ap
            INNER JOIN {arupstatementquestions} sqs ON ap.id = sqs.applicationid
            LEFT JOIN {arupstatementanswers} san ON sqs.id = san.questionid AND san.userid = " . $userid . "
            WHERE ap.id = " . $applicationid;
        $questionsanswers = $DB->get_records_sql($sql);
        //Declaration statements
        $sql = "SELECT dqs.id as declarationid, dqs.declaration, ap.id as applicationid, dac.answer, dac.id
FROM {arupapplication} ap
INNER JOIN {arupdeclarations} dqs ON ap.id = dqs.applicationid
LEFT JOIN {arupdeclarationanswers} dac ON dqs.id = dac.declarationid AND dac.userid = " . $userid . "
WHERE ap.id = " . $applicationid;
        $declarationanswers = $DB->get_records_sql($sql);


        $mform->addElement('html', '<h2>' . get_string('heading:sponsorstatement', 'arupapplication') . '</h2>');

        $mform->addElement('html', html_writer::tag('div', $sponsorstatement_hint, array('class' => 'hint')));

        $mform->addElement('textarea', 'sponsorstatement', get_string('sponsorstatement', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('sponsorstatement', PARAM_NOTAGS);
        $mform->addElement('advcheckbox', 'sponsordeclaration', '', $sponsordeclarationlabel, array('group' => 0), array(0, 1));
        if ($submissiondetails) {
            $mform->setDefault('sponsorstatement', $submissiondetails->sponsorstatement);
            $mform->setDefault('sponsordeclaration', $submissiondetails->sponsordeclaration);
        }

        $mform->addElement('hidden', 'sponsorcompleted', $sponsorsubmitted);
        $mform->setType('sponsorcompleted', PARAM_INT);

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'confirmsubmit', get_string('button:submit', 'arupapplication'), array('class' => 'btn-primary'));
        $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));

        if ($sponsorsubmitted) {
            $cancellabel = get_string('button:continue', 'arupapplication');
            $cancelclass = array('class' => 'btn-default');
        } else {
            $cancellabel = get_string('button:exitnosave', 'arupapplication');
            $cancelclass = array('class' => 'btn-danger');
        }
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', $cancellabel, $cancelclass);

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');


        $mform->addElement('html', '<h2>' . get_string('heading:applicationcompleted', 'arupapplication') . '</h2>');

        if ($submissiondetails) {

            $mform->addElement('header', 'applicantdetails', get_string('heading:applicantdetails', 'arupapplication'));

            $mform->addElement('static', 'title', get_string('title', 'arupapplication'));
            $mform->setDefault('title', $submissiondetails->title);

            $mform->addElement('static', 'firstname', get_string('firstname', 'arupapplication'));
            $mform->setDefault('firstname', $user->firstname);

            $mform->addElement('static', 'lastname', get_string('surname', 'arupapplication'));
            $mform->setDefault('lastname', $user->lastname);

            $mform->addElement('static', 'passportname', get_string('passportname', 'arupapplication'));
            $mform->setDefault('passportname', $submissiondetails->passportname);

            $mform->addElement('static', 'knownas', get_string('knownas', 'arupapplication'));
            $mform->setDefault('knownas', $submissiondetails->knownas);


            $mform->addElement('static', 'dob', get_string('dateofbirth', 'arupapplication'));
            $mform->setDefault('dob', gmdate("d/m/Y", $submissiondetails->dateofbirth));

            $mform->addElement('static', 'countryofresidence', get_string('countryofresidence', 'arupapplication'));
            $mform->setDefault('countryofresidence', $submissiondetails->countryofresidence);

            if($submissiondetails->requirevisa == 0) {
                $thisvisarequired = 'No';
            } else {
                $thisvisarequired = 'Yes';
            }
            $mform->addElement('static', 'visa', get_string('requirevisa', 'arupapplication'));
            $mform->setDefault('visa', $thisvisarequired);

            $mform->addElement('header', 'arup', get_string('legend:applicantdetails:arup', 'arupapplication'));

            $mform->addElement('static', 'staffid', get_string('staffid', 'arupapplication'));
            $mform->setDefault('staffid', $user->idnumber);

            $mform->addElement('static', 'thisgrade', get_string('grade', 'arupapplication'));
            $mform->setDefault('thisgrade', $submissiondetails->grade);

            $mform->addElement('static', 'jobtitle', get_string('jobtitle', 'arupapplication'));
            $mform->setDefault('jobtitle', $submissiondetails->jobtitle);

            $mform->addElement('static', 'discipline', get_string('discipline', 'arupapplication'));
            $mform->setDefault('discipline', $submissiondetails->discipline);

            $mform->addElement('static', 'joining', get_string('joiningdate', 'arupapplication'));
            $mform->setDefault('joining', gmdate("m/Y", $submissiondetails->joiningdate));

            $mform->addElement('static', 'arupgroup', get_string('group', 'arupapplication'));
            $mform->setDefault('arupgroup', $submissiondetails->arupgroup);

            $mform->addElement('static', 'businessarea', get_string('businessarea', 'arupapplication'));
            $mform->setDefault('businessarea', $submissiondetails->businessarea);

            $mform->addElement('static', 'region', get_string('region', 'arupapplication'));
            $mform->setDefault('region', arupapplication_userregion($userid));

            $mform->addElement('static', 'officelocation', get_string('officelocation', 'arupapplication'));
            $mform->setDefault('officelocation', $submissiondetails->officelocation);

            $mform->addElement('static', 'otherofficelocation', get_string('otherofficelocation', 'arupapplication'));
            $mform->setDefault('otherofficelocation', $submissiondetails->otherofficelocation);

            if ($questionsanswers) {
                $mform->addElement('header', 'statement', get_string('heading:statement', 'arupapplication'));
                foreach($questionsanswers as $questionsanswer) {
                    $mform->addElement('static', 'qidanswer'. $questionsanswer->id, $questionsanswer->question);
                    $mform->setDefault('qidanswer'. $questionsanswer->id, format_string($questionsanswer->answer));
                }
            }

            $mform->addElement('header', 'qualifications', get_string('heading:qualifications', 'arupapplication'));

            $mform->addElement('static', 'degree', get_string('degree', 'arupapplication'));
            $mform->setDefault('degree', $submissiondetails->degree);

            $fs = get_file_storage();
            $files = $fs->get_area_files($contextid, 'mod_arupapplication', 'submission', $submissiondetails->id);
            if ($files) {
                $mform->addElement('static', 'cvfile', get_string('cv', 'arupapplication'));
                $file = array_pop($files);
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                $mform->setDefault('cvfile', html_writer::link($url, $file->get_filename()));
            }

            if ($declarationanswers) {
                $mform->addElement('header', 'declaration', get_string('heading:declaration', 'arupapplication'));
                foreach($declarationanswers as $declarationanswer) {
                    if ($declarationanswer->answer == 1) {
                        $ans = 'Yes';
                    } else {
                        $ans = 'No';
                    }
                    $mform->addElement('static', 'declarationans'. $declarationanswer->declarationid, $declarationanswer->declaration);
                    $mform->setDefault('declarationans'. $declarationanswer->declarationid, $ans);
                }
            }

        }
        $mform->addElement('html', html_writer::tag('div', $footermessage, array('class' => 'hint')));
    }

    function definition_after_data() {

        $mform =& $this->_form;

        if ($mform->elementExists('sponsorcompleted')) {
            $valuesponsorcompleted = $mform->getElementValue('sponsorcompleted');
            if ($valuesponsorcompleted == 1) {
                $mform->disabledIf('sponsorstatement', 'sponsorcompleted', 'eq', 1);
                $mform->disabledIf('sponsordeclaration', 'sponsorcompleted', 'eq', 1);
                $mform->disabledIf('confirmsubmit', 'sponsorcompleted', 'eq', 1);
                $mform->disabledIf('savevalues', 'sponsorcompleted', 'eq', 1);
            }
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['confirmsubmit'])) {
            foreach ($data as $key => $value) {
                switch($key) {
                    case 'sponsorstatement':
                        if (empty($value)) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                    break;
                    case 'sponsordeclaration':
                        if ($value == 0) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                        break;
                }
            }
        }
        return $errors;
    }
}