<?php
namespace local_custom_certification\form;

require_once($CFG->dirroot . '/lib/formslib.php');

class certification_certification_filter_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform       =& $this->_form;
        $categories = $this->_customdata['categories'];

        $mform->addElement('html', \html_writer::tag('h4', get_string('certifications', 'local_custom_certification'), ['class' => 'programcontent']));

        $mform->addElement('text', 'fullname', get_string('fullname', 'local_custom_certification'), 'maxlength="254" size="50"');
        $mform->setType('fullname', PARAM_TEXT);

        $select = $mform->addElement('select', 'category', get_string('category', 'local_custom_certification'), $categories);
        $select->setMultiple(true);
        $mform->setType('category', PARAM_INT);

        $mform->addElement('checkbox', 'visible', get_string('visibleprogram', 'local_custom_certification'));
        $mform->setType('visible', PARAM_INT);

        $mform->addElement('submit', 'addfilter', get_string('filter', 'local_custom_certification'));
        $mform->disable_form_change_checker();
    }
}