<?php
namespace local_custom_certification\form;

use local_custom_certification\certification;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_certification_form extends \moodleform
{
    function definition()
    {
        $mform =& $this->_form;
        $certif = $this->_customdata['certif'];
        $mform->addElement('header', 'programdetails', get_string('recertificationdetails', 'local_custom_certification'));
        $header = \html_writer::tag('p', get_string('instructions:recertificationdetails', 'local_custom_certification'), ['class' => 'instructions']);
        $mform->addElement('html', $header);

        $recertificationdateoptions[certification::CERTIFICATION_EXPIRY_DATE] = get_string('useexpirydate', 'local_custom_certification');
        $recertificationdateoptions[certification::CERTIFICATION_COMPLETION_DATE] = get_string('usecompletiondate', 'local_custom_certification');
        $mform->addElement('select', 'recertificationdatetype', get_string('recertificationdate', 'local_custom_certification'), $recertificationdateoptions, []);
        $mform->setType('recertificationdatetype', PARAM_INT);
        $mform->setDefault('recertificationdatetype', isset($certif->recertificationdatetype) ? $certif->recertificationdatetype : certification::CERTIFICATION_EXPIRY_DATE);

        $certificationperiod = \html_writer::tag('p', get_string('certificationperiod', 'local_custom_certification'), ['class' => 'instructions']);
        $certificationperiod .= \html_writer::tag('p', get_string('instructions:certificationperiod', 'local_custom_certification'), ['class' => 'instructions']);
        $mform->addElement('html', $certificationperiod);
        $duration = certification::get_time_periods();

        $activeperiodtime=array();
        $activeperiodtime[] =& $mform->createElement('text', 'activeperiodtime', '', 'size="10"');
        $activeperiodtime[] =& $mform->createElement('select', 'activeperiodtimeunit', '', $duration);
        $mform->addGroup($activeperiodtime, 'activeperiodtimegr', get_string('certificationactive', 'local_custom_certification'), ' ', false);


        $mform->setDefault('activeperiodtime', $certif->activeperiodtime);
        $mform->setType('activeperiodtime', PARAM_INT);
        $mform->setDefault('activeperiodtimeunit', $certif->activeperiodtimeunit);

        $recertificationperiod = \html_writer::tag('p', get_string('recertificationperiod', 'local_custom_certification'), ['class' => 'instructions']);
        $mform->addElement('html', $recertificationperiod);

        $windowperiodtime=array();
        $windowperiodtime[] =& $mform->createElement('text', 'windowperiodtime', '', 'size="10"');
        $windowperiodtime[] =& $mform->createElement('select', 'windowperiodtimeunit', '', $duration);
        $mform->addGroup($windowperiodtime, 'windowperiodtimegr', get_string('recertificationwindowperiod', 'local_custom_certification'), ' ', false);

        $mform->setDefault('windowperiodtime', $certif->windowperiod);
        $mform->setType('windowperiodtime', PARAM_INT);
        $mform->setDefault('windowperiodtimeunit', $certif->windowperiodunit);

        $mform->addElement('submit', 'savedatabtn', get_string('savedatabtn', 'local_custom_certification'), ['class' => 'form-submit savedatabtn']);
    }

    function validation($data, $files)
    {
        $errors = [];
        
        $datetime = new \DateTime();
        $datetime->setTimestamp(0);
        $interval = new \DateInterval('P'.$data['activeperiodtime'].certification::get_time_period_for_interval($data['activeperiodtimeunit']));
        $datetime->add($interval);
        $active = $datetime->getTimestamp();
        
        $datetime = new \DateTime();
        $datetime->setTimestamp(0);
        $interval = new \DateInterval('P'.$data['windowperiodtime'].certification::get_time_period_for_interval($data['windowperiodtimeunit']));
        $datetime->add($interval);
        $window = $datetime->getTimestamp();
        
        if($window >= $active){
            $errors['windowperiodtimegr'] = get_string('error:windowperiodtime', 'local_custom_certification');
        }

        return $errors;
    }
}