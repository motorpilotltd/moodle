<?php
namespace block_certification_report\form;


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_report_exemption_form extends \moodleform
{
    function definition()
    {
        $mform =& $this->_form;

        $exemption = $this->_customdata['exemption'];

        $mform->addElement('hidden', 'exemption_certifid', $this->_customdata['certifid']);
        $mform->setType('exemption_certifid', PARAM_INT);
        $mform->addElement('hidden', 'exemption_userid', $this->_customdata['userid']);
        $mform->setType('exemption_userid', PARAM_INT);
        $mform->addElement('text', 'reason', get_string('reason', 'block_certification_report'));
        $mform->setType('reason', PARAM_TEXT);
        if($exemption){
            $mform->setDefault('reason', $exemption->reason);
        }

        $mform->addElement('date_selector', 'timeexpires', get_string('exemptionexpiry', 'block_certification_report'), ['startyear' => date('Y'), 'optional' => true]);
        $mform->setType('timeexpires', PARAM_INT);

        if($exemption){
            $mform->setDefault('timeexpires', $exemption->timeexpires);
        }


        if(has_capability('block/certification_report:set_exemption', \context_system::instance())) {
            $btngroup [] = $mform->createElement('button', 'savedatabtn', get_string('savedatabtn', 'block_certification_report'), ['class' => 'btn-primary savebtn']);
        }
        $btngroup [] = $mform->createElement('button', 'cancelbtn', get_string('cancelbtn', 'block_certification_report'), ['class' => 'btn-cancel cancelbtn']);
        if($exemption && has_capability('block/certification_report:set_exemption', \context_system::instance())) {
            $btngroup [] = $mform->createElement('button', 'deletebtn', get_string('deletebtn', 'block_certification_report'), ['class' => 'btn-danger deletebtn']);
        }
        $mform->addGroup($btngroup, 'btngroup');

        if($exemption){
            $mform->addElement('html', \html_writer::span(get_string('createdby', 'block_certification_report').' '.$exemption->modifier.' '.get_string('on', 'block_certification_report').' '.userdate($exemption->timecreated), 'note-author'));
        }

        $mform->disable_form_change_checker();
    }
}