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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class mod_aruphonestybox_completion_form extends moodleform
{
    public function definition()
    {
        global $CFG, $COURSE, $USER, $DB, $OUTPUT;
        $mform = $this->_form;

        $aruphonestyboxuser = $this->_customdata['aruphonestyboxuser'] ? $this->_customdata['aruphonestyboxuser'] : null;
        // display completion date picker
        if ($this->_customdata['showdate']) {
            $mform->addElement('date_selector', 'completiondate',  get_string('setcompletiondate', 'mod_aruphonestybox'));
            $defaultdate = isset($aruphonestyboxuser->completiondate) ? $aruphonestyboxuser->completiondate : time();
            $mform->setDefault('completiondate', $defaultdate);
        }

        // display filemanager for certificate
        if ($this->_customdata['showfilemanager']) {
            $fileoptions = array(
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => $COURSE->maxbytes
            );
            $mform->addElement('filemanager', 'completioncertificate',
                get_string('uploadcertificate', 'mod_aruphonestybox'),
                null, $fileoptions);


            $entryid = isset($aruphonestyboxuser->userid) ? $aruphonestyboxuser->userid : null ;

            $draftitemid = file_get_submitted_draft_itemid("completioncertificate");
            file_prepare_draft_area($draftitemid, $this->_customdata['contextid'], 'mod_aruphonestybox', "certificate", $entryid,
                $fileoptions);
            $mform->setDefault("completioncertificate", $draftitemid);

        }

        if(isset($aruphonestyboxuser)) {
            $mform->addElement('hidden', 'timemodified', $aruphonestyboxuser->timemodified);
            $mform->setType('timemodified', PARAM_INT);

            $mform->addElement('hidden', 'approved', $aruphonestyboxuser->approved);
            $mform->setType('approved', PARAM_INT);

            $mform->addElement('hidden', 'completion', $aruphonestyboxuser->completion);
            $mform->setType('completion', PARAM_INT);
        }


        // user was editing existing an aruphonestyboxuser
        if(isset($this->_customdata['action']) && !empty($this->_customdata['action'])) {
            $mform->addElement('hidden', 'action', $this->_customdata['action']);
            $mform->setType('action', PARAM_ALPHA);
            $mform->addElement('hidden', 'ahbuserid', $aruphonestyboxuser->id);
            $mform->setType('ahbuserid', PARAM_INT);

        }
        $this->add_action_buttons(true, get_string('submit'));

    }
}