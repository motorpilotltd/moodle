<?php
// This file is part of the appraisal plugin for Moodle
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
 * @package    mod_appraisal
 * @copyright  2015 Sonsbeekmedia
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class apform_development extends moodleform {
    public function definition() {
        global $PAGE;
        
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->updateAttributes(array('class' => $mform->getAttribute('class').' oa-save-session-check'));

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'development');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraiseeedit', $data->appraiseeedit);
        $mform->setType('appraiseeedit', PARAM_INT);

        $mform->addElement('hidden', 'appraiseredit', $data->appraiseredit);
        $mform->setType('appraiseredit', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas);
        $mform->setType('view', PARAM_TEXT);

        $appraiseelocked = '';
        if ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $appraiseelocked = ' locked="yes"';
        }

        $appraiserlocked = '';
        if ($data->appraiseredit == APPRAISAL_FIELD_LOCKED) {
            $appraiserlocked = ' locked="yes"';
        }

        $mform->addElement('html', '<hr class="tophr">');
        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));
        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        $mform->addElement('textarearup', 'seventy', $this->str('seventy'), 'rows="10" cols="70"' . $appraiseelocked, $this->str('seventyhelp'), 'appraisee');
        $mform->setType('seventy', PARAM_RAW);
        $mform->disabledIf('seventy', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'twenty', $this->str('twenty'), 'rows="5" cols="70"' . $appraiseelocked, $this->str('twentyhelp'), 'appraisee');
        $mform->setType('twenty', PARAM_RAW);
        $mform->disabledIf('twenty', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'ten', $this->str('ten'), 'rows="3" cols="70"' . $appraiseelocked, $this->str('tenhelp'), 'appraisee');
        $mform->setType('ten', PARAM_RAW);
        $mform->disabledIf('ten', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'comments', $this->str('comments'), 'rows="3" cols="70"' . $appraiserlocked, $this->str('commentshelp'), 'appraiser');
        $mform->setType('comments', PARAM_RAW);
        $mform->disabledIf('comments', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);

        if ($data->appraiseeedit == APPRAISAL_FIELD_EDIT || $data->appraiseredit == APPRAISAL_FIELD_EDIT) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'), array('class' => 'm-l-5'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

            // Saving nag modal.
            $renderer = $PAGE->get_renderer('local_onlineappraisal');
            $mform->addElement('html', $renderer->render_from_template('local_onlineappraisal/modal_save_nag', new stdClass()));
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str($string) {
        return get_string('form:development:' . $string, 'local_onlineappraisal');
    }
    
    function definition_after_data() {
        global $USER;
        $mform =& $this->_form;
        $data = $this->_customdata;
        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
    }
}
