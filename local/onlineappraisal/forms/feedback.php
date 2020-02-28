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

class apform_feedback extends moodleform {

    /**
     * @array translations for feedback email found.
     */
    private $translations;

    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;

        $strrequired = get_string('required');

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'feedback');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas);
        $mform->setType('view', PARAM_TEXT);

        $mform->addElement('hidden', 'hascustomemail', !empty($data->customemail), array('id' => 'hascustomemail'));
        $mform->setType('hascustomemail', PARAM_INT);

        $title = $data->formid > 0 ? 'title:resend' : 'title';
        $mform->addElement('html', html_writer::tag('h2', $this->str($title)));

        $mform->addElement('text', 'firstname', $this->str('firstname'));
        $mform->setType('firstname', PARAM_RAW);
        $mform->addRule('firstname', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'lastname', $this->str('lastname'));
        $mform->setType('lastname', PARAM_RAW);
        $mform->addRule('lastname', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'email', $this->str('email'));
        $mform->setType('email', PARAM_RAW);
        $mform->addRule('email', $strrequired, 'required', null, 'client');

        $customemailclass = '';
        if ($data->formid <= 0) {
            if ($data->appraisal->viewingas == 'appraiser') {
                $languages = $this->get_translations('email:body:appraiserfeedbackmsg');
                $mform->addElement('select', 'language', $this->str('language'), $languages);
                $emailtext = $this->split_body(get_string('email:body:appraiserfeedbackmsg', 'local_onlineappraisal'));
            } else {
                $languages = $this->get_translations('email:body:appraiseefeedbackmsg');
                $mform->addElement('select', 'language', $this->str('language'), $languages);
                $emailtext = $this->split_body(get_string('email:body:appraiseefeedbackmsg', 'local_onlineappraisal'));
            }

            if (array_key_exists(current_language(), $this->translations)) {
                $mform->setDefault('language', current_language());
            } else {
                $mform->setDefault('language', 'en');
            }

            $mform->addElement('html', '<span id="editmsg" class="btn btn-default m-b-5" title="'.$this->str('providefirstnamelastname').'">' . $this->str('editemail') . '</span>');
            $mform->addElement('html', '<div id="emailmsg" class="well emailmsg">
                <span  id="emailtextstart">' . $emailtext->start . '</span>');

            $mform->addElement('textarea', 'emailtext', '', 'rows="15" cols="70"');
            $mform->setType('emailtext', PARAM_RAW);
            $mform->addElement('html',  '<span id="emailtextend">' . $emailtext->end . '</span></div>');

            $customemailclass = ' class="hidden"';
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">'.$this->str('resendhelp').'</div>');
        }

        $mform->addElement('html', '<div id="customemailmsg"'.$customemailclass.'>');
        $mform->addElement('textarea', 'customemailmsg', '', 'rows="25" cols="70"');
        $mform->setType('textarea', PARAM_RAW);
        $mform->addElement('html', '</div>');

        $buttonarray = array();
        $submitbutton = $data->formid > 0 ? 'resendemailbtn' : 'sendemailbtn';
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $this->str($submitbutton));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

    }

    private function str($string) {
        return get_string('form:feedback:' . $string, 'local_onlineappraisal');
    }

    /**
     * Splits the email string body so it can wrap the textarea where the user
     * can type a message to the recipient.
     * @param string $string Language string to be used in email.
     */
    private function split_body($string) {
        $emailtext = new stdClass();
        $emailtext->start = '';
        $emailtext->end = '';

        foreach ($this->translations as $lang => $translation) {
            $class = 'language language-' . $lang;
            if (current_language() != $lang) {
                $class .= ' hidden';
            }
            // Prevent hiding everything when the current selected language has no translation.
            if ($lang == 'en' && !array_key_exists(current_language(), $this->translations)) {
                $class = 'language language-en';
            }
            $emailstring = explode('{{emailtext}}', $translation);
            $emailtext->start .= html_writer::tag('div',  $emailstring[0], array('class' => $class));
            $emailtext->end .= html_writer::tag('div', $emailstring[1], array('class' => $class));
        }
        return $emailtext;
    }

    /**
     * Stored data for this form. This method is called from \local_onlineappraisal\forms if
     * it exists for a form after a form has been instantiated.
     * @param \local_onlineappraisal\forms $forms. An instance of the \local_onlineappraisal\forms class.
     */
    public static function stored_form(\local_onlineappraisal\forms $forms) {
        global $DB;

        $feedbackid = optional_param('feedbackid', -1, PARAM_INT);

        $params = array(
            'appraisalid' => $forms->appraisal->appraisal->id,
            'requested_by' => $forms->appraisal->user->id,
            'id' => $feedbackid,
        );
        $feedback = $DB->get_record('local_appraisal_feedback', $params);
        if (!$feedback) {
            $feedback = new stdClass();
            $feedback->formid = -1;
        } else {
            $feedback->formid = $feedback->id;
            // Different naming conmvention DB <=> form.
            $feedback->customemailmsg = html_to_text($feedback->customemail, 0, false);
        }
        $feedback->userid = $forms->appraisal->user->id;
        $feedback->appraisalid = $forms->appraisal->appraisal->id;
        $feedback->appraisal = $forms->appraisal->appraisal;
        $feedback->viewingas = $forms->appraisal->appraisal->viewingas;
        $feedback->nexturl = $forms->appraisal->get_nextpage();
        return $feedback;
    }

    /**
     * Store data for this form. This method is called from \local_onlineappraisal\forms if
     * it exists for a form after a form has been submitted.
     * @param object $appraisal. An instance of the \local_onlineappraisal\appraisal with this form loaded.
     * @param array $data. Array of name => value pairs received after the form has been submitted.
     */
    public function store_data(\local_onlineappraisal\forms $forms, $data) {
        $feedback = new \local_onlineappraisal\feedback($forms->appraisal);
        return $feedback->store_feedback_recipient($data);
    }

    /**
     * Gets the list of translations available for this language string
     * @param string $langstring. Search for this language string
     * @return array $languages. Array of languages that have a translation for this string.
     */
    public function get_translations($langstring) {
        global $CFG;
        $stringman = get_string_manager();
        $languages = $stringman->get_list_of_translations();

        $string = $stringman->get_string($langstring, 'local_onlineappraisal', null, 'en');

        foreach ($languages as $langtype => $langname) {
            $stringtranslation = $stringman->get_string($langstring, 'local_onlineappraisal', null, $langtype);
            if ($langtype == 'en') {
                $this->translations[$langtype] = $stringtranslation;
                continue;
            }
            if ($string == $stringtranslation) {
                unset($languages[$langtype]);
            } else {
                $this->translations[$langtype] = $stringtranslation;
            }
        }
        return $languages;
    }

    /**
     * Returns the URL to redirect to when cancelled.
     *
     * @return moodle_url
     */
    public function cancel_redirect_url() {
        $data = $this->_customdata;
        // Back to main feedback page.
        return new moodle_url(
            '/local/onlineappraisal/view.php',
            array(
                'page' => 'feedback',
                'appraisalid' => $data->appraisalid,
                'view' => $data->appraisal->viewingas
            ));
    }

    /**
     * Validate the Feedback form
     */
    function validation($data, $files) {
        $errors = array();
        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }
        return $errors;
    }
}
