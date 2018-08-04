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

class user_permission_check extends \moodleform {

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
        $userpermissionsdata = $this->costcentre->userpermissionsdata;
        $currentuser = $this->costcentre->currentuser;
        // Display user's permission per cost centre
        if (!empty($userpermissionsdata)) {
            $mform->addElement('header', '', $currentuser->name);

            foreach ($userpermissionsdata as $key) {
                $headerclass = 'header-costcentre-' . $key->icq;
                $mform->addElement('header', $headerclass, $key->costcentre);
                $mform->setExpanded($headerclass, true);
                $permissions = $key->permissions;
                if (!empty($permissions)) {
                    foreach ($permissions as $key1 => $val) {
                        $mform->addElement('advcheckbox', 'permissions['.$key->icq . '][]', '', get_string('label:' . $key1, 'local_costcentre'), array(), array(null, $val));
                    }
                }
            }
            // Hidden values
            $mform->addElement('hidden', 'user', $currentuser->id);
            $mform->addElement('hidden', 'action', $this->costcentre->validaction);
            $mform->setType('action', PARAM_ALPHA);
            $mform->setType('user', PARAM_INT);
            // Buttons
            $this->add_action_buttons(true, get_string('label:removepermissions', 'local_costcentre'));
        }

    }

    public function process() {
        if ($this->costcentre->action != 'load') {
            // Not editing.
            return false;
        }

        // Get data and process.
        $data = $this->get_data();
        // TODO: process the submitted checkbox value to update users permission
        if ($data && !empty($data->permissions) && !empty($data->user)) {
            foreach ($data->permissions as $key => $val) {
                $permissions = array_filter($val, function($var) {
                    return !empty($var);
                });
                \local_costcentre\costcentre::update_user_permissions($data->user, $key, array(), $permissions);
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
        // Get permissions
        if (!empty($_POST['permissions'])) {
            $data->permissions = $_POST['permissions'];
        }
        return $data;
    }
}