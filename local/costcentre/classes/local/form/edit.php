<?php
// This file is part of the Arup cost centre system
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
 * Version details
 *
 * @package     local_costcentre
 * @copyright   2016 Motorpilot Ltd
 * @author      Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\local\form;

defined('MOODLE_INTERNAL') || die();

class edit extends \moodleform {

    private $costcentre;

    /**
     * Override constructor.
     * Ensure that the form method is _always_ POST and pass to parent for actual construction.
     *
     * @param mixed $action
     * @param mixed $customdata
     * @param string $method
     * @param string $target
     * @param mixed $attributes
     * @param bool $editable
     */
    public function __construct($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true) {
        // Ensure the form always uses the POST method.
        parent::__construct($action, $customdata, 'post', $target, $attributes, $editable);
    }

    public function definition() {
        $mform =& $this->_form;

        $this->costcentre = $this->_customdata['costcentre'];

        $this->_pre_validation();

        $mform->addElement('checkbox', 'enableappraisal', get_string('label:enableappraisal', 'local_costcentre'));

        $mform->addElement('checkbox', 'appraiserissupervisor', get_string('label:appraiserissupervisor', 'local_costcentre'));

        $appraiser = $mform->addElement(
                'select',
                'appraiser',
                get_string('label:appraiser', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $appraiser->setMultiple(true);

        $signatory = $mform->addElement(
                'select',
                'signatory',
                get_string('label:signatory', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $signatory->setMultiple(true);

        $reporter = $mform->addElement(
                'select',
                'reporter',
                get_string('label:reporter', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $reporter->setMultiple(true);

        // Admin only settings (GL/BA).
        $mform->addElement('header', 'header-adminonly', get_string('header:adminonly', 'local_costcentre'));
        $mform->setExpanded('header-adminonly', false);

        $mform->addElement('html', get_string('html:adminonly', 'local_costcentre'));

        $groupleader = $mform->addElement(
                'select',
                'groupleader',
                get_string('label:groupleader', 'local_costcentre'),
                array('' => ''),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $groupleader->setMultiple(true);

        $businessadmin = $mform->addElement(
                'select',
                'businessadmin',
                get_string('label:businessadmin', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $businessadmin->setMultiple(true);

        // Admin only settings (HR).
        $mform->addElement('header', 'header-hrusers', get_string('header:hrusers', 'local_costcentre'));
        $mform->setExpanded('header-hrusers', false);

        $mform->addElement('html', get_string('html:hrusers', 'local_costcentre'));

        $hrleader = $mform->addElement(
                'select',
                'hrleader',
                get_string('label:hrleader', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $hrleader->setMultiple(true);

        $hradmin = $mform->addElement(
                'select',
                'hradmin',
                get_string('label:hradmin', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $hradmin->setMultiple(true);

        // Appraisal specific groupleader settings.
        $mform->addElement('header', 'header-groupleaderappraisal', get_string('header:groupleaderappraisal', 'local_costcentre'));
        $mform->setExpanded('header-groupleaderappraisal', false);

        $mform->addElement('html', get_string('html:groupleaderappraisal', 'local_costcentre'));

        $mform->addElement('checkbox', 'groupleaderactive', get_string('label:groupleaderactive', 'local_costcentre'));

        $groupleaderappraisal = $mform->addElement(
                'select',
                'groupleaderappraisal',
                get_string('label:groupleaderappraisal', 'local_costcentre'),
                array('' => ''),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $groupleaderappraisal->setMultiple(true);

        // Learning specific settings.
        $mform->addElement('header', 'header-learning', get_string('header:learning', 'local_costcentre'));
        $mform->setExpanded('header-learning', false);

        $mform->addElement('html', get_string('html:learning', 'local_costcentre'));

        $learningreporter = $mform->addElement(
                'select',
                'learningreporter',
                get_string('label:learningreporter', 'local_costcentre'),
                array(),
                array('class' => 'select2-user', 'data-placeholder' => get_string('selectusers', 'local_costcentre'))
                );
        $learningreporter->setMultiple(true);

        if (!$this->costcentre->canaccessall) {
            $mform->freeze('enableappraisal');
            $mform->freeze('groupleaderactive');
            foreach (array('groupleader', 'groupleaderappraisal', 'hrleader', 'hradmin', 'businessadmin') as $type) {
                // Intentional variable variable.
                $attributes = $$type->getAttributes();
                $attributes['disabled'] = 'disabled';
                $$type->setAttributes($attributes);
            }
        }

        $mform->addElement('hidden', 'costcentre', $this->costcentre->costcentre);
        $mform->setType('costcentre', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ALPHA);

        $this->add_action_buttons();
    }

    public function set_data($defaultvalues) {
        global $DB;

        foreach ($this->_customdata['costcentre']->permissions as $type) {
            if (!empty($defaultvalues->{$type})) {
                $fullname = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
                list($usql, $params) = $DB->get_in_or_equal($defaultvalues->{$type}, SQL_PARAMS_NAMED, 'id');
                $options = $DB->get_records_select_menu('user', "id {$usql}", $params, 'lastname ASC', "id, $fullname");
                $select = $this->_form->getElement($type);
                foreach ($options as $value => $text) {
                    $select->addOption($text, $value, array('selected' => 'selected'));
                }
            }
            unset($defaultvalues->{$type});
        }

        parent::set_data($defaultvalues);
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if (empty($data->enableappraisal)) {
            $data->enableappraisal = 0;
        }
        if (empty($data->appraiserissupervisor)) {
            $data->appraiserissupervisor = 0;
        }
        if (empty($data->groupleaderactive)) {
            $data->groupleaderactive = 0;
        }
        foreach ($this->_customdata['costcentre']->permissions as $type) {
            $data->{$type} = is_array($_POST[$type]) ? optional_param_array($type, array(), PARAM_INT) : array();
        }

        return $data;
    }

    public function process() {
        if ($this->costcentre->action != 'edit') {
            // Not editing.
            return false;
        }

        // Get data and process.
        $data = $this->get_data();
        if ($data) {
            if (!$this->costcentre->canaccessall) {
                unset($data->enableappraisal);
                unset($data->groupleaderactive);
            }
            $this->costcentre->save($data);
            $this->costcentre->process_mappings($data);
            return true;
        }

        return false;
    }

    public function render() {
        if ($this->costcentre->action != 'edit') {
            // Not editing.
            return;
        }

        // Set data before calling parent render function.
        $data = $this->costcentre->settings;
        $mappings = $this->costcentre->get_mappings();
        foreach ($mappings as $name => $value) {
            $data->{$name} = $value;
        }
        $this->set_data($data);

        return parent::render();
    }

    private function _pre_validation() {
        if (isset($_POST['_qf__local_costcentre_local_form_edit']) && !$this->costcentre->canaccessall) {
            $originalmappings = $this->costcentre->get_mappings();
            foreach (['groupleader', 'groupleaderappraisal', 'hrleader', 'hradmin', 'businessadmin'] as $type) {
                $_POST[$type] = empty($originalmappings->{$type}) ? [] : $originalmappings->{$type};
            }
        }
    }
}
