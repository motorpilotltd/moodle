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
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;

class email {

    private $emailvars;
    private $to;
    private $from;
    private $cc;
    private $subject;
    public $body;
    private $lang = 'en';

    /**
     * Constructor
     * 
     * @param string|stdClass $email
     * @param stdClass $emailvars
     * @param stdClass $to
     * @param stdClass $from
     * @param array $cc
     * @throws Exception
     */
    public function __construct($email, $emailvars, $to, $from, $cc = array(), $lang = null) {
        if (is_string($email)) {
            $strman = get_string_manager();
            $subjectexists = $strman->string_exists('email:subject:'.$email, 'local_onlineappraisal');
            $bodyexists = $strman->string_exists('email:body:'.$email, 'local_onlineappraisal');

            if (!$subjectexists || !$bodyexists) {
                throw new moodle_exception('error:invalidemail', 'local_onlineappraisal');
            }

            $this->subject = $strman->get_string('email:subject:'.$email, 'local_onlineappraisal', null, $lang);
            $this->body = $strman->get_string('email:body:'.$email, 'local_onlineappraisal', null, $lang);

            if (!is_null($lang)) {
                $basebody = $strman->get_string('email:body:'.$email, 'local_onlineappraisal', null, 'en');
                $this->lang = ($this->body == $basebody) ? 'en' : $lang;
            }
        } else if (!empty($email->subject) && !empty($email->body)) {
            $this->subject = $email->subject;
            $this->body = $email->body;
        } else {
            throw new moodle_exception('error:invalidemaildata', 'local_onlineappraisal');
        }

        $this->emailvars = $emailvars;
        $this->to = $to;
        $this->to->mailformat = 1;
        $this->from = $from;
        $this->from->maildisplay = true;
        $this->cc = $cc;
    }

    /**
     * Prepare subject/body by replacing placeholder patterns.
     */
    public function prepare() {
        // Patterns like {{name}} are allowed in the language file. These are auto-replaced
        // by this bit of code.
        $pattern = '|{{(.*)}}|Ums';
        $this->subject = preg_replace_callback($pattern, array($this, 'replace_emailvars'), $this->subject);
        $this->body = preg_replace_callback($pattern, array($this, 'replace_emailvars'), $this->body);
    }

    /**
     * Placeholder replacement callback.
     * 
     * @param array $matches
     * @return string
     */
    private function replace_emailvars($matches) {
        $var = $matches[1];
        if (!empty($this->emailvars->$var)) {
            return $this->emailvars->$var;
        } else {
            return '';
        }
    }

    /**
     * Send the email.
     * 
     * @return bool
     */
    public function send() {
        return email_to_user(
                $this->to,
                $this->from,
                $this->subject,
                html_to_text($this->body),
                $this->body,
                '', '', true, '', '', 79, // Standard parameters.
                $this->cc
                );
    }

    /**
     * Set/update an email variable.
     * 
     * @param string $name
     * @param string $value
     */
    public function set_emailvar($name, $value) {
        $this->emailvars->{$name} = $value;
    }

    /**
     * Return the language used for the email (body).
     *
     * @return string
     */
    public function used_language() {
        return $this->lang;
    }
}