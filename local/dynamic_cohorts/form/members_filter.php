<?php
namespace local_dynamic_cohorts\form;

require_once($CFG->dirroot . '/lib/formslib.php');

class dynamic_cohorts_members_filter_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform =& $this->_form;
        
        $mform->addElement('text', 'fullname', get_string('fullname'));
        $mform->setType('fullname', PARAM_TEXT);

        $mform->addElement('submit', 'addfilter', get_string('search'));
        $mform->disable_form_change_checker();
    }
}