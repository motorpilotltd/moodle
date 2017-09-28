<?php

namespace wa_learning_path\form;

/*
 * Learning path introduction and summary form (the first tab).
 *
 * @package     local_wa_learning_path
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Defines the form for editing learning path introduction and summary.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2015 Webanywhere (http://www.webanywhere.co.uk)
 */
class matrix_form extends \moodleform {

    /**
     * Overrides the abstract moodleform::definition method for defining what the form that is to be
     * presented to the user.
     */
    public function definition() {
        global $PAGE, $CFG;
        \wa_learning_path\lib\load_model('learningpath');

        $mform = & $this->_form;
        $pluginname = 'local_wa_learning_path';
        $strrequired = get_string('required');
        $learningpath = $this->_customdata['data'];
        $mform->_attributes['id'] = 'matrixform';

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'matrix');
        $mform->setType('matrix', PARAM_RAW);

        $mform->addElement('hidden', 'returnhash');
        $mform->setType('returnhash', PARAM_RAW);

        $this->add_action_buttons(true, get_string('save', $pluginname));
    }

    function add_action_buttons($cancel = true, $submitlabel=null){
        if (is_null($submitlabel)){
            $submitlabel = get_string('savechanges');
        }

        $mform =& $this->_form;
        if ($cancel){
            //when two elements we need a group
            $buttonarray=array();
            $pluginname = 'local_wa_learning_path';
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('save_and_close', $pluginname));
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton3', get_string('save_and_display', $pluginname));
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        } else {
            //no group needed
            $mform->addElement('submit', 'submitbutton', $submitlabel);
            $mform->closeHeaderBefore('submitbutton');
        }
    }

    public function add_id_field() {
        $this->_form->insertElementBefore($this->_form->createElement('hidden', 'learningpath_id', get_string('learningpath_id', 'local_wa_learning_path')), 'id');
    }

    /**
     * Add hidden field to form.
     * @param String Field name.
     * @param String Value of field.
     */
    public function add_hidden($name, $value) {
        $this->_form->addElement('hidden', $name, $value);
    }

    public function add_header($text) {
        $this->_form->insertElementBefore($this->_form->createElement('header', 'moodle', $text), 'id');
    }

    public function get_all_errors() {
        return $this->_form->_errors;
    }

}
