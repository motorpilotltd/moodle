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

class apform_leaderplan extends moodleform {
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

        $mform->addElement('hidden', 'page', 'leaderplan');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas, ['id' => 'oa-ldp-view']);
        $mform->setType('view', PARAM_TEXT);

        $isformlocked = !empty($data->ldplocked);
        $islockedforuser = ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) && ($data->appraiseredit == APPRAISAL_FIELD_LOCKED);
        $islocked = (int) ($isformlocked || $islockedforuser); // Integer for JS.
        $mform->addElement('hidden', 'islockedforuser', $islockedforuser); // For locking ability to unlock.
        $mform->setType('islockedforuser', PARAM_INT);
        $mform->addElement('hidden', 'islocked', $islocked, ['id' => 'oa-ldp-islocked']); // For locking other fields as checkbox will be frozen.
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
        if ($isformlocked) {
            // Only display if form actually locked, not just locked for user.
            $alert = new \local_onlineappraisal\output\alert($this->str('islocked'), 'warning', false);
            $mform->addElement('html', $renderer->render($alert));
        }

        if ($data->appraisal->viewingas === 'appraisee' && !$islocked) {
            $importhtml = $this->get_import_html();
            $mform->addElement('html', $importhtml);
        }

        $answers = [];
        $class = 'select2-general';
        $i = 1;
        $answerstring = "ldppotential:answer:{$i}";
        while ($this->str_exists($answerstring)) {
            $answer = $this->str($answerstring);
            $answers[$answer] = $answer;
            $i++;
            $answerstring = "ldppotential:answer:{$i}";
        }
        if (isset($_POST['ldppotential']) || isset($data->ldppotential)) {
            // Handle custom additions (user specific);
            $ldppotentials = [];
            $filterflags = FILTER_REQUIRE_ARRAY | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK;
            $ldppotentials += (array) filter_input(INPUT_POST, 'ldppotential', FILTER_SANITIZE_STRING, $filterflags);
            $ldppotentials += (isset($data->ldppotential) ? $data->ldppotential : []);
            foreach ($ldppotentials as $ldppotential) {
                if (!in_array($ldppotential, $answers)) {
                    $answers[$ldppotential] = $ldppotential;
                }
            }
        }
        $element = $mform->addElement('select', 'ldppotential', $this->str('ldppotential'), $answers, ['class' => $class]);
        $element->setMultiple(true);
        $mform->disabledIf('ldppotential', 'islocked', 'eq', 1);
        $mform->disabledIf('ldppotential', 'view', 'neq', 'appraisee');
        if (!$islocked && $data->appraisal->viewingas === 'appraisee') {
            $html = '
                <div class="form-group">
                    <label class="sr-only" for="ldppotentialnew">Add alternative...</label>
                    <div class="input-group ldppotentialnew">
                    <input type="text" class="form-control" id="ldppotentialnew" placeholder="Add alternative...">
                    <div class="input-group-addon" id="ldppotentialnew-add"><button>+</button></div>
                    </div>
                </div>
                ';
            $mform->addElement('html', $html);
        }

        $strengths = [
            2,
            !empty($data->ldpstrengths) ? count($data->ldpstrengths) + 1 : 0,
            !empty($_POST['ldpstrengths']) ? count($_POST['ldpstrengths']) : 0
        ];
        $maxstrengths = max($strengths);
        if ($islocked || $data->appraisal->viewingas !== 'appraisee') {
            $maxstrengths = (!empty($data->ldpstrengths) ? count($data->ldpstrengths) : 1);
        }
        for ($i = 0; $i < $maxstrengths; $i++) {
            $label = ($i === 0 ? $this->str('ldpstrengths') : '');
            $mform->addElement('text', "ldpstrengths[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("ldpstrengths[{$i}]", PARAM_TEXT);
            $mform->disabledIf("ldpstrengths[{$i}]", 'islocked', 'eq', 1);
            $mform->disabledIf("ldpstrengths[{$i}]", 'view', 'neq', 'appraisee');
        }
        $noscript = "<p class=\"visibleifnotjs\">{$this->str('ldpstrengths:add:noscript')}</p>";
        $button = '<button class="btn btn-xs btn-primary oa-add-repeating-element" data-index="'.($i - 1).'" data-type="ldpstrengths">'.$this->str('ldpstrengths:add').'</button>';
        $mform->addElement(
                'html',
                $button.$noscript
                );

        $developmentareas = [
            2,
            !empty($data->ldpdevelopmentareas) ? count($data->ldpdevelopmentareas) + 1 : 0,
            !empty($_POST['ldpdevelopmentareas']) ? count($_POST['ldpdevelopmentareas']) : 0
        ];
        $maxdevelopmentareas = max($developmentareas);
        if ($islocked || $data->appraisal->viewingas === 'appraiser') {
            $maxdevelopmentareas = (!empty($data->ldpdevelopmentareas) ? count($data->ldpdevelopmentareas) : 1);
        }
        for ($i = 0; $i < $maxdevelopmentareas; $i++) {
            $label = ($i === 0 ? $this->str('ldpdevelopmentareas') : '');
            $mform->addElement('text', "ldpdevelopmentareas[{$i}]", $label, ['class' => 'oa-repeating-element']);
            $mform->setType("ldpdevelopmentareas[{$i}]", PARAM_TEXT);
            $mform->disabledIf("ldpdevelopmentareas[{$i}]", 'islocked', 'eq', 1);
            $mform->disabledIf("ldpdevelopmentareas[{$i}]", 'view', 'neq', 'appraisee');
        }
        $noscript = "<p class=\"visibleifnotjs\">{$this->str('ldpdevelopmentareas:add:noscript')}</p>";
        $button = '<button class="btn btn-xs btn-primary oa-add-repeating-element" data-index="'.($i - 1).'" data-type="ldpdevelopmentareas">'.$this->str('ldpdevelopmentareas:add').'</button>';
        $mform->addElement(
                'html',
                $button.$noscript
                );

        $data702010 = $this->get_702010();
        $islockedattr = '';
        if ($islocked || $data->appraisal->viewingas !== 'appraisee') {
            $islockedattr = ' locked="yes"';
        }
        $mform->addElement('textarearup', 'ldpdevelopmentplan', $this->str('ldpdevelopmentplan') . $data702010, 'rows="3" cols="70"' . $islockedattr, '', '');
        $mform->setType('ldpdevelopmentplan', PARAM_RAW);
        $mform->disabledIf('ldpdevelopmentplan', 'islocked', 'eq', 1);
        $mform->disabledIf('ldpdevelopmentplan', 'view', 'neq', 'appraisee');

        $attributes = ['group' => 1];
        if ($data->appraisal->viewingas === 'appraisee') {
            $attributes['data-toggle'] = 'tooltip';
            $attributes['title'] = $this->str('ldplocked:tooltip');
        }
        $mform->addElement('advcheckbox', 'ldplocked', '', $this->str('ldplocked'), $attributes, [0, 1]);
        $mform->disabledIf('ldplocked', 'view', 'neq', 'appraiser');
        if (!$isformlocked) {
            $mform->disabledIf('ldplocked', 'islocked', 'eq', 1);
        } else {
            $mform->disabledIf('ldplocked', 'islockedforuser', 'eq', 1);
        }

        if (!$islocked || ($isformlocked && !$islockedforuser)) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'), ['class' => ($isformlocked) ? 'oa-unlock-ldp' : '']);
            if ($isformlocked) {
                $mform->disabledIf('submitbutton', 'ldplocked', 'eq', 1);
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
        return $this->stringman->string_exists('form:leaderplan:' . $string, 'local_onlineappraisal');
    }

    private function str($string) {
        return get_string('form:leaderplan:' . $string, 'local_onlineappraisal');
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
        switch ($this->_customdata->appraisal->viewingas) {
            case 'appraisee' :
                // Handle empty multi select, but only if unlocked.
                if (!isset($data->ldppotential) && !$data->islocked) {
                    $data->ldppotential = [];
                }
                // Clear empty inputs.
                if (isset($data->ldpstrengths)) {
                    $data->ldpstrengths = array_values(array_filter($data->ldpstrengths));
                }
                if (isset($data->ldpdevelopmentareas)) {
                    $data->ldpdevelopmentareas = array_values(array_filter($data->ldpdevelopmentareas));
                }
                // Remove non-appraisee field.
                if (isset($data->ldplocked)) {
                    unset($ldplocked);
                }
                break;
            case 'appraiser' :
                // Remove non-appraiser fields.
                foreach (['ldppotential', 'ldpstrengths', 'ldpdevelopmentareas', 'ldpdevelopmentplan'] as $field) {
                    if (isset($data->{$field})) {
                        unset ($data->{$field});
                    }
                }
                // Locking or unlocking?
                if (!$data->islocked && $data->ldplocked) {
                    $this->locking = true;
                }
                if ($data->islocked && !$data->ldplocked) {
                    $this->unlocking = true;
                }
                break;
            default:
                // Remove all fields.
                $data = new stdClass();
                break;
        }
        // Tidy up control fields.
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
                            'comment:ldp:unlocking',
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
                            'comment:ldp:locking',
                            'local_onlineappraisal',
                            $a
                            )
                    );
        }
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

    /**
     *
     * @global \moodle_database $DB
     * @global type $PAGE
     * @return boolean
     */
    private function get_702010() {
        global $DB, $PAGE;

        $form = 'development';
        $fields = ['seventy', 'twenty', 'ten'];

        $renderer = $PAGE->get_renderer('local_onlineappraisal', 'leaderplan');

        $data702010 = [];
        $formrec = $DB->get_record(
                'local_appraisal_forms',
                [
                    'appraisalid' => $this->_customdata->appraisal->id,
                    'user_id' => $this->_customdata->appraisal->appraisee->id,
                    'form_name' => $form
                ]);
        if ($formrec) {
            list($in, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
            $params['formid'] = $formrec->id;
            $fieldrecs = $DB->get_records_select('local_appraisal_data', "form_id = :formid AND name {$in}", $params);
            foreach ($fieldrecs as $fieldrec) {
                $out = new stdClass();
                $out->question = get_string("form:{$form}:{$fieldrec->name}", 'local_onlineappraisal');
                if ($fieldrec->type == 'array') {
                    $out->isarray = true;
                    $out->data = unserialize($fieldrec->data);
                } else {
                    $out->data = $fieldrec->data;
                }
                $data702010[] = clone($out);
            }

            array_walk($data702010, function(&$val) {
                if (!empty($val->isarray)) {
                    array_walk($val->data, function(&$val) { $val = nl2br($val); });
                } else {
                    $val->data = nl2br($val->data);
                }
            });
        }

        $template = new \local_onlineappraisal\output\leaderplan\leaderplan_702010($data702010);
        return $renderer->render($template);
    }

    /**
     *
     */
    private function get_import_html() {
        global $DB, $PAGE;

        $renderer = $PAGE->get_renderer('local_onlineappraisal', 'leaderplan');

        $data = new stdClass();
        $data->disabled = false;
        $url = new moodle_url(
            '/local/onlineappraisal/view.php',
            [
                'page' => 'leaderplan',
                'appraisalid' => $this->_customdata->appraisal->id,
                'view' => $this->_customdata->appraisal->viewingas,
                'leaderplanaction' => 'import',
                'sesskey' => sesskey(),
            ]);
        $data->url = $url->out();

        $formrec = $DB->get_record(
            'local_appraisal_forms',
            [
                'appraisalid' => $this->_customdata->appraisal->id,
                'user_id' => $this->_customdata->appraisal->appraisee->id,
                'form_name' => 'leaderplan'
            ]);

        if ($formrec) {
            $fields = ['ldppotential', 'ldpstrengths', 'ldpdevelopmentareas', 'ldpdevelopmentplan'];
            list($in, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
            $params['formid'] = $formrec->id;
            $fieldrecs = $DB->get_records_select('local_appraisal_data', "form_id = :formid AND name {$in}", $params);
            foreach ($fieldrecs as $fieldrec) {
                if ($fieldrec->type === 'array' && empty(unserialize($fieldrec->data))) {
                    continue;
                } else if (empty($fieldrec->data)) {
                    continue;
                }
                $data->disabled = true;
                break;
            }
        }

        return $renderer->render_from_template('local_onlineappraisal/leaderplan_import', $data);
    }
}
