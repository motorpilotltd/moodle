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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;
use moodle_exception;
use local_onlineappraisal\permissions as permissions;
use local_onlineappraisal\comments as comments;
use local_onlineappraisal\email as email;

class stages {

    /**
     * @var \local_onlineappraisal\appraisal $appraisal
     */
    private $appraisal;

    /**
     * Holds instance of string manager
     * @var \core_string_manager $strman
     */
    private $strman;

    /**
     * Form data for appraisal.
     * @var array $formdata
     */
    private $formdata = array();

    /**
     * Contains error objects.
     * @var array $errors
     */
    private $errors = array();

    /**
     * Which pages have failed validation.
     * @var array $failedvalidation
     */
    private $failedvalidation = array();

    /**
     * Should a redirect be triggered after processing, i.e. if DB update of status has successfully occurred.
     *
     * @var boolean $redirect
     */
    private $redirect = false;

    /**
     * Multi-dimensional array indicating what the current status can be updated to
     * (via either submitting or returning) and what the associated permissions should be.
     * e.g. $updatepaths[5]['return'][3] = 4;
     *      Indicates that when at stage 5 the appraisal can 'return' to stage 3 with permissions set to 4.
     *
     * @var array $updatepaths
     */
    private static $updatepaths = array(
        1 => array('submit' => array(2 => 2)),
        2 => array('submit' => array(3 => 3)),
        3 => array(
            'return' => array(2 => 3),
            'submit' => array(4 => 4)
            ),
        4 => array(
            'return' => array(3 => 4),
            'submit' => array(5 => 5)
            ),
        5 => array(
            'return' => array(4 => 4),
            'submit' => array(6 => 6)
            ),
        6 => array(
            'submit' => array(7 => 7)
            ),
        7 => array(
            'submit' => array(9 => 7)
            ),
    );

    /**
     * Required data to move forward to next stage.
     * Array with current statuses as keys and values containing an array showing what needs validating.
     * This array has keys indicating appraisal or form and values containing:
     * For appraisal: array of field names
     * For form: array with keys indicating which form and values which field within that form.
     *
     * @var array $reuired
     */
    private static $required = array(
        2 => array(
            'any' => array(
                'appraisal' => array('operational_job_title', 'held_date'),
                'form' => true,
            ),
        ),
        3 => array(
            'appraisal' => array('held_date', 'face_to_face_held'),
            'form' => array(
                'summaries' => array('appraiser', 'recommendations')
            ),
        ),
        4 => array(
            'form' => array(
                'summaries' => array('appraisee')
            ),
        ),
        6 => array(
            'form' => array(
                'summaries' => array('signoff')
            ),
        ),
        7 => array(
            'special' => array(
                'validate_groupleader',
                'validate_groupleader_summary'
            ),
        ),
    );

    /**
     * Constructor.
     *
     * @param stdClass $appraisal Appraisal record (minimum).
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        $this->appraisal = $appraisal;
        $this->strman = get_string_manager();
    }

    /**
     * Magic getter
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    /**
     * Get the next status given whether it is for 'submit' or 'return'.
     *
     * @param string $type The type of update ('submit' or 'return').
     * @return int|false Next status or false.
     */
    public function get_update_path($type = 'submit') {
        $statusid = $this->appraisal->appraisal->statusid;
        if (isset(self::$updatepaths[$statusid][$type])) {
            reset(self::$updatepaths[$statusid][$type]);
            return key(self::$updatepaths[$statusid][$type]);
        }
        $this->set_error('invalidpath');
        return false;
    }

    /**
     * Checks whether it is a valid update path from the current status to the provided new status.
     * 
     * @param int $newstatus The new status to check is valid.
     * @return int|false New permissions ID or false.
     */
    public function is_valid_update_path($newstatus) {
        $statusid = $this->appraisal->appraisal->statusid;
        foreach (self::$updatepaths[$statusid] as $validstatuses) {
            if (array_key_exists($newstatus, $validstatuses)) {
                // Return permissions id.
                return $validstatuses[$newstatus];
            }
        }
        $this->set_error('invalidpath');
        return false;
    }

    /**
     * Checks whether the appraisal can be updated to the passed status.
     *
     * @param int $newstatus
     * @return boolean
     */
    public function can_update_status($newstatus) {
        $appraisal = $this->appraisal->appraisal;
        // This is based on status id not permissions id as is related to who 'owns' the appraisal at this stage.
        if (!permissions::is_allowed('appraisal:update', $appraisal->statusid, $appraisal->viewingas, $appraisal->archived, $appraisal->legacy)) {
            $this->set_error('nopermission');
            return false;
        }
        
        if ($this->is_valid_update_path($newstatus)) {
            return $this->validate($newstatus);
        }

        return false;
    }

