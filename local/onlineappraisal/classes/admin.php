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
use local_onlineappraisal\comments as comments;
use local_onlineappraisal\email as email;
use local_onlineappraisal\output\alert as alert;

class admin {

    public $pagetitle;
    public $pageheading;
    public $pages;

    private $user;
    private $page;
    private $groupid;
    private $groupleaderactive;
    private $groups;
    private $cohorts;
    private $cohortid;

    private $groupcohort;

    private $form;

    private $renderer;

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param string page the name of the page
     * @param int $groupid the id of the group
     */
    public function __construct($page, $groupid, $cohortid) {
        global $PAGE, $USER;

        $this->user = $USER;
        $this->page = $page;

        $this->groupid = $groupid;
        $this->groupleaderactive = costcentre::get_cost_centre_groupleaderactive($groupid);
        $this->setup_groups($groupid);

        $this->cohortid = $cohortid;
        $this->setup_cohorts();

        $this->groupcohort = $this->get_groupcohort();

        // Check if this user is allowed to view admin.
        if (!$this->can_view_admin() && !is_siteadmin($USER)) {
            print_error('error:noaccess', 'local_onlineappraisal');
        }

        // Set up pages for navigation.
        $this->admin_pages();

        // Finally set up renderer.
        $this->renderer = $PAGE->get_renderer('local_onlineappraisal', 'admin');
    }

    /**
     *  Magic getter.
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
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    /**
     * Check permissions for admin pages.
     *
     * @return bool true if user can view.
     */
    private function can_view_admin() {
        if (!empty($this->groupid) && array_key_exists($this->groupid, $this->groups)) {
            return true;
        }
        return false;
    }

