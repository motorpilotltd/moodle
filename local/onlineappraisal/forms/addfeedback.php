<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class apform_addfeedback extends moodleform {
    private $appraisalid;
    private $pw;

    public function definition() {
        $this->appraisalid = required_param('id', PARAM_INT);
        $this->pw = required_param('pw', PARAM_RAW);
        $this->appraiserrequest = optional_param('appraiser', 0, PARAM_INT);
        $strrequired = get_string('required');
        $mform = $this->_form;

        if ($data = $this->load_feedback($this->appraisalid, $this->pw)) {
            if ($data->received_date) {
                $alert = html_writer::tag('div', $this->str('submitted'), array('class' => 'alert alert-success m-t-10'));
                $mform->addElement('html', $alert);
            } else {
                if ($this->pw == 'self') {
                    $mform->addElement('text', 'firstname', $this->str('firstname'));
                    $mform->setType('firstname', PARAM_RAW);
                    $mform->addRule('firstname', $strrequired, 'required', null, 'client');

                    $mform->addElement('text', 'lastname', $this->str('lastname'));
                    $mform->setType('lastname', PARAM_RAW);
                    $mform->addRule('lastname', $strrequired, 'required', null, 'client');
                }

                $mform->addElement('hidden', 'page', 'addfeedback');
                $mform->setType('page', PARAM_TEXT);

                $mform->addElement('hidden', 'id', $data->appraisalid);
                $mform->setType('id', PARAM_INT);

                $mform->addElement('hidden', 'feedbackid', $data->id);
                $mform->setType('feedbackid', PARAM_INT);

                $mform->addElement('hidden', 'pw', $this->pw);
                $mform->setType('pw', PARAM_RAW);

                $mform->addElement('hidden', 'buttonclicked', 0);
                $mform->setType('buttonclicked', PARAM_INT);

                $mform->addElement('hidden', 'appraiserrequest', $this->appraiserrequest);
                $mform->setType('appraiserrequest', PARAM_INT);

                $mform->addElement('textarearup', 'feedback', $this->str('addfeedback'), 'rows="8" cols="70"',
                    $this->str('addfeedbackhelp'), '');
                $mform->setType('feedback', PARAM_RAW);
                $mform->setDefault('feedback', $data->feedback);
                if ($this->pw == 'self') {
                    $mform->addHelpButton('feedback', 'form:addfeedback:addfeedback', 'local_onlineappraisal');
                }

                $mform->addElement('textarearup', 'feedback_2', $this->str('addfeedback_2'), 'rows="8" cols="70"',
                    $this->str('addfeedback_2help'), '');
                $mform->setType('feedback_2', PARAM_RAW);
                $mform->setDefault('feedback_2', $data->feedback_2);

                $mform->addElement('html', html_writer::div($this->str('warning'), 'm-b-10'));

                // Change Upgrade Log Appraisal V3 id 5
                // $label = get_string('confidential_label', 'local_onlineappraisal');
                // $description = get_string('confidential_label_text', 'local_onlineappraisal');
                // $mform->addElement('advcheckbox', 'confidential', $label, $description, array('group' => 1), array(0, 1));
                // $mform->setDefault('confidential', $data->confidential);
                $buttonarray=array();
                if ($this->pw == 'self') {
                    $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $this->str('savefeedback'),
                    'class="sendfeedbackbtn"');
                } else {
                    $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $this->str('sendemailbtn'),
                    'class="sendfeedbackbtn"');
                    $buttonarray[] = &$mform->createElement('submit', 'savedraft', $this->str('savedraftbtn'),
                    'class="savedraftbtn" data-toggle="tooltip" data-placement="top" title="' . $this->str('savedraftbtntooltip') . '"');
                }

                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            }
        } else {
            $alert = html_writer::tag('div', $this->str('notfound'), array('class' => 'alert alert-danger'));
            $mform->addElement('html', $alert);
        }
    }

    private function str($string) {
        return get_string('form:addfeedback:' . $string, 'local_onlineappraisal');
    }

    private function load_feedback($appraisalid, $pw) {
        global $DB;
        if ($pw == 'self') {
            $fb = new stdClass();
            $fb->id = 0;
            $fb->appraisalid = $appraisalid;
            if ($this->appraiserrequest) {
                $fb->feedback_user_type = 'appraiser';
            } else {
                $fb->feedback_user_type = 'appraisee';
            }
            $fb->password = 'self';
            $fb->feedback = '';
            $fb->feedback_2 = '';
            $fb->received_date = 0;
            return $fb;
        }
        if ($fb = $DB->get_record('local_appraisal_feedback', array('appraisalid' => $appraisalid, 'password' => $pw))) {
            return $fb;
        }
    }

    /**
     * Store data for this form. This method is called from \local_onlineappraisal\forms if
     * it exists for a form after a form has been submitted.
     * @param object $appraisal An instance of the \local_onlineappraisal\appraisal with this form loaded.
     * @param array $data Array of name => value pairs received after the form has been submitted.
     */
    public function store_data(\local_onlineappraisal\forms $forms, $data) {
        global $DB, $USER;
        if ($data->pw == 'self') {
            $data->appraisalid = $data->id;
            $data->requested_by = $USER->id;
            $data->password = 'self';
            $data->created_date = time();
            $data->received_date = time();
            $data->email = $USER->email;
            if ($data->appraiserrequest) {
                $data->feedback_user_type = 'appraiser';
            } else {
                $data->feedback_user_type = 'appraisee';
            }
            $data->confidential = 0;
            $data->id = $DB->insert_record('local_appraisal_feedback', $data);
        }

        $feedback = new \local_onlineappraisal\feedback($forms->appraisal);
        $feedback->user_feedback($data);

        if ($data->pw ==  'self') {
            $params = array('page' => 'feedback',
                'view' => 'appraisee',
                'appraisalid' => $data->appraisalid);
            if ($data->appraiserrequest) {
                $params['view'] = 'appraiser';
            }
            $redirect = new moodle_url('/local/onlineappraisal/view.php', $params);
            redirect($redirect);
        }
    }

    /**
     * Returns the URL to redirect to when submitted or false to not redirect.
     *
     * @return false|moodle_url
     */
    public function redirect_url() {
        // Back to add feedback page.
        return new moodle_url(
            '/local/onlineappraisal/add_feedback.php',
            array(
                'id' => $this->appraisalid,
                'pw' => $this->pw
            ));
    }

    /**
     * Does the user have permission to submit this form?
     */
    public function has_permission() {
        // Always yes as it's anonymous.
        return true;
    }

    /**
     * Validate the form.
     */
    function validation($data, $files) {
        $errors = array();
        if ($data['buttonclicked'] != 2) {
            if (!isset($data['feedback']) || empty(trim($data['feedback']))) {
                $errors['feedback'] = get_string('required');
            }
            if (!isset($data['feedback_2']) || empty(trim($data['feedback_2']))) {
                $errors['feedback_2'] = get_string('required');
            }
        }
        return $errors;
    }
}
