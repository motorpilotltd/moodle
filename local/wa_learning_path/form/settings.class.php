<?php

namespace wa_learning_path\form;

/*
 * Learning path introduction and summary form.
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

        $PAGE->requires->js(new \moodle_url($CFG->wwwroot . '/local/wa_learning_path/js/jquery.minicolors.min.js'));
        $PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/local/wa_learning_path/css/jquery.minicolors.css'));

        $mform = & $this->_form;
        $pluginname = 'local_wa_learning_path';
        $cohort = $this->_customdata['data'];
        
        // Section Header.
        $mform->addElement('header', 'header_cohort_settings', get_string('header_cohort_settings', $pluginname));

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('cohortid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'cohort'), 'maxlength="254" size="50"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'cohort'), 'maxlength="254" size="50"');
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->setDefault('idnumber', '');
        
        $this->add_action_buttons(true, get_string('save', $pluginname));

    }

    /**
     * Add hidden field to form.
     * @param String Field name.
     * @param String Value of field.
     */
    public function add_hidden($name, $value) {
        $this->_form->addElement('hidden', $name, $value);
    }

}
