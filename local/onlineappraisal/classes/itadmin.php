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
use Exception;
use moodle_exception;
use moodle_url;
use local_costcentre\costcentre as costcentre;
use local_onlineappraisal\output\alert as alert;
use local_onlineappraisal\comments as comments;

class itadmin {

    public $pagetitle;
    public $pageheading;
    public $pages;
    public $search;

    private $baseurl = '/local/onlineappraisal/itadmin.php';
    private $user;
    private $action;
    public $page;
    public $appraisals = array();
    public $deletefeedbackform;
    public $statusconfirmform;

    private $searchform;
    private $statusform;
    private $renderer;

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param string page the name of the page
     * @param int $groupid the id of the group
     */
    public function __construct($page, $search) {
        global $PAGE, $USER;

        $this->user = $USER;
        $this->page = $page;
        $this->search = $search;

        // Check if this user is allowed to view admin.
        if (!$this->can_view_admin()) {
            print_error('error:noaccess', 'local_onlineappraisal');
        }

        // Execute actions
        $this->action = optional_param('itadminaction', '', PARAM_TEXT);
        $this->itadmin_actions();

        // Set up pages for navigation.
        $this->admin_pages();

        // Finally set up renderer.
        $this->renderer = $PAGE->get_renderer('local_onlineappraisal', 'itadmin');
    }

    /**
     * Itadmin actions.
     */
    private function itadmin_actions() {
        global $DB, $USER, $SESSION;
        if ($this->action == 'removefeedback') {
            $requestid = required_param('requestid', PARAM_INT);
            $appraisalid = required_param('appraisalid', PARAM_INT);
            if ($feedback = $DB->get_record('local_appraisal_feedback', array('id' => $requestid))) {
                $customdata = array('search' => $this->search,
                    'requestid' => $requestid,
                    'appraisalid' => $appraisalid,
                    'sender' => $feedback->firstname . ' ' . $feedback->lastname
                    );
                $deletefeedbackform = new \local_onlineappraisal\form\itadmin_deletefeedback(
                        null, $customdata, 'post', '', array('class' => 'itadmin_group clearfix')
                    );
                
                if ($deletefeedbackform->is_submitted() && ($data = $deletefeedbackform->get_data())) {
                    if ($data->reason) {
                        $a = new stdClass();
                        $a->itadmin = fullname($USER);
                        $a->sender = $feedback->firstname . ' ' . $feedback->lastname;
                        $a->reason = $data->reason;
                        comments::save_comment($appraisalid, get_string('comment:removed:feedback', 'local_onlineappraisal', $a));
                        $DB->delete_records('local_appraisal_feedback', array('id' => $feedback->id));

                        if (empty($SESSION->local_onlineappraisal)) {
                            $SESSION->local_onlineappraisal = new stdClass();
                        }
                        $SESSION->local_onlineappraisal->alert = new stdClass();
                        $SESSION->local_onlineappraisal->alert->type = 'success';
                        $SESSION->local_onlineappraisal->alert->message = get_string('itadmin:feedbackdeleted', 'local_onlineappraisal');
                        $SESSION->local_onlineappraisal->alert->button = true;
                        $params = array(
                            'search' => $this->search,
                            'appraisalid' => $appraisalid);
                        $redirect = new moodle_url($this->baseurl, $params);
                        $redirect->set_anchor('changestatus');
                        redirect($redirect);
                    }
                } else {
                    $this->deletefeedbackform = $deletefeedbackform->render();
                }
            }
        }
        if ($this->action == 'changestatus') {
            $appraisalid = required_param('appraisalid', PARAM_INT);
            $statusid = required_param('newstatusid', PARAM_INT);
            $appraisal = $DB->get_record('local_appraisal_appraisal', array('id' => $appraisalid));
            $customdata = array('search' => $this->search,
                    'newstatusid' => $statusid,
                    'appraisalid' => $appraisal->id,
                    'statusconfirm' => true
                    );

            // Load the status form showing the reason box.
            $statusconfirm = new \local_onlineappraisal\form\itadmin_status(
                null, $customdata, 'post', '', array('class' => 'clearfix')
            );

            if ($statusconfirm->is_submitted() && ($data = $statusconfirm->get_data())) {
                if ($data->reason) {
                    // Add comment for audit trail.
                    $a = new stdClass();
                    $a->itadmin = fullname($USER);
                    $a->status = get_string('status:'.$data->newstatusid, 'local_onlineappraisal');
                    $a->reason = $data->reason;
                    comments::save_comment($appraisal->id, get_string('comment:status:change', 'local_onlineappraisal', $a));

                    // New appraisal_appraisal values
                    $appraisal->statusid = $data->newstatusid;
                    $appraisal->permissionsid = $data->newstatusid;
                    $appraisal->status_history .= '|' . $data->newstatusid;
                    if ($DB->update_record('local_appraisal_appraisal', $appraisal)) {
                        // Set up flash alert in session.
                        if (empty($SESSION->local_onlineappraisal)) {
                            $SESSION->local_onlineappraisal = new stdClass();
                        }
                        $SESSION->local_onlineappraisal->alert = new stdClass();
                        $SESSION->local_onlineappraisal->alert->type = 'success';
                        $SESSION->local_onlineappraisal->alert->message = get_string('itadmin:updatesuccess', 'local_onlineappraisal');
                        $SESSION->local_onlineappraisal->alert->button = true;
                        $params = array(
                            'search' => $this->search,
                            'appraisalid' => $appraisal->id);
                        $newstatusurl = new moodle_url($this->baseurl, $params);
                        $newstatusurl->set_anchor('changestatus');
                        redirect($newstatusurl);
                    }
                }
            } else {
                $this->statusconfirmform = $statusconfirm->render();
            }
        }
    }

