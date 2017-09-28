<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Activity editing form for mod_arupadvert.
 *
 * @package     mod_arupadvert
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Activity editing form class for mod_arupadvert.
 *
 * @package   mod_arupadvert
 * @copyright 2016 Motorpilot Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_arupadvert_mod_form extends moodleform_mod {

    /** @var array $_datatypes */
    protected $_datatypes;

    /**
     * Extra form feature initialisation.
     *
     * @return void
     */
    protected function init_features() {
        parent::init_features();
        global $PAGE;
        $this->_datatypes = arupadvert_load_datatypes();
        // Add JS.
        $PAGE->requires->js_call_amd('mod_arupadvert/edit', 'initialise');
    }

    /**
     * Called to define this moodle form.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        // If adding, check to see if one already exists.
        if (!$this->current->instance) {
            $sql = "SELECT COUNT(*)
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                WHERE m.name = :modulename AND cm.course = :courseid
                ";
            $params = array(
                'modulename' => 'arupadvert',
                'courseid' => $this->current->course
            );
            if ($DB->get_field_sql($sql, $params)) {
                $url = new moodle_url('/course/view.php', array('id' => $this->current->course));
                notice(get_string('alreadyexists', 'arupadvert'), $url);
                exit;
            }
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'arupadvert'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $filemanageroptions = array();
        $filemanageroptions['return_types'] = 3;
        $filemanageroptions['accepted_types'] = array('web_image');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['mainfile'] = false;

        $mform->addElement('filemanager', 'advertblockimage', get_string('advertblockimage', 'arupadvert'), null, $filemanageroptions);
        $mform->addHelpButton('advertblockimage', 'advertblockimage', 'arupadvert');

        $datatypes = arupadvert_available_datatypes();
        $mform->addElement('select', 'datatype', get_string('datatype', 'arupadvert'), $datatypes);
        $mform->addRule('datatype', null, 'required', null, 'client');

        // Alternative word.
        $mform->addElement('text', 'altword', get_string('altword', 'arupadvert', core_text::strtolower(get_string('course'))), array('size' => '64'));
        $mform->setType('altword', PARAM_TEXT);
        $mform->addRule('altword', null, 'maxlength', 255, 'client', false, false);

        // Show headings.
        $mform->addElement('checkbox', 'showheadings', get_string('showheadings', 'arupadvert'));
        $mform->setDefault('showheadings', 1);

        foreach ($this->_datatypes as $datatype) {
            $datatype->mod_form_definition($mform, $this->current, $this->context);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Set form data.
     *
     * @param array|stdClass $defaultvalues
     */
    public function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }

        if (isset($defaultvalues['datatype']) && isset($this->_datatypes[$defaultvalues['datatype']])) {
            $this->_datatypes[$defaultvalues['datatype']]->mod_form_set_data($defaultvalues);
        }

        parent::set_data($defaultvalues);
    }

    /**
     * Carry out data preprocessing.
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('advertblockimage');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_arupadvert', 'originalblockimage', 0);
            $defaultvalues['advertblockimage'] = $draftitemid;
        }
    }

    /**
     * Carry definition after data.
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform =& $this->_form;

        $currentdatatype = $mform->exportValue('datatype');

        foreach ($this->_datatypes as $name => $datatype) {
            if ($name != $currentdatatype) {
                $datatype->mod_form_remove_elements($mform);
            }
        }

    }

    /**
     * Carry out validation.
     *
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($this->_datatypes[$data['datatype']])) {
            $errors = $errors + $this->_datatypes[$data['datatype']]->mod_form_validation($data, $files);
        }

        return $errors;
    }
}