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

class viewsubmission_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $contextid = $this->_customdata['contextid'];
        $cmid = $this->_customdata['cmid'];
        $userid = $this->_customdata['userid'];
        $applicationid = $this->_customdata['applicationid'];
        $submissionid = $this->_customdata['submissionid'];

        $applicationdetails = $DB->get_record('arupapplication', array('id'=>$applicationid));
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

            if (!empty($submissiondetails->otherofficelocation)) {
                $mform->addElement('static', 'otherofficelocation', get_string('otherofficelocation', 'arupapplication'));
                $mform->setDefault('otherofficelocation', $submissiondetails->otherofficelocation);
            }

            if ($questionsanswers) {
                $mform->addElement('header', 'statement', get_string('heading:statement', 'arupapplication'));
                foreach($questionsanswers as $questionsanswer) {
                    $mform->addElement('static', 'qidanswer'. $questionsanswer->id, $questionsanswer->question);
                    $mform->setDefault('qidanswer'. $questionsanswer->id, $questionsanswer->answer);
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
                    $mform->addElement('checkbox', 'declarationid'.$declarationanswer->id, '', $declarationanswer->declaration, array('disabled'=>'disabled'));
                    $mform->setDefault('declarationid'.$declarationanswer->id, $declarationanswer->answer);
                }
            }

            $mform->addElement('header', 'techinicalreferee', get_string('heading:technicalreference', 'arupapplication'));

            if ($submissiondetails->referencesubmitted == 0) {
                $refereeemailresendlink = '<a href="'.$CFG->wwwroot.'/mod/arupapplication/complete.php?id='. $cmid .'&gopage=0">' . get_string('resendemail', 'arupapplication') . '</a>';
            } else {
                $refereeemailresendlink = '';
            }

            $mform->addElement('checkbox', 'referencesubmitted', get_string('progress:receivedtechnicalreference', 'arupapplication'), $refereeemailresendlink, array('disabled'=>'disabled'));
            if ($submissiondetails->referencesubmitted) {
                $mform->setDefault('referencesubmitted', 1);
            }

            if ($applicationdetails->sponsorstatementreq) {
                $mform->addElement('header', 'sponsorstatements', get_string('heading:sponsorstatement', 'arupapplication'));

                if ($submissiondetails->sponsorsubmitted == 0) {
                    $sponsoremailresendlink = '<a href="'.$CFG->wwwroot.'/mod/arupapplication/complete.php?id='. $cmid .'&gopage=5">' . get_string('resendemail', 'arupapplication') . '</a>';
                } else {
                    $sponsoremailresendlink = '';
                }

                $mform->addElement('checkbox', 'sponsorsubmitted', get_string('progress:receivedsponsorstatement', 'arupapplication'), $sponsoremailresendlink, array('disabled'=>'disabled'));
                if ($submissiondetails->sponsorsubmitted) {
                    $mform->setDefault('sponsorsubmitted', 1);
                }
            }
        }

       // new dBug($submissiondetails);


    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}