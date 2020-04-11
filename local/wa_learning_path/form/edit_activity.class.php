<?php

namespace wa_learning_path\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

class edit_activity_form extends \moodleform {

    private $learningPathId;
    private $activityId;
    private $activity;
    private $roleId;
    private $activityData;

    /**
     * Overrides the abstract moodleform::definition method for defining what the form that is to be
     * presented to the user.
     */
    public function definition() {
        global $PAGE, $CFG;
        \wa_learning_path\lib\load_model('learningpath');

        $mform = & $this->_form;
        $this->learningPathId = $this->_customdata['id'];
        $this->activityId = $this->_customdata['activityid'];
        $this->roleId = $this->_customdata['roleid'];
        $this->activityData = $this->_customdata['activitydata'];
        $pluginname = 'local_wa_learning_path';
        $mform->_attributes['id'] = 'matrixform';
        $mform->_attributes['class'] = 'mform edit_activities';

        $learningPath = \wa_learning_path\model\learningpath::get($this->learningPathId);
        $matrix = json_decode($learningPath->matrix);
        $this->activity = $matrix->activities->{'#'.$this->activityId};

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);

        $mform->addElement('hidden', 'roleid', $this->roleId);
        $mform->setType('roleid', PARAM_RAW);

        $mform->addElement('hidden', 'activityid', $this->activityId);
        $mform->setType('activityid', PARAM_RAW);

        $mform->addElement('hidden', 'matrix');
        $mform->setType('matrix', PARAM_RAW);

        $mform->addElement('hidden', 'returnhash');
        $mform->setType('returnhash', PARAM_RAW);
    
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">' . get_string('description', $pluginname) . '</div><div class="felement" id="role_activity_description">' . $this->activity->content . '</div></div>');

        $content =  reset($this->activityData)->overridedescription ?? '';
        $params = $this->get_editor_params();
        $mform->addElement('editor', 'overridedescription', get_string('override_description', $pluginname), $params)->setValue(['text' => $content]);
        $mform->setType('overridedescription', PARAM_RAW);
        
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
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('save_and_return', $pluginname));
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

    public function get_all_errors() {
        return $this->_form->_errors;
    }

    public function print_table() {
        $mform = & $this->_form;
        $pluginname = 'local_wa_learning_path';
    
        $mform->addElement('html', '<div class="fitem">');
        $mform->addElement('html', '<div class="fitemtitle">');
        
        $mform->addElement('html', '<label>'.get_string('select_activities', $pluginname).'</label>');
        $mform->addElement('html', '</div>');
        
        $mform->addElement('html', '<div class="felement">');
        $mform->addElement('html', '<div id="editing_matrix">');
        $mform->addElement('html', '<div class="wrapper">');
        $mform->addElement('html', '<div id="main">');
        $mform->addElement('html', '<div class="wa_matrix">');
        $mform->addElement('html', '<div class="cols">');
        $mform->addElement('html', '<div class="col_header">' . get_string('activity_name', $pluginname) . '</div>');
        $mform->addElement('html', '<div class="col_header">' . get_string('default_ere', $pluginname) . '</div>');
        $mform->addElement('html', '<div class="col_header">' . get_string('override_ere', $pluginname) . '</div>');
        $mform->addElement('html', '<div class="col_header">' . get_string('visible_in_role', $pluginname) . '</div>');
        $mform->addElement('html', '</div>'); // .cols

        $positions = [
            'default' => get_string('default', $pluginname),
            'essential' => get_string('essential', $pluginname),
            'recommended' => get_string('recommended', $pluginname),
            'elective' => get_string('elective', $pluginname)
        ];

        foreach ($this->activity->positions as $positionName => $position) {
            foreach ($position as $activity) {

                // Some activities/modules do not have ID for some reason, so let's just skip them.
                // Those will be saved with ID 0 into database.
                if (!$activity->id) {
                    continue;
                }

                $id = $activity->id;
                if (!empty($this->activityData)) {
                    foreach ($this->activityData as $key => $data) {
                        if ($data->itemid == $id && $data->type == $activity->type) {
                            $overrideere = $data->overrideere;
                            $activityVisible = $data->visible;
                        }
                    }
                }
                $mform->addElement('hidden', 'act_type_' . $id, $activity->type);
                $mform->setType('act_type_' . $id, PARAM_RAW);
                $mform->addElement('hidden', 'act_defaultere_' . $id, $positionName);
                $mform->setType('act_defaultere_' . $id, PARAM_RAW);
                $mform->addElement('html', '<div class="wa_row">');
                $activityName = $activity->type == 'module' ? $activity->fullname : $activity->title;
                $mform->addElement('html', '<div class="cell">' . $activityName . '</div>');
                $mform->addElement('html', '<div class="cell">' . get_string($positionName, $pluginname) . '</div>');
                $mform->addElement('html', '<div class="cell">');
                $mform->addElement('select', 'act_overrideere_' . $id, '', $positions);
                if (isset($overrideere) && !is_null($overrideere)) {
                    $mform->setDefault('act_overrideere_' . $id, $overrideere);
                } else {
                    $mform->setDefault('act_overrideere_' . $id, 'default');
                }
                $mform->addElement('html', '</div>'); // .cell
                $mform->addElement('html', '<div class="cell checkbox-cell">');
                $mform->addElement('advcheckbox', 'act_activityvisible_' . $id, null, null);
                if (isset($activityVisible)) {
                    $mform->setDefault('act_activityvisible_' . $id, $activityVisible);
                } else {
                    $mform->setDefault('act_activityvisible_' . $id, 1);
                }
                $mform->addElement('html', '</div>'); // .cell.checkbox-cell
                $mform->addElement('html', '</div>'); // .wa_row
            }
        }
        $mform->addElement('html', '</div>'); // .wa_matrix
        $mform->addElement('html', '</div>'); // #main
        $mform->addElement('html', '</div>'); // .wrapper
        $mform->addElement('html', '</div>'); // #editing_matrix
        $mform->addElement('html', '<br/>');
    }
}
