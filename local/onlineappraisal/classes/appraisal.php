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
use moodle_exception;
use local_costcentre\costcentre as costcentre;
use local_onlineappraisal\permissions as permissions;
use local_onlineappraisal\output\alert as alert;

class appraisal {
    const ACTION_NOT_DONE = 0;
    const ACTION_DONE_SUCCESS = 1;
    const ACTION_DONE_FAILURE = 2;

    const HR_NONE = 0;
    const HR_BEFORE = 1;
    const HR_AFTER = 2;

    public $pagetitle;
    public $pageheading;
    public $pages;
    public $called;

    private $user;
    private $appraisalid;
    private $appraisal;
    private $page;
    private $formid;
    private $form;

    private $renderer;

    /**
     * Allowed user types and whether user must be set/exist.
     * @var array $types
     */
    private static $types = array (
        'appraisee' => array('required' => true, 'load' => true),
        'appraiser' => array('required' => true, 'load' => true),
        'signoff' => array('required' => true, 'load' => true),
        'groupleader' => array('required' => false, 'load' => true),
        'hrleader' => array('required' => false, 'load' => false),
        'guest' => array('required' => false, 'load' => false),
    );

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param object $user the full user object
     * @param int $appraisalid the id of the requested appraisal
     * @param string $viewingas the type of user viewing as
     * @param string page the name of the page
     * @param int $formid the id of the form
     */
    public function __construct($user, $appraisalid, $viewingas, $page, $formid) {
        global $PAGE;

        $this->user = $user;
        $this->appraisalid = $appraisalid;
        $this->page = $page;
        $this->formid = $formid;

        // Load appraisal and inject users.
        $this->load_appraisal($viewingas);

        // Check if this user is allowed to view this appraisal.
        if (!$this->can_view_appraisal()) {
            print_error('error:noaccess', 'local_onlineappraisal');
        }

        // Initially set an empty action.
        $this->set_action();

        // Serve the default welcome page if appraisee and new appraisal.
        if ($this->appraisal->statusid == 1 && $this->appraisal->viewingas == 'appraisee') {
            $this->page = 'introduction';
        }

        // Set up pages for navigation.
        $this->appraisal_pages();

        // Finally set up renderer.
        $this->renderer = $PAGE->get_renderer('local_onlineappraisal', 'dashboard');
    }

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
     * Load requested appraisal instance
     *
     * @global \moodle_database $DB
     * @param string $viewingas the type of user viewing as
     */
    private function load_appraisal($viewingas) {
        global $DB;

        if ($this->appraisalid) {
            $this->appraisal = $DB->get_record('local_appraisal_appraisal', array('id' => $this->appraisalid, 'deleted' => 0));
        } else {
            $select = 'appraisee_userid = :uid AND deleted = :deleted';
            $params = array('uid' => $this->user->id, 'deleted' => 0);
            $sort = 'created_date DESC';
            $appraisal = $DB->get_records_select('local_appraisal_appraisal', $select, $params, $sort, '*', 0, 1);
            $this->appraisal = reset($appraisal);
        }

        if (!$this->appraisal) {
            print_error('error:loadappraisal', 'local_onlineappraisal');
        }

        // Check groupleader active flag and hrleaders.
        $costcentre = $DB->get_field('user', 'icq', array('id' => $this->appraisal->appraisee_userid));
        if (!empty($this->appraisal->groupleader_userid) && !costcentre::get_cost_centre_groupleaderactive($costcentre)) {
            $this->appraisal->groupleader_userid = null;
        }

        // Set type user is trying to view as.
        $this->appraisal->viewingas = !key_exists($viewingas, self::$types) ? 'appraisee' : $viewingas;

        // Is VIP?
        $this->appraisal->isvip = $DB->get_field('local_appraisal_users', 'value', array('userid' => $this->appraisal->appraisee_userid, 'setting' => 'appraisalvip'));

        $this->appraisal_users($costcentre);

        $this->appraisal_actions();
    }