    /**
     * Define the configured pages.
     */
    public function admin_pages() {
        global $USER;

        if ($this->can_view_admin()) {
            $pagesarray = array(
                'allstaff' => true,
                'initialise' => false,
                'inprogress' => false,
                'complete' => false,
                'archived' => false
            );
        } else {
            $pagesarray = [];
        }

        // Only add 'deleted' page if allowed to permanently delete appraisals.
        if (has_capability('local/onlineappraisal:deleteappraisal', \context_system::instance())) {
            $pagesarray['deleted'] = false;
        }

        // Display appraisal cycle page for site admin only
        if (is_siteadmin($USER)) {
            $pagesarray = ['cycle' => true] + $pagesarray;
        }

        $pagesarray['help'] = false;

        $this->pages = array();
        $count = 0;
        foreach ($pagesarray as $name => $hook) {
            $page = new stdClass();
            $page->name = $name;

            // Special cases...
            switch ($page->name) {
                case 'help':
                    $url = get_config('local_onlineappraisal', 'helpurl');
                    if ($url) {
                        $page->url = new moodle_url($url);
                        $page->popup = true;
                        $page->noform = true;
                    }
                    break;
                case 'cycle':
                    $page->url = new moodle_url('/local/onlineappraisal/admin.php', array('page' => $name));
                    break;
            }

            // Default/fallback.
            if (empty($page->url)) {
                $page->url = new moodle_url('/local/onlineappraisal/admin.php', array('page' => $name, 'groupid' => $this->groupid, 'cohortid' => $this->cohortid));
            }

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

            $this->pages[$name] = $page;
        }

        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_onlineappraisal');
        }
    }

    /**
     * Prepare the admin page and form.
     */
    public function prepare_page() {
        if (!empty($this->pages[$this->page]->noform)) {
            return;
        }

        // Prepare form.
        $customdata = [
            'groups' => $this->groups,
            'page' => $this->page,
            'groupid' => $this->groupid,
            'cohorts' => $this->cohorts,
            'cohortid' => $this->cohortid,
        ];
        $this->form = new \local_onlineappraisal\form\admin_group(
                null, $customdata, 'post', '', array('class' => 'admin_group clearfix')
            );
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

        $numberdimmed = ['archived', 'deleted'];

        foreach ($this->pages as $page) {
            $navitem = clone($page);
            $navitem->subactive = '';
            $navitem->name = get_string($page->name, 'local_onlineappraisal');
            $navitem->numbered = $this->get_number($page->name);
            $navitem->numberdimmed = in_array($page->name, $numberdimmed);
            $navigation->items[] = $navitem;
        }
        return $navigation;
    }

    /**
     * Returns the number to highlight next to a menu item.
     * @param string $pagename
     */
    private function get_number($pagename) {
        $number = 0;
        switch ($pagename) {
            case 'initialise' :
                $number = empty($this->groupcohort->closed) ? count($this->get_group_users_initialise(false)) : 0;
                break;
            case 'inprogress' :
            case 'complete' :
                $number = empty($this->groupcohort->closed) ? count($this->get_group_appraisals($pagename)) : 0;
                break;
            case 'archived' :
            case 'deleted' :
                $number = count($this->get_group_appraisals($pagename));
                break;
        }
        return $number;
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
            } else if ($this->page === 'cycle') {
                $formhtml = '';
                $class = "\\local_onlineappraisal\\output\\admin\\{$this->page}";
                $page = new $class($this);
                $pagehtml = $this->renderer->render($page);
            } else {
                $formhtml = !empty($this->form) ? $this->form->render() : '';
                $class = "\\local_onlineappraisal\\output\\admin\\{$this->page}";
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
     * Load applicable groups for current BA and set default if required.
     *
     * @global \moodle_database  $DB
     */
    public function setup_groups() {
        global $DB;


        $joins = array();
        $wheres = array(
            "lc.enableappraisal = 1",
        );
        $params = array();
        if (!has_capability('local/costcentre:administer', \context_system::instance())) {
            $joins[] = 'JOIN {local_costcentre_user} lcu ON lcu.costcentre = lc.costcentre';

            $wheres[] = "lcu.userid = :userid";
            $params['userid'] = $this->user->id;

            $bitandba = $DB->sql_bitand('lcu.permissions', costcentre::BUSINESS_ADMINISTRATOR);
            $bitandhrl = $DB->sql_bitand('lcu.permissions', costcentre::HR_LEADER);
            $bitandhra = $DB->sql_bitand('lcu.permissions', costcentre::HR_ADMIN);
            $wheres[] = "({$bitandba} = :bitandba OR {$bitandhrl} = :bitandhrl OR {$bitandhra} = :bitandhra)";
            $params['bitandba'] = costcentre::BUSINESS_ADMINISTRATOR;
            $params['bitandhrl'] = costcentre::HR_LEADER;
            $params['bitandhra'] = costcentre::HR_ADMIN;
        }

        $join = implode("\n", $joins);
        $where = implode("\n AND ", $wheres);

        $sql = "
            SELECT
                DISTINCT(lc.costcentre)
            FROM {local_costcentre} lc
            {$join}
            WHERE
                {$where}
            ORDER BY
                lc.costcentre ASC";

        $groups = $DB->get_records_sql($sql, $params);

        $this->groups = array();
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
            $this->groups = $this->groups + array($group->costcentre => $group->costcentre.' - ' . $groupname);
        }

        if (empty($this->groupid)) {
            reset($this->groups);
            $this->groupid = key($this->groups);
        }
    }

    /**
     * Load cohorts and set default.
     *
     * @global \moodle_database  $DB
     */
    public function setup_cohorts() {
        global $DB;

        $this->cohorts = $DB->get_records_select_menu(
                'local_appraisal_cohorts',
                'availablefrom < :now',
                ['now' => time()],
                'availablefrom DESC',
                'id, name');

        if (!array_key_exists($this->cohortid, $this->cohorts)) {
            reset($this->cohorts);
            $this->cohortid = key($this->cohorts);
        }
    }

    /**
     * Returns current cohort name.
     *
     * @return string Cohort name.
     */
    public function get_cohort_name() {
        return $this->cohorts[$this->cohortid];
    }

    /**
     * Get cohort information for this group.
     *
     * @global \moodle_database $DB
     * @return stdClass group cohort record
     */
    public function get_groupcohort() {
        global $DB;

        if (!empty($this->groupcohort)) {
            return $this->groupcohort;
        }

        $groupcohort = $DB->get_record('local_appraisal_cohort_ccs', ['cohortid' => $this->cohortid, 'costcentre' => $this->groupid]);
        if (!$groupcohort) {
            // Only set up if the most recently available.
            $latestcohort = $DB->get_field_select(
                'local_appraisal_cohorts',
                'id',
                'availablefrom = (SELECT MAX(availablefrom) FROM {local_appraisal_cohorts} WHERE availablefrom < :now)',
                ['now' => time()]);
            if ($latestcohort != $this->cohortid) {
                $url = new moodle_url('/local/onlineappraisal/admin.php', ['page' => $this->page, 'groupid' => $this->groupid]);
                throw new moodle_exception('error:cohortold', 'local_onlineappraisal', '', $url->out(false));
            }

            $groupcohort = new stdClass();
            $groupcohort->cohortid = $this->cohortid;
            $groupcohort->costcentre = $this->groupid;
            $groupcohort->started = $groupcohort->locked = $groupcohort->closed = $groupcohort->duedate = null;
            $groupcohort->id = $DB->insert_record('local_appraisal_cohort_ccs', $groupcohort);
        }

        return $groupcohort;
    }

    /**
     * Get users in group for managing cohort.
     *
     * @global \moodle_database $DB
     * @param bool $suspended Include suspended users.
     * @return stdClass arrays of user records
     */
    public function get_group_users_allstaff($suspended = true) {
        global $DB;
        $suspendedwhere = '';
        if (!$suspended) {
            $suspendedwhere = 'AND u.suspended = 0';
        }
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $sql = "SELECT
                u.id, u.firstname, u.lastname, u.icq, u.idnumber, u.suspended,
                h.GRADE as grade, h.EMPLOYMENT_CATEGORY as employmentcategory, h.LATEST_HIRE_DATE as latesthiredate,
                acu.id as assigned,
                an.id as appraisalnotrequired,
                an.reason as appraisalnotrequiredreason,
                au.value as isvip
            FROM
                {user} u
            LEFT JOIN
                SQLHUB.ARUP_ALL_STAFF_V h
                ON h.EMPLOYEE_NUMBER = {$castidnumber}
            LEFT JOIN
                {local_appraisal_cohort_users} acu
                ON acu.userid = u.id AND acu.cohortid = :cohortid
            LEFT JOIN
                {local_appraisal_notrequired} an
                ON an.userid = u.id AND superseded IS NULL
            LEFT JOIN
                {local_appraisal_users} au
                ON au.userid = u.id AND au.setting = 'appraisalvip'
            WHERE
                u.icq = :icq
                AND u.confirmed = 1
                AND u.deleted = 0
                {$suspendedwhere}
            ORDER BY
                u.lastname ASC, u.firstname ASC
            ";
        $params = array(
            'icq' => $this->groupid,
            'cohortid' => $this->cohortid,
        );
        $allusers = $DB->get_records_sql($sql, $params);

        $users = new stdClass();
        $users->all = [];
        $users->assigned = [];
        $users->notassigned = [];

        $utctz = new \DateTimeZone('UTC');
        $monthsago18 = strtotime('-18 months', time());

        // Temporary arrays for sorting.
        $required = [];
        $notrequired = [];
        foreach ($allusers as $user) {
            if ($user->appraisalnotrequired && empty($user->appraisalnotrequiredreason)) {
                $user->appraisalnotrequiredreason = get_string('admin:appraisalnotrequired:noreason', 'local_onlineappraisal');
            }
            $startdate = \DateTime::createFromFormat('dmY', $user->latesthiredate, $utctz);
            $user->startdate = ($startdate && $startdate->getTimestamp() > $monthsago18) ? userdate($startdate->getTimestamp(), get_string('strftimedate'), $utctz) : '';

            if ($user->assigned) {
                $users->assigned[$user->id] = $user;
            } else if (!$user->suspended) {
                $user->appraisalnotrequired ? $notrequired[$user->id] = $user : $required[$user->id] = $user;
            }
        }
        $users->notassigned = $required + $notrequired;

        return $users;
    }

    /**
     * Get users in group who can have an appraisal initialised.
     *
     * @global \moodle_database $DB
     * @param bool $full Include full initialise info.
     * @return array user records
     */
    public function get_group_users_initialise($full = true) {
        global $DB;
        $castuidnumber = $DB->sql_cast_char2int('u.idnumber');
        $fullsql = [
            'select' => '',
            'join1' => '',
            'join2' => '',
        ];
        if ($full) {
            $fullsql['select'] = ', aa2.appraiser_userid as auid, aa2.signoff_userid as suid, aa2.groupleader_userid as glid, h.SUP_EMPLOYEE_NUMBER as supidnumber';
            $fullsql['join1'] = "JOIN
                SQLHUB.ARUP_ALL_STAFF_V h
                ON h.EMPLOYEE_NUMBER = {$castuidnumber}";
            $fullsql['join2'] = 'LEFT JOIN (
                SELECT
                    aainner.*
                FROM
                    {local_appraisal_appraisal} aainner
                JOIN (
                    SELECT
                        appraisee_userid, MAX(created_date) as created_date
                    FROM
                        {local_appraisal_appraisal}
                    WHERE
                        archived = 1
                        AND deleted = 0
                    GROUP BY appraisee_userid
                    ) as aainner2
                    ON aainner.appraisee_userid = aainner2.appraisee_userid
                    AND aainner.created_date = aainner2.created_date
                ) as aa2
                ON aa2.appraisee_userid = u.id';
        }
        $sql = "
            SELECT
                u.id, u.firstname, u.lastname, u.icq, u.idnumber{$fullsql['select']}
            FROM
                {user} u
            {$fullsql['join1']}
            JOIN
                {local_appraisal_cohort_users} acu
                ON acu.userid = u.id
            LEFT JOIN
                {local_appraisal_appraisal} aa
                ON aa.appraisee_userid = u.id
                AND aa.archived = 0
                AND aa.deleted = 0
            {$fullsql['join2']}
            LEFT JOIN
                {local_appraisal_notrequired} an
                ON an.userid = u.id AND an.superseded IS NULL
            WHERE
                u.icq = :icq
                AND u.confirmed = 1
                AND u.suspended = 0
                AND u.deleted = 0
                AND acu.cohortid = :cohortid
                AND aa.id IS NULL
                AND an.id IS NULL
                ORDER BY u.lastname ASC, u.firstname ASC";
        $params = array(
            'icq' => $this->groupid,
            'cohortid' => $this->cohortid,
        );
        $users = $DB->get_records_sql($sql, $params);
        if ($full) {
            foreach ($users as $user) {
                if (!empty($user->supidnumber)) {
                    $user->auid2 = $DB->get_field('user', 'id', ['idnumber' => str_pad($user->supidnumber, 6, '0', STR_PAD_LEFT), 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0]);
                } else {
                    $user->auid2 = null;
                }
            }
        }
        return $users;
    }

    /**
     * Get appraisals from group.
     *
     * @global \moodle_database $DB
     * @param int $status
     * @return array appraisal records.
     */
    public function get_group_appraisals($status) {
        global $DB;

        switch ($status) {
            case 'deleted' :
                $deleted = 1;
                $status = '>= 0';
                break;
            case 'archived' :
                $archived = 1;
                $deleted = 0;
                $status = '>= 0';
                break;
            case 'complete' :
                $archived = 0;
                $deleted = 0;
                $status = '>= 7';
                break;
            case 'inprogress' :
                $archived = 0;
                $deleted = 0;
                $status = 'BETWEEN 1 AND 6';
                break;
            default :
                return array();
        }
        // As deleted appraisal could be flagged as archived (old cycles).
        if (isset($archived)) {
            $archived = "AND aa.archived = {$archived}";
        } else {
            $archived = '';
        }

        $appraisee = $DB->sql_concat_join("' '", array('u.firstname', 'u.lastname'));
        $appraiser = $DB->sql_concat_join("' '", array('au.firstname', 'au.lastname'));
        $signoff = $DB->sql_concat_join("' '", array('su.firstname', 'su.lastname'));
        $groupleader = $DB->sql_concat_join("' '", array('gl.firstname', 'gl.lastname'));
        $sql = "
            SELECT
                aa.*,
                lac.created_date as latestcheckin,
                u.id as uid, {$appraisee} as appraisee, u.email as appraiseeemail, u.suspended, u.idnumber,
                au.id as auid, {$appraiser} as appraiser,
                su.id as suid, {$signoff} as signoff,
                gl.id as glid, {$groupleader} as groupleader
            FROM
                {local_appraisal_appraisal} aa
            JOIN
                {local_appraisal_cohort_apps} aca
                ON aca.appraisalid = aa.id
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
            WHERE
                u.icq = :ccid
                AND aa.statusid {$status}
                {$archived}
                AND aa.deleted = {$deleted}
                AND aca.cohortid = :cohortid
            ORDER BY
                u.lastname ASC, u.firstname ASC";

        $params = array(
            'ccid' => $this->groupid,
            'cohortid' => $this->cohortid,
        );
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get selectable users (of chosen type) within a group.
     *
     * @global \moodle_database $DB
     * @param string $type
     * @return array user objects
     */
    public function get_selectable_users($type) {
        global $DB;

        $basesql = "
            SELECT
                u.*
            FROM
                {user} u
            JOIN
                {local_costcentre_user} as lcu
                ON lcu.userid = u.id
            WHERE
                u.confirmed = :confirmed
                AND u.suspended = :suspended
                AND u.deleted = :deleted
                AND lcu.costcentre = :ccid
                AND [[bitand]] = :permission
            ORDER BY
                u.lastname ASC, u.firstname ASC
            ";

        $baseparams = array(
            'confirmed' => 1,
            'suspended' => 0,
            'deleted' => 0,
            'ccid' => $this->groupid,
        );

        switch ($type) {
            case 'appraiser' :
                $permission = costcentre::APPRAISER;
                // Need all users in costcentre.
                $params = array(
                    'confirmed' => 1,
                    'suspended' => 0,
                    'deleted' => 0,
                    'icq' => $this->groupid,
                );
                $switchusers = $DB->get_records('user', $params, 'lastname ASC, firstname ASC');
                break;
            case 'signoff' :
                $permission = costcentre::SIGNATORY;
                // Need to include group leader.
                $bitand = $DB->sql_bitand('lcu.permissions', costcentre::GROUP_LEADER);
                $params = $baseparams + array('permission' => costcentre::GROUP_LEADER);
                $sql = str_replace('[[bitand]]', $bitand, $basesql);
                $switchusers = $DB->get_records_sql($sql, $params);
                break;
            case 'groupleader' :
                $permission = costcentre::GROUP_LEADER;
                // Need to include appraisal specific group leaders.
                $bitand = $DB->sql_bitand('lcu.permissions', costcentre::GROUP_LEADER_APPRAISAL);
                $params = $baseparams + array('permission' => costcentre::GROUP_LEADER_APPRAISAL);
                $sql = str_replace('[[bitand]]', $bitand, $basesql);
                $switchusers = $DB->get_records_sql($sql, $params);
                break;
            default :
                return array();
        }

        // Now those with specific permission.
        $bitand = $DB->sql_bitand('lcu.permissions', $permission);
        $params = $baseparams + array('permission' => $permission);
        $sql = str_replace('[[bitand]]', $bitand, $basesql);
        $permusers = $DB->get_records_sql($sql, $params);

        // Merge arrays but maintain keys.
        $users = $switchusers + $permusers;

        // Only sort if necessary
        if (!empty($switchusers) && !empty($permusers)) {
            uasort($users, array($this, 'sort_users'));
        }

        return $users;
    }

    /**
     * Compare user objects (sort callback).
     *
     * @param stdClass $usera
     * @param stdClass $userb
     * @return int
     */
    private function sort_users($usera, $userb) {
        if ($usera->lastname < $userb->lastname) {
            return -1;
        } else if ($usera->lastname > $userb->lastname) {
            return 1;
        } else if ($usera->firstname < $userb->firstname) {
            return -1;
        } else if ($usera->firstname > $userb->firstname) {
            return 1;
        }
        return 0;
    }

    /**
     * Initialise an appraisal (called via AJAX).
     *
     * @global stdClass $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function initialise_appraisal() {
        global $CFG, $DB, $USER;

        require_once($CFG->libdir . '/filelib.php');

        $groupid = required_param('groupid', PARAM_ALPHANUMEXT);
        $cohortid = required_param('cohortid', PARAM_INT);
        $appraiseeid = required_param('appraiseeid', PARAM_INT);
        $appraiserid = required_param('appraiserid', PARAM_INT);
        $signoffid = required_param('signoffid', PARAM_INT);
        $groupleaderid = optional_param('groupleaderid', 0, PARAM_INT);
        $duedate = required_param('duedate', PARAM_INT);

        $appraisee = $DB->get_record('user', array('id' => $appraiseeid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        $appraiser = $DB->get_record('user', array('id' => $appraiserid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        $signoff = $DB->get_record('user', array('id' => $signoffid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));

        $caninitialise = $DB->get_record_select(
                'local_appraisal_cohort_ccs',
                'cohortid = :cohortid AND costcentre = :groupid AND locked > 0 AND closed IS NULL',
                ['cohortid' => $cohortid, 'groupid' => $groupid]);
        if (!$caninitialise) {
            // Can't initialise for this appraisal cycle.
            throw new moodle_exception('error:appraisalcycle:closed', 'local_onlineappraisal');
        }

        if ($groupleaderid && costcentre::get_cost_centre_groupleaderactive($appraisee->icq)) {
            $groupleader = $DB->get_record('user', array('id' => $groupleaderid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        } else {
            // Group leader not activated for this cost centre or no groupleaderid set, simply forcibly reset variables.
            $groupleaderid = null; // Null for DB record.
            $groupleader = false; // To match possible return from get_record().
        }

        if (empty($appraisee) || empty($appraiser) || empty($signoff) || ($groupleaderid && empty($groupleader))) {
            // Not valid users.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if (!$DB->get_record('local_appraisal_cohort_users', ['cohortid' => $cohortid, 'userid' => $appraiseeid])) {
            throw new moodle_exception('error:cohortuser', 'local_onlineappraisal');
        }

        if ($appraiser->id === $appraisee->id || $signoff->id === $appraisee->id || ($groupleader && $groupleader->id === $appraisee->id)) {
            // Cannot be own appraiser/sign off/group leader.
            throw new moodle_exception('error:appraiseeassuperior', 'local_onlineappraisal');
        }

        if ($groupleader && $groupleader->id === $signoff->id) {
            // Sign off cannot be groupleader.
            throw new moodle_exception('error:signoffasgroupleader', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $appraisee->icq)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisal:create', 'local_onlineappraisal');
        }

        if ($appraiser->icq != $appraisee->icq && !costcentre::is_user($appraiser->id, costcentre::APPRAISER, $appraisee->icq)) {
            // Not appraiser for this cost centre.
            throw new moodle_exception('error:appraisernotvalid', 'local_onlineappraisal');
        }

        if (!costcentre::is_user($signoff->id, array(costcentre::SIGNATORY, costcentre::GROUP_LEADER), $appraisee->icq)) {
            // Not sign off for this cost centre.
            throw new moodle_exception('error:signoffnotvalid', 'local_onlineappraisal');
        }

        if ($groupleader && !costcentre::is_user($groupleader->id, array(costcentre::GROUP_LEADER, costcentre::GROUP_LEADER_APPRAISAL), $appraisee->icq)) {
            // Not groupleader for this cost centre.
            throw new moodle_exception('error:groupleadernotvalid', 'local_onlineappraisal');
        }

        $existingparams = array(
            'appraisee_userid' => $appraisee->id,
            'archived' => 0,
            'deleted' => 0,
        );
        $existing = $DB->get_records('local_appraisal_appraisal', $existingparams);
        if (!empty($existing)) {
            // Active appraisal already exists.
            throw new moodle_exception('error:appraisalexists', 'local_onlineappraisal');
        }

        // New appraisal record.
        $record = new stdClass();
        $record->appraisee_userid = $appraisee->id;
        $record->appraiser_userid = $appraiser->id;
        $record->signoff_userid = $signoff->id;
        $record->groupleader_userid = $groupleaderid; // Use $groupleaderid as always set.
        $record->statusid = 1;
        $record->permissionsid = 1;
        $record->modified_date = $record->created_date = time();
        $record->due_date = $duedate;
        $record->status_history = '1';

        $query = "
            SELECT
                CORE_JOB_TITLE as jobtitle, GRADE as grade
            FROM
                SQLHUB.ARUP_ALL_STAFF_V
            WHERE
                EMPLOYEE_NUMBER = :idnumber
        ";
        $params= ['idnumber' => (int) $appraisee->idnumber];

        $hubdata = $DB->get_record_sql($query, $params);

        $record->job_title = !empty($hubdata->jobtitle) ? $hubdata->jobtitle : '';
        $record->grade = !empty($hubdata->grade) ? $hubdata->grade : '';

        $record->id = $DB->insert_record('local_appraisal_appraisal', $record);

        $return = new stdClass();
        $return->success = $record->id;
        $return->data = '';

        if ($return->success) {
            // Map appraisal to cohort.
            $cohortmap = new stdClass();
            $cohortmap->appraisalid = $record->id;
            $cohortmap->cohortid = $cohortid;
            $DB->insert_record('local_appraisal_cohort_apps', $cohortmap);

            $emailvars = new stdClass();
            $emailvars->appraiseefirstname = $appraisee->firstname;
            $emailvars->appraiseelastname = $appraisee->lastname;
            $emailvars->appraiseeemail = $appraisee->email;
            $emailvars->appraiserfirstname = $appraiser->firstname;
            $emailvars->appraiserlastname = $appraiser->lastname;
            $emailvars->appraiseremail = $appraiser->email;
            $emailvars->signofffirstname = $signoff->firstname;
            $emailvars->signofflastname = $signoff->lastname;
            $emailvars->signoffemail = $signoff->email;
            $emailvars->groupleaderfirstname = $groupleader ? $groupleader->firstname : '-';
            $emailvars->groupleaderlastname = $groupleader ? $groupleader->lastname : '-';
            $emailvars->groupleaderemail = $groupleader ? $groupleader->email : '-';
            $url = new \moodle_url(
                    '/local/onlineappraisal/view.php',
                    array('appraisalid' => $record->id, 'view' => 'appraisee', 'page' => 'overview')
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
            $emailvars->duedate = userdate($record->due_date, get_string('strftimedate'));
            $emailvars->status = get_string("status:{$record->statusid}", 'local_onlineappraisal');
            $emailvars->bafirstname = $USER->firstname;
            $emailvars->balastname = $USER->lastname;
            $emailvars->baemail = $USER->email;

            $appraiseeemail = new email('status:0_to_1:appraisee', $emailvars, $appraisee, $USER);
            if ($appraiseeemail->used_language() != current_language()) {
                $appraiseeemail->set_emailvar('duedate', self::userdate($appraiseeemail->used_language(), $record->due_date, 'strftimedate', '', new \DateTimeZone('UTC')));
            }
            $appraiseeemail->prepare();
            $appraiseeemailsent = $appraiseeemail->send();

            $appraiseremail = new email('status:0_to_1:appraiser', $emailvars, $appraiser, $USER);
            if ($appraiseremail->used_language() != current_language()) {
                $appraiseremail->set_emailvar('duedate', self::userdate($appraiseremail->used_language(), $record->due_date, 'strftimedate', '', new \DateTimeZone('UTC')));
            }
            $appraiseremail->prepare();
            $appraiseremailsent = $appraiseremail->send();

            // Add comment
            $a = new stdClass();
            $a->status = get_string('status:1', 'local_onlineappraisal');
            $a->relateduser = fullname($USER);
            $comment = comments::save_comment(
                    $record->id,
                    get_string(
                            'comment:status:0_to_1',
                            'local_onlineappraisal',
                            $a
                            )
                    );
            $return->message = get_string('success:appraisal:create', 'local_onlineappraisal');
            if (!$appraiseeemailsent) {
                $return->message .= get_string('error:appraisal:create:appraiseeemail', 'local_onlineappraisal');
            }
            if (!$appraiseremailsent) {
                $return->message .= get_string('error:appraisal:create:appraiseremail', 'local_onlineappraisal');
            }
            if (!$comment->id) {
                $return->message .= get_string('error:appraisal:create:comment', 'local_onlineappraisal');
            }
        } else {
            $return->message = get_string('error:appraisal:create', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Update appraisal appraiser and sign off user (called via AJAX).
     *
     * @global stdClass $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function update_appraisal() {
        global $DB, $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $appraiserid = required_param('appraiserid', PARAM_INT);
        $signoffid = required_param('signoffid', PARAM_INT);
        $groupleaderid = optional_param('groupleaderid', 0, PARAM_INT);
        $date = optional_param('date', null, PARAM_INT);

        $params = array(
            'id' => $appraisalid,
            'archived' => 0,
            'deleted' => 0,
        );
        $appraisal = $DB->get_record('local_appraisal_appraisal', $params);
        if (empty($appraisal)) {
            // The appraisal doesn't exist.
            throw new moodle_exception('error:loadappraisal', 'local_onlineappraisal');
        }

        $appraisee = $DB->get_record('user', array('id' => $appraisal->appraisee_userid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        $appraiser = $DB->get_record('user', array('id' => $appraiserid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        $signoff = $DB->get_record('user', array('id' => $signoffid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));

        if ($groupleaderid && costcentre::get_cost_centre_groupleaderactive($appraisee->icq)) {
            $groupleader = $DB->get_record('user', array('id' => $groupleaderid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));
        } else {
            // Group leader not activated for this cost centre or no groupledaerid set, simply forcibly reset variables.
            $groupleaderid = null; // Null for DB record.
            $groupleader = false; // To match possible return from get_record().
        }

        if (empty($appraisee) || empty($appraiser) || empty($signoff) || ($groupleaderid && empty($groupleader))) {
            // Not valid users.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if ($appraiser->id === $appraisal->appraiser_userid
                && $signoff->id === $appraisal->signoff_userid
                && $groupleaderid === $appraisal->groupleader_userid // Use $groupleaderid as will always be set.
                && $date == $appraisal->held_date) {
            // No changes made.
            throw new moodle_exception('error:nochanges', 'local_onlineappraisal');
        }

        if ($appraiser->id === $appraisee->id || $signoff->id === $appraisee->id || ($groupleader && $groupleader->id === $appraisee->id)) {
            // Cannot be own appraiser/sign off/group leader.
            throw new moodle_exception('error:appraiseeassuperior', 'local_onlineappraisal');
        }

        if ($groupleader && $groupleader->id === $signoff->id) {
            // Sign off cannot be groupleader.
            throw new moodle_exception('error:signoffasgroupleader', 'local_onlineappraisal');
        }

        if (!is_null($date) && $appraisal->face_to_face_held && $date != $appraisal->held_date) {
            // Cannot change date once held.
            throw new moodle_exception('error:f2fdate:update:held', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $appraisee->icq)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisal:update', 'local_onlineappraisal');
        }

        if ($appraiser->icq != $appraisee->icq && !costcentre::is_user($appraiser->id, costcentre::APPRAISER, $appraisee->icq)) {
            // Not appraiser for this cost centre.
            throw new moodle_exception('error:appraisernotvalid', 'local_onlineappraisal');
        }

        if (!costcentre::is_user($signoff->id, array(costcentre::SIGNATORY, costcentre::GROUP_LEADER), $appraisee->icq)) {
            // Not sign off for this cost centre.
            throw new moodle_exception('error:signoffnotvalid', 'local_onlineappraisal');
        }

        if ($groupleader && !costcentre::is_user($groupleader->id, array(costcentre::GROUP_LEADER, costcentre::GROUP_LEADER_APPRAISAL), $appraisee->icq)) {
            // Not groupleader for this cost centre.
            throw new moodle_exception('error:groupleadernotvalid', 'local_onlineappraisal');
        }

        // What's been updated?
        $appraiserupdated = ($appraisal->appraiser_userid !== $appraiser->id) ? $appraisal->appraiser_userid : false;
        $signoffupdated = ($appraisal->signoff_userid !== $signoff->id) ? $appraisal->signoff_userid : false;
        // Relaxed checking due to possible NULLs/empty.
        $groupleaderupdated = ($appraisal->groupleader_userid != $groupleaderid) ? $appraisal->groupleader_userid : false;

        // Update appraisal record.
        $appraisal->appraiser_userid = $appraiser->id;
        $appraisal->signoff_userid = $signoff->id;
        $appraisal->groupleader_userid = $groupleaderid; // Use $groupleaderid as always set.
        $appraisal->modified_date = time();
        if (!is_null($date)) {
            $appraisal->held_date = $date;
        }

        $return = new stdClass();
        $return->success = $DB->update_record('local_appraisal_appraisal', $appraisal);
        $return->data = '';

        if ($return->success) {
            // Add comments.
            $a = new stdClass();
            $a->ba = fullname($USER);
            // Send Emails.
            $emailvars = new stdClass();
            $emailvars->bafirstname = $USER->firstname;
            $emailvars->balastname = $USER->lastname;
            $emailvars->baemail = $USER->email;
            $emailvars->appraiseefirstname = $appraisee->firstname;
            $emailvars->appraiseelastname = $appraisee->lastname;
            $emailvars->appraiseeemail = $appraisee->email;
            if ($appraiserupdated) {
                $oldappraiser = $DB->get_record('user', array('id' => $appraiserupdated));
                $a->oldappraiser = fullname($oldappraiser);
                $a->newappraiser = fullname($appraiser);
                comments::save_comment($appraisal->id, get_string('comment:updated:appraiser', 'local_onlineappraisal', $a));
                // Email new appraiser, cc appraisee and old appraiser
                $emailvars->usertype = get_string('appraiser', 'local_onlineappraisal');
                $emailvars->newfirstname = $appraiser->firstname;
                $emailvars->newlastname = $appraiser->lastname;
                $emailvars->newemail = $appraiser->email;
                $emailvars->oldfirstname = $oldappraiser->firstname;
                $emailvars->oldlastname = $oldappraiser->lastname;
                $emailvars->oldemail = $oldappraiser->email;
                $cc = array($appraisee, $oldappraiser);
                $ccemails = array($appraisee->email, $oldappraiser->email);
                $emailvars->ccemails = implode(get_string('email:appraisal:update:ccseparator', 'local_onlineappraisal'), $ccemails);
                $appraiseremail = new email('appraisal:update', $emailvars, $appraiser, $USER, $cc);
                $appraiseremail->prepare();
                $appraiseremailsent = $appraiseremail->send();
            }
            if ($signoffupdated) {
                $oldsignoff = $DB->get_record('user', array('id' => $signoffupdated));
                $a->oldsignoff = fullname($oldsignoff);
                $a->newsignoff = fullname($signoff);
                comments::save_comment($appraisal->id, get_string('comment:updated:signoff', 'local_onlineappraisal', $a));
                // Email new signoff, cc appraisee and old signoff
                $emailvars->usertype = get_string('signoff', 'local_onlineappraisal');
                $emailvars->newfirstname = $signoff->firstname;
                $emailvars->newlastname = $signoff->lastname;
                $emailvars->newemail = $signoff->email;
                $emailvars->oldfirstname = $oldsignoff->firstname;
                $emailvars->oldlastname = $oldsignoff->lastname;
                $emailvars->oldemail = $oldsignoff->email;
                $cc = array($appraisee, $oldsignoff);
                $ccemails = array($appraisee->email, $oldsignoff->email);
                $emailvars->ccemails = implode(get_string('email:appraisal:update:ccseparator', 'local_onlineappraisal'), $ccemails);
                $signoffemail = new email('appraisal:update', $emailvars, $signoff, $USER, $cc);
                $signoffemail->prepare();
                $signoffemailsent = $signoffemail->send();
            }
            if ($groupleaderupdated !== false) { // Strict check as could be null which signifies unset.
                // Could be null before or after!
                $oldgroupleader = is_null($groupleaderupdated) ? false : $DB->get_record('user', array('id' => $groupleaderupdated));
                $a->oldgroupleader = $oldgroupleader ? fullname($oldgroupleader) : get_string('comment:updated:groupleader:empty', 'local_onlineappraisal');
                $a->newgroupleader = $groupleader ? fullname($groupleader) : get_string('comment:updated:groupleader:empty', 'local_onlineappraisal');
                comments::save_comment($appraisal->id, get_string('comment:updated:groupleader', 'local_onlineappraisal', $a));
                if ($groupleader) {
                    // Email new groupleader (if not null), cc appraisee and old groupleader (if not null)
                    $emailvars->usertype = get_string('groupleader', 'local_onlineappraisal');
                    $emailvars->newfirstname = $groupleader->firstname;
                    $emailvars->newlastname = $groupleader->lastname;
                    $emailvars->newemail = $groupleader->email;
                    $emailvars->oldfirstname = $oldgroupleader ? $oldgroupleader->firstname : '';
                    $emailvars->oldlastname = $oldgroupleader ? $oldgroupleader->lastname : get_string('comment:updated:groupleader:empty', 'local_onlineappraisal');;
                    $emailvars->oldemail = $oldgroupleader ? $oldgroupleader->email : '';
                    $cc = array($appraisee);
                    $ccemails = array($appraisee->email);
                    if ($oldgroupleader) {
                        $cc[] = $oldgroupleader;
                        $ccemails[] = $oldgroupleader->email;
                    }
                    $emailvars->ccemails = implode(get_string('email:appraisal:update:ccseparator', 'local_onlineappraisal'), $ccemails);
                    $groupleaderemail = new email('appraisal:update', $emailvars, $groupleader, $USER, $cc);
                    $groupleaderemail->prepare();
                    $groupleaderemailsent = $groupleaderemail->send();
                }
            }
            $return->message = get_string('success:appraisal:update', 'local_onlineappraisal');
            if (isset($appraiseremailsent) && !$appraiseremailsent) {
                $return->message .= get_string('error:appraisal:update:appraiseremail', 'local_onlineappraisal');
            }
            if (isset($signoffemailsent) && !$signoffemailsent) {
                $return->message .= get_string('error:appraisal:update:signoffemail', 'local_onlineappraisal');
            }
            if (isset($groupleaderemailsent) && !$groupleaderemailsent) {
                $return->message .= get_string('error:appraisal:update:groupleaderemail', 'local_onlineappraisal');
            }
        } else {
            $return->message = get_string('error:appraisal:update', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Delete appraisal (called via AJAX).
     * Can be flagged as deleted or permanently deleted.
     *
     * @global stdClass $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function delete_appraisal() {
        global $DB, $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $method = optional_param('method', 'flag', PARAM_ALPHA);

        if ($method === 'permanent' && !has_capability('local/onlineappraisal:deleteappraisal', \context_system::instance())) {
            throw new moodle_exception('error:permission:appraisal:delete', 'local_onlineappraisal');
        }

        $params = array(
            'id' => $appraisalid,
            'archived' => 0,
            'deleted' => (int) ($method === 'permanent'), // Can only permanently delete appraisals flagged as deleted.
        );
        $appraisal = $DB->get_record('local_appraisal_appraisal', $params);
        if (empty($appraisal)) {
            // The appraisal doesn't exist.
            throw new moodle_exception('error:loadappraisal', 'local_onlineappraisal');
        }

        $appraisee = $DB->get_record('user', array('id' => $appraisal->appraisee_userid));

        if (empty($appraisee)) {
            // Not valid users.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $appraisee->icq)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisal:delete', 'local_onlineappraisal');
        }

        $return = new stdClass();
        $return->data = '';

        if ($method === 'permanent') {
            // Delete appraisal record.
            $return->success = $DB->delete_records('local_appraisal_appraisal', array('id' => $appraisal->id));
            $DB->delete_records('local_appraisal_comment', array('appraisalid' => $appraisal->id));
            $DB->delete_records('local_appraisal_feedback', array('appraisalid' => $appraisal->id));
            foreach ($DB->get_records('local_appraisal_forms', array('appraisalid' => $appraisal->id)) as $form) {
                // Delete appraisal form data.
                $DB->delete_records('local_appraisal_data', array('form_id' => $form->id));
            }
            // Delete appraisal form instances.
            $DB->delete_records('local_appraisal_forms', array('appraisalid' => $appraisal->id));
            // Legacy tables.
            $DB->delete_records('local_appraisal_dev_objectiv', array('appraisalid' => $appraisal->id));
            $DB->delete_records('local_appraisal_per_objectiv', array('appraisalid' => $appraisal->id));
            $DB->delete_records('local_appraisal_summary', array('appraisalid' => $appraisal->id));
        } else {
            // Flag appraisal record as deleted.
            $appraisal->deleted = 1;
            $appraisal->modified_date = time();
            $return->success = $DB->update_record('local_appraisal_appraisal', $appraisal);
        }

        if ($return->success) {
            $return->message = get_string('success:appraisal:delete', 'local_onlineappraisal');
        } else {
            $return->message = get_string('error:appraisal:delete', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Toggle appraisal required.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     */
    public static function toggle_appraisal_required() {
        global $DB, $USER;

        $userid = required_param('userid', PARAM_INT);
        $confirm = optional_param('confirm', false, PARAM_BOOL);
        $reason = optional_param('reason', '', PARAM_TEXT);

        $user = $DB->get_record('user', array('id' => $userid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));

        if (empty($user)) {
            // Not valid user.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $user->icq)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisal:toggle', 'local_onlineappraisal');
        }

        $return = new stdClass();

        $notrequired = $DB->get_record('local_appraisal_notrequired', array('userid' => $user->id, 'superseded' => null));

        $existingparams = array(
            'appraisee_userid' => $userid,
            'archived' => 0,
            'deleted' => 0,
        );
        $existing = $DB->get_records('local_appraisal_appraisal', $existingparams);

        // Only worry about archiving/deleting if switching to appraisal not required.
        if (!$confirm) {
            $return->success = false;
            $return->data = 'confirm';
            $a = new stdClass();
            $a->yes = \html_writer::link(
                    '#',
                    get_string('form:confirm:cancel:yes', 'local_onlineappraisal'),
                    array('class' => 'btn btn-primary m-t-5 oa-toggle-required-confirm', 'data-userid' => $userid, 'data-confirm' => 1)
                    );
            $a->no = \html_writer::link(
                    '#',
                    get_string('form:confirm:cancel:no', 'local_onlineappraisal'),
                    array('class' => 'btn btn-default m-t-5 oa-toggle-required-confirm', 'data-userid' => $userid, 'data-confirm' => 0)
                    );
            if (!$notrequired && $existing) {
                // Active appraisal exists.
                $return->message = get_string('error:togglerequired:confirmnotrequired:appraisalexists', 'local_onlineappraisal', $a);
            } else if (!$notrequired) {
                // Confirm making not required.
                $return->message = get_string('error:togglerequired:confirmnotrequired', 'local_onlineappraisal', $a);
            } else {
                // Confirm making required.
                $return->message = get_string('error:togglerequired:confirmrequired', 'local_onlineappraisal', $a);
            }
            return $return;
        } else if (!$notrequired && !$reason) {
            // Now need to check reason if setting not required.
            $return->success = false;
            $return->data = 'reason';
            $a = new stdClass();
            $a->reasonfield = \html_writer::empty_tag(
                    'input',
                    ['type' => 'text', 'name' => 'reason', 'class' => 'form-control']);
            $a->continue = \html_writer::link(
                    '#',
                    get_string('error:togglerequired:reason:continue', 'local_onlineappraisal'),
                    array('class' => 'btn btn-primary m-t-5 oa-toggle-required-reason', 'data-userid' => $userid, 'data-reason' => 1)
                    );
            $a->cancel = \html_writer::link(
                    '#',
                    get_string('error:togglerequired:reason:cancel', 'local_onlineappraisal'),
                    array('class' => 'btn btn-default m-t-5 oa-toggle-required-reason', 'data-userid' => $userid, 'data-reason' => 0)
                    );
            $return->message = get_string('error:togglerequired:reason', 'local_onlineappraisal', $a);
            return $return;
        } else if (!empty($existing) && $confirm) {
            // Archive/delete first.
            foreach ($existing as $appraisal) {
                if ($appraisal->statusid == APPRAISAL_NOT_STARTED) {
                    $appraisal->deleted = 1;
                } else {
                    $appraisal->archived = 1;
                }
                $DB->update_record('local_appraisal_appraisal', $appraisal);
            }
        }

        if ($notrequired) {
            // Toggle.
            $notrequired->superseded = time();
            $notrequired->supersededby = $USER->id;
            $return->success = $DB->update_record('local_appraisal_notrequired', $notrequired);
        } else {
            $notrequired = new stdClass();
            $notrequired->userid = $user->id;
            $notrequired->reason = $reason;
            $notrequired->timecreated = time();
            $notrequired->createdby = $USER->id;
            $return->success = $notrequired->id = $DB->insert_record('local_appraisal_notrequired', $notrequired);
        }

        if ($return->success) {
            $cccohort = $DB->get_record_select(
                    'local_appraisal_cohort_ccs',
                    'costcentre = :icq AND locked > 0 AND closed IS NULL',
                    ['icq' => $user->icq]);
            if ($cccohort && !empty($notrequired->superseded)
                    && !$DB->get_record('local_appraisal_cohort_users', ['cohortid' => $cccohort->cohortid, 'userid' => $user->id])) {
                // Assign to cycle.
                $assign = new stdClass();
                $assign->cohortid = $cccohort->cohortid;
                $assign->userid = $user->id;
                $DB->insert_record('local_appraisal_cohort_users', $assign);
            } else if ($cccohort && empty($notrequired->superseded)) {
                // Un-assign from cycle.
                $DB->delete_records('local_appraisal_cohort_users', ['cohortid' => $cccohort->cohortid, 'userid' => $user->id]);
            }
            $identifier = !empty($notrequired->superseded) ? 'admin:appraisalrequired' : 'admin:appraisalnotrequired';
            $return->data = get_string($identifier, 'local_onlineappraisal');
            $return->message = get_string('success:appraisal:toggle', 'local_onlineappraisal');
        } else {
            $return->message = get_string('error:appraisal:toggle', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Toggle appraisal VIP.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     */
    public static function toggle_appraisal_vip() {
        global $DB, $USER;

        $userid = required_param('userid', PARAM_INT);

        $user = $DB->get_record('user', array('id' => $userid, 'confirmed' => 1, 'suspended' => 0, 'deleted' => 0));

        if (empty($user)) {
            // Not valid user.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, costcentre::HR_LEADER, $user->icq)) {
            // Not Admin/HR Leader on this cost centre.
            throw new moodle_exception('error:permission:appraisal:togglevip', 'local_onlineappraisal');
        }

        $return = new stdClass();

        $record = $DB->get_record('local_appraisal_users', array('userid' => $user->id, 'setting' => 'appraisalvip'));

        if ($record) {
            // Toggle.
            $record->value = !$record->value;
            $return->success = $DB->update_record('local_appraisal_users', $record);
        } else {
            $record = new stdClass();
            $record->userid = $user->id;
            $record->setting = 'appraisalvip';
            // Must be setting as VIP.
            $record->value = 1;
            $return->success = $record->id = $DB->insert_record('local_appraisal_users', $record);
        }

        if ($return->success) {
            $identifier = $record->value ? 'admin:appraisalnotvip' : 'admin:appraisalvip';
            $return->data = get_string($identifier, 'local_onlineappraisal');
            $return->message = get_string('success:appraisal:togglevip', 'local_onlineappraisal');
        } else {
            $return->message = get_string('error:appraisal:togglevip', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Assign/un-assign user to/from the current appraisal cycle.
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     */
    public static function appraisalcycle_assign() {
        global $DB, $USER;

        $userid = required_param('userid', PARAM_INT);
        $assign = required_param('assign', PARAM_BOOL);
        $confirm = optional_param('confirm', false, PARAM_BOOL);
        $reason = optional_param('reason', '', PARAM_TEXT);

        // Can be suspended if unassigning.
        $params = [
            'id' => $userid,
            'confirmed' => 1,
            'deleted' => 0,
        ];
        if ($assign) {
            $params['suspended'] = 0;
        }
        $user = $DB->get_record('user', $params);

        if (empty($user)) {
            // Not valid user.
            throw new moodle_exception('error:loadusers', 'local_onlineappraisal');
        }

        if (!has_capability('local/costcentre:administer', \context_system::instance())
                && !costcentre::is_user($USER->id, array(costcentre::BUSINESS_ADMINISTRATOR, costcentre::HR_LEADER, costcentre::HR_ADMIN), $user->icq)) {
            // Not BA/Admin/HR on this cost centre.
            throw new moodle_exception('error:permission:appraisal:toggle', 'local_onlineappraisal');
        }

        $return = new stdClass();

        $existingparams = array(
            'appraisee_userid' => $userid,
            'archived' => 0,
            'deleted' => 0,
        );
        $existing = $DB->get_records('local_appraisal_appraisal', $existingparams);

        // Only worry about archiving/deleting if switching to appraisal not required.
        if (!$confirm) {
            $return->success = false;
            $return->data = 'confirm';
            $a = new stdClass();
            $a->yes = \html_writer::link(
                    '#',
                    get_string('form:confirm:cancel:yes', 'local_onlineappraisal'),
                    array('class' => 'btn btn-primary m-t-5 oa-toggle-assign-confirm', 'data-userid' => $userid, 'data-confirm' => 1)
                    );
            $a->no = \html_writer::link(
                    '#',
                    get_string('form:confirm:cancel:no', 'local_onlineappraisal'),
                    array('class' => 'btn btn-default m-t-5 oa-toggle-assign-confirm', 'data-userid' => $userid, 'data-confirm' => 0)
                    );
            if ((!$assign) && $existing) {
                // Active appraisal exists.
                $return->message = get_string('error:toggleassign:confirm:unassign:appraisalexists', 'local_onlineappraisal', $a);
            } else if (!$assign) {
                // Confirm unassigning.
                $return->message = get_string('error:toggleassign:confirm:unassign', 'local_onlineappraisal', $a);
            } else {
                // Confirm assigning.
                $return->message = get_string('error:toggleassign:confirm:assign', 'local_onlineappraisal', $a);
            }
            return $return;
        } else if (!$assign && !$reason) {
            // Now need to check reason if unassigning.
            $return->success = false;
            $return->data = 'reason';
            $a = new stdClass();
            $a->reasonfield = \html_writer::empty_tag(
                    'input',
                    ['type' => 'text', 'name' => 'reason', 'class' => 'form-control']);
            $a->continue = \html_writer::link(
                    '#',
                    get_string('error:toggleassign:reason:continue', 'local_onlineappraisal'),
                    array('class' => 'btn btn-primary m-t-5 oa-toggle-assign-reason', 'data-userid' => $userid, 'data-reason' => 1)
                    );
            $a->cancel = \html_writer::link(
                    '#',
                    get_string('error:toggleassign:reason:cancel', 'local_onlineappraisal'),
                    array('class' => 'btn btn-default m-t-5 oa-toggle-assign-reason', 'data-userid' => $userid, 'data-reason' => 0)
                    );
            $return->message = get_string('error:toggleassign:reason', 'local_onlineappraisal', $a);
            return $return;
        } else if (!empty($existing) && $confirm) {
            // Archive/delete first.
            foreach ($existing as $appraisal) {
                if ($appraisal->statusid == APPRAISAL_NOT_STARTED) {
                    $appraisal->deleted = 1;
                } else {
                    $appraisal->archived = 1;
                }
                $DB->update_record('local_appraisal_appraisal', $appraisal);
            }
        }

        // General success - may yet fail.
        $return->success = true;

        $notrequired = $DB->get_record('local_appraisal_notrequired', array('userid' => $user->id, 'superseded' => null));

        if ($notrequired) {
            // Toggle.
            $notrequired->superseded = time();
            $notrequired->supersededby = $USER->id;
            $return->success = $DB->update_record('local_appraisal_notrequired', $notrequired);
        }
        if (!$assign) {
            $notrequired = new stdClass();
            $notrequired->userid = $user->id;
            $notrequired->reason = $reason;
            $notrequired->timecreated = time();
            $notrequired->createdby = $USER->id;
            $return->success = $notrequired->id = $DB->insert_record('local_appraisal_notrequired', $notrequired);
        }

        if ($return->success) {
            $cccohort = $DB->get_record_select(
                    'local_appraisal_cohort_ccs',
                    'costcentre = :icq AND locked > 0 AND closed IS NULL',
                    ['icq' => $user->icq]);
            if ($cccohort && $assign) {
                // Assign to cycle.
                if (!$DB->get_record('local_appraisal_cohort_users', ['cohortid' => $cccohort->cohortid, 'userid' => $user->id])) {
                    $usermap = new stdClass();
                    $usermap->cohortid = $cccohort->cohortid;
                    $usermap->userid = $user->id;
                    $return->success = $DB->insert_record('local_appraisal_cohort_users', $usermap);
                }
                // Find existing appraisal (latest if more than one - will only occur for appraisals from before upgrade).
                $select = 'appraisalid IN (SELECT id FROM {local_appraisal_appraisal} WHERE appraisee_userid = :userid AND deleted = 0) AND cohortid = :cohortid';
                $appraisaltoupdate = $DB->get_field_select(
                        'local_appraisal_cohort_apps',
                        'MAX(appraisalid) AS id',
                        $select,
                        ['userid' => $user->id, 'cohortid' => $cccohort->cohortid]);
                if ($appraisaltoupdate) {
                    $updatesql = 'UPDATE {local_appraisal_appraisal} SET archived = 0 WHERE id = :id';
                    $DB->execute($updatesql, ['id' => $appraisaltoupdate]);
                }
            } else if ($cccohort && !$assign) {
                // Un-assign from cycle.
                $return->success = $DB->delete_records('local_appraisal_cohort_users', ['cohortid' => $cccohort->cohortid, 'userid' => $user->id]);
            }
        }

        if ($return->success) {
            // URL to redirect to.
            $return->data = (new moodle_url(
                    '/local/onlineappraisal/admin.php',
                    ['page' => 'allstaff', 'groupid' => $user->icq, 'cohortid' => $cccohort->cohortid]
                    ))->out(false);
            // Return message not used but set for session based alert.
            $a = fullname($user);
            if ($assign && !empty($appraisaltoupdate)) {
                // Assigned.
                $return->message = get_string('success:appraisalcycle:assign:reactivated', 'local_onlineappraisal', $a);
            } else if ($assign) {
                // Assigned.
                $return->message = get_string('success:appraisalcycle:assign', 'local_onlineappraisal', $a);
            } else if ($user->suspended) {
                // Unassigned && Suspended
                $return->message = get_string('success:appraisalcycle:unassign:suspended', 'local_onlineappraisal', $a);
            } else {
                // Unassigned
                $return->message = get_string('success:appraisalcycle:unassign', 'local_onlineappraisal', $a);
            }
            // Set an alert for when reloaded.
            appraisal::set_alert($return->message, 'success');
        } else {
            $return->message = get_string('error:appraisal:toggle', 'local_onlineappraisal');
        }

        return $return;
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