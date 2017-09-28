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

class submission_form extends moodleform {
    public function definition() {
        global $USER;

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

        $mform =& $this->_form;

        $mform->addElement('html', '<h2>' . get_string('heading:applicantdetails', 'arupapplication') . '</h2>');
        $mform->addElement('header', 'personal', get_string('legend:applicantdetails:personal', 'arupapplication'));

        $mform->addElement('text', 'title', get_string('title', 'arupapplication'));
        $mform->setType('title', PARAM_NOTAGS);
        $mform->addRule('title', get_string('error:maxlength', 'arupapplication', $a=10), 'maxlength', 10, 'server', false, false);
        $mform->addHelpButton('title', 'title', 'arupapplication');

        $mform->addElement('text', 'firstname', get_string('firstname', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->setDefault('firstname', $USER->firstname);

        $mform->addElement('text', 'lastname', get_string('surname', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->setDefault('lastname', $USER->lastname);

        $mform->addElement('text', 'passportname', get_string('passportname', 'arupapplication'));
        $mform->setType('passportname', PARAM_NOTAGS);
        $mform->addHelpButton('passportname', 'passportname', 'arupapplication');

        $mform->addElement('text', 'knownas', get_string('knownas', 'arupapplication'));
        $mform->setType('knownas', PARAM_NOTAGS);
        $mform->addHelpButton('knownas', 'knownas', 'arupapplication');

        $mform->addElement('date_selector', 'dateofbirth', get_string('dateofbirth', 'arupapplication'), array('timezone' => 0, 'startyear'=>date('Y')-70, 'stopyear'=>date('Y')-16));
        $mform->addHelpButton('dateofbirth', 'dateofbirth', 'arupapplication');

        $mform->addElement('text', 'countryofresidence', get_string('countryofresidence', 'arupapplication'));
        $mform->setType('countryofresidence', PARAM_NOTAGS);
        $mform->addRule('countryofresidence', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
        $mform->addHelpButton('countryofresidence', 'countryofresidence', 'arupapplication');

        $mform->addElement('selectyesno', 'requirevisa', get_string('requirevisa', 'arupapplication'));
        $mform->addHelpButton('requirevisa', 'requirevisa', 'arupapplication');

        $mform->addElement('header', 'arup', get_string('legend:applicantdetails:arup', 'arupapplication'));

        $mform->addElement('text', 'staffid', get_string('staffid', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('staffid', PARAM_NOTAGS);
        $mform->setDefault('staffid', $USER->idnumber);

        $mform->addElement('select', 'grade', get_string('grade', 'arupapplication'), $gradeoptions);

        $mform->addElement('text', 'jobtitle', get_string('jobtitle', 'arupapplication'));
        $mform->setType('jobtitle', PARAM_NOTAGS);
        $mform->addRule('jobtitle', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);

        $mform->addElement('text', 'discipline', get_string('discipline', 'arupapplication'));
        $mform->setType('discipline', PARAM_NOTAGS);
        $mform->addRule('discipline', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
        $mform->addHelpButton('discipline', 'discipline', 'arupapplication');

        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12,0,0,$i,15,2000), "%B");
        }
        for ($i=date('Y')-50; $i<=date("Y"); $i++) {
            $years[$i] = $i;
        }

        $selectarray=array();
        $selectarray[] =& $mform->createElement('select', 'joiningmonth', get_string('month', 'form'), $months, 0, true);
        $selectarray[] =& $mform->createElement('select', 'joiningyear', get_string('year', 'form'), $years, 0, true);
        $mform->addGroup($selectarray, 'joiningdate', get_string('joiningdate', 'arupapplication'), array(' '), false);
        $mform->addHelpButton('joiningdate', 'joiningdate', 'arupapplication');

        $mform->addElement('text', 'arupgroup', get_string('group', 'arupapplication'));
        $mform->setType('arupgroup', PARAM_NOTAGS);
        $mform->addRule('arupgroup', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
        $mform->addHelpButton('arupgroup', 'group', 'arupapplication');

        $mform->addElement('text', 'businessarea', get_string('businessarea', 'arupapplication'));
        $mform->setType('businessarea', PARAM_NOTAGS);
        $mform->addHelpButton('businessarea', 'businessarea', 'arupapplication');
        $mform->addRule('businessarea', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);

        $mform->addElement('text', 'region', get_string('region', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('region', PARAM_NOTAGS);
        $mform->setDefault('region', arupapplication_userregion());
        $mform->addHelpButton('region', 'region', 'arupapplication');

        $mform->addElement('select', 'officelocation', get_string('officelocation', 'arupapplication'), $officelocationoptions);

        $mform->addElement('text', 'otherofficelocation', get_string('otherofficelocation', 'arupapplication'));
        $mform->setType('otherofficelocation', PARAM_NOTAGS);
        $mform->addRule('otherofficelocation', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);
        $mform->disabledIf('otherofficelocation', 'officelocation', 'Neq', 'Other');

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'gopreviouspage', get_string('button:saveback', 'arupapplication'), array('class' => 'btn-default'));
        $buttonarray[] = &$mform->createElement('submit', 'gonextpage', get_string('button:savecontinue', 'arupapplication'), array('class' => 'btn-primary'));
        $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exitnosave', 'arupapplication'), array('class' => 'btn-danger'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('hidden', 'startdate', '');
        $mform->setType('startdate', PARAM_NOTAGS);
        $mform->addElement('hidden', 'gopage', 1);
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'thispage', 'details');
        $mform->setType('thispage', PARAM_ALPHA);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $todaysdate = gmmktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $currentmonth = gmmktime(0, 0, 0, date("m"), 1, date("Y"));
        $joiningdate = gmmktime(0, 0, 0, $data['joiningmonth'], 1, $data['joiningyear']);

        if($data['dateofbirth'] > $todaysdate) {
            $errors['dateofbirth'] = get_string('error:dateofbirth', 'arupapplication');
        }
        if($joiningdate >= $currentmonth) {
            $errors['joiningdate'] = get_string('error:joiningdate', 'arupapplication');
        } else {
            $data['startdate'] = $joiningdate;
        }
        return $errors;
    }
}