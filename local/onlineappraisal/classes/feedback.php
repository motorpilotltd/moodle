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

use stdClass;
use moodle_url;
use Exception;
use local_onlineappraisal\email as email;

class feedback {

    private $appraisal;
    private $called;
    private $feedbackactions;
    private $emailvars;
    private $viewfeedback;
    private $count;
    private $counta;

    /**
     * Constructor.
     *
     * @param object $appraisal the full appraisal object
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal = null) {
        $this->appraisal = $appraisal;
    }

    /**
     * Hook
     *
     * This function is called from the main appraisal controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_onlineappraisal\appraisal->add_page();
     */
    public function hook() {
        global $USER, $DB;
        //Process the actions in the feedback table
        $feedbackaction = optional_param('feedbackaction', '', PARAM_RAW);
        $request = optional_param('request', 0, PARAM_INT);
        $replaceaddress = optional_param('replaceaddress', '', PARAM_RAW);

        $requestrecord = $DB->get_record('local_appraisal_feedback', array('id' => $request));

        if ($feedbackaction && $request) {
            $this->appraisal->set_action($feedbackaction, $request);

            // Edit action
            if ($requestrecord->requested_by == $USER->id) {
                if ($this->appraisal->called->action == 'edit' && $replaceaddress) {
                    $this->replaceaddress($replaceaddress);
                }
                // Resend email action
                if ($this->appraisal->called->action == 'resend') {
                    $this->resendrequest($request);
                }
            }
        }

        // Redirect following processing (to avoid refresh resubmissions).
        if (!empty($this->appraisal->called->done)) {
            $redirecturl = new moodle_url(
                    '/local/onlineappraisal/view.php',
                    array(
                        'page' => 'feedback',
                        'appraisalid' => $this->appraisal->appraisal->id,
                        'view' => $this->appraisal->appraisal->viewingas
                    ));
            redirect($redirecturl);
        }
    }

    /**
     * Replace the email address in a feedback request.
     */
    private function replaceaddress($replaceaddress) {
        global $DB, $USER;
        // Make it lowercase for consistency.
        $replaceaddress = \core_text::strtolower($replaceaddress);
        // Check if the user can still add users. Just to be sure.
        if (!$this->appraisal->check_permission('feedback:add')) {
            return false;
        }
        if (!validate_email($replaceaddress)) {
            $this->appraisal->failed_action('feedback_invalid');
            return false;
        }
        if ($DB->get_records('local_appraisal_feedback', array('email' => $replaceaddress, 'requested_by' => $USER->id))) {
            $this->appraisal->failed_action('feedback_inuse');
            return false;
        }
        $fb = $DB->get_record('local_appraisal_feedback', array('id' => $this->appraisal->called->actionid));
        if ($fb) {
            $fb->email = $replaceaddress;
            $fb->password = $this->get_random_string();
            $DB->update_record('local_appraisal_feedback', $fb);
            $this->resendrequest($this->appraisal->called->actionid);
            $this->appraisal->complete_action('feedback');
        } else {
            $this->appraisal->failed_action('feedback');
        }
    }

    /**
     * Prepare the feedback request item to be viewed.
     * this will be stored in the class and used when constructing the templates that will be send
     * to the renderer in get_feedback_requests();
     */
    private function viewrequest($request) {
        global $DB;
        if ($fb = $DB->get_record('local_appraisal_feedback', array('id' => $request))) {
            $this->viewfeedback = $fb;
        }
    }