    /**
     * Check permissions for admin pages.
     *
     * @return bool true if user can view.
     */
    private function can_view_admin() {
        if (has_capability('local/onlineappraisal:itadmin', \context_system::instance())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Define the configured pages.
     */
    public function admin_pages() {
        $pagesarray = array(
            'itadmin' => false
        );


        $this->pages = array();
        $count = 0;
        foreach ($pagesarray as $name => $hook) {
            $page = new stdClass();
            $page->name = $name;

            if ($hook && $this->page == $name) {
                $class = "\\local_onlineappraisal\\$name";
                $classinstance = new $class($this);
                $classinstance->hook();
            }

            $page->order = $count++;

            $page->active = '';
            if ($this->page == $name) {
                $page->active = 'active';
            }

            $page->showinnav = true;

            $this->pages[$name] = $page;
        }

        // Help link
        $helppage = new stdClass();
        $helppage->name = 'help';
        $url = get_config('local_onlineappraisal', 'helpurl');
        if ($url) {
            $helppage->url = new moodle_url($url);
            $helppage->popup = true;
        } else {
            $helppage->url = new moodle_url('/local/onlineappraisal/itadmin.php', array('page' => 'help'));
        }
        $helppage->order = $count++;
        $helppage->showinnav = false;

        $this->pages[$helppage->name] = $helppage;

        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_onlineappraisal');
        }
    }
    
    /**
     * Prepare the admin page and form.
     */
    public function prepare_page() {
        
        // Prepare form.
        $customdata = array(
            'search' => $this->search,
            'page' => $this->page
        );
        $this->searchform = new \local_onlineappraisal\form\itadmin_search(
                null, $customdata, 'post', '', array('class' => 'itadmin_group clearfix')
            );

        if ($this->searchform->is_submitted() && ($data = $this->searchform->get_data())) {
            $this->search = $data->search;
        }
        if ($this->search) {
            $this->appraisals = $this->appraisals($this->search, 'appraisee', 'current');
        }
    }

    /**
     * Setup the page variables.
     */
    public function setup_page() {
        $this->pagetitle = get_string($this->page, 'local_onlineappraisal');
        $this->pageheading = get_string($this->page, 'local_onlineappraisal');
    }

    /**
     * Generate the user navigation menu structure.
     *
     * @return stdClass navigation.
     */
    public function get_navigation() {
        $navigation = new stdClass();
        $navigation->items = array();

        foreach ($this->pages as $page) {
            if (!$page->showinnav) {
                continue;
            }
            $navitem = clone($page);
            $navitem->subactive = '';
            $navitem->name = get_string($page->name, 'local_onlineappraisal');
            $navigation->items[] = $navitem;
        }
        return $navigation;
    }

    /**
     * Generate the main content for the page
     *
     * @return string html
     */
    public function main_content() {
        global $PAGE, $SESSION;

        if ($this->page && array_key_exists($this->page, $this->pages)) {

            if ($this->page === 'help') {
                $formhtml = '';
                $page = new \local_onlineappraisal\output\help\help();
                $pagehtml = $PAGE->get_renderer('local_onlineappraisal', 'help')->render($page);
            } else {
                $formhtml = !empty($this->searchform) ? $this->searchform->render() : '';
                $class = "\\local_onlineappraisal\\output\\itadmin\\{$this->page}";
                $page = new $class($this);
                $pagehtml = $this->renderer->render($page);
            }
            
            // Is there an alert.
            $alerthtml = '';
            if (!empty($SESSION->local_onlineappraisal->alert)) {
                $alert = new alert($SESSION->local_onlineappraisal->alert->message, $SESSION->local_onlineappraisal->alert->type, $SESSION->local_onlineappraisal->alert->button);
                $alerthtml .= $this->renderer->render($alert);
                unset($SESSION->local_onlineappraisal->alert);
            }

            return $alerthtml . $formhtml . $pagehtml;
        } else {
            $alert = new alert(get_string('error:pagenotfound', 'local_onlineappraisal', $this->page), 'danger', false);
            return $this->renderer->render($alert);
        }
    }

    /**
     * Get appraisals for type/state.
     * 
     * @global stdClass $DB
     * @global stdClass $USER
     * @param string $type
     * @param string $state
     * @return array appraisal records
     */
    public function appraisals($search, $type = 'appraisee', $state = 'current') {
        global $DB;
        if (!$user = $DB->get_record('user', array('idnumber' => strval($search)))){
            return 'nouser';
        }


        $params = array();
        switch ($state) {
            case 'current' :
                $params['archived'] = 0;
                break;
            case 'archived' :
                $params['archived'] = 1;
                break;
            default :
                // Invalid state, return empty array.
                return array();
        }


        $typefilter = "AND aa.{$type}_userid = :userid";
        $params['userid'] = $user->id;
            
        $appraisee = $DB->sql_concat_join("' '", array('u.firstname', 'u.lastname'));
        $appraiser = $DB->sql_concat_join("' '", array('au.firstname', 'au.lastname'));
        $signoff = $DB->sql_concat_join("' '", array('su.firstname', 'su.lastname'));
        $groupleader = $DB->sql_concat_join("' '", array('gl.firstname', 'gl.lastname'));
        $sql = "
            SELECT
                aa.*,
                lau.value as isvip,
                lac.created_date as latestcheckin,
                u.id as uid, {$appraisee} as appraisee, u.email as appraiseeemail, u.icq as costcentre,
                au.id as auid, {$appraiser} as appraiser,
                su.id as suid, {$signoff} as signoff,
                gl.id as glid, {$groupleader} as groupleader
            FROM
                {local_appraisal_appraisal} aa
            LEFT JOIN
                {local_appraisal_users} lau
                ON lau.userid = aa.appraisee_userid AND lau.setting = 'appraisalvip'
            LEFT JOIN
                (SELECT appraisalid, MAX(created_date) as created_date FROM {local_appraisal_checkins} GROUP BY appraisalid) AS lac
                ON lac.appraisalid = aa.id
            LEFT JOIN
                {user} u
                ON u.id = aa.appraisee_userid
            LEFT JOIN
                {user} au
                ON au.id = aa.appraiser_userid
            LEFT JOIN
                {user} su
                ON su.id = aa.signoff_userid
            LEFT JOIN
                {user} gl
                ON gl.id = aa.groupleader_userid
            LEFT JOIN
                {local_costcentre} c
                ON c.costcentre = u.icq
            WHERE
                aa.archived = :archived
                AND aa.deleted = 0
                {$typefilter}
            ORDER BY
                u.lastname ASC, u.firstname ASC";

        $appraisals = $DB->get_records_sql($sql, $params);
        foreach ($appraisals as $appraisal) {
            $appraisal->admins = $this->find_appraisal_administrator($appraisal);
            $appraisal->viewingas = 'appraisee';
            $appraisal->selectform = $this->appraisal_status_select_form($appraisal);
            $appraisal->progress = $this->progress($appraisal);
            $appraisal->feedback = $this->appraisal_feedback($appraisal);
        }
        return $appraisals;
    }

    private function find_appraisal_administrator($appraisal) {
        global $DB;

        $admins = array();

        // Find users with the correct capability.
        $costcentreadmins = get_users_by_capability(\context_system::instance(), 'local/costcentre:administer');
        foreach ($costcentreadmins as $csa) {
            if ($csa->icq == $appraisal->costcentre) {
                $csa->fullname = fullname($csa);
                $admins[] = $csa;
            }
        }

        // Find users from the costcentre configuration.
        $ccsql = "SELECT * FROM {local_costcentre_user}
                          WHERE costcentre = :costcentre
                            AND permissions IN (:perm1, :perm2, :perm3)";

        $params = array('costcentre' => $appraisal->costcentre,
            'perm1' => costcentre::BUSINESS_ADMINISTRATOR,
            'perm2' => costcentre::HR_LEADER,
            'perm3' => costcentre::HR_ADMIN
            );

        $ccroles = $DB->get_records_sql($ccsql, $params);

        foreach ($ccroles as $ccadmin) {
            if ($ccadminuser = $DB->get_record('user', array('id' => $ccadmin->userid))) {
                $ccadminuser->fullname = fullname($ccadminuser);
                $admins[] = $ccadminuser;
            }
        }
        return $admins;
    }

    private function appraisal_status_select_form($appraisal) {
        global $DB, $SESSION, $USER;
        // Statuses.
        if ($this->action == 'changestatus') {
            return '';
        }

        if ($appraisal->groupleader_userid) {
            $numoptions = 9;
        } else {
            $numoptions = 8;
        }
        $allstatusoptions = array('0' => get_string('itadmin:selectstatus', 'local_onlineappraisal'));
        for ($i = 1; $i < $numoptions; $i++) {
            if ($appraisal->statusid == $i) {
                continue;
            }
            if ($appraisal->groupleader_userid && $i == 7) {
                $allstatusoptions[$i] = get_string('status:7:leadersignoff', 'local_onlineappraisal');
            } else {
                $allstatusoptions[$i] = get_string('status:'.$i, 'local_onlineappraisal');
            }
        }

        $statusoptions = $allstatusoptions;
        
        $customdata = array(
            'search' => $this->search,
            'appraisalid' => $appraisal->id,
            'statusoptions' => $statusoptions,
            'statusconfirm' => false,
            'newstatusid' => 0);

        $statusform = new \local_onlineappraisal\form\itadmin_status(
            null, $customdata, 'post', '', array('class' => 'itadmin_group clearfix')
        );

        if ($statusform->is_submitted() && ($data = $statusform->get_data())) {
            if ($data->newstatusid > 0) {
                $params = array(
                    'search' => $this->search,
                    'appraisalid' => $appraisal->id,
                    'itadminaction' => 'changestatus',
                    'newstatusid' => $data->newstatusid);
                $newstatusurl = new moodle_url($this->baseurl, $params);
                $newstatusurl->set_anchor('changestatus');
                redirect($newstatusurl);
            }
        } else {
            return $statusform->render();
        }
    }

    /**
     * Returns the variables for the progess panel given the user type, status and face to face status.
     *
     * @global stdClass $CFG
     * @return stdClass
     */
    private function progress($appraisal) {
        global $CFG;

        // Necessary due to numbering being different on graphic compared to actual status.
        $status = $appraisal->statusid - 1;

        $svgclasses = array(
            'progress-svg',
            "progress-svg-{$appraisal->viewingas}"
        );
        if ($appraisal->face_to_face_held) {
            $svgclasses[] = 'progress-svg-f2f';
        }
        if (!empty($appraisal->groupleader)) {
            // Signifies groupleader active.
            $svgclasses[] = 'progress-svg-gla';
        }
        $svgclasses[] = "progress-svg-{$status}";
        $progressvars = new stdClass();
        $progressvars->class = implode(' ', $svgclasses);
        if ($appraisal->groupleader_userid) {
            $progressvars->svg = file_get_contents($CFG->dirroot.'/local/onlineappraisal/pix/progress_extra.svg');
        } else {
            $progressvars->svg = file_get_contents($CFG->dirroot.'/local/onlineappraisal/pix/progress.svg');
        }
        return $progressvars;
    }

    private function appraisal_feedback($appraisal) {
        global $DB;
        $requests = $DB->get_records('local_appraisal_feedback',
            array('appraisalid' => $appraisal->id));
        foreach ($requests as &$request) {
            $request->datesend = userdate($request->created_date, get_string('strftimedate'));
            if ($request->received_date) {
                $request->received = true;
            }
            $params = array('search' => $this->search,
                'itadminaction' => 'removefeedback',
                'requestid' => $request->id,
                'appraisalid' => $appraisal->id);
            $request->removeurl = new moodle_url($this->baseurl, $params);
            $request->removeurl->set_anchor('deletefeedback');
        }
        return array_values($requests);
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