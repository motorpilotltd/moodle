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

require_once("$CFG->libdir/formslib.php");

class qualifications_form extends moodleform {
    public function definition() {
	global $CFG;

        $mform =& $this->_form;

        $mform->addElement('html', '<h2>' . get_string('heading:qualifications', 'arupapplication') . '</h2>');

        $mform->addElement('textarea', 'degree', get_string('degree', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('degree', PARAM_NOTAGS);
        $mform->addHelpButton('degree', 'degree', 'arupapplication');
        $filemanager_options = array();
        $filemanager_options['return_types'] = 3;
            $filemanager_options['accepted_types'] = array(ARUPAPPLICATION_MAX_FILETYPE);
            $filemanager_options['maxbytes'] = ARUPAPPLICATION_MAX_FILESIZE;
            $filemanager_options['maxfiles'] = ARUPAPPLICATION_MAX_FILES;
        $filemanager_options['mainfile'] = false;

        $mform->addElement('filepicker', 'cv', get_string('cv', 'arupapplication'), null, $filemanager_options);
        $mform->addHelpButton('cv', 'cv', 'arupapplication');

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'gopreviouspage', get_string('button:saveback', 'arupapplication'), array('class' => 'btn-default'));
        $buttonarray[] = &$mform->createElement('submit', 'gonextpage', get_string('button:savecontinue', 'arupapplication'), array('class' => 'btn-primary'));
        $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exitnosave', 'arupapplication'), array('class' => 'btn-danger'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('hidden', 'gopage', 3);
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'thispage', 'qualification');
        $mform->setType('thispage', PARAM_ALPHA);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}