    /**
     * Get the list of feedback request.
     * If a feedbackaction and request is send along this allows to view a request.
     */
    public function get_feedback_requests() {
        global $DB;

        $feedbackaction = optional_param('feedbackaction', '', PARAM_RAW);
        $request = optional_param('request', 0, PARAM_INT);

        if (($feedbackaction == 'view' || $feedbackaction == 'viewrequest') && $request) {
            $this->viewrequest($request);
        }
        $return = array();

        if ($requests = $DB->get_records('local_appraisal_feedback',
            array('appraisalid' => $this->appraisal->appraisal->id))) {

            foreach ($requests as $request) {
                $fbuser = new stdClass();
                $fbuser->name = $request->firstname . ' ' . $request->lastname;
                // I don't know why this user type requires an asterix after their name
                // I simply copied this from the logic on the previous version.
                if ($request->feedback_user_type == 'appraiser') {
                    $fbuser->name .= ' *';
                }

                if ($action = $this->appraisal->check_action($request->id, 'edit')) {

                    $fbuser->formurl = new moodle_url('/local/onlineappraisal/view.php',
                    array('page' => 'feedback', 'appraisalid' => $this->appraisal->appraisal->id,
                        'feedbackaction' => 'edit', 'request' => $request->id, 'view' => $this->appraisal->appraisal->viewingas));
                    $fbuser->emailform = $request->id;
                    $fbuser->emailplaceholder = $request->email;
                } else {
                    $fbuser->email = $request->email;
                }
                $fbuser->date = userdate($request->created_date, get_string('strftimedate'));
                if (empty($request->received_date)) {
                    $fbuser->incomplete = true;
                    $fbuser->received = false;
                } else {
                    $fbuser->incomplete = false;
                    $fbuser->received = userdate($request->received_date, get_string('strftimedate'));
                }
                $fbuser->confidential = $request->confidential;
                $fbuser->actions = $this->appraisee_actions($request);

                $fbuser->hasactions = (count($this->feedbackactions) > 0);
                
                if (isset($this->viewfeedback->id) && $request->id == $this->viewfeedback->id) {
                    if ($feedbackaction == 'view') {
                        $fbuser->viewfeedback = format_text($this->viewfeedback->feedback, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                        $fbuser->viewfeedback_2 = format_text($this->viewfeedback->feedback_2, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));;
                    }
                    if ($feedbackaction == 'viewrequest') {
                        $fbuser->customemail = $this->viewfeedback->customemail;
                    }
                    $fbuser->closeurl = new moodle_url('/local/onlineappraisal/view.php',
                        array('page' => 'feedback', 'view' => $this->appraisal->appraisal->viewingas,
                            'appraisalid' => $this->appraisal->appraisal->id));
                }
                $return[] = $fbuser;
            }
        }

        return $return;
    }

    /**
     * Feedback actions
     */
    private function appraisee_actions($request) {
        global $USER;
        $this->feedbackactions = array();

        if (empty($request->received_date)) {

            if (!$this->appraisal->check_permission('feedback:add')) {
                return array();
            }

            if ($this->appraisal->appraisal->archived == 0 &&
                $this->appraisal->appraisal->deleted == 0 &&
                !empty($this->appraisal->appraisal->held_date) &&
                $USER->id == $request->requested_by) {

                // Resend the request.
                $this->feedback_action('resend', $request->id);
                $this->feedback_action('edit', $request->id);
            }
        } else if ($this->appraisal->check_permission('feedback:view')){
            if ($this->appraisal->appraisal->is_appraiser) {
                $this->feedback_action('view', $request->id);
            } else if ($this->appraisal->appraisal->is_appraisee && $request->confidential == 0) {
                $this->feedback_action('view', $request->id);
            }
        } else if ($this->appraisal->check_permission('feedbackown:view') &&
            $request->feedback_user_type != 'appraiser' &&
            $request->confidential == 0) {
            $this->feedback_action('view', $request->id);
        }
        
        if (($USER->id == $request->requested_by) && $request->customemail && ($this->appraisal->check_permission('feedbackown:view') || $this->appraisal->check_permission('feedback:view'))) {
            $this->feedback_action('viewrequest', $request->id);
        }

        return $this->feedbackactions;

    }

    /**
     * Create an action object that can be rendered on the Feedback requests table
     *
     * @param string $action
     * @return object $feedbackaction.
     */
    private function feedback_action($actionstring, $requestid) {
        // Don't add the action currently being called
        if ($this->appraisal->check_action($requestid, $actionstring)) {
            return new stdClass();
        }
        // The base URL for all actions
        $actionurl = new moodle_url('/local/onlineappraisal/view.php',
                    array('page' => 'feedback', 'appraisalid' => $this->appraisal->appraisal->id, 'feedbackaction' => '', 'request' => $requestid, 'view' => $this->appraisal->appraisal->viewingas));

        $action = new stdClass();
        $actionurl->param('feedbackaction', $actionstring);
        $action->url = $actionurl->out();
        $action->action = $action;
        $string = 'appraisee_feedback_' . $actionstring . '_text';
        $action->name = get_string($string, 'local_onlineappraisal');
        $this->feedbackactions[] = $action;
    }

