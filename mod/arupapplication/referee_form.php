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

class referee_form extends moodleform {
    public function definition() {

        $mform =& $this->_form;

        $referencesubmitted = $this->_customdata['referencesubmitted'];
        $reference_hint = $this->_customdata['reference_hint'];
        $footermessage = $this->_customdata['footermessage'];

        $mform->addElement('html', '<h2>' . get_string('heading:technicalreference', 'arupapplication') . '</h2>');

        $mform->addElement('html', html_writer::tag('div', $reference_hint, array('class' => 'hint')));

        $mform->addElement('text', 'applicantname', get_string('applicantname', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('applicantname', PARAM_NOTAGS);
        $mform->addElement('text', 'moduletitle', get_string('moduletitle', 'arupapplication'), array('disabled'=>'disabled'));
        $mform->setType('moduletitle', PARAM_NOTAGS);

        $mform->addElement('text', 'reference_phone', get_string('referencephone', 'arupapplication'));
        $mform->setType('reference_phone', PARAM_NOTAGS);
        $mform->addRule('reference_phone', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);

        $mform->addElement('text', 'referenceposition', get_string('referenceposition', 'arupapplication'));
        $mform->setType('referenceposition', PARAM_NOTAGS);
        $mform->addRule('referenceposition', get_string('error:maxlength', 'arupapplication', 255), 'maxlength', 255, 'server', false, false);

        $mform->addElement('textarea', 'referenceknown', get_string('referenceknown', 'arupapplication'), 'wrap="virtual" rows="2" cols="65"');
        $mform->setType('referenceknown', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referenceperformance', get_string('referenceperformance', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('referenceperformance', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencetalent', get_string('referencetalent', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('referencetalent', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencemotivation', get_string('referencemotivation', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('referencemotivation', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referenceknowledge', get_string('referenceknowledge', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('referenceknowledge', PARAM_NOTAGS);

        $mform->addElement('textarea', 'referencecomments', get_string('referencecomments', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
        $mform->setType('referencecomments', PARAM_NOTAGS);

        $mform->addElement('hidden', 'referencecompleted', $referencesubmitted);
        $mform->setType('referencecompleted', PARAM_INT);

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'confirmsubmit', get_string('button:submit', 'arupapplication'), array('class' => 'btn-primary'));
        $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));

        if ($referencesubmitted) {
            $cancellabel = get_string('button:continue', 'arupapplication');
            $cancelclass = array('class' => 'btn-default');
        } else {
            $cancellabel = get_string('button:exitnosave', 'arupapplication');
            $cancelclass = array('class' => 'btn-danger');
        }
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', $cancellabel, $cancelclass);

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('html', html_writer::tag('div', $footermessage, array('class' => 'hint')));
    }

    function definition_after_data() {

        $mform =& $this->_form;

        if ($mform->elementExists('referencecompleted')) {
            $valuereferencecompleted = $mform->getElementValue('referencecompleted');
            if ($valuereferencecompleted == 1) {
                $mform->disabledIf('reference_phone', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referenceposition', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referenceknown', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referenceperformance', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referencetalent', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referencemotivation', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referenceknowledge', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('referencecomments', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('confirmsubmit', 'referencecompleted', 'eq', 1);
                $mform->disabledIf('savevalues', 'referencecompleted', 'eq', 1);
            }
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['confirmsubmit'])) {
            foreach ($data as $key => $value) {
                switch($key) {
                    case 'reference_phone':
                    case 'referenceposition':
                    case 'referenceknown':
                    case 'referencetalent':
                    case 'referenceperformance':
                    case 'referencemotivation':
                    case 'referenceknowledge':
                    case 'referencecomments':
                        if (empty($value)) {
                            $errors[$key] = get_string('error:required', 'arupapplication');
                        }
                        break;
                }
            }
        }
        return $errors;
    }
}