    /**
     * Check the permissions wrapper function
     * example of permission check: feedback:add
     *
     * @param string $permission
     * @return bool true / false
     */
    public function check_permission($permission) {
        // Checks against the current permissionsid of appraisal.
        return permissions::is_allowed($permission, $this->appraisal->permissionsid, $this->appraisal->viewingas, $this->appraisal->archived, $this->appraisal->legacy);
    }

    /**
     * Load users for requested appraisal instance and add variables to the appraisal object
     * in the form of:
     * is_appraisee, is_appraiser, etc.
     *
     * @param string $costcentre
     */
    private function appraisal_users($costcentre) {
        global $DB;

        foreach (self::$types as $type => $info) {
            $isuser = "is_{$type}";

            // Preset to false.
            $this->appraisal->$isuser = false;

            if ($info['load']) {
                $typeuid = "{$type}_userid";
                $userid = !empty($this->appraisal->$typeuid) ? $this->appraisal->$typeuid : null;

                $this->appraisal->{$type} = $userid ? $DB->get_record('user', array('id' => $userid)) : false;

                if ($info['required'] && !$this->appraisal->{$type}) {
                    print_error('error:loadusers', 'local_onlineappraisal');
                }

                if ($userid == $this->user->id) {
                    $this->appraisal->$isuser = true;
                }
            }
        }

        // Special cases.
        $this->appraisal->is_guest = true; // Everyone can be a guest.
        if (!$this->appraisal->is_groupleader && !$this->appraisal->isvip) {
            // Could be a generic groupleader (but only if not a VIP appraisal).
            $this->appraisal->is_groupleader = array_key_exists($this->user->id, costcentre::get_cost_centre_users($costcentre, costcentre::GROUP_LEADER));
        }
        // Is hrleader?
        $this->appraisal->is_hrleader = array_key_exists($this->user->id, costcentre::get_cost_centre_users($costcentre, costcentre::HR_LEADER));
        if (!$this->appraisal->is_hrleader && !$this->appraisal->isvip) {
            // HR_ADMIN allowed access if not a VIP appraisal.
            $this->appraisal->is_hrleader = array_key_exists($this->user->id, costcentre::get_cost_centre_users($costcentre, costcentre::HR_ADMIN));
        }
        // Access to old appraisals for current appraiser.
        if ($this->appraisal->viewingas === 'appraiser' && !$this->appraisal->is_appraiser && $this->appraisal->archived) {
            // Are they appraiser for this appraisee's current appraisal?
            $this->appraisal->is_appraiser = (bool) $DB->count_records(
                    'local_appraisal_appraisal',
                    [
                        'appraisee_userid' => $this->appraisal->appraisee_userid,
                        'appraiser_userid' => $this->user->id,
                        'archived' => 0,
                        'deleted' => 0,
                    ]);
        }
    }

    /**
     * Appraisal actions.
     */
    private function appraisal_actions() {
        $action = optional_param('appraisalaction', '', PARAM_TEXT);
        if ($action == 'start') {
            $stages = new \local_onlineappraisal\stages($this);
            if ($stages->is_valid_update_path(APPRAISEE_DRAFT)) {
                $stages->update_status(APPRAISEE_DRAFT);
                // @Bas: $stages->errors will return an array of error objects with the properties message, first and last.
                // First and last flags are handy for when looping through in template :-).
            }
            // Redirect to 'overview' page.
            $reloadurl = new moodle_url(
                    '/local/onlineappraisal/view.php',
                    array('appraisalid' => $this->appraisal->id, 'page' => 'overview', 'view' => $this->appraisal->viewingas)
                    );
            redirect($reloadurl);
        }
    }

    /**
     * Check permissions for the current appraisal
     *
     * @return bool true if user can view.
     */
    private function can_view_appraisal() {

        if (empty($this->appraisal)) {
            print_error('error:noappraisal', 'local_onlineappraisal');
        }

        foreach (array_keys(self::$types) as $type) {
            $isuser = "is_{$type}";
            if ($this->appraisal->$isuser && $this->appraisal->viewingas == $type) {
                return true;
            }
        }
        return false;

    }