    /**
     * Store adding a feedback recipient from the form
     * @param array $data. The name => value pair type of data
     */
    public function store_feedback_recipient($data) {
        global $DB, $USER;

        $fb = new stdClass();
        // Retrieved from appraisal.
        $fb->appraisalid = $this->appraisal->appraisal->id;
        $fb->requested_by = $this->appraisal->user->id;
        $fb->feedback_user_type = $this->appraisal->appraisal->viewingas;

        // Retreived from $data in form.
        $fb->additional_message = $data->emailtext;
        $fb->firstname = $data->firstname;
        $fb->lastname = $data->lastname;
        // Make it lowercase for consistency.
        $fb->email = \core_text::strtolower($data->email);
        $fb->lang = $data->language;

        // Defaults.
        $fb->created_date = time();
        $fb->feedback = null;
        $fb->feedback_2 = null;
        $fb->confidential = 0;
        $fb->received_date = null;
        $fb->password = $this->get_random_string();
        if ($data->hascustomemail) {
            $fb->customemail = nl2br($data->customemailmsg);
        }

        $fb->recipient = \local_onlineappraisal\user::get_dummy_appraisal_user($fb->email, $fb->firstname, $fb->lastname);

        // Feedback created, email contributor.
        if ($fbexisting = $DB->get_record('local_appraisal_feedback',
            array('email' => $fb->email, 'appraisalid' => $fb->appraisalid, 'requested_by' => $USER->id))) {
            $this->appraisal->set_action('email', $fbexisting->id);
            $this->appraisal->failed_action('feedback_inuse');
        } else if ($fb->id = $DB->insert_record('local_appraisal_feedback', $fb)) {
            $this->appraisal->set_action('email', $fb->id);
            $emailvars = $this->get_feedback_vars($fb);
            
            if (!$data->hascustomemail) {
                // Load default feedback email message.
                $email = $fb->feedback_user_type == 'appraiser' ? 'appraiserfeedbackmsg' : 'appraiseefeedbackmsg';
                $sender = $fb->feedback_user_type == 'appraiser' ? $this->appraisal->appraisal->appraiser : $this->appraisal->appraisal->appraisee;
                $baseemail = new email($email, $emailvars, $fb->recipient, $sender, array(), $fb->lang);
                $baseemail->prepare();
                $fb->customemail = $baseemail->body;
            }

            $emailvars->emailmsg = $fb->customemail;

            if ($fb->feedback_user_type == 'appraiser') {
                $feedbackmail = new email('appraiserfeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraiser, array(), $fb->lang);
            } else {
                $feedbackmail = new email('appraiseefeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraisee, array(), $fb->lang);
            }

            $feedbackmail->prepare();
            $feedbackmail->send();

            $DB->update_record('local_appraisal_feedback', $fb);
        }
    }

    public function get_feedback_vars($fb) {
        $this->emailvars = new stdClass();
        $this->emailvars->firstname = $fb->recipient->firstname;
        $this->emailvars->lastname = $fb->recipient->lastname;
        $this->emailvars->appraisee_fullname = fullname($this->appraisal->appraisal->appraisee);
        $this->emailvars->appraiser_fullname = fullname($this->appraisal->appraisal->appraiser);
        $this->emailvars->held_date = self::userdate($fb->lang, $this->appraisal->appraisal->held_date, 'strftimedate', '', new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        $this->emailvars->emailtext = !empty($fb->additional_message) ? format_text($fb->additional_message, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true)) : get_string('feedback_comments_none', 'local_onlineappraisal');
        $url = new moodle_url('/local/onlineappraisal/add_feedback.php', array('id' => $this->appraisal->appraisal->id, 'pw' => $fb->password));
        $this->emailvars->link = \html_writer::link($url, get_string_manager()->get_string('email:body:appraiseefeedback_link_here', 'local_onlineappraisal', null, $fb->lang));
        $this->emailvars->linkurl = $url->out();
        return $this->emailvars;
    }


    /**
     * Resend the email requesting feedback
     * @param int $requestid. DB id of previously created request
     */
    private function resendrequest($requestid) {
        global $DB;

        if ($fb = $DB->get_record('local_appraisal_feedback', array('id' => $requestid))) {

            $fb->recipient = \local_onlineappraisal\user::get_dummy_appraisal_user($fb->email, $fb->firstname, $fb->lastname);
            $emailvars = $this->get_feedback_vars($fb);

            if (!$fb->customemail) {
                // Load default feedback email message.
                $email = $fb->feedback_user_type == 'appraiser' ? 'appraiserfeedbackmsg' : 'appraiseefeedbackmsg';
                $sender = $fb->feedback_user_type == 'appraiser' ? $this->appraisal->appraisal->appraiser : $this->appraisal->appraisal->appraisee;
                $baseemail = new email($email, $emailvars, $fb->recipient, $sender, array(), $fb->lang);
                $baseemail->prepare();
                $fb->customemail = $baseemail->body;
            }

            $emailvars->emailmsg = $fb->customemail;

            if ($fb->feedback_user_type == 'appraiser') {
                $feedbackmail = new email('appraiserfeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraiser, array(), $fb->lang);
            } else {
                $feedbackmail = new email('appraiseefeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraisee, array(), $fb->lang);
            }

            $feedbackmail->prepare();

            if ($feedbackmail->send()) {
                $DB->set_field('local_appraisal_feedback','created_date', time(), array('id' => $requestid));
                $this->appraisal->complete_action('feedback');
            }
        } else {
            $this->appraisal->failed_action('feedback');
        }
    }

    /**
     * Add user feedback to the table. Called from the addfeedback form.
     */
    public function user_feedback($data) {
        global $DB;

        if ($data->buttonclicked == 1) {
            $submitted = true;
            $this->appraisal->set_action('userfeedback', $data->feedbackid);
        } else if ($data->buttonclicked == 2 ) {
            $draft = true;
            $submitted = false;
            $this->appraisal->set_action('savedraft', $data->feedbackid);
        } else {
            $this->appraisal->set_action('userfeedback', $data->feedbackid);
            $this->appraisal->failed_action('feedback');
        }

        // Get this feedback.
        if ($fb = $DB->get_record('local_appraisal_feedback', array('id' => $data->feedbackid, 'password' => $data->pw))) {

            $fb->feedback = $data->feedback;
            $fb->feedback_2 = $data->feedback_2;
            // $fb->confidential = $data->confidential;

            if ($submitted) {
                $fb->received_date = time();
            }

            // Add this to the current record.
            if ($DB->update_record('local_appraisal_feedback', $fb)) {

                if ($submitted) {
                    // trigger completed event.
                    $event = \local_onlineappraisal\event\feedback_completed::create(array('objectid' => $fb->id));
                    $event->trigger();
                }
                if ($submitted || $draft) {
                    $this->appraisal->complete_action('feedback');
                }
            }
        }
    }

    /**
     * Get the Feedback request data to be used in the Feedback requests pages.
     * The template date returned is used by the feedback_requests render and template
     * @return object $template.
     */
    public function request_data() {
        global $DB, $USER;

        $template = new stdClass();

        // Do a case insensitive comparison.
        $like = $DB->sql_like('af.email', ':email', false);

        // The global Join used by the 2 queries.
        $join = "SELECT af.*, aa.held_date, aa.face_to_face_held, aa.permissionsid, aa.archived, aa.legacy, u.firstname as ufirstname, u.lastname as ulastname, u.id as appraiseeid
                   FROM {local_appraisal_feedback} af
                   JOIN {local_appraisal_appraisal} aa
                     ON aa.id = af.appraisalid
                   JOIN {user} u
                     ON u.id = aa.appraisee_userid
                  WHERE {$like}
                    AND aa.archived = 0
                    AND aa.deleted = 0";

        // Get the outstanding Feedback requests from the DB.
        $outstanding = "{$join}
                    AND (received_date IS NULL OR received_date = 0)
               ORDER BY held_date DESC";

        $outstandingrecords = $DB->get_records_sql($outstanding, array('email' => $USER->email));        

        foreach ($outstandingrecords as $or) {
            if (!\local_onlineappraisal\permissions::is_allowed('feedback:submit', $or->permissionsid, 'guest', $or->archived, $or->legacy)) {
                continue;
            }
            $or->requested = $this->get_requestedby($or);
            $or->feedbacklink = new moodle_url('/local/onlineappraisal/add_feedback.php',
                array('id' => $or->appraisalid, 'pw' => $or->password));
            $this->request_userdates($or);
            $template->outstanding[] = $or;
        }

        // Get the completed Feedback feedback requests from the DB.
        $completed = "{$join}
                    AND (received_date IS NOT NULL OR received_date > 0)
               ORDER BY received_date DESC";

        $completedrecords = $DB->get_records_sql($completed, array('email' => $USER->email));

        foreach ($completedrecords as $cr) {
            $cr->feedbacklink = new moodle_url('/local/onlineappraisal/feedback_requests.php',
                array('id' => $cr->id, 'action' => 'resend'));
            $this->request_userdates($cr);
            $cr->requested = $this->get_requestedby($cr);
            $template->completed[] = $cr;
        }

        return $template;
    }

    /**
     * Get the name of the user requesting this feedback.
     * @param object $record feedback request record;
     * @return string $appraisername
     */
    private function get_requestedby($record) {
        global $DB;
        if ($record->appraiseeid != $record->requested_by) {
            $appraiser = $DB->get_record('user', array('id' => $record->requested_by));
            $requested = fullname($appraiser);
        } else {
            $requested = $record->ufirstname . ' ' . $record->ulastname;
        }
        return $requested;
    }

    /**
     * Helper function to turn timestamps into human readable dates.
     * @param object $request. DB object containing feedback request data
     */
    private function request_userdates(&$request) {
        $dates = array('feedbackcreated' => 'created_date',
            'facetofacedate' => 'held_date',
            'completeddate' => 'received_date'
            );

        foreach ($dates as $templatedate => $dbdate) {
            if (!empty($request->$dbdate)) {
                $request->$templatedate = userdate($request->$dbdate, get_string('strftimedate'));
            } else {
                $request->$templatedate = '-';
            }
        }
    }

    /**
     * Actions coming form the feedback request page.
     */
    public function request_action($action, $requestid) {
        global $DB, $PAGE, $USER;
        if ($action == 'resend') {
            if ($fb = $DB->get_record('local_appraisal_feedback', array('id' => $requestid))) {
                // Make both lowercase for comparison just in case (legacy data).
                if (\core_text::strtolower($fb->email) === \core_text::strtolower($USER->email)) {
                    // Set the appraisal
                    $appuser = $DB->get_record('user', array('id' => $fb->requested_by));
                    
                    $emailvars = new stdClass();
                    $emailvars->recipient = fullname($USER);
                    $emailvars->appraisee = fullname($appuser);
                    $emailvars->confidential = $fb->confidential ?
                        get_string('feedbackrequests:confidential', 'local_onlineappraisal') :
                        get_string('feedbackrequests:nonconfidential', 'local_onlineappraisal');
                    $emailvars->feedback = format_text($fb->feedback, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                    $emailvars->feedback_2 = format_text($fb->feedback_2, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));

                    $to = \local_onlineappraisal\user::get_dummy_appraisal_user($fb->email, $fb->firstname, $fb->lastname);

                    $feedbackemail = new \local_onlineappraisal\email('myfeedback', $emailvars, $to, $USER);
                    $feedbackemail->prepare();
                    $feedbackemail->send();
                }
            }
            // Avoid attempts to resend again on refresh (e.g. changing language).
            redirect($PAGE->url);
        }
    }

    /**
     *
     */
    public static function get_placeholders() {
        global $USER;
        $appraisalid = optional_param('appraisalid', 0, PARAM_INT);
        $view = optional_param('view', 0, PARAM_TEXT);
        $lang = optional_param('fblang', current_language(), PARAM_ALPHANUMEXT); // Avoid 'lang' as param as can trigger Moodle language change.
        $oa = new \local_onlineappraisal\appraisal($USER, $appraisalid, $view, 'feedback', 0);
        $result = new stdClass();
        $result->success = 'success';
        $result->message = 'message' . $appraisalid;
        $result->data = new stdClass();
        $result->data->held_date = self::userdate($lang, $oa->appraisal->held_date, 'strftimedate', '', new \DateTimeZone('UTC'));  // Always UTC (from datepicker).
        $result->data->appraisee_fullname = fullname($oa->appraisal->appraisee);
        $result->data->appraiser_fullname = fullname($oa->appraisal->appraiser);
        return $result;
    }

    private function get_random_string() {
        global $DB;
        $randomstring = random_string(32);
        // Make sure random string is unique (just in case we get two of the same for one appraisal).
        while ($DB->get_record('local_appraisal_feedback', array('password' => $randomstring), 'id')) {
            // Get another.
            $randomstring = random_string(32);
        }
        return $randomstring;
    }

    /**
     * Generate the user navigation menu structure.
     *
     * @return stdClass navigation.
     */
    public function get_navigation() {
        $index = new \local_onlineappraisal\index('contributor');

        return $index->get_navigation();
    }

    /**
     * Wrapper for userdate() to work with language overrides.
     *
     * @param string $lang Language identifier.
     * @param int $date Timestamp.
     * @param string $format Format string or language string identifier.
     * @param null|string $component Null ($format is format string) or language string component.
     * @param int|float|string $timezone by default, uses the user's time zone. if numeric and
     *        not 99 then daylight saving will not be added.
     *        {@link http://docs.moodle.org/dev/Time_API#Timezone}
     * @param bool $fixday If true (default) then the leading zero from %d is removed.
     *        If false then the leading zero is maintained.
     * @param bool $fixhour If true (default) then the leading zero from %I is removed.
     * @return string the formatted date/time.
     */
    private static function userdate($lang,  $date, $format = '', $component = null, $timezone = 99, $fixday = true, $fixhour = true) {
        global $SESSION;

        // Force to requested language.
        force_current_language($lang);
        
        if (!is_null($component)) {
            $format = get_string($format, $component);
        }
        $formatteddate = userdate($date, $format, $timezone, $fixday, $fixhour);

        // Restore language.
        unset($SESSION->forcelang);
        moodle_setlocale();

        return $formatteddate;
    }
}
