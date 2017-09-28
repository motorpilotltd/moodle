<?php

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir.'/formslib.php');

class local_learningpath_edit_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('html', html_writer::tag('div', get_string('header:edit', 'local_learningpath'), array('class' => 'learningpath-header')));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (has_capability('local/learningpath:edit', context_system::instance())) {
            $mform->addElement('select', 'categoryid', get_string('label:categoryid', 'local_learningpath'), learningpath_get_categories_list(null, true), array('class'=>'select2'));
            $mform->addRule('categoryid', null, 'required', null, 'client');
            $mform->addHelpButton('categoryid', 'label:categoryid','local_learningpath');
        }

        $mform->addElement('text', 'name', get_string('label:name', 'local_learningpath'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 150), 'maxlength', 150, 'client');
        $mform->addHelpButton('name', 'label:name','local_learningpath');

        $visibleoptions = array(
            0 => get_string('option:hidden', 'local_learningpath'),
            1 => get_string('option:visible', 'local_learningpath'),
        );
        $mform->addElement('select', 'visible', get_string('label:visible', 'local_learningpath'), $visibleoptions);

        $mform->addElement('textarea', 'description', get_string('label:description', 'local_learningpath'));
        $mform->setType('description', PARAM_RAW_TRIMMED);

        $mform->addElement('html', html_writer::tag('div', get_string('footer:edit', 'local_learningpath'), array('class' => 'learningpath-header')));

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('button:save', 'local_learningpath'));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('button:saveandclose', 'local_learningpath'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}