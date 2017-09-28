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

class technicalreference_form extends moodleform {
    public function definition() {
	global $CFG;

        $mform =& $this->_form;

        $refereemessage_hint = $this->_customdata['refereemessage_hint'];
        $referee_email = $this->_customdata['referee_email'];
        $referee_audit = $this->_customdata['referee_audit'];
        $footermessage = $this->_customdata['footermessage'];
        $referencereceived = $this->_customdata['referencereceived'];

        $mform->addElement('html', '<h2>' . get_string('heading:technicalreference', 'arupapplication') . '</h2>');

        $mform->addElement('html', html_writer::tag('div', $refereemessage_hint, array('class' => 'hint')));

        if ($referencereceived) {
            $mform->addElement('static', 'referee_email', get_string('refereeemail', 'arupapplication'));
            $mform->addElement('static', 'referee_message', get_string('refereemessage', 'arupapplication'));
        } else {
            $mform->addElement('text', 'referee_email', get_string('refereeemail', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
            $mform->setType('referee_email', PARAM_EMAIL);
            $mform->addHelpButton('referee_email', 'refereeemail', 'arupapplication');

            $mform->addElement('textarea', 'referee_message', get_string('refereemessage', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
            $mform->setType('referee_message', PARAM_NOTAGS);
            $mform->addHelpButton('referee_message', 'refereemessage', 'arupapplication');
        }

        $mform->addElement('html', html_writer::start_tag('div', array('class' => 'footermessage')));
        $mform->addElement('static', 'footermessage');
        $mform->setDefault('footermessage', $footermessage);
        $mform->addElement('html', html_writer::end_tag('div'));

        $mform->addElement('hidden', 'gopage', 0);
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'thispage', 'referee');
        $mform->setType('thispage', PARAM_ALPHA);
        $mform->addElement('hidden', 'referencereceived', $referencereceived);
        $mform->setType('referencereceived', PARAM_INT);

        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        if (strlen($referee_audit)) {
            if (!$referencereceived) {
                $buttonarray[] = &$mform->createElement('submit', 'resendrefemail', get_string('button:resend', 'arupapplication'), array('class' => 'btn-default'));
            }
            $buttonarray[] = &$mform->createElement('submit', 'continuebutton', get_string('button:continue', 'arupapplication'), array('class' => 'btn-primary'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exit', 'arupapplication'), array('class' => 'btn-default'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'gonextpage', get_string('button:sendcontinue', 'arupapplication'), array('class' => 'btn-primary'));
            $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exitnosave', 'arupapplication'), array('class' => 'btn-danger'));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if (!empty($referee_audit)) {

            $mform->addElement('header', 'general', get_string('heading:previousemails', 'arupapplication'));

            $records = explode('$$$', $referee_audit);
            $table = new html_table();
            $table->cellpadding = 4;
            $table->attributes['class'] = 'generaltable boxalignleft';
            $table->head = array(get_string('heading:to', 'arupapplication'), get_string('heading:from', 'arupapplication'), get_string('heading:date', 'arupapplication'), get_string('heading:message', 'arupapplication'));
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }
            $mform->addElement('html', html_writer::table($table));
        }
    }

    function definition_after_data() {
        $mform =& $this->_form;

        if ($mform->elementExists('referencereceived')) {
            $valuereferencereceived = $mform->getElementValue('referencereceived');
            if ($valuereferencereceived) {
                $mform->disabledIf('resendrefemail', 'referencereceived', 'eq', 1);
            }
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!$data['referencereceived']) {
            if (isset($data['resendrefemail']) || isset($data['gonextpage'])) {
                if (! validate_email($data['referee_email'])) {
                    $errors['referee_email'] = get_string('invalidemail');
                } else if (!arupapplication_validate_emailaddress($data['referee_email'])) {
                    $errors['referee_email'] = get_string('invalidemail');
                }
                if (empty($data['referee_message'])) {
                    $errors['referee_message'] = get_string('error:required', 'arupapplication');
                }
            }
        }
        return $errors;
    }
}