    /**
     * Define the configured pages.
     */
    public function appraisal_pages() {
        $this->pages = array();
        $this->add_page('dashboard', 'introduction', false, false, true, false);
        $this->add_page('dashboard', 'overview', false, 'overview');
        $this->add_page('form', 'userinfo');
        $this->add_page('dashboard', 'feedback', 'feedback', 'feedback');
        $this->add_page('form', 'lastyear');
        $this->add_page('form', 'careerdirection');
        $this->add_page('form', 'impactplan');
        $this->add_page('form', 'development');
        $this->add_page('form', 'summaries');
        $this->add_page('form', 'sixmonth');
        $this->add_page('dashboard', 'checkin', false, 'checkins', true, false, self::HR_AFTER);
        $this->add_page('form', 'successionplan');
        $this->add_page('form', 'leaderplan', false, 'leaderplan');
        $this->add_page('dashboard', 'help', false, false, true, false, self::HR_BEFORE);
        $this->add_page('dashboard', 'addfeedback', 'addfeedback', false, false, false);

        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_onlineappraisal');
        }
    }

    /**
     * Create form objects for later use in the appraisal
     * navigation structure
     *
     * @param string $type The page types, these are used when rendering the content
     * @param string $name A unique name for this page. Used in navigation
     * @param string $preloadform Preload this form if page is not of type form
     * @param string $hook Classname to load when this page is added. This class needs to define
     * a method called hook.
     * @param boolean $showinnav Whether to show in navigation or not.
     * @param boolean $redirectto Whether to allow redirection to page on 'save and continue'.
     */
    private function add_page($type, $name, $preloadform = false, $hook = false, $showinnav = true, $redirectto = true, $hr = self::HR_NONE) {
        global $DB;

        $page = new stdClass();

        $viewpermission = $name . ':view';
        $addpermission = $name . ':add';

        if ($type == 'form') {
            // These permissions _must_ be set for forms.
            $page->view = $this->check_permission($viewpermission);
            $page->add = $this->check_permission($addpermission);
        } else {
            if (permissions::exists($viewpermission)) {
                $page->view = $this->check_permission($viewpermission);
            } else {
                // Fallback to generic appraisal:view permission.
                $page->view = $this->check_permission('appraisal:view');
            }
            // Will be false if doesn't exist.
            $page->add = $this->check_permission($addpermission);
        }

        if (!$page->view && !$page->add) {
            // Don't add it as no view/add access.
            return;
        }

        $page->name = $name;
        $page->type = $type;

        // Special cases...
        switch ($page->name) {
            case 'successionplan':
                // Check if enabled for appraisal.
                if (empty($this->appraisal->successionplan)) {
                    return;
                }
                break;
            case 'leaderplan':
                // Check if enabled for appraisal.
                if (empty($this->appraisal->leaderplan)) {
                    return;
                }
                break;
            case 'help':
                $url = get_config('local_onlineappraisal', 'helpurl');
                if ($url) {
                    $page->url = new moodle_url($url);
                    $page->popup = true;
                }
                break;
        }

        // Default/fallback.
        if (empty($page->url)) {
            $page->url = new moodle_url('/local/onlineappraisal/view.php', array('page' => $name, 'appraisalid' => $this->appraisal->id, 'view' => $this->appraisal->viewingas));
        }
        $page->preloadform = $preloadform;
        $page->showinnav = $showinnav;
        $page->redirectto = $redirectto;

        $page->active = '';
        if ($this->page == $name) {
            $page->active = 'active';
        }

        $page->hr = $hr;

        $this->pages[$name] = $page;

        // Run hooks last, after page added to pages array.
        // Add checking checkins hook for leaderplan page.
        if (($hook && $this->page == $name) || ($hook == 'checkins' && $this->page == 'leaderplan')) {
            $class = "\\local_onlineappraisal\\$hook";
            $classinstance = new $class($this);
            $classinstance->hook();
    }
    }

    /**
     * Get the next appraisal page.
     */
    public function get_nextpage() {

        $nexturl = new moodle_url('/local/onlineappraisal/view.php');
        // Filter out pages that are not part of redirection cycle.
        $pages = array_filter($this->pages, function($v){return $v->redirectto;});
        // Grab the firstpage as may be needed if redirecting from last page.
        $firstpage = reset($pages);

        foreach ($pages as $page) {
            // Make sure we're also cycling through the array one step ahead...
            next($pages);
            if ($this->page == $page->name) {
                // Grab next page or first page if on last page.
                $nextpage = current($pages) ? current($pages) : $firstpage;
                if ($nextpage) {
                    $nexturl = new moodle_url(
                            '/local/onlineappraisal/view.php',
                            array('page' => $nextpage->name,
                                'appraisalid' => $this->appraisal->id,
                                'view' => $this->appraisal->viewingas)
                            );
                    return $nexturl;
                }
            }
        }
        return $nexturl;
    }

    /**
     * Redirect the user to the next appraisal page.
     * navigation structure.
     */
    public function redirect_to_nextpage() {
        $nexturl = $this->get_nextpage();
        redirect($nexturl);
    }

    /**
     * Prepare the online appraisal page and support a form if required.
     */
    public function prepare_page() {
        // Prepare form if required.
        $page = $this->pages[$this->page];

        if ($page->type == 'form' || $page->preloadform) {
            $aform = new \local_onlineappraisal\forms($this);
            $aform->get_form();
            $this->form = $aform->form;
        }

        // Add print menu/button HTML to page (to appear alongside breadcrumb).
        $this->add_print_menu();
    }

    /**
     * Return alert should current language not be the site default.
     *
     * @global stdClass $CFG
     * @return string html
     */
    public function language_alert() {
        global $CFG;

        $defaultlang = isset($CFG->lang) ? $CFG->lang : 'en';

        // Alert only if language changed from default.
        if (current_language() != $defaultlang) {
            $alert = new alert(
                    get_string('alert:language:notdefault', 'local_onlineappraisal'),
                    get_string('alert:language:notdefault:type', 'local_onlineappraisal'),
                    false);
            return $this->renderer->render($alert);
        }

        return '';
    }

    /**
     * Returns print button/dropdown html if applicable for current user.
     *
     * @return string html
     */
    private function add_print_menu() {
        global $DB, $PAGE;

        $printvars = new stdClass();
        $printvars->options = array();

        $printurl = new moodle_url(
                '/local/onlineappraisal/print.php',
                array(
                    'appraisalid' => $this->appraisal->id,
                    'view' => $this->appraisal->viewingas
                )
            );

        $options = array('appraisal' => 'appraisal', 'feedback' => 'feedback', 'feedbackown' => 'feedback', 'successionplan' => 'successionplan', 'leaderplan' => 'leaderplan');
        foreach ($options as $permission => $option) {
            if (permissions::is_allowed("{$permission}:print", $this->appraisal->permissionsid, $this->appraisal->viewingas, $this->appraisal->archived, $this->appraisal->legacy)) {
                $object = new stdClass();
                $printurl->param('print', $option);
                $object->url = $printurl->out(false);
                $object->text = get_string("print:button:{$option}", 'local_onlineappraisal');
                $printvars->options[$permission] = clone($object);
            }
        }

        if (isset($printvars->options['successionplan'])) {
            // Only display SDP download link if has been saved.
            $sql = "SELECT COUNT(lad.id)
                  FROM {local_appraisal_data} lad
                  JOIN {local_appraisal_forms} laf ON laf.id = lad.form_id
                 WHERE laf.form_name = :form_name
                       AND laf.appraisalid = :appraisalid
                       AND laf.user_id = :user_id";
            $params = [
                'form_name' => 'successionplan',
                'appraisalid' => $this->appraisal->id,
                'user_id' => $this->appraisal->appraisee->id,
            ];
            if ($DB->count_records_sql($sql, $params) === 0) {
                // Not yet saved, remove SDP download link.
                unset($printvars->options['successionplan']);
            }
        }

        if (isset($printvars->options['leaderplan'])) {
            // Only display SDP download link if has been saved.
            $sql = "SELECT COUNT(lad.id)
                  FROM {local_appraisal_data} lad
                  JOIN {local_appraisal_forms} laf ON laf.id = lad.form_id
                 WHERE laf.form_name = :form_name
                       AND laf.appraisalid = :appraisalid
                       AND laf.user_id = :user_id";
            $params = [
                'form_name' => 'leaderplan',
                'appraisalid' => $this->appraisal->id,
                'user_id' => $this->appraisal->appraisee->id,
            ];
            if ($DB->count_records_sql($sql, $params) === 0) {
                // Not yet saved, remove SDP download link.
                unset($printvars->options['leaderplan']);
            }
        }

        if (isset($printvars->options['feedback']) && isset($printvars->options['feedbackown'])) {
            unset($printvars->options['feedbackown']);
        }

        if (empty($printvars->options)) {
            return;
        }

        // Re-index options for mustache.
        $printvars->options = array_values($printvars->options);

        $printvars->dropdown = count($printvars->options) > 1;

        // Reverse order as they pull-right!
        $button = $this->renderer->render_from_template('local_onlineappraisal/print_menu', $printvars). $PAGE->button;

        $PAGE->set_button($button);
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
     * @return object navigation.
     */
    public function get_navigation() {
        global $CFG;
        $navigation = new stdClass();
        $navigation->items = array();

        // Validation for flagging.
        $stages = new \local_onlineappraisal\stages($this);
        $newstatus = $stages->get_update_path('submit');
        $cansubmit = permissions::is_allowed('appraisal:update', $this->appraisal->statusid, $this->appraisal->viewingas, $this->appraisal->archived, $this->appraisal->legacy);
        if ($this->appraisal->statusid >= APPRAISER_REVIEW && $newstatus && $cansubmit) {
            // Will validate and set failure flags.
            $stages->validate($newstatus);
        }

        $forms = \local_onlineappraisal\forms::get_user_forms($this->appraisal->id, $this->appraisal->appraisee->id);
        // Add each of the form pages
        $prevhrafter = true; // Don't want an <hr> _before_ first item!
        foreach ($this->pages as $page) {
            $navitem = clone($page);
            if (!$navitem->showinnav) {
                continue;
            }
            $navitem->subactive = '';
            $navitem->name = get_string($page->name, 'local_onlineappraisal');
            $navitem->view = $this->appraisal->viewingas;
            $navitem->appraisalid = $this->appraisal->id;

            if ($page->type == 'form') {
                $stored = in_array($page->name, $forms);
                $navitem->checkbox = true;
                if ($stored) {
                    $navitem->checked = 'checked';
                }
            }

            $navitem->flagged = (bool) count(array_intersect(array('all', $page->name), $stages->failedvalidation));

            $navitem->hrbefore = $navitem->hrafter = false;
            if (!$prevhrafter && ($page->hr & self::HR_BEFORE)) {
                $navitem->hrbefore = true;
            }
            $prevhrafter = $page->hr & self::HR_AFTER;
            if ($prevhrafter) {
                $navitem->hrafter = true;
            }

            $navigation->items[] = $navitem;
        }

        // Ensure last item doesn't have an <hr> after.
        end($navigation->items);
        $endkey = key($navigation->items);
        $navigation->items[$endkey]->hrafter = false;
        reset($navigation->items);

        // Debugging information

        $debug = optional_param('debug', 0, PARAM_INT);
        if ($CFG->debugdisplay || $debug) {
            global $USER, $CFG;
            $navigation->debug = new stdClass();
            $navigation->debug->canview = $this->pages[$this->page]->view ? 'yes' : 'no';
            $navigation->debug->canadd = $this->pages[$this->page]->add ? 'yes' : 'no';
            $navigation->debug->page = $this->page;
            $navigation->debug->appraisalid = $this->appraisal->id;
            $navigation->debug->viewas = $this->appraisal->viewingas;
            $navigation->debug->me = fullname($USER);
            $navigation->debug->statusid = $this->appraisal->statusid;
            $navigation->debug->permissionsid = $this->appraisal->permissionsid;
            $navigation->debug->groupleaderactive = $this->appraisal->groupleader ? 'yes' : 'no';
        }
        // End of debugging information
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
                $page = new \local_onlineappraisal\output\help\help();
                $pagehtml = $PAGE->get_renderer('local_onlineappraisal', 'help')->render($page);
            } else {
                $page = $this->pages[$this->page];
                $contentmethod = 'content_'.$page->type;

                $pagehtml = $this->$contentmethod();
            }

            // Are there alerts.
            // First the lmaguage alert.
            $alerthtml = $this->language_alert();
            // Now session alert.
            if (!empty($SESSION->local_onlineappraisal->alert)) {
                $alert = new alert($SESSION->local_onlineappraisal->alert->message, $SESSION->local_onlineappraisal->alert->type, $SESSION->local_onlineappraisal->alert->button);
                $alerthtml .= $this->renderer->render($alert);
                unset($SESSION->local_onlineappraisal->alert);
            }

            return $alerthtml . $pagehtml;
        } else {
            $alert = new alert(get_string('error:pagenotfound', 'local_onlineappraisal', $this->page), 'danger', false);
            return $this->renderer->render($alert);
        }
    }

    /**
     * Get the content for a form. In order for forms
     * to work correctly on data submit the form must be loaded
     * before this call. We only want to get the view here.
     *
     * @return string html
     */
    private function content_form() {
        if ($this->form) {

            if ($this->page == 'leaderplan') {
                $content = '';
                $content .= $this->form->render();
                $page = new \local_onlineappraisal\output\dashboard\checkin($this, 'leaderplan');
                $content .= $this->renderer->render($page);
                return $content;
            } else {
                return $this->form->render();
            }
        } else {
            $alert = new alert(get_string('error:formnotinit', 'local_onlineappraisal'), 'danger', false);
            return $this->renderer->render($alert);
        }
    }

    /**
     * Get the content for the add feedback page.
     *
     * @return string html
     */
    private function content_dashboard() {
        $class = "\\local_onlineappraisal\\output\\dashboard\\{$this->page}";
        $page = new $class($this);
        return $this->renderer->render($page);
    }

    /**
     * Set the current active action
     *
     * @param string $action Type of action
     * @param int $actionid The request this applies to.
     * @param int $done 0/1/2 no, yes (success), yes (fail)
     */
    public function set_action($action = null, $actionid = 0, $done = self::ACTION_NOT_DONE) {
        // Create an empty action object
        $this->called = new stdClass();
        $this->called->action = $action;
        $this->called->actionid = $actionid;
        $this->called->done = $done;
        $this->called->status = new stdClass();
        $this->called->status->text = '';
        $this->called->status->result = '';
    }

    /**
     * Get the current active action based on the requestid
     *
     * @param string $actionid. Id number of this Feedback request
     *
     * @return $this->action;
     */
    public function check_action($actionid, $action) {
        // Create an empty action object
        if ($this->called->actionid == $actionid &&
            $this->called->action == $action &&
            !$this->called->done) {
            return $this->called;
        } else {
            return false;
        }
    }

    /**
     * Complete the current action
     */
    public function complete_action($type) {
        $this->called->done = self::ACTION_DONE_SUCCESS;
        $string = 'appraisee_' . $type . '_' . $this->called->action . '_success';
        self::set_alert(get_string($string, 'local_onlineappraisal'), 'success', true);
    }

    /**
     * Failed the current action
     */
    public function failed_action($type) {
        $this->called->done = self::ACTION_DONE_FAILURE;
        $string = 'appraisee_' . $type . '_' . $this->called->action . '_error';
        self::set_alert(get_string($string, 'local_onlineappraisal'), 'danger', true);
    }

    /**
     * Set an alert in the session for display on page (re)load.
     *
     * @global stdClass $SESSION
     * @param string $message
     * @param string $type
     * @param bool $button
     */
    public static function set_alert($message, $type, $button = true) {
        global $SESSION;

        // Prevent overwriting alerts.
        if (isset($SESSION->local_onlineappraisal->alert)) {
            return '';
        }

        // Set up flash alert in session.
        if (empty($SESSION->local_onlineappraisal)) {
            $SESSION->local_onlineappraisal = new stdClass();
        }
        $SESSION->local_onlineappraisal->alert = new stdClass();
        $SESSION->local_onlineappraisal->alert->type = $type;
        $SESSION->local_onlineappraisal->alert->message = $message;
        $SESSION->local_onlineappraisal->alert->button = $button;
    }

    /**
     * Set an appraisal database field.
     * @param string $field The Appraisal DB field name.
     * @param string $value The Appraisal DB field value.
     * @return bool $success.
     */
    public function set_appraisal_field($field, $value) {
        global $DB;

        // Array of safe fields and their associated permission check string.
        $safefields = array(
            'held_date' => 'f2f:add',
            'operational_job_title' => 'userinfo:add',
            'face_to_face_held' => 'f2f:complete',
            'six_month_review' => 'sixmonth:add',
            'six_month_review_date' => 'sixmonth:add',
        );

        if (!array_key_exists($field, $safefields)) {
            throw new Exception('Refused to set ' .$field. ' on this appraisal');
        }

        if ($this->check_permission($safefields[$field])) {
            if ($DB->set_field('local_appraisal_appraisal', $field, $value, array('id' => $this->appraisal->id))) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Update datahub fields (called via AJAX).
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function userinfo_datahub_update() {
        global $CFG, $DB, $USER;

        require_once($CFG->libdir . '/filelib.php');

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $view = required_param('view', PARAM_ALPHA);
        $field = required_param('field', PARAM_ALPHA);

        $appraisal = new appraisal($USER, $appraisalid, $view, 'userinfo', 0);

        if ($appraisal->appraisal->statusid >= APPRAISER_FINAL_REVIEW) {
            throw new moodle_exception('error:userinfo:datahub:update:status', 'local_onlineappraisal');
        }

        $return = new stdClass();
        $return->success = false;
        $return->data = new stdClass();

        $query = "
            SELECT
                CORE_JOB_TITLE as jobtitle, GRADE as grade
            FROM
                SQLHUB.ARUP_ALL_STAFF_V
            WHERE
                EMPLOYEE_NUMBER = :idnumber
        ";
        $params= ['idnumber' => (int) $appraisal->appraisal->appraisee->idnumber];

        $hubdata = $DB->get_record_sql($query, $params);

        if ($hubdata) {
            switch ($field) {
                case 'jobtitle':
                    $return->data->jobtitle = $appraisal->appraisal->job_title = !empty($hubdata->jobtitle) ? $hubdata->jobtitle : '';
                    break;
                case 'grade':
                    $return->data->grade = $appraisal->appraisal->grade = !empty($hubdata->grade) ? $hubdata->grade : '';
                    break;
            }
            $DB->update_record('local_appraisal_appraisal', $appraisal->appraisal);
            $return->success = true;
        }

        if ($return->success) {
            $return->message = get_string('success:userinfo:datahub:update', 'local_onlineappraisal');
        } else {
            $return->message = get_string('error:userinfo:datahub:update', 'local_onlineappraisal');
        }

        return $return;
    }

    /**
     * Simple access check (called via AJAX).
     *
     * @global \moodle_database $DB
     * @global stdClass $USER
     * @return stdClass result
     * @throws moodle_exception
     */
    public static function check_session() {
        // If we're here then all is OK (as ajax.php checks the session key).
        $return = new stdClass();
        $return->success = true;
        $return->data = '';
        $return->message = 'OK';

        return $return;
    }
}