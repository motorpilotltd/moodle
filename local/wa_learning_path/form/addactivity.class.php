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
class addactivity_form extends \moodleform {
    
    /**
     * Overrides the abstract moodleform::definition method for defining what the form that is to be
     * presented to the user.
     */
    public function definition() {
        global $PAGE, $CFG;

        $taps = new \local_taps\taps();

        \wa_learning_path\lib\load_model('activity');

        $mform = & $this->_form;
        $pluginname = 'local_wa_learning_path';
        $strrequired = get_string('required');

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'idlearningpath');
        $mform->setType('idlearningpath', PARAM_INT);

        // Title.
        $mform->addElement('text', 'title', get_string('title', $pluginname), 'maxlength="254" size="50"');
        $mform->addRule('title', $strrequired, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('title', PARAM_TEXT);

        // Region.
        $choices = \wa_learning_path\lib\get_regions();
        $select = $mform->addElement('select', 'region', get_string('region', $pluginname), $choices, array());
        $select->setMultiple(true);
        $mform->addRule('region', $strrequired, 'required', null, 'client');
        $mform->setDefault('region', '');

        // Type.
        $type = $this->get_activity_type();
        $select = $mform->addElement('select', 'type', get_string('activity_type', $pluginname), $type, array());
        $select->setMultiple(false);
        $mform->addRule('type', $strrequired, 'required', null, 'client');
        $mform->setDefault('type', \wa_learning_path\model\activity::TYPE_VIDEO);

        // Description wysiwyg.
        $params = $this->get_editor_params();
        $mform->addElement('editor', 'description_editor', get_string('activity_description', $pluginname), array('id' => 'activity_description_id'),
                $params);
        $mform->setType('description_editor', PARAM_RAW);

        // Header for CPD
        $mform->addElement('header', 'cdpfieldsassociated', get_string('cdp_fields_associated', $pluginname));

        // Enable send to CPD
        $mform->addElement('checkbox', 'enablecdp', get_string('enable_send_to_cdp', $pluginname));

        // Learning method
        $choices = $taps->get_classtypes('cpd');
        $select = $mform->addElement('select', 'learningmethod', get_string('learningmethod', $pluginname), $choices,
                array());
        $select->setMultiple(false);
        $mform->addRule('learningmethod', $strrequired, 'required', null, 'client');
        $mform->setDefault('learningmethod', 'SELF-LED');

        // Provider.
        $mform->addElement('text', 'provider', get_string('provider', $pluginname), 'maxlength="254" size="50"');
        $mform->addRule('provider', $strrequired, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('provider', PARAM_TEXT);
        $mform->setDefault('provider', get_string('self-led_learning', $pluginname));

        // Duration
        $durationarray[] = $mform->createElement('text', 'duration', get_string('duration', $pluginname),
                'maxlength="99" size="4"');
        $mform->setType('duration', PARAM_INT);
        $mform->setDefault('duration', '0');
        $durationarray[] = $mform->createElement('select', 'unit', get_string('duration_unit', $pluginname),
                $this->get_duration_unit());
        $mform->setDefault('unit', 'MIN');
        $separator = \html_writer::span(get_string('duration_unit', $pluginname), 'duration_unit_text');
        $mform->addGroup($durationarray, 'durationarray', get_string('duration', $pluginname), $separator, false);
        $mform->addRule('durationarray', $strrequired, 'required', null, 'client');
        
        // Subject category.
        $choices = $taps->get_classcategory();
        $select = $mform->addElement('select', 'subject', get_string('subject_category', $pluginname), $choices, array());
        $select->setMultiple(false);
        $mform->setDefault('subject', 'PD');

        // Learning description
        $mform->addElement('editor', 'learningdescription', get_string('learning_description', $pluginname));
        $mform->setType('learningdescription', PARAM_RAW);

        if(!isset($this->_customdata['submit_button']) || ( isset($this->_customdata['submit_button']) && $this->_customdata['submit_button'] == true ) ){
            $this->add_action_buttons(true, get_string('save', $pluginname));
        }
    }

    function add_action_buttons($cancel = true, $submitlabel = null) {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('savechanges');
        }
        $mform = & $this->_form;
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Gets list of acitivty types
     * @return Array Subjects category
     */
    public function get_activity_type($empty = true) {
        $component = 'local_wa_learning_path';
        \wa_learning_path\lib\load_model('activity');

        $list = array(
            \wa_learning_path\model\activity::TYPE_VIDEO => get_string('type_' . \wa_learning_path\model\activity::TYPE_VIDEO,
                    $component),
            \wa_learning_path\model\activity::TYPE_TEXT => get_string('type_' . \wa_learning_path\model\activity::TYPE_TEXT,
                    $component),
            'external_course' => get_string('type_external_course', $component),
            'teaching' => get_string('type_teaching', $component),
            'projectwork' => get_string('type_projectwork', $component),
            'mentoring' => get_string('type_mentoring', $component),
            'other' => get_string('type_other', $component),
        );

        if ($empty) {
            $list = array('' => get_string('choose_one', $component)) + $list;
        }

        return $list;
    }

    /**
     * Gets list of acitivt duration units
     * @return Array Subjects category
     */
    public function get_duration_unit() {
        $component = 'local_wa_learning_path';

        $list = array(
            'H' => get_string('H', $component),
            'HPM' => get_string('HPM', $component),
            'HPW' => get_string('HPW', $component),
            'M' => get_string('M', $component),
            'MIN' => get_string('MIN', $component),
            'Q' => get_string('Q', $component),
            'W' => get_string('W', $component),
            'Y' => get_string('Y', $component),
        );

        return $list;
    }

    /**
     * Return default wysiwyg editor.
     * @return array
     * @throws \dml_exception
     */
    public function get_editor_params() {
        $systemcontext = \context_system::instance();
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
            'maxbytes' => 6 * 1024 * 1024,
            'subdirs' => 0,
            'accepted_types' => $accepted_types,
            'context' => \context_system::instance(),
        );
    }

    public function add_id_field() {
        $this->_form->insertElementBefore($this->_form->createElement('static', 'learningpath_id',
                        get_string('learningpath_id', 'local_wa_learning_path')), 'id');
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
