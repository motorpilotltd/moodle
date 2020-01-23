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

use \local_onlineappraisal\comments as comments;

class apform_successionplan extends moodleform {
    private $stringman;

    private $userdata = ['location' => '', 'group' => ''];

    private $locking = false;
    private $unlocking = false;

    public function definition() {
        global $DB, $PAGE;

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

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas, ['id' => 'oa-sdp-view']);
        $mform->setType('view', PARAM_TEXT);

        $isformlocked = !empty($data->locked);
        $islockedforuser = ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) && ($data->appraiseredit == APPRAISAL_FIELD_LOCKED);
        $islocked = (int) ($isformlocked || $islockedforuser); // Integer for JS.
        $mform->addElement('hidden', 'islockedforuser', $islockedforuser); // For locking unlock checkbox.
        $mform->setType('islockedforuser', PARAM_INT);
        $mform->addElement('hidden', 'islocked', $islocked, ['id' => 'oa-sdp-islocked']); // For locking other fields as checkbox will be frozen.
        $mform->setType('islocked', PARAM_INT);

        $islockedattr = '';
        if ($islocked) {
            $islockedattr = ' locked="yes"';
        }

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
        if ($isformlocked) {
            // Only display if form actually locked, not just locked for user.
            $alert = new \local_onlineappraisal\output\alert($this->str('islocked'), 'warning', false);
            $mform->addElement('html', $renderer->render($alert));
        }

        // Americas hidden fields.
        // Available if appraisee region is NOT Americas (TAPS).
        $americassql = "SELECT lru.geotapsregionid
                          FROM {local_regions_use} lru
                          JOIN {local_regions_reg} lrr ON lrr.id = lru.geotapsregionid
                         WHERE lru.userid = :userid AND lrr.name = 'Americas'";
        $americas = $DB->get_field_sql($americassql, array('userid' => $data->appraisal->appraisee->id));

        foreach (['assessment', 'readiness', 'potential'] as $question) {
            if (in_array($question, ['assessment', 'readiness']) && $americas) {
                continue;
            }
            $answers = ($question === 'potential') ? [] : ['' => ''];
            $class = ($question === 'potential') ? 'select2-general' : '';
            $i = 1;
            $answerstring = "{$question}:answer:{$i}";
            while ($this->str_exists($answerstring)) {
                $answer = $this->str($answerstring);
                $answers[$answer] = $answer;
                $i++;
                $answerstring = "{$question}:answer:{$i}";
            }
            $element = $mform->addElement('select', $question, $this->str($question), $answers, ['class' => $class]);
            if ($question === 'potential') {
                $element->setMultiple(true);
            }
            $mform->disabledIf($question, 'islocked', 'eq', 1);
            $mform->disabledIf($question, 'view', 'eq', 'appraisee');
        }

        $strengths = [
            2,
            !empty($data->strengths) ? count($data->strengths) + 1 : 0,
            !empty($_POST['strengths']) ? count($_POST['strengths']) : 0
        ];
        $maxstrengths = max($strengths);
        if ($islocked || $data->appraisal->viewingas === 'appraisee') {
            $maxstrengths = (!empty($data->strengths) ? count($data->strengths) : 1);
        }
        for ($i = 0; $i < $maxstrengths; $i++) {
            $label = ($i === 0 ? $this->str('strengths') : '');
            $mform->addElement('text', "strengths[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("strengths[{$i}]", PARAM_TEXT);
            $mform->disabledIf("strengths[{$i}]", 'islocked', 'eq', 1);
            $mform->disabledIf("strengths[{$i}]", 'view', 'eq', 'appraisee');
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
        if ($islocked || $data->appraisal->viewingas === 'appraisee') {
            $maxdevelopmentareas = (!empty($data->developmentareas) ? count($data->developmentareas) : 1);
        }
        for ($i = 0; $i < $maxdevelopmentareas; $i++) {
            $label = ($i === 0 ? $this->str('developmentareas') : '');
            $mform->addElement('text', "developmentareas[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("developmentareas[{$i}]", PARAM_TEXT);
            $mform->disabledIf("developmentareas[{$i}]", 'islocked', 'eq', 1);
            $mform->disabledIf("developmentareas[{$i}]", 'view', 'eq', 'appraisee');
        }
        $noscript = "<p class=\"visibleifnotjs\">{$this->str('developmentareas:add:noscript')}</p>";
        $button = '<button class="btn btn-xs btn-primary oa-add-repeating-element" data-index="'.($i - 1).'" data-type="developmentareas">'.$this->str('developmentareas:add').'</button>';
        $mform->addElement(
                'html',
                $button.$noscript
                );

        $mform->addElement('textarearup', 'developmentplan', $this->str('developmentplan'), 'rows="3" cols="70"' . $islockedattr, '', '');
        $mform->setType('developmentplan', PARAM_RAW);

        if (!$isformlocked) {
            $mform->addElement('advcheckbox', 'locked', '', $this->str('locked'), array('group' => 1), array(0, 1));
            $mform->disabledIf('locked', 'islocked', 'eq', 1);
            $mform->disabledIf('locked', 'view', 'eq', 'appraisee');
        } else {
            $mform->addElement('advcheckbox', 'unlock', '', $this->str('unlock'), array('group' => 1), array(0, 1));
            $mform->disabledIf('unlock', 'islockedforuser', 'eq', 1);
            $mform->disabledIf('unlock', 'view', 'eq', 'appraisee');
        }

        if (!$islocked || ($isformlocked && !$islockedforuser)) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'), ['class' => ($isformlocked) ? 'oa-unlock-sdp' : '']);
            if ($isformlocked) {
                $mform->disabledIf('submitbutton', 'unlock', 'eq', 0);
            }
            if (!$isformlocked) {
                $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            }
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
        // Clear empty inputs.
        if (isset($data->strengths)) {
            $data->strengths = array_values(array_filter($data->strengths));
        }
        if (isset($data->developmentareas)) {
            $data->developmentareas = array_values(array_filter($data->developmentareas));
        }
        // Handle empty multi select, but only if unlocked.
        if (!isset($data->potential) && !$data->islocked) {
            $data->potential = [];
        }
        // Tidy up control fields.
        if (!$data->islocked && !empty($data->locked)) {
            $this->locking = true;
        }
        if (!empty($data->unlock)) {
            $data->locked = 0;
            $this->unlocking = true;
            unset($data->unlock);
        }
        if (isset($data->islocked)) {
            unset($data->islocked);
        }
        if (isset($data->islockedforuser)) {
            unset($data->islockedforuser);
        }
        return $data;
    }

    public function set_data($default_values) {
        parent::set_data($default_values);
        // Overwrite user data.
        $this->_form->setDefaults($this->userdata);
    }

    public function store_data($formsclass, $data) {
        global $USER;
        $formsclass->store_data($data);
        $a = new stdClass();
        $a->relateduser = fullname($USER);
        if ($this->unlocking) {
            // Add comment
            $comment = comments::save_comment(
                    $formsclass->appraisal->appraisalid,
                    get_string(
                            'comment:sdp:unlocking',
                            'local_onlineappraisal',
                            $a
                            )
                    );
        }
        if ($this->locking) {
            // Add comment
            $comment = comments::save_comment(
                    $formsclass->appraisal->appraisalid,
                    get_string(
                            'comment:sdp:locking',
                            'local_onlineappraisal',
                            $a
                            )
                    );
        }

        return true;
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