    /**
     * Validates required data based on requirements in self::$required.
     * 
     * @param int $newstatus
     * @return boolean
     */
    public function validate($newstatus) {
        $statusid = $this->appraisal->appraisal->statusid;
        if ($newstatus <= $statusid) {
            // Only validate moving forwards.
            return true;
        }
        if (!array_key_exists($statusid, self::$required)) {
            // Moving from this status doesn't require validation.
            return true;
        }

        // Validation needed.
        $result = true;
        foreach (self::$required[$statusid] as $what => $fields) {
            $method = "validate_{$what}";
            if (!method_exists($this, $method)) {
                continue;
            }
            $validated = call_user_func(array($this, $method), $fields);
            $result = $result && $validated;
        }

        return $result;
    }

    /**
     * Validates presence of at least one piece of data in specified fields.
     * 
     * @param boolean $what
     * @return boolean
     */
    private function validate_any($what) {
        $appraisal = $this->appraisal->appraisal;
        foreach ($what as $type => $fields) {
            switch ($type) {
                case 'appraisal' :
                    foreach ($fields as $field) {
                        if (!empty($appraisal->{$field})) {
                            return true;
                        }
                    }
                    break;
                case 'form' :
                    $this->load_form_data('all');
                    foreach ($this->formdata as $form) {
                        foreach ($form->data as $data) {
                            if (!empty($data)) {
                                return true;
                            }
                        }
                    }
                    break;
            }
        }
        // All empty.
        $this->failedvalidation[] = 'all';
        $identifier = "overview:button:{$appraisal->viewingas}:{$appraisal->statusid}:submit";
        $a = get_string($identifier, 'local_onlineappraisal', $appraisal);
        $this->set_error('validation:any', $a);
        return false;
    }

    /**
     * Validates required appraisal data.
     *
     * @param array $fields
     * @return boolean
     */
    private function validate_appraisal($fields) {
        $appraisal = $this->appraisal->appraisal;
        $result = true;
        foreach ($fields as $field) {
            if (empty($appraisal->{$field})) {
                // Required field is empty.
                $result = false;
                $str = "validation:appraisal:{$field}";
                if ($this->error_exists($str)) {
                    $this->set_error($str, 'local_onlineappraisal');
                } else {
                    $a = new stdClass();
                    $a->what = 'appraisal';
                    $a->field = $field;
                    $this->set_error('validation', $a);
                }
                // Appraisal info is all on userinfo page.
                $this->failedvalidation[] = 'userinfo';
            }
        }
        return $result;
    }

    /**
     * Validates required form fields.
     * 
     * @param array $formfields
     * @return boolean
     */
    private function validate_form($formfields) {
        $result = true;
        foreach ($formfields as $form => $fields) {
            if (!isset($this->formdata[$form])) {
                // We haven't yet loaded form data, do it now.
                $this->load_form_data($form);
            }
            foreach ($fields as $field) {
                if (empty($this->formdata[$form]->data[$field])) {
                    // Form field data is empty.
                    $result = false;
                    $fieldstr = "form:{$form}:{$field}";
                    if ($this->strman->string_exists($fieldstr, 'local_onlineappraisal')) {
                        $a = get_string("form:{$form}:{$field}", 'local_onlineappraisal');
                    } else {
                        $a = $fieldstr;
                    }

                    $this->set_error('validation:form', $a);

                    $this->failedvalidation[] = $form;
                }
            }
        }
        return $result;
    }
    
    /**
     * Validates any special requirements.
     *
     * @param array $methods Methods to call to validate.
     * @return boolean
     */
    private function validate_special($methods) {
        $result = true;
        foreach ($methods as $method) {
            if (!method_exists($this, $method)) {
                continue;
            }
            $validated = call_user_func(array($this, $method));
            $result = $result && $validated;
        }

        return $result;
    }

    /**
     * Validates user is groupleader responsible for submitting.
     *
     * @return boolean
     */
    private function validate_groupleader() {
        global $USER;

        if ($this->appraisal->appraisal->groupleader && $this->appraisal->appraisal->groupleader->id === $USER->id) {
            return true;
        }

        $this->set_error('validation:groupleader');
        return false;
    }

    /**
     * Validates groupleader summary field requirements.
     *
     * @return boolean
     */
    private function validate_groupleader_summary() {
        global $USER;
        // No validation required or not for this particular user.
        if (!$this->appraisal->appraisal->groupleader || $this->appraisal->appraisal->groupleader->id != $USER->id) {
            return true;
        }
        $formfields = array(
            'summaries' => array('grpleader')
        );
        return $this->validate_form($formfields);
    }

