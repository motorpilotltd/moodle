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
 * The main arupapplication configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_arupapplication
 * @copyright  2014 Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/formslib.php');

class declarations_form extends moodleform {
    public function definition() {
        global $CFG;
        $mform =& $this->_form;

        $applicationid = $this->_customdata['applicationid'];
        $declarationid = $this->_customdata['declarationid'];
        $sortorder = $this->_customdata['sortorder'];
        $dowhat = $this->_customdata['dowhat'];

        switch ($dowhat) {
            case 'edit':
                $generalheader = get_string('heading:declaration:edit', 'arupapplication');
                $submitbuttonlabel = get_string('button:update', 'arupapplication');
                break;
            default:
                $generalheader = get_string('heading:declaration:add', 'arupapplication');
                $submitbuttonlabel = get_string('button:save', 'arupapplication');
                break;
        }

        $mform->addElement('header', 'general', $generalheader);
        $mform->addElement('textarea', 'declaration', get_string('heading:declaration', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('declaration', PARAM_TEXT);
        $mform->addRule('declaration', get_string('error:required', 'arupapplication'), 'required', null, 'server', false, false);

        $mform->addElement('hidden', 'applicationid', $applicationid);
        $mform->setType('applicationid', PARAM_INT);
        $mform->addElement('hidden', 'declarationid', $declarationid);
        $mform->setType('declarationid', PARAM_INT);
        $mform->addElement('hidden', 'sortorder', $sortorder);
        $mform->setType('sortorder', PARAM_INT);
        $mform->addElement('hidden', 'dowhat', $dowhat);
        $mform->setType('dowhat', PARAM_ALPHA);

        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitbuttonlabel, array('class' => 'btn-primary'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:cancel', 'arupapplication'), array('class' => 'btn-default'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}