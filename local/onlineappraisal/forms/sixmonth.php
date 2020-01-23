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

class apform_sixmonth extends moodleform {
    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'sixmonth');
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


        $usertype = ($data->appraisal->viewingas == 'appraiser') ? 'appraiser' : 'appraisee';

        $locked = '';
        if (($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) && ($usertype == 'appraisee')) {
            $locked = ' locked="yes"';
        } else if (($data->appraiseredit == APPRAISAL_FIELD_LOCKED) && ($usertype == 'appraiser')) {
            $locked = ' locked="yes"';
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

        // Set user type as field can be open to multiple.

        // Last modified date for se in lang string.
        $a = $data->appraisal->six_month_review_date ? userdate($data->appraisal->six_month_review_date) : $this->str('never');
        $mform->addElement('textarearup', 'sixmonthreview', $this->str('sixmonthreview'), 'rows="6" cols="70"' . $locked, $this->str('sixmonthreviewhelp', $a), $usertype);
        $mform->setType('sixmonthreview', PARAM_RAW);
        $mform->setDefault('sixmonthreview', $data->appraisal->six_month_review);
        $mform->disabledIf('sixmonthreview', "{$usertype}edit", 'neq', APPRAISAL_FIELD_EDIT);

        $useredit = "{$usertype}edit";
        if ($data->{$useredit} == APPRAISAL_FIELD_EDIT) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'), array('class' => 'm-l-5'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str($string, $a = null) {
        return get_string('form:sixmonth:' . $string, 'local_onlineappraisal', $a);
    }

    public function definition_after_data() {
        global $USER;
        $mform =& $this->_form;
        $data = $this->_customdata;
        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
    }

    /**
     * Store data for this form. This method is called from \local_onlineappraisal\forms if
     * it exists for a form after a form has been submitted.
     * @param object $appraisal. An instance of the \local_onlineappraisal\appraisal with this form loaded.
     * @param array $data. Array of name => value pairs received after the form has been submitted.
     */
    public function store_data(\local_onlineappraisal\forms $forms, $data) {
        // Save review and set/update timestamp.
        $appraisal = $forms->appraisal;
        $appraisal->set_appraisal_field('six_month_review', $data->sixmonthreview);
        $appraisal->set_appraisal_field('six_month_review_date', time());

        return true;
    }
}