    /**
     * Loads form id and form data based on passed form name and current appraisal id/appraisee.
     *
     * @global \moodle_database $DB
     * @param string $formname
     */
    private function load_form_data($formname) {
        global $DB;

        $appraisal = $this->appraisal->appraisal;
        
        $params = array(
            'appraisalid' => $appraisal->id,
            'user_id' => $appraisal->appraisee->id,
        );
        if ($formname !== 'all') {
            $params['form_name'] = $formname;
        }
        $forms = $DB->get_records('local_appraisal_forms', $params);
        
        foreach ($forms as $form) {
            $this->formdata[$form->form_name] = new stdClass();
            $this->formdata[$form->form_name]->id = $form->id;
            $this->formdata[$form->form_name]->data = array();

            $formrecords = $DB->get_records('local_appraisal_data', array('form_id' => $form->id));
            // Process stored data.
            foreach ($formrecords as $record) {
                if ($record->type == 'array') {
                    $data = unserialize($record->data);
                } else {
                    $data = $record->data;
                }
                $this->formdata[$form->form_name]->data[$record->name] = $data;
            }
        }
    }

    /**
     * Gets new status, based on passed direction, and passes off to update_status().
     * 
     * @param string $direction 'submit' or 'return'
     * @param string $comment
     * @return boolean
     */
    public function update_direction($direction, $comment = '') {
        if (empty($comment) && $direction == 'return') {
            $this->set_error('comment:required');
            return false;
        }

        $newstatus = $this->get_update_path($direction);
        if (!$newstatus) {
            return false;
        }

        return $this->update_status($newstatus, $comment);
    }

    /**
     * Updates the current appraisal status, updates the permissionsid accordingly and updates the status history.
     *
     * @global moodle_database $DB
     * @param int $newstatus
     * @param string $comment
     * @return boolean
     * @throws moodle_exception
     */
    public function update_status($newstatus, $comment = '') {
        global $DB;

        if (!$this->can_update_status($newstatus)) {
            return false;
        }

        $now = time();

        $appraisal = $this->appraisal->appraisal;

        // Required to determine email/comment.
        $oldstatus = $appraisal->statusid;

        $appraisal->permissionsid = $this->is_valid_update_path($newstatus);
        $appraisal->statusid = $newstatus;
        if (empty($appraisal->status_history)) {
            $appraisal->status_history = $newstatus;
        } else {
            $appraisal->status_history .= '|' . $newstatus;
        }
        if ($appraisal->statusid == APPRAISAL_COMPLETE) {
            $appraisal->completed_date = $now;
        }
        $appraisal->modified_date = $now;

        $updated = $DB->update_record('local_appraisal_appraisal', $appraisal);

        if (!$updated) {
            $this->set_error('updatefailed');
            return false;
        }

        // DB update occurred so we'll need to redirect.
        $this->redirect = true;
        
        $emailssent = $this->send_email($oldstatus, $newstatus, $comment);
        $autocommentadded = $this->add_comment($oldstatus, $newstatus);

        $usercommentadded = true;
        if (!empty($comment)) {
            // Ensure user comment is after system comment.
            sleep(1);
            $usercommentadded = comments::save_comment($appraisal->id, $comment, $this->appraisal->user->id, $appraisal->viewingas);
            if (!$usercommentadded) {
                $this->set_error('commentfailed:user');
            }
        }
        
        return $emailssent && $autocommentadded && $usercommentadded;
    }

