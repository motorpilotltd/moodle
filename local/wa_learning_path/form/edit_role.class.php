<?php

namespace wa_learning_path\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

class edit_role_form extends \moodleform {

    private $learningPathId;
    private $roleData;

    /**
     * Overrides the abstract moodleform::definition method for defining what the form that is to be
     * presented to the user.
     */
    public function definition() {
        global $PAGE, $CFG, $DB;
        \wa_learning_path\lib\load_model('learningpath');

        $mform = & $this->_form;
        $this->learningPathId = $this->_customdata['id'];
        $this->roleId = $this->_customdata['role_id'];
        $pluginname = 'local_wa_learning_path';
        $mform->_attributes['id'] = 'matrixform';
        $mform->_attributes['class'] = 'mform edit_role';
        
        if($this->roleId) {
            $this->roleData = $DB->get_record('wa_learning_path_role', ['id' => $this->roleId]);
        }
        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'role_id', $this->roleId);
        $mform->setType('role_id', PARAM_RAW);
        
        $mform->addElement('hidden', 'matrix');
        $mform->setType('matrix', PARAM_RAW);

        $mform->addElement('hidden', 'returnhash');
        $mform->setType('returnhash', PARAM_RAW);

        $mform->addElement('text', 'rolename', get_string('role_title', $pluginname));
        $mform->setType('rolename', PARAM_RAW);
        $mform->addRule('rolename', get_string('required'), 'required', null, 'client');
        if ($this->roleData) {
            $mform->setDefault('rolename', $this->roleData->name);
        }

        $mform->addElement('advcheckbox', 'rolevisible', get_string('role_visible', $pluginname));
        if ($this->roleData) {
            $mform->setDefault('rolevisible', $this->roleData->visible);
        }

        $mform->addElement('textarea', 'roledesc', get_string('role_desc', $pluginname));
        $mform->setType('roledesc', PARAM_TEXT);
        if ($this->roleData) {
            $mform->setDefault('roledesc', $this->roleData->description);
        }
        $this->print_table();

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
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('save_and_edit_role_activities', $pluginname));
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton3', get_string('save_and_return_to_lp', $pluginname));
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

    public function print_table() {
        $mform = & $this->_form;

        $learningPath = \wa_learning_path\model\learningpath::get($this->learningPathId);
        $regions = \wa_learning_path\lib\get_regions();

        $matrix = json_decode($learningPath->matrix);
        $activities = $matrix->activities;

        $activityIds = [];
        foreach ($activities as $id => $activity) {
            $activityIds[] = $id;
        }

        if ($this->roleData) {
            $enabledActivities = [];
            foreach ($matrix->activities as $id => $activity) {

                if (isset($activity->enabledForRoles) && in_array($this->roleData->id, $activity->enabledForRoles)) {
                    $enabledActivities[] = $id;
                }
            }
        }
    
        $mform->addElement('html', '<div class="fitem">');
        $mform->addElement('html', '<div class="fitemtitle">');
        $mform->addElement('html', '<label>'.get_string('select_cells', 'local_wa_learning_path').'</label>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="felement">');
        $mform->addElement('html', '<div id="editing_role">');
        $mform->addElement('html', '<div class="wrapper">');
        $mform->addElement('html', '<div id="main">');
        $mform->addElement('html', '<div class="wa_matrix">');
        $mform->addElement('html', '<div class="cols">');
        $mform->addElement('html', '<div class="empty"></div>');
        // Regions
        $config = get_config('local_wa_learning_path');
        foreach ($matrix->cols as $key => $col) {
            $mform->addElement('html', '<div class="col_header" title="'.$col->name.'">');
            $mform->addElement('html', '<div>');
            foreach($col->region as $regionId) {
                $regionName =str_replace(' ', '', strtolower($regions[$regionId]));
                $class = 'class_' . $regionName;
                $shortcut = 'shortcut_' . $regionName;
                $mform->addElement('html', '<span id="'.$regionId.'" class="label '.$config->{$class}.' pull-left">' . $config->{$shortcut} . '</span>');
            }
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<br>');
    
            $length = strlen($col->name);
            $title = substr($col->name,0,20);
            if($length > strlen($title)) {
                $title .= '...';
            }
            $mform->addElement('html', '<span class="col-title pull-left">' . $title . '</span></div>');
        }
        $mform->addElement('html', '</div>'); // .cols

        // Levels
        foreach ($matrix->rows as $key => $row) {
            $mform->addElement('html', '<div class="wa_row" title="'.$row->name.'">');
    
            $length = strlen($row->name);
            $title = substr($row->name,0,20);
            if($length > strlen($title)) {
                $title .= '...';
            }
            
            $mform->addElement('html', '<div class="row_header">' . $title . '</div>');

            // Checkboxes
            foreach ($matrix->cols as $col) {
                $activityId = $col->id . '_' . $row->id;
                $checkboxName = 'activity-' . $activityId;
                if (in_array('#' . $activityId, $activityIds)) {
                    if ($this->roleData && in_array('#' . $activityId, $enabledActivities)) {
                        $mform->addElement('html', '<div class="cell checkbox-cell cell-selected">');
                    } else {
                        $mform->addElement('html', '<div class="cell checkbox-cell">');
                    }
                    $mform->addElement('advcheckbox', $checkboxName, null, null, ['onclick' => '$(this).closest($("div.checkbox-cell")).toggleClass("cell-selected")']);
                    if ($this->roleData && in_array('#' . $activityId, $enabledActivities)) {
                        $mform->setDefault($checkboxName, 1);
                    }
                } else {
                    $mform->addElement('html', '<div class="cell checkbox-cell">');
                    $mform->addElement('advcheckbox', $checkboxName, null, null, ['disabled' => 'disabled']);
                }
                $mform->addElement('html', '</div>'); // .cell.checkbox-cell
            }
            $mform->addElement('html', '</div>'); // .wa_row
        }

        $mform->addElement('html', '</div>'); // .wa_matrix
        $mform->addElement('html', '</div>'); // #main
        $mform->addElement('html', '</div>'); // .wrapper
        $mform->addElement('html', '</div>'); // #editing_matrix
        $mform->addElement('html', '</div>'); // .felement
        $mform->addElement('html', '</div>'); // .fitem
        $mform->addElement('html', '<br/>');
    }
}
