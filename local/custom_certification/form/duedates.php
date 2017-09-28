<?php
namespace local_custom_certification\form;

use local_custom_certification\certification;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_duedates_form extends \moodleform
{
    function definition()
    {
        $mform =& $this->_form;
        $assignment = $this->_customdata['assignment'];
        
        $optional[] =& $mform->createElement('checkbox', 'duedateoptional', '', get_string('enable', 'local_custom_certification'));

        $mform->addGroup($optional, 'duedateoptionalgr', get_string('optional', 'local_custom_certification'), ' ', false);

        if ($assignment->duedatetype == certification::DUE_DATE_NOT_SET) {
            $mform->setDefault('duedateoptional', true);
        }

        $mform->addElement('date_selector', 'fixeddate', get_string('to'), ['startyear' => date('Y'), 'optional' => true]);
        $mform->setType('fixeddate', PARAM_INT);
        $datetype = 0;

        if ($assignment->duedatetype == certification::DUE_DATE_FIXED) {
            $datetype = $assignment->duedateperiod;
            $mform->setDefault('fixeddate[enabled]', true);
        }

        $mform->setDefault('fixeddate', $datetype);

        $duration = certification::get_time_periods();

        $duedatefromfirstlogin[] =& $mform->createElement('text', 'duedatefromfirstlogin', '', 'size="10"');
        $duedatefromfirstlogin[] =& $mform->createElement('select', 'duedatefromfirstloginunit', '', $duration);
        $duedatefromfirstlogin[] =& $mform->createElement('checkbox', 'duedatefromfirstlogincheck', '', get_string('enable', 'local_custom_certification'));

        $mform->addGroup($duedatefromfirstlogin, 'duedatefromfirstlogingr', get_string('duedatefromfirstlogin', 'local_custom_certification'), ' ', false);

        $datetype = 0;
        if ($assignment->duedatetype == certification::DUE_DATE_FROM_FIRST_LOGIN) {
            $datetype = $assignment->duedateperiod;
            $mform->setDefault('duedatefromfirstlogincheck', true);

        }
        $mform->setDefault('duedatefromfirstlogin', $datetype);
        $mform->setType('duedatefromfirstlogin', PARAM_INT);
        $mform->setDefault('duedatefromfirstloginunit', $assignment->duedateunit);

        $duedatefromenrolment[] =& $mform->createElement('text', 'duedatefromenrolment', '', 'size="10"');
        $duedatefromenrolment[] =& $mform->createElement('select', 'duedatefromenrolmentunit', '', $duration);
        $duedatefromenrolment[] =& $mform->createElement('checkbox', 'duedatefromenrolmentcheck', '', get_string('enable', 'local_custom_certification'));
        $mform->addGroup($duedatefromenrolment, 'duedatefromenrolmentgr', get_string('duedatefromenrolment', 'local_custom_certification'), ' ', false);

        $datetype = 0;
        if ($assignment->duedatetype == certification::DUE_DATE_FROM_ENROLMENT) {
            $datetype = $assignment->duedateperiod;
            $mform->setDefault('duedatefromenrolmentcheck', true);
        }
        $mform->setDefault('duedatefromenrolment', $datetype);
        $mform->setType('duedatefromenrolment', PARAM_INT);
        $mform->setDefault('duedatefromenrolmentunit', $assignment->duedateunit);


        $btngroup [] = $mform->createElement('submit', 'savedatabtn', get_string('savedatabtn', 'local_custom_certification'), ['class' => 'savedatabtn']);
        $btngroup [] = $mform->createElement('button', 'cancelbtn', get_string('cancelbtn', 'local_custom_certification'), ['class' => 'cancelbtn']);
        $mform->addGroup($btngroup, 'btngroup');
        $mform->disable_form_change_checker();
    }
}