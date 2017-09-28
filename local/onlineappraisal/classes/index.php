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
use local_onlineappraisal\permissions as permissions;
use local_onlineappraisal\output\alert as alert;

class index {

    public $pagetitle;
    public $pageheading;
    public $pages;

    private $user;
    private $page;
    private $groupid;

    private $renderer;
    
    private $is = array(
        'appraisee' => false,
        'appraiser' => false,
        'signoff' => false,
        'groupleader' => false,
        'hrleader' => false,
        'contributor' => false,
        'businessadmin' => false,
        'itadmin' => false,
        'costcentreadmin' => false,
    );

    private $requiresaction = array(
        'appraisee' => array(1 => true, 2 => true, 4 => true),
        'appraiser' => array(3 => true, 5 => true),
        'signoff' => array(6 => true),
        'groupleader' => array(7 => 'custom'),
    );

    private $groupleaderactive = array();

    private $canviewvip = array();

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param string page the page requested
     */
    public function __construct($page) {
        global $PAGE, $USER;

        $this->user = $USER;
        $this->page = $page;

        $this->set_user_types();
        
        // Check if this user is allowed to view index page.
        if (!$this->can_view_index()) {
            print_error('error:noaccess', 'local_onlineappraisal');
        }

        // Set up pages for navigation.
        $this->index_pages();

        // Finally set up renderer.
        $this->renderer = $PAGE->get_renderer('local_onlineappraisal', 'index');
    }

    /**
     * Magic getter.
     * 
     * @param string $name
     * @return mixed property
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' . $name . ' requested');
        }
        return $this->{$name};
    }

    /**
     * Set accessible user types for current user.
     * 
     * @global \moodle_database $DB
     */
    private function set_user_types() {
        global $DB;
        // Is appraisee?
        $this->is['appraisee'] = $DB->count_records('local_appraisal_appraisal', array('appraisee_userid' => $this->user->id, 'deleted' => 0));
        // Is appraiser?
        $this->is['appraiser'] = $DB->count_records('local_appraisal_appraisal', array('appraiser_userid' => $this->user->id, 'deleted' => 0));
        // Is signoff?
        $this->is['signoff'] = $DB->count_records('local_appraisal_appraisal', array('signoff_userid' => $this->user->id, 'deleted' => 0));
        // Is groupleader? (Specifically assigned or general)
        $groupleadersql = "
            SELECT COUNT(aa.id)
            FROM {local_appraisal_appraisal} aa
            JOIN {user} u
                ON u.id = aa.appraisee_userid
            LEFT JOIN {local_costcentre} c
                ON c.costcentre = u.icq
            WHERE
                aa.deleted = :deleted
                AND aa.groupleader_userid = :groupleaderid
                AND c.groupleaderactive = :groupleaderactive";
        $groupleaderparams = array('groupleaderid' => $this->user->id, 'groupleaderactive' => 1, 'deleted' => 0);
        $this->is['groupleader'] = $DB->count_records_sql($groupleadersql, $groupleaderparams)
                || costcentre::is_user($this->user->id, costcentre::GROUP_LEADER);
        // Is hrleader?
        $this->is['hrleader'] = costcentre::is_user($this->user->id, array(costcentre::HR_LEADER, costcentre::HR_ADMIN));
        // Is contributor?
        // This is _very_ similar to code in \local_onlineappraisal\navbarmenu.
        $sort = 'received_date DESC, lastname ASC, firstname ASC';
        $like = $DB->sql_like('email', ':email', false);
        $feedbacks = $DB->get_records_select('local_appraisal_feedback', $like, array('email' => $this->user->email), $sort);
        foreach ($feedbacks as $feedback) {
            $appraisal = $DB->get_record('local_appraisal_appraisal', array('id' => $feedback->appraisalid, 'deleted' => 0));
            if (!$appraisal) {
                // Appraisal doesn't exist or has been deleted.
                continue;
            }
            if ($feedback->received_date) {
                // Will appear in completed feedback requests table so need menu link.
                 $this->is['contributor'] = true;
                 break;
            }
            $permission = 'feedback:submit';
            $stage = $appraisal->permissionsid;
            $usertype = 'guest';
            // Will appear in outstanding feedback requests table if can submit.
            if (\local_onlineappraisal\permissions::is_allowed($permission, $stage, $usertype, $appraisal->archived, $appraisal->legacy)) {
                 $this->is['contributor'] = true;
                 break;
            }
        }
        $this->is['itadmin'] = has_capability('local/onlineappraisal:itadmin', \context_system::instance());

        // Can use is_business_adminstrator() function from here.
        $navbarmenu = new navbarmenu();
        $this->is['businessadmin'] = $navbarmenu->is_business_administrator($this->user->id);
        $this->is['costcentreadmin'] = has_capability('local/costcentre:administer', \context_system::instance()) || costcentre::is_user($this->user->id, costcentre::BUSINESS_ADMINISTRATOR);

    }

