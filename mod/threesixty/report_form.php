<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_report_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $selffilters = $this->_customdata['selffilters'];
        $respondentfilters = $this->_customdata['respondentfilters'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('header', 'filters', get_string('filters', 'threesixty'));
        $mform->setExpanded('filters', false);

        if (!empty($selffilters)) {
            $selftypes = array();
            foreach ($selffilters as $code => $name) {
                $selftypes[] =& $mform->createElement('advcheckbox', $code, '', $name, array('group' => 'selftypes'));
                $mform->setDefault("selftype[$code]", 1);
            }
            $mform->addGroup($selftypes, 'selftype', get_string('selftype', 'threesixty'), html_writer::span('', 'threesixty-selftype-separator'));
            $mform->addGroupRule('selftype', get_string('selectatleastone', 'threesixty'), 'nonzero', null, 1);
        }

        $respondenttypes = array();
        foreach ($respondentfilters as $code => $name) {
            $respondenttypes[] =& $mform->createElement('advcheckbox', $code, '', $name, array('group' => 'respondenttypes'));
            $mform->setDefault("respondenttype[$code]", 1);
        }
        $mform->addGroup($respondenttypes, 'respondenttype', get_string('respondenttype', 'threesixty'), html_writer::span('', 'threesixty-respondenttype-separator'));

        $mform->addElement('advcheckbox', 'showrespondentaverage', get_string('showrespondentaverage', 'threesixty'));
        $mform->setDefault('showrespondentaverage', 1);

        $mform->addElement('submit', 'submitbutton', get_string('applybutton', 'threesixty'));
    }
}
