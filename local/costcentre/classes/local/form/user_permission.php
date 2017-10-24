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
 * @copyright   2017 Motorpilot Ltd
 * @author      Aleks Daloso
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcentre\local\form;

defined('MOODLE_INTERNAL') || die();

class user_permission extends \moodleform {
    private $costcentre;
    private $permissions;
    public function definition() {
        $mform =& $this->_form;
        $strrequired = get_string('required');

        $this->costcentre = $this->_customdata['costcentre'];
        $this->permissions = $this->costcentre->permissions;
        // User lists.
        $users = $mform->addElement(
            'select',
            'userlist',
            get_string('label:userlist', 'local_costcentre'),
            array(),
            array('class' => 'select2-user', 'data-placeholder' => get_string('chooseuser', 'local_costcentre'))
        );
        $users->setMultiple(true);
        $mform->addRule('userlist', $strrequired, 'required', null, 'client');

        // Cost centre lists.
        $costcentres = $mform->addElement(
            'select',
            'costcentre',
            get_string('label:costcentre', 'local_costcentre'),
            array('' => '') + $this->costcentre->costcentresmenu,
            array('class' => 'select2-costcentre', 'data-placeholder' => get_string('choosecostcentre', 'local_costcentre')));
        $costcentres->setMultiple(true);
        $mform->addRule('costcentre', $strrequired, 'required', null, 'client');

        // Permission lists.
        foreach ($this->permissions as $key => $val) {
            $this->permissions[$key] = get_string('label:' . $val, 'local_costcentre');
        }

        $permissions = $mform->addElement(
            'select',
            'permissions',
            get_string('label:permissions', 'local_costcentre'),
            array('' => '') + $this->permissions,
            array('class' => 'select2', 'data-placeholder' => get_string('choospermission', 'local_costcentre')));
        $permissions->setMultiple(true);
        $mform->addRule('permissions', $strrequired, 'required', null, 'client');

        $mform->addElement('hidden', 'action', $this->costcentre->validaction);
        $mform->setType('action', PARAM_ALPHA);
        $this->add_action_buttons();
    }

    public function process() {
        if ($this->costcentre->action != 'save') {
            // Not editing.
            return false;
        }

        // Get data and process.
        $data = $this->get_data();
        if ($data && !empty($data->userlist)) {
            foreach ($data->costcentre as $cs) {
                foreach ($data->userlist as $user) {
                    \local_costcentre\costcentre::update_user_permissions($user, $cs, $data->permissions);
                }
            }
            return true;
        }

        return false;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        $data->userlist = is_array($_POST['userlist']) ? optional_param_array('userlist', array(), PARAM_INT) : array();
        return $data;
    }

}