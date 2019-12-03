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
    private $feedbackactions;
    private $emailvars;
    private $viewfeedback;

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
        // No hooks here.
    }

    /**
     * Prepare the feedback request item to be viewed.
     * this will be stored in the class and used when constructing the templates that will be send
     * to the renderer in get_feedback_requests();
     */
    private function viewrequest($request) {
        global $DB;

        if ($this->appraisal->appraisal->viewingas == 'guest') {
            return;
        }

        $viewperm = $this->appraisal->check_permission('feedback:view');
        $viewownperm = $this->appraisal->check_permission('feedbackown:view');

        if (!$viewperm && !$viewownperm){
            return;
        }

        $fb = $DB->get_record('local_appraisal_feedback', ['id' => $request]);
        if (!$fb) {
            return;
        }

        $canview = ($this->appraisal->appraisal->is_appraisee && $fb->confidential == 0) || !$this->appraisal->appraisal->is_appraisee;
        $canviewown = $fb->feedback_user_type != 'appraiser' && $fb->confidential == 0;
        if (($viewperm && $canview) || ($viewownperm && $canviewown)) {
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

                $fbuser->email = $request->email;
                $fbuser->date = userdate($request->created_date, get_string('strftimedate'));
                if (empty($request->received_date)) {
                    $fbuser->incomplete = true;
                    $fbuser->received = false;
                    $fbuser->draft = !empty($request->feedback) || !empty($request->feedback_2);
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
                $return[$request->id] = $fbuser;
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
                $USER->id == $request->requested_by &&
                empty($request->feedback) &&
                empty($request->feedback_2)) {

                // Resend the request.
                $this->feedback_action('editresend', $request->id);
            }
        } else if ($this->appraisal->check_permission('feedback:view')){
            if ($this->appraisal->appraisal->is_appraisee && $request->confidential == 0) {
                $this->feedback_action('view', $request->id);
            } else if ($this->appraisal->appraisal->viewingas != 'guest') {
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
                    array('page' => 'feedback', 'appraisalid' => $this->appraisal->appraisal->id, 'feedbackaction' => $actionstring, 'request' => $requestid, 'view' => $this->appraisal->appraisal->viewingas));

        if ($actionstring == 'editresend') {
            // Special case, need to send user to form.
            $actionurl->remove_params(['feedbackaction', 'request']);
            $actionurl->params([
                'addfeedback' => 1,
                'feedbackid' => $requestid,
            ]);
        }

        $action = new stdClass();
        $action->url = $actionurl->out();
        $action->action = $action;
        $string = 'appraisee_feedback_' . $actionstring . '_text';
        $action->name = get_string($string, 'local_onlineappraisal');
        $this->feedbackactions[] = $action;
    }

    /**
     * Store adding a feedback recipient from the form
     * @param array $data The name => value pair type of data
     */
    public function store_feedback_recipient($data) {
        global $DB;

        // Trim and make it lowercase for consistency.
        $data->email = \core_text::strtolower(trim($data->email));

        if ($data->formid > 0) {
            $params = array(
                'appraisalid' => $this->appraisal->appraisal->id,
                'requested_by' => $this->appraisal->user->id,
                'id' => $data->formid,
            );
            $fb = $DB->get_record('local_appraisal_feedback', $params);
        }

        if (empty($fb)) {
            $fb = new stdClass();
            $fb->lang = $data->language; // Only available/set on creation.
        }
        if (empty($fb->email) || (!empty($fb->email) && $fb->email != $data->email)) {
            $fb->password = $this->get_random_string();

            // Defaults.
            $fb->created_date = time();
            $fb->feedback = null;
            $fb->feedback_2 = null;
            $fb->confidential = 0;
            $fb->received_date = null;
        }
        // Retrieved from appraisal.
        $fb->appraisalid = $this->appraisal->appraisal->id;
        $fb->requested_by = $this->appraisal->user->id;
        $fb->feedback_user_type = $this->appraisal->appraisal->viewingas;

        // Retreived from $data in form.
        $fb->additional_message = !empty($data->emailtext) ? $data->emailtext : ''; // Only available/set on creation.
        $fb->firstname = $data->firstname;
        $fb->lastname = $data->lastname;
        $fb->email = $data->email;

        if ($data->hascustomemail) {
            // Don't user nl2br() as doesn't actually remove line breaks, resulting in extra whitespace.
            $fb->customemail = str_replace(["\r\n", "\r", "\n"], '<br>', $data->customemailmsg);
        }

        $fb->recipient = \local_onlineappraisal\user::get_dummy_appraisal_user($fb->email, $fb->firstname, $fb->lastname);

        // Is the chosen email in use already (ignoring request being edited if applicable).
        $fbexistingselect = 'email = :email AND appraisalid = :appraisalid AND requested_by = :requested_by';
        $fbexistingparams = [
            'email' => $fb->email,
            'appraisalid' => $fb->appraisalid,
            'requested_by' => $fb->requested_by,
        ];
        if (!empty($fb->id)) {
            $fbexistingselect .= ' AND id <> :id';
            $fbexistingparams['id'] = $fb->id;
        }
        $fbexisting = $DB->get_record_select('local_appraisal_feedback', $fbexistingselect, $fbexistingparams);
        if ($fbexisting) {
            $this->appraisal->set_action('email', $fbexisting->id);
            $this->appraisal->failed_action('feedback_inuse');
            return;
        }

        // Get email variables in preparation.
        $emailvars = $this->get_feedback_vars($fb);

        // Update/create feedback.
        if (!empty($fb->id)) {
            $result = $DB->update_record('local_appraisal_feedback', $fb);
        } else {
            // Prep customemail field from default if not already customised.
            if (!$data->hascustomemail) {
                // Load default feedback email message.
                $email = $fb->feedback_user_type == 'appraiser' ? 'appraiserfeedbackmsg' : 'appraiseefeedbackmsg';
                $sender = $fb->feedback_user_type == 'appraiser' ? $this->appraisal->appraisal->appraiser : $this->appraisal->appraisal->appraisee;
                $baseemail = new email($email, $emailvars, $fb->recipient, $sender, array(), $fb->lang);
                $baseemail->prepare();
                $fb->customemail = $baseemail->body;
            }

            $result = $fb->id = $DB->insert_record('local_appraisal_feedback', $fb);
        }

        // If OK, email contributor.
        if ($result) {
            $this->appraisal->set_action('email', $fb->id);

            $emailvars->emailmsg = $fb->customemail;

            if ($fb->feedback_user_type == 'appraiser') {
                $feedbackmail = new email('appraiserfeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraiser, array(), $fb->lang);
            } else {
                $feedbackmail = new email('appraiseefeedback', $emailvars, $fb->recipient, $this->appraisal->appraisal->appraisee, array(), $fb->lang);
            }

            $feedbackmail->prepare();
            $feedbackmail->send();
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
     * Add user feedback to the table. Called from the addfeedback form.
     */
    public function user_feedback($data) {
        global $DB;

        $submitted = false;
        $draft = false;

        if ($data->buttonclicked == 1) {
            $submitted = true;
            $this->appraisal->set_action('userfeedback', $data->feedbackid);
        } else {
            // Default to saving as draft.
            $draft = true;
            $this->appraisal->set_action('savedraft', $data->feedbackid);
        }

        // Get this feedback.
        $fb = $DB->get_record('local_appraisal_feedback', array('id' => $data->feedbackid, 'password' => $data->pw));
        if (!$fb) {
            $this->appraisal->failed_action('feedback');
            return false;
        }

        $fb->feedback = $data->feedback;
        $fb->feedback_2 = $data->feedback_2;
        // $fb->confidential = $data->confidential;

        if ($submitted) {
            $fb->received_date = time();
        }

        // Add this to the current record.
        if (!$DB->update_record('local_appraisal_feedback', $fb)) {
            $this->appraisal->failed_action('feedback');
            return false;
        }

        if ($submitted) {
            // trigger completed event.
            $event = \local_onlineappraisal\event\feedback_completed::create(array('objectid' => $fb->id));
            $event->trigger();
        }
        if ($submitted || $draft) {
            $this->appraisal->complete_action('feedback');
        }

        return true;
    }

    /**
     * Get the Feedback request data to be used in the Feedback requests pages.
     * The template date returned is used by the feedback_requests render and template
     * @return object $template.
     */
    public function request_data() {
        global $DB, $USER;

        $template = new stdClass();
        $template->filter = $DB->get_records_select_menu(
                'local_appraisal_cohorts',
                'availablefrom < :now',
                ['now' => time()],
                'availablefrom DESC',
                'id, name');

        // Do a case insensitive comparison.
        $like = $DB->sql_like('af.email', ':email', false);

        // The global Join used by the 2 queries.
        $join = "SELECT af.*,
                        aa.held_date, aa.face_to_face_held, aa.permissionsid, aa.archived, aa.legacy,
                        u.firstname as ufirstname, u.lastname as ulastname, u.id as appraiseeid,
                        lac.id as cohortid, lac.name as cohortname, lac.availablefrom as cohortavailablefrom
                   FROM {local_appraisal_feedback} af
                   JOIN {local_appraisal_appraisal} aa
                     ON aa.id = af.appraisalid
                   JOIN {local_appraisal_cohort_apps} laca ON aa.id = laca.appraisalid
                   JOIN {local_appraisal_cohorts} lac ON lac.id = laca.cohortid
                   JOIN {user} u
                     ON u.id = aa.appraisee_userid
                  WHERE {$like}
                    AND aa.deleted = 0";

        // Get the outstanding Feedback requests from the DB.
        $outstanding = "{$join}
                    AND aa.archived = 0
                    AND (af.received_date IS NULL OR af.received_date = 0)
               ORDER BY lac.availablefrom ASC, aa.held_date ASC";

        $outstandingrecords = $DB->get_records_sql($outstanding, array('email' => $USER->email));

        foreach ($outstandingrecords as $or) {
            if (!\local_onlineappraisal\permissions::is_allowed('feedback:submit', $or->permissionsid, 'guest', $or->archived, $or->legacy)) {
                continue;
            }
            $or->requested = $this->get_requestedby($or);
            $or->feedbacklink = new moodle_url('/local/onlineappraisal/add_feedback.php',
                array('id' => $or->appraisalid, 'pw' => $or->password));
            $or->continuefeedback = !empty($or->feedback) || !empty($or->feedback_2)? true : false;
            $this->request_userdates($or);
            $template->outstanding[] = $or;
        }

        // Get the completed Feedback feedback requests from the DB.
        $completed = "{$join}
                    AND af.received_date > 0
               ORDER BY lac.availablefrom DESC, af.received_date DESC";

        $completedrecords = $DB->get_records_sql($completed, array('email' => $USER->email));

        $template->filterselected = optional_param('filter', key($template->filter), PARAM_INT);
        $cohortcount = array_fill_keys(array_keys($template->filter), 0);
        foreach ($completedrecords as $cr) {
            $cohortcount[$cr->cohortid]++;
            if ($cr->cohortid != $template->filterselected) {
                continue;
            }
            $cr->feedbacklink = new moodle_url('/local/onlineappraisal/feedback_requests.php',
                array('id' => $cr->id, 'action' => 'resend'));
            $this->request_userdates($cr);
            $cr->requested = $this->get_requestedby($cr);
            $template->completed[] = $cr;
        }

        // Add count to filters.
        foreach ($template->filter as $key => $value) {
            $template->filter[$key] = $value . " ({$cohortcount[$key]})";
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