    /**
     * Check permissions for index pages.
     *
     * @return bool true if user can view.
     */
    private function can_view_index() {
        if (in_array(true, $this->is)) {
            // User has at least one dashboard they can view.
            return true;
        }
        return false;
    }

    /**
     * Define the configured pages.
     */
    public function index_pages() {
        $pagesarray = array();

        foreach ($this->is as $page => $visible) {
            if ($visible) {
                $pagesarray[] = $page;
            }
        }

        // Set a page if not passed.
        if (empty($this->page)) {
            $this->page = reset($pagesarray);
        }

        $this->pages = array();
        $count = 0;

        foreach ($pagesarray as $name) {
            $page = new stdClass();
            $page->name = $name;
            $page->url = new moodle_url('/local/onlineappraisal/index.php', array('page' => $name));
            $page->order = $count++;
            $page->showinnav = true;

            $page->active = '';
            if ($this->page == $name) {
                $page->active = 'active';
            }

            // Exceptions to general rules.
            if ($name == 'contributor') {
                $page->url = new moodle_url('/local/onlineappraisal/feedback_requests.php');
            }
            if ($name == 'businessadmin') {
                $page->url = new moodle_url('/local/onlineappraisal/admin.php');
            }
            if ($name == 'costcentreadmin') {
                $page->url = new moodle_url('/local/costcentre/index.php');
            }
            if ($name == 'itadmin') {
                $page->url = new moodle_url('/local/onlineappraisal/itadmin.php');
            }

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
            $helppage->url = new moodle_url('/local/onlineappraisal/index.php', array('page' => 'help'));
        }
        $helppage->order = $count++;
        $helppage->showinnav = false;

        $this->pages[$helppage->name] = $helppage;

        // Return to Moodle link.
        $returnpage = new stdClass();
        $returnpage->name = 'moodle';
        $returnpage->url = new moodle_url('/');
        $returnpage->order = $count++;
        $returnpage->showinnav = true;

        $this->pages[$returnpage->name] = $returnpage;

        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_onlineappraisal');
        }
    }

    /**
     * Setup the page variables.
     */
    public function setup_page() {
        if (substr($this->pages[$this->page]->url->get_path(), -strlen('/local/onlineappraisal/index.php')) !== '/local/onlineappraisal/index.php') {
            // Chosen page is not _actually_ an index page but has been injected into the menu.
            // Let's redirect.
            redirect($this->pages[$this->page]->url);
        }
        $this->pagetitle = $this->pageheading = get_string('index:' . $this->page, 'local_onlineappraisal');
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
            $navitem->name = get_string('index:' . $page->name, 'local_onlineappraisal');
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
                $formhtml = $this->inject_form();
                $class = "\\local_onlineappraisal\\output\\index\\{$this->page}";
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

    private function inject_form() {
        if ($this->page !== 'hrleader') {
            return '';
        }

        // Get requested groupid.
        $this->groupid = optional_param('groupid', null, PARAM_ALPHANUMEXT);

        // Prepare form.
        $customdata = array(
            'groups' => $this->get_groups(),
            'page' => $this->page,
            'groupid' => $this->groupid
        );
        // Use the same form as the admin pages.
        $form = new \local_onlineappraisal\form\admin_group(
                null, $customdata, 'post', '', array('class' => 'admin_group clearfix')
            );
        return $form->render();
    }

    /**
     * Load applicable groups for current hrleader.
     *
     * @global stdClass $DB
     */
    public function get_groups() {
        global $DB;

        $params = array(
            'userid' => $this->user->id,
            'bitandhrl' => costcentre::HR_LEADER,
            'bitandhra' => costcentre::HR_ADMIN,
        );

        $bitandhrl = $DB->sql_bitand('lcu.permissions', costcentre::HR_LEADER);
        $bitandhra = $DB->sql_bitand('lcu.permissions', costcentre::HR_ADMIN);

        $sql = "
            SELECT
                DISTINCT(lc.costcentre)
            FROM {local_costcentre} lc
            JOIN {local_costcentre_user} lcu ON lcu.costcentre = lc.costcentre
            WHERE
                lc.enableappraisal = 1
                AND lcu.userid = :userid
                AND ({$bitandhrl} = :bitandhrl OR {$bitandhra} = :bitandhra)
            ORDER BY
                lc.costcentre ASC";

        $groups = $DB->get_records_sql($sql, $params);

        $options = array('' => get_string('form:all', 'local_onlineappraisal'));
        foreach($groups as $group) {
            // Find the group name.
            $sql = "
                SELECT
                    u.department
                FROM
                    {user} u
                INNER JOIN
                    (SELECT
                        MAX(id) maxid
                    FROM
                        {user} inneru
                    INNER JOIN
                        (SELECT
                            MAX(timemodified) as maxtimemodified
                        FROM
                            {user}
                        WHERE
                            icq = :icq1
                        ) groupedicq
                        ON inneru.timemodified = groupedicq.maxtimemodified
                    WHERE
                        icq = :icq2
                    ) groupedid
                    ON u.id = groupedid.maxid
                WHERE
                    u.icq = :icq3";
            $params = array_fill_keys(array('icq1', 'icq2', 'icq3'), $group->costcentre);
            $groupname = $DB->get_field_sql($sql, $params);
            $options = $options + array($group->costcentre => $group->costcentre.' - ' . $groupname);
            if ($this->is['hrleader']) {
                $this->canviewvip[$group->costcentre] = costcentre::is_user($this->user->id, costcentre::HR_LEADER, $group->costcentre);
            }
        }
        
        return $options;
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
    public function get_appraisals($type = 'appraisee', $state = 'current') {
        global $DB, $USER;

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

        switch ($type) {
            case 'appraisee' :
            case 'signoff' :
                $typefilter = "AND aa.{$type}_userid = :userid";
                $params['userid'] = $USER->id;
                break;
            case 'appraiser' :
                $typefilter = "AND (aa.{$type}_userid = :userid OR aa.appraisee_userid IN (SELECT appraisee_userid FROM {local_appraisal_appraisal} WHERE {$type}_userid = :userid2 AND archived = 0 AND deleted = 0))";
                $params['userid'] = $USER->id;
                $params['userid2'] = $USER->id;
                break;
            case 'groupleader' :
                $typefilter = "AND ((aa.{$type}_userid = :userid AND c.groupleaderactive = :groupleaderactive)";
                $params['userid'] = $USER->id;
                $params['groupleaderactive'] = 1;

                $groups = costcentre::get_user_cost_centres($USER->id, costcentre::GROUP_LEADER);
                if (!empty($groups)) {
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED);
                    $params = $params + $inparams;
                    $typefilter .= " OR u.icq {$insql}";
                }
                
                $typefilter .= ')';
                break;
            case 'hrleader' :
                $groups = costcentre::get_user_cost_centres($USER->id, array(costcentre::HR_LEADER, costcentre::HR_ADMIN));
                if (empty($groups)) {
                    return array();
                }
                if (!empty($this->groupid)) {
                    $params['uicq'] = $this->groupid;
                    $typefilter = "AND u.icq = :uicq";
                } else {
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED);
                    $params = $params + $inparams;
                    $typefilter = "AND u.icq {$insql}";
                }
                break;
            default :
                // Invalid type, return empty array.
                return array();
        }

        $appraisee = $DB->sql_concat_join("' '", array('u.firstname', 'u.lastname'));
        $appraiser = $DB->sql_concat_join("' '", array('au.firstname', 'au.lastname'));
        $signoff = $DB->sql_concat_join("' '", array('su.firstname', 'su.lastname'));
        $sql = "
            SELECT
                aa.*,
                lau.value as isvip,
                lac.created_date as latestcheckin,
                u.id as uid, {$appraisee} as appraisee, u.email as appraiseeemail, u.icq as costcentre, u.suspended,
                au.id as auid, {$appraiser} as appraiser,
                su.id as suid, {$signoff} as signoff
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
                {local_costcentre} c
                ON c.costcentre = u.icq
            WHERE
                aa.archived = :archived
                AND aa.deleted = 0
                {$typefilter}
            ORDER BY
                u.lastname ASC, u.firstname ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns whether appraisal requires action give user type and status.
     * 
     * @param string $type
     * @param object $appraisal
     * @return boolean requires action
     */
    public function requires_action($type, $appraisal) {
        $statusid = $appraisal->statusid;
        if (empty($this->requiresaction[$type])) {
            return false;
        }
        if (!array_key_exists($statusid, $this->requiresaction[$type])) {
            return false;
        }
        if ($this->requiresaction[$type][$statusid] === 'custom') {
            $method = "requires_action_{$type}_{$statusid}";
            if (!method_exists($this, $method)) {
                return false;
            }
            return call_user_func(array($this, $method), $appraisal);
        }
        return $this->requiresaction[$type][$statusid];
    }

    /**
     * Custom requires action check for groupleader/status 7.
     *
     * @param object $appraisal
     * @return boolean requires action
     */
    private function requires_action_groupleader_7($appraisal) {
        if (!isset($this->groupleaderactive[$appraisal->costcentre])) {
            $this->groupleaderactive[$appraisal->costcentre] = costcentre::get_cost_centre_groupleaderactive($appraisal->costcentre);
        }
        return $this->groupleaderactive[$appraisal->costcentre] && $this->user->id == $appraisal->groupleader_userid;
    }

    /**
     * Toggle F2F status for an appraisal.
     * 
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function toggle_f2f_complete() {
        global $DB, $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);

        $params = array(
            'id' => $appraisalid,
            'archived' => 0,
            'deleted' => 0
        );
        $appraisal = $DB->get_record('local_appraisal_appraisal', $params);
        if (empty($appraisal)) {
            // The appraisal doesn't exist.
            throw new moodle_exception('error:loadappraisal', 'local_onlineappraisal');
        }

        if ($USER->id == $appraisal->appraisee_userid) {
            $viewingas = 'appraisee';
        } else if ($USER->id == $appraisal->appraiser_userid) {
            $viewingas = 'appraiser';
        } else {
            $viewingas = null;
        }

        if (!permissions::is_allowed('f2f:complete', $appraisal->permissionsid, $viewingas, $appraisal->archived, $appraisal->legacy)) {
            throw new moodle_exception('error:permission:f2f:complete', 'local_onlineappraisal');
        }

        $return = new stdClass();

        // Toggle F2F status in appraisal record.
        $appraisal->face_to_face_held = $appraisal->face_to_face_held ? 0 : 1;
        $appraisal->modified_date = time();
        $return->data = (bool) $appraisal->face_to_face_held;
        $return->success = $DB->update_record('local_appraisal_appraisal', $appraisal);

        $not = $appraisal->face_to_face_held ? '' : 'not';
        if ($return->success) {
            $return->message = get_string("success:togglef2f:{$not}complete", 'local_onlineappraisal');
        } else {
            $return->message = get_string("error:togglef2f:{$not}complete", 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Update F2F date for an appraisal.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass
     * @throws moodle_exception
     */
    public static function update_f2f_date() {
        global $DB, $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $date = required_param('date', PARAM_INT);

        $params = array(
            'id' => $appraisalid,
            'archived' => 0,
            'deleted' => 0
        );
        $appraisal = $DB->get_record('local_appraisal_appraisal', $params);
        if (empty($appraisal)) {
            // The appraisal doesn't exist.
            throw new moodle_exception('error:loadappraisal', 'local_onlineappraisal');
        }

        if ($USER->id == $appraisal->appraisee_userid) {
            $viewingas = 'appraisee';
        } else if ($USER->id == $appraisal->appraiser_userid) {
            $viewingas = 'appraiser';
        } else {
            $viewingas = null;
        }

        if (!permissions::is_allowed('f2f:add', $appraisal->permissionsid, $viewingas, $appraisal->archived, $appraisal->legacy)) {
            throw new moodle_exception('error:permission:f2f:add', 'local_onlineappraisal');
        }

        $return = new stdClass();
        $return->data = '';

        // Update F2F date in appraisal record.
        $appraisal->held_date = $date;
        $appraisal->modified_date = time();
        $return->success = $DB->update_record('local_appraisal_appraisal', $appraisal);

        if ($return->success) {
            $return->message = get_string("success:f2fdate:update", 'local_onlineappraisal');
        } else {
            $return->message = get_string("error:f2fdate:update", 'local_onlineappraisal');
        }

        return $return;
    }
}