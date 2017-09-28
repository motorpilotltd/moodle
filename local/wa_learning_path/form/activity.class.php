<?php

namespace wa_learning_path\form;

/*
 * Learning path activity form.
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
require_once($CFG->dirroot . '/local/wa_learning_path/lib/wa_editor.php');

/**
 * Defines the form for editing learning path introduction and summary.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2015 Webanywhere (http://www.webanywhere.co.uk)
 */
class activity_form extends \moodleform {

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

        $mform->_attributes['id'] = 'activitiyform';

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Description.

        $params = $this->get_editor_params();
        $editor = new \MoodleQuickForm_waeditor('content_editor', get_string('description', $pluginname), $params, $params);
        $content_editor = $mform->addElement($editor);
        $mform->setType('content_editor', PARAM_RAW);

        // Items. Regular input will be replaced by JS.
        $mform->addElement('static', 'items', get_string('items', $pluginname), '[WA_FORM]');
        $mform->addRule('items', $strrequired, 'required', null, 'client');
        $mform->setType('items', PARAM_TEXT);


        $this->add_action_buttons(true, get_string('save', $pluginname));
    }

    function getDraftId() {
        return $this->_form->getElement('content_editor')->getValue()['itemid'];
    }

    function setDraftId($draftid) {
        return $this->_form->getElement('content_editor')->setValue('itemid', $draftid);
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
            $buttonarray[] = &$mform->createElement('submit', 'asubmitbutton', $submitlabel);
            $buttonarray[] = &$mform->createElement('cancel', 'acancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        } else {
            //no group needed
            $mform->addElement('submit', 'submitbutton', $submitlabel);
            $mform->closeHeaderBefore('submitbutton');
        }
    }

    /**
     * Return default wysiwyg editor.
     * @return array
     * @throws \dml_exception
     */
    public function get_editor_params() {
        $systemcontext   = \context_system::instance();
        return array(
            'subdirs' => false,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'changeformat' => 0,
            'context' => $systemcontext,
            'noclean' => true,
            'trusttext' => 0,
            'autosave' => false,
            'enable_filemanagement' => true
        );
    }

    public function add_id_field() {
        $this->_form->insertElementBefore($this->_form->createElement('static', 'learningpath_id', get_string('learningpath_id', 'local_wa_learning_path')), 'id');
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