    /**
     * Send emails to users.
     * Will silently continue if no email template present for a particular user/status change.
     * 
     * @param int $oldstatus
     * @param int $newstatus
     * @param string $comment
     * @return boolean
     * @throws moodle_exception
     */
    private function send_email($oldstatus, $newstatus, $comment = '') {
        global $USER;
        
        $appraisal = $this->appraisal->appraisal;

        $result = true;

        $emailvars = new stdClass();
        $emailvars->appraiseefirstname = $appraisal->appraisee->firstname;
        $emailvars->appraiseelastname = $appraisal->appraisee->lastname;
        $emailvars->appraiseeemail = $appraisal->appraisee->email;
        $emailvars->appraiserfirstname = $appraisal->appraiser->firstname;
        $emailvars->appraiserlastname = $appraisal->appraiser->lastname;
        $emailvars->appraiseremail = $appraisal->appraiser->email;
        $emailvars->signofffirstname = $appraisal->signoff->firstname;
        $emailvars->signofflastname = $appraisal->signoff->lastname;
        $emailvars->signoffemail = $appraisal->signoff->email;
        $emailvars->groupleaderfirstname = $appraisal->groupleader ? $appraisal->groupleader->firstname : '-';
        $emailvars->groupleaderlastname = $appraisal->groupleader ? $appraisal->groupleader->lastname : '-';
        $emailvars->groupleaderemail = $appraisal->groupleader ? $appraisal->groupleader->email : '-';
        $url = new \moodle_url(
                '/local/onlineappraisal/view.php',
                array('appraisalid' => $appraisal->id, 'view' => 'appraisee', 'page' => 'overview')
                );
        $urldashboard = new \moodle_url(
                '/local/onlineappraisal/index.php',
                array('page' => 'appraisee')
                );
        $emailvars->linkappraisee = $url->out();
        $emailvars->linkappraiseedashboard = $urldashboard->out();
        $url->param('view', 'appraiser');
        $urldashboard->param('page', 'appraiser');
        $emailvars->linkappraiser = $url->out();
        $emailvars->linkappraiserdashboard = $urldashboard->out();
        $url->param('view', 'signoff');
        $urldashboard->param('page', 'signoff');
        $emailvars->linksignoff = $url->out();
        $emailvars->linksignoffdashboard = $urldashboard->out();
        $url->param('view', 'groupleader');
        $urldashboard->param('page', 'groupleader');
        $emailvars->linkgroupleader = $url->out();
        $emailvars->linkgroupleaderdashboard = $urldashboard->out();
        $emailvars->status = get_string("status:{$appraisal->statusid}", 'local_onlineappraisal');
        $emailvars->comment = empty($comment) ? '' : get_string('email:replacement:comment', 'local_onlineappraisal', $comment);

        $recipients = array('appraisee', 'appraiser', 'signoff');
        if ($appraisal->groupleader) {
            $recipients[] = 'groupleader';
        }

        // Cycle through recipients sending emails if applicable email found.
        foreach ($recipients as $type) {
            $stremail = "status:{$oldstatus}_to_{$newstatus}:{$type}";
            $to = $appraisal->{$type};
            // Extra string populated later if groupleader is active and extra string exists.
            if ($appraisal->groupleader) {
                $extrastr = "email:body:{$stremail}:groupleaderextra";
                $extrastrexists = get_string_manager()->string_exists($extrastr, 'local_onlineappraisal');
                $emailvars->groupleaderextra = $extrastrexists ? get_string($extrastr, 'local_onlineappraisal') : '';
            } else {
                $emailvars->groupleaderextra = '';
            }
            try {
                $email = new email($stremail, $emailvars, $to, $USER);
                $email->prepare();
                if (!$email->send()) {
                    $result = false;
                    $this->set_error('emailnotsent', fullname($to));
                }
            } catch (moodle_exception $e) {
                // If error indicates language string doesn't exist there is no email for this user (i.e. a known error).
                if (!($e->errorcode == 'error:invalidemail' && $e->module == 'local_onlineappraisal')) {
                    // Don't know what the error is, so store it.
                    $result = false;
                    $this->set_error($e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Automatically add a comment if string exists.
     *
     * @param int $oldstatus
     * @param int $newstatus
     * @return boolean
     */
    private function add_comment($oldstatus, $newstatus) {
        global $USER;

        $str = "comment:status:{$oldstatus}_to_{$newstatus}";
        if (!$this->strman->string_exists($str, 'local_onlineappraisal')) {
            // No comment for this status change.
            return true;
        }
        $a = new stdClass();
        $a->status = get_string("status:{$newstatus}", 'local_onlineappraisal');
        $a->relateduser = fullname($USER);
        $comment = comments::save_comment(
                $this->appraisal->appraisal->id,
                get_string(
                        $str,
                        'local_onlineappraisal',
                        $a
                        )
                );
        if (!$comment) {
            $this->set_error('commentfailed:auto');
            return false;
        }
        return true;
    }

    /**
     * Wrapper for string_exists to check if error exists.
     * 
     * @param string $identifier
     * @return boolean
     */
    private function error_exists($identifier) {
        $str = "error:stages:{$identifier}";
        return $this->strman->string_exists($str, 'local_onlineappraisal');
    }

    /**
     * Adds an error object to the error array.
     * Identifier is used in generic error message if string not found.
     *
     * @param string $identifier String identifier.
     * @param string|stdClass $a Replacement(s) for error string.
     */
    private function set_error($identifier, $a = '') {
        $error = new stdClass();
        $component = 'local_onlineappraisal';
        if ($this->error_exists($identifier)) {
            $str = "error:stages:{$identifier}";
            $error->message = get_string($str, $component, $a);
        } else {
            $error->message = get_string('error:stages:general', $component, $identifier);
        }
        // These will be set on retrieval.
        $error->first = false;
        $error->last = false;

        $this->errors[] = $error;
    }

    /**
     * Gets array of error objects, setting first and last flags in the process.
     * 
     * @return array
     */
    public function get_errors() {
        // Update first flag.
        $first = array_shift($this->errors);
        if ($first) {
            $first->first = true;
            array_unshift($this->errors, $first);
        }
        // Update last flag.
        $last = array_pop($this->errors);
        if ($last) {
            $last->last = true;
            array_push($this->errors, $last);
        }
        return $this->errors;
    }

    /**
     * Clears array of error objects.
     */
    public function clear_errors() {
        $this->errors = array();
    }
}
