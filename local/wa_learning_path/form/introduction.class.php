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
class introduction_form extends \moodleform {

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

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('learningpathid', PARAM_INT);

        // Region.
        $choices = \wa_learning_path\lib\get_regions();
        $select = $mform->addElement('select', 'region', get_string('region', $pluginname), $choices, array());
        $select->setMultiple(true);
        $mform->addRule('region', $strrequired, 'required', null, 'client');
        $mform->setDefault('region', '');

        // Category.
        $choices = \wa_learning_path\lib\get_categories();
        $select = $mform->addElement('select', 'category', get_string('category', $pluginname), $choices, array());
        $select->setMultiple(false);
        $mform->addRule('category', $strrequired, 'required', null, 'client');
        $mform->setDefault('category', WA_LEARNING_PATH_DRAFT);

        // Title.
        $mform->addElement('text', 'title', get_string('title', $pluginname), 'maxlength="254" size="50"');
        $mform->addRule('title', $strrequired, 'required', null, 'client');
        $mform->setType('title', PARAM_TEXT);

        // Summary.
        $mform->addElement('text', 'summary', get_string('summary', $pluginname), 'maxlength="254" size="50"');
        $mform->addRule('summary', $strrequired, 'required', null, 'client');
        $mform->setType('summary', PARAM_TEXT);

        // Introduction wysiwyg.
        $params = $this->get_editor_params();
        $mform->addElement('editor', 'introduction_editor', get_string('introduction', $pluginname), $params, $params);
        $mform->setType('introduction_editor', PARAM_RAW);

        // Image.
        $accepted_types = preg_split('/\s*,\s*/', trim($CFG->courseoverviewfilesext), -1, PREG_SPLIT_NO_EMPTY);
        $options = array(
            'maxfiles' => 1,
            'maxbytes' => 6*1024*1024,
            'subdirs' => 0,
            'accepted_types' => $accepted_types,
            'context' => \context_system::instance(),
        );

        $mform->addElement('filemanager', 'image_filemanager', get_string('image', $pluginname), null, $options);
        $mform->addHelpButton('image_filemanager', 'image', $pluginname);

        // Description.
        $mform->addElement('textarea', 'keywords', get_string('keywords', $pluginname));
        $mform->setType('keywords', PARAM_TEXT);

        // Status.
        $choices = array(WA_LEARNING_PATH_DRAFT => get_string('draft', $pluginname));
        $choices[WA_LEARNING_PATH_PUBLISH] = get_string('publish', $pluginname);
        $choices[WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE] = get_string('publish_not_visible', $pluginname);

        // Status.
        if (!\wa_learning_path\lib\has_capability('publishlearningpath')) {
            $options = array('disabled' => 'disabled');
        } else {
            $options = array();
        }

        $select = $mform->addElement('select', 'status', get_string('status', $pluginname), $choices, $options);
        $select->setMultiple(false);

        if (\wa_learning_path\lib\has_capability('publishlearningpath')) {
            $mform->addRule('status', $strrequired, 'required', null, 'client');
        }

        $mform->setDefault('status', WA_LEARNING_PATH_DRAFT);

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
            'enable_filemanagement' => true
        );
    }

    /**
     * Return default filemanager options
     * @return array
     * @throws \dml_exception
     */
    public static function get_filemanager_params() {
        global $CFG;
        $accepted_types = preg_split('/\s*,\s*/', trim($CFG->courseoverviewfilesext), -1, PREG_SPLIT_NO_EMPTY);
        return array(
            'maxfiles' => 1,
            'maxbytes' => 6*1024*1024,
            'subdirs' => 0,
            'accepted_types' => $accepted_types,
            'context' => \context_system::instance(),
        );
    }

    public function add_id_field() {
        //$this->_form->insertElementBefore($this->_form->createElement('static', 'learningpath_id', get_string('learningpath_id', 'local_wa_learning_path')), 'id');
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
