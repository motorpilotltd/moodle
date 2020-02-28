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

class apform_userinfo extends moodleform {
    private $haspermission = false;

    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'userinfo');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'prepop', 1);
        $mform->setType('prepop', PARAM_INT);

        $mform->addElement('hidden', 'appraiseeedit', $data->appraiseeedit);
        $mform->setType('appraiseeedit', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas);
        $mform->setType('view', PARAM_TEXT);

        // Retreive the permissions for the face2face fields.
        $facetofaceedit = $facetofaceheldedit = APPRAISAL_FIELD_LOCKED;
        if (\local_onlineappraisal\permissions::is_allowed('f2f:add',
            $data->appraisal->permissionsid, $data->appraisal->viewingas, $data->appraisal->archived, $data->appraisal->legacy)) {
            $facetofaceedit = APPRAISAL_FIELD_EDIT;
        }
        $mform->addElement('hidden', 'facetofaceedit', $facetofaceedit);
        $mform->setType('facetofaceedit', PARAM_INT);

        if (\local_onlineappraisal\permissions::is_allowed('f2f:complete',
            $data->appraisal->permissionsid, $data->appraisal->viewingas, $data->appraisal->archived, $data->appraisal->legacy)) {
            $facetofaceheldedit = APPRAISAL_FIELD_EDIT;
        }
        $mform->addElement('hidden', 'facetofaceheldedit', $facetofaceheldedit);
        $mform->setType('facetofaceheldedit', PARAM_INT);

        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', '<hr class="tophr">');
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));

        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        $mform->addElement('text', 'fullname', $this->str('name'));
        $mform->setType('fullname', PARAM_TEXT);
        $mform->setDefault('fullname', $appraiseename);
        $mform->disabledIf('fullname', 'prepop', 'eq', 1);

        $mform->addElement('text', 'staffid', $this->str('staffid'));
        $mform->setType('staffid', PARAM_TEXT);
        $mform->setDefault('staffid', $data->appraisal->appraisee->idnumber);
        $mform->disabledIf('staffid', 'prepop', 'eq', 1);

        $mform->addElement('text', 'grade', $this->str('grade'));
        $mform->setType('grade', PARAM_TEXT);
        $mform->setDefault('grade', $data->appraisal->grade);
        $mform->disabledIf('grade', 'prepop', 'eq', 1);

        $mform->addElement('text', 'jobtitle', $this->str('jobtitle'));
        $mform->setType('jobtitle', PARAM_TEXT);
        $mform->setDefault('jobtitle', $data->appraisal->job_title);
        $mform->disabledIf('jobtitle', 'prepop', 'eq', 1);

        $mform->addElement('text', 'operationaljobtitle', $this->str('operationaljobtitle'));
        $mform->setType('operationaljobtitle', PARAM_RAW);
        $mform->setDefault('operationaljobtitle', $data->appraisal->operational_job_title);
        $mform->disabledIf('operationaljobtitle', 'view', 'neq', 'appraisee');
        $mform->disabledIf('operationaljobtitle', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $facetofacedate = '';
        if ($data->appraisal->held_date) {
            $facetofacedate = userdate($data->appraisal->held_date, get_string('strftimedaydate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        }


        /**
         * New form field for the date selector. rendered moustache, requires amd JS and
         * a hidden form field to store the associated Unix timestamp.
         *
         * @param string dateselect
         * @param string fieldname
         * @param string label
         * @param array options
         * @param string hiddenfieldname.
         */
        // The hidden field.
        $mform->addElement('hidden', 'facetoface', $data->appraisal->held_date);
        $mform->setType('facetoface', PARAM_INT);

        // The dateselect form field.
        $mform->addElement('dateselect', 'facetofacedate', $this->str('facetoface'), array(), 'facetoface');
        $mform->setType('facetofacedate', PARAM_TEXT);
        $mform->setDefault('facetofacedate', $facetofacedate);
        $mform->disabledIf('facetofacedate', 'facetofaceedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('advcheckbox', 'facetofaceheld', '', $this->str('facetofaceheld'), array('group' => 1), array(0, 1));
        $mform->setDefault('facetofaceheld', $data->appraisal->face_to_face_held);
        $mform->disabledIf('facetofaceheld', 'facetofaceheldedit', 'eq', APPRAISAL_FIELD_LOCKED);

        // Quite a few checks. Should this be abstracted to something simpler?
        if ($facetofaceedit == APPRAISAL_FIELD_EDIT ||
            $facetofaceheldedit == APPRAISAL_FIELD_EDIT ||
            $data->appraiseeedit == APPRAISAL_FIELD_EDIT
            ) {
            $this->haspermission = true;
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
        return get_string('form:userinfo:' . $string, 'local_onlineappraisal', $a);
    }

    /**
     * Store data for this form. This method is called from \local_onlineappraisal\forms if
     * it exists for a form after a form has been submitted.
     * @param object $forms An instance of \local_onlineappraisal\forms.
     * @param stdClass $data Object of name => value pairs received after the form has been submitted.
     */
    public function store_data(\local_onlineappraisal\forms $forms, $data) {
        $appraisal = $forms->appraisal;
        if (!empty($data->facetoface)) {
            $appraisal->set_appraisal_field('held_date', $data->facetoface);
        }

        if (!empty($data->operationaljobtitle)) {
            $appraisal->set_appraisal_field('operational_job_title', $data->operationaljobtitle);
        }
        $appraisal->set_appraisal_field('face_to_face_held', $data->facetofaceheld);

        return true;
    }

    /**
     * Does the user have permission to submit this form?
     */
    public function has_permission() {
        return $this->haspermission;
    }
}