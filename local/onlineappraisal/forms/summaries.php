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

class apform_summaries extends moodleform {
    public function definition() {
        global $DB, $PAGE, $USER;

        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->updateAttributes(array('class' => $mform->getAttribute('class').' oa-save-session-check'));

        if ($data->groupleaderedit == APPRAISAL_FIELD_EDIT) {
            if ($data->appraisal->statusid > APPRAISAL_COMPLETE
                    || !$data->appraisal->groupleader
                    || $data->appraisal->groupleader->id != $USER->id) {
                // Should be locked.
                $data->groupleaderedit = APPRAISAL_FIELD_LOCKED;
            }
        }

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'summaries');
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

        $mform->addElement('hidden', 'signoffedit', $data->signoffedit);
        $mform->setType('signoffedit', PARAM_INT);

        $mform->addElement('hidden', 'groupleaderedit', $data->groupleaderedit);
        $mform->setType('groupleaderedit', PARAM_INT);

        $appraiseelocked = '';
        if ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $appraiseelocked = ' locked="yes"';
        }

        $appraiserlocked = '';
        if ($data->appraiseredit == APPRAISAL_FIELD_LOCKED) {
            $appraiserlocked = ' locked="yes"';
        }

        $signofflocked = '';
        if ($data->signoffedit == APPRAISAL_FIELD_LOCKED) {
            $signofflocked = ' locked="yes"';
        }

        $groupleaderlocked = '';
        if ($data->groupleaderedit == APPRAISAL_FIELD_LOCKED) {
            $groupleaderlocked = ' locked="yes"';
        }


        $mform->addElement('html', '<hr class="tophr">');
        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));
        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        $mform->addElement('textarearup', 'appraiser', $this->str('appraiser'), 'rows="3" cols="70"' . $appraiserlocked, $this->str('appraiserhelp'), 'appraiser');
        $mform->setType('appraiser', PARAM_RAW);
        $mform->disabledIf('appraiser', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);

        // Special field for Americas.
        // Available if appraisee region is Americas (TAPS).
        // Visible to appraiser and sign off.
        // Appraiser can edit as per other summaries.
        // Never visible to appraisee.
        $americassql = "SELECT lru.geotapsregionid
            FROM {local_regions_use} lru
            JOIN {local_regions_reg} lrr ON lrr.id = lru.geotapsregionid
            WHERE lru.userid = :userid AND lrr.name = 'Americas'";
        if ($data->appraisal->viewingas != 'appraisee' && $DB->get_field_sql($americassql, array('userid' => $data->appraisal->appraisee->id))) {
            $question = 'Please provide your assessment of the Appraisee\'s adequacy in their grade by choosing the best option from the list below.
                This information is your recommendation to Local Practice Leader & Group Leader and should NOT be discussed with the Appraisee.';
            $answers = array(
                '',
                'Recommend promotion to next grade this cycle',
                'Well place in current grade',
                'Needs development in current grade',
                'Not acceptable in current grade',
                'Too new to assess',
            );
            $mform->addElement('select', 'promotion', $question, array_combine($answers, $answers));
            $mform->disabledIf('promotion', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);
        }

        $mform->addElement('textarearup', 'recommendations', $this->str('recommendations'), 'rows="3" cols="70"' . $appraiserlocked, $this->str('recommendationshelp'), 'appraiser');
        $mform->setType('recommendations', PARAM_RAW);
        $mform->disabledIf('recommendations', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'appraisee', $this->str('appraisee'), 'rows="3" cols="70"' . $appraiseelocked, $this->str('appraiseehelp'), 'appraisee');
        $mform->setType('appraisee', PARAM_RAW);
        $mform->disabledIf('appraisee', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'signoff', $this->str('signoff'), 'rows="3" cols="70"' . $signofflocked , $this->str('signoffhelp'), 'signoff');
        $mform->setType('signoff', PARAM_RAW);
        $mform->disabledIf('signoff', 'signoffedit', 'eq', APPRAISAL_FIELD_LOCKED);

        if ($data->appraisal->groupleader || !empty($data->grpleader)) {
            $mform->addElement('textarearup', 'grpleader', $this->str('grpleader'), 'rows="3" cols="70"' . $groupleaderlocked, $this->str('grpleaderhelp'), 'groupleader');
            $mform->setType('grpleader', PARAM_RAW);
            $mform->disabledIf('grpleader', 'groupleaderedit', 'eq', APPRAISAL_FIELD_LOCKED);
            if ($data->groupleaderedit === APPRAISAL_FIELD_EDIT) {
                // Only append these special fields if groupleader is editing.
                // Hidden field to store group leader user id (in case of previous multiple group leaders or removal of user after data entry).
                $mform->addElement('hidden', 'groupleaderid', $USER->id);
                $mform->setType('groupleaderid', PARAM_INT);
                // Hidden field to store timestamp
                $mform->addElement('hidden', 'grpleadertimestamp', time());
                $mform->setType('grpleadertimestamp', PARAM_INT);
            }
        }

        if ($data->appraiseeedit == APPRAISAL_FIELD_EDIT
                || $data->appraiseredit == APPRAISAL_FIELD_EDIT
                || $data->signoffedit == APPRAISAL_FIELD_EDIT
                || $data->groupleaderedit == APPRAISAL_FIELD_EDIT) {
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

            // Saving nag modal.
            $renderer = $PAGE->get_renderer('local_onlineappraisal');
            $mform->addElement('html', $renderer->render_from_template('local_onlineappraisal/modal_save_nag', new stdClass()));
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str($string, $a = '') {
        return get_string('form:summaries:' . $string, 'local_onlineappraisal', $a);
    }

    public function definition_after_data() {
        global $DB, $PAGE, $USER;

        $mform =& $this->_form;
        $data = $this->_customdata;
        
        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
        if (($data->appraisal->groupleader || !empty($data->grpleader)) && !empty($data->groupleaderid)) {
            $user = $DB->get_record('user', array('id' => $data->groupleaderid));
            if ($user) {
                $holder = &$mform->createElement('static', 'holder');
                $renderer = $PAGE->get_renderer('local_onlineappraisal');
                $a = new stdClass();
                $a->fullname = fullname($user);
                $a->date = empty($data->grpleadertimestamp) ? '' : ' (' .userdate($data->grpleadertimestamp, get_string('strftimedate')) . ')';
                $caption = new \local_onlineappraisal\output\caption($this->str('grpleadercaption', $a));
                $mform->insertElementBefore($holder, 'grpleader');
                $mform->insertElementBefore($mform->removeElement('grpleader', false), 'holder');
                $mform->insertElementBefore($mform->createElement('html', $renderer->render($caption)), 'holder');
                $mform->removeElement('holder', true);
            }
        }
    }

    /**
     * Override parent method to update timestamp.
     *
     * @return \stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if(isset($data->grpleadertimestamp)) {
            // Update it to actual saved time.
            $data->grpleadertimestamp = time();
        }
        // Only want to reset if grpleader is set _and_ empty.
        if (isset($data->grpleader) && !$data->grpleader) {
            // Wipe timestamp/id.
            $data->groupleaderid = null;
            $data->grpleadertimestamp = null;
        }
        return $data;
    }
}
