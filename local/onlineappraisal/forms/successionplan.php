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

class apform_successionplan extends moodleform {
    private $stringman;

    private $userdata = ['location' => '', 'group' => ''];

    public function definition() {
        global $PAGE;

        $this->get_userdata();

        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->updateAttributes(array('class' => $mform->getAttribute('class').' oa-save-session-check'));

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'successionplan');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas);
        $mform->setType('view', PARAM_TEXT);

        $mform->addElement('hidden', 'appraiseeedit', $data->appraiseeedit);
        $mform->setType('appraiseeedit', PARAM_INT);

        $mform->addElement('hidden', 'appraiseredit', $data->appraiseredit);
        $mform->setType('appraiseredit', PARAM_INT);

        $appraiseelocked = '';
        if ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $appraiseelocked = ' locked="yes"';
        }

        $appraiserlocked = '';
        if ($data->appraiseredit == APPRAISAL_FIELD_LOCKED) {
            $appraiserlocked = ' locked="yes"';
        }

        $islocked = (empty($data->locked) ? 0 : 1);
        $mform->addElement('hidden', 'islocked', $islocked, ['id' => 'oa-sdp-islocked']); // For locking other fields as checkbox will be frozen.
        $mform->setType('islocked', PARAM_INT);

        // Hidden fields to be set from HUB data.
        $mform->addElement('hidden', 'group', $this->userdata['group']);
        $mform->setType('group', PARAM_TEXT);
        $mform->addElement('hidden', 'location', $this->userdata['location']);
        $mform->setType('location', PARAM_TEXT);

        $mform->addElement('html', '<hr class="tophr">');
        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));
        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        // Renderer for alert and/or nag modal.
        $renderer = $PAGE->get_renderer('local_onlineappraisal');
        if ($islocked) {
            $alert = new \local_onlineappraisal\output\alert($this->str('islocked'), 'warning', false);
            $mform->addElement('html', $renderer->render($alert));
        }

        foreach (['assessment', 'readiness', 'potential'] as $question) {
            $answers = ['' => ''];
            $i = 1;
            $answerstring = "{$question}:answer:{$i}";
            while ($this->str_exists($answerstring)) {
                $answer = $this->str($answerstring);
                $answers[$answer] = $answer;
                $i++;
                $answerstring = "{$question}:answer:{$i}";
            }
            $mform->addElement('select', $question, $this->str($question), $answers);
            $mform->disabledIf($question, 'islocked', 'eq', 1);
        }

        $strengths = [
            2,
            !empty($data->strengths) ? count($data->strengths) + 1 : 0,
            !empty($_POST['strengths']) ? count($_POST['strengths']) : 0
        ];
        $maxstrengths = max($strengths);
        if ($islocked) {
            $maxstrengths = (!empty($data->strengths) ? count($data->strengths) : 1);
        }
        for ($i = 0; $i < $maxstrengths; $i++) {
            $label = ($i === 0 ? $this->str('strengths') : '');
            $mform->addElement('text', "strengths[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("strengths[{$i}]", PARAM_TEXT);
            $mform->disabledIf("strengths[{$i}]", 'islocked', 'eq', 1);
        }
        $noscript = "<p class=\"visibleifnotjs\">{$this->str('strengths:add:noscript')}</p>";
        $button = '<button class="btn btn-xs btn-primary oa-add-repeating-element" data-index="'.($i - 1).'" data-type="strengths">'.$this->str('strengths:add').'</button>';
        $mform->addElement(
                'html',
                $button.$noscript
                );

        $developmentareas = [
            2,
            !empty($data->developmentareas) ? count($data->developmentareas) + 1 : 0,
            !empty($_POST['developmentareas']) ? count($_POST['developmentareas']) : 0
        ];
        $maxdevelopmentareas = max($developmentareas);
        if ($islocked) {
            $maxdevelopmentareas = (!empty($data->developmentareas) ? count($data->developmentareas) : 1);
        }
        for ($i = 0; $i < $maxdevelopmentareas; $i++) {
            $label = ($i === 0 ? $this->str('developmentareas') : '');
            $mform->addElement('text', "developmentareas[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("developmentareas[{$i}]", PARAM_TEXT);
            $mform->disabledIf("developmentareas[{$i}]", 'islocked', 'eq', 1);
        }
        $noscript = "<p class=\"visibleifnotjs\">{$this->str('developmentareas:add:noscript')}</p>";
        $button = '<button class="btn btn-xs btn-primary oa-add-repeating-element" data-index="'.($i - 1).'" data-type="developmentareas">'.$this->str('developmentareas:add').'</button>';
        $mform->addElement(
                'html',
                $button.$noscript
                );

        $mform->addElement('textarearup', 'developmentplan', $this->str('developmentplan'), 'rows="3" cols="70"', '', '');
        $mform->setType('developmentplan', PARAM_RAW);

        if (!$islocked) {
            $mform->addElement('advcheckbox', 'locked', '', $this->str('locked'), array('group' => 1), array(0, 1));
        }

        if (!$islocked && ($data->appraiseeedit == APPRAISAL_FIELD_EDIT || $data->appraiseredit == APPRAISAL_FIELD_EDIT)) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'), array('class' => 'm-l-5'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

            // Saving nag modal.
            $mform->addElement('html', $renderer->render_from_template('local_onlineappraisal/modal_save_nag', new stdClass()));
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str_exists($string) {
        if (empty($this->stringman)) {
            $this->stringman = get_string_manager();
        }
        return $this->stringman->string_exists('form:successionplan:' . $string, 'local_onlineappraisal');
    }

    private function str($string) {
        return get_string('form:successionplan:' . $string, 'local_onlineappraisal');
    }

    public function definition_after_data() {
        global $USER;

        $mform =& $this->_form;
        $data = $this->_customdata;

        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
    }

    public function get_data() {
        $data = parent::get_data();
        if (isset($data->islocked)) {
            unset($data->islocked);
        }
        // Clear empty inputs.
        if (isset($data->strengths)) {
            $data->strengths = array_filter($data->strengths);
        }
        if (isset($data->developmentareas)) {
            $data->developmentareas = array_filter($data->developmentareas);
        }
        return $data;
    }

    public function set_data($default_values) {
        parent::set_data($default_values);
        // Overwrite user data.
        $this->_form->setDefaults($this->userdata);
    }

    private function get_userdata() {
        global $DB;

        $sql = "SELECT LOCATION_NAME as location, GROUP_NAME as groupname, GROUP_CODE as groupcode
                  FROM SQLHUB.ARUP_ALL_STAFF_V
                 WHERE EMPLOYEE_NUMBER = :idnumber";
        $params= ['idnumber' => (int) $this->_customdata->appraisal->appraisee->idnumber];
        $hubdata = $DB->get_record_sql($sql, $params);
        if ($hubdata) {
            $this->userdata['location'] = $hubdata->location;
            $this->userdata['group'] = "{$hubdata->groupname} ({$hubdata->groupcode})";
        }
    }
}
