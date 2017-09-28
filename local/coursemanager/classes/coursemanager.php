<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use Exception;
use moodle_exception;

class coursemanager {

    public $pages;
    public $sort;
    public $classsort;
    public $courselist;
    public $classlist;
    public $hook;
    public $numrecords;
    public $start;
    public $limit;
    public $filters;
    public $filterparams;
    public $filteroptions;
    public $currentsearch;
    public $searchlevel;
    public $setfilters;
    public $direction;
    public $searchparams;
    public $baseurl;
    public $editing;
    public $pendingdeleteclass;
    public $hassearch;

    private $page;
    private $user;
    private $cmcourse;
    private $context;
    private $formid;
    private $form;
    private $renderer;
    private $datefields;
    private $action;

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param object $user the full user object
     * @param int $coursemanagerid the id of the requested coursemanager
     * @param string $viewingas the type of user viewing as
     * @param string page the name of the page
     * @param int $formid the id of the form
     */
    public function __construct($cmcourse, $cmclass, $page, $formid) {
        global $PAGE, $USER;

        // Get / Set the start / limit for amount of courses retreived from DB.
        $this->start = optional_param('start', 0, PARAM_INT);
        $this->limit = optional_param('limit', 100, PARAM_INT);
        $this->sort = optional_param('sort', '', PARAM_TEXT);
        $this->classsort = optional_param('classsort', '', PARAM_TEXT);
        $this->direction = optional_param('dir', 'ASC', PARAM_TEXT);
        $edit = optional_param('edit', -1, PARAM_BOOL);
        if (($edit == 1)) {
            $this->editing = 1;
        } else {
            $this->editing = 0;
        }

        $this->baseurl = '/local/coursemanager/index.php';
        $this->searchparams = array();
        $this->user = $USER;
        $this->cmcourse = $this->get_course($cmcourse);
        $this->cmclass = $this->get_class($cmclass);
        $this->page = $page;
        $this->formid = $formid;
        $this->context = $this->get_context();
        $this->datefields = array('startdate', 'enddate', 'timemodified');
        $this->hassearch = 0;
        $this->get_setfilters();
        $this->filters();
        $this->filter_date_search_form();

        // Execute quick actions
        $this->action = optional_param('action', '', PARAM_TEXT);
        $this->actions();

        $this->get_all_courses();
        $this->find_classes();
        $this->coursemanager_pages();
        // Finally set up renderer.
        $this->renderer = $PAGE->get_renderer('local_coursemanager', 'dashboard');
    }

    /**
     * Magic Getter function
     * @param string partial function name after _get
     * @return result of function.
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

    public function get_current_pageobject() {
        return $this->pages[$this->page];
    }

    public function set_page($pagename) {
        $this->page = $pagename;
    }

    public function get_current_courseobject($courseid) {
        global $DB;

        if ($record = $DB->get_record('local_taps_course', array('id' => $courseid))) {

            $sql = "SELECT COUNT(id) as numclasses from {local_taps_class}
                         WHERE courseid = ?
                           AND (archived IS NULL OR archived = 0)";
            if ($num = $DB->get_record_sql($sql, array($record->courseid))) {
                $record->classes = $num->numclasses;
            } else {
                $record->classes = 0 ;
            }
            if ($mdlcourse = $DB->get_record('course', array('idnumber' => $record->courseid))) {
                $record->moodlecourse = $mdlcourse;
            } else {
                $record->moodlecourse = false;
            }
            
            $this->courselist[$record->id] = $record;
            return $record;
        }
        return false;
    }

    private function actions() {
        global $DB;
        
        $this->pendingdeleteclass = false;
        $this->pendingdeletecourse = false;
        if ($this->action) {

            if ($this->action == 'deletecourse') {
                if (!$this->check_permission('local/coursemanager:deletecourse')) {
                    return false;
                }
                $cmcourse = $this->get_current_courseobject($this->cmcourse->id);
                if ($cmcourse->classes > 0) {
                    $this->pendingdeletecourse = $this->cmcourse->id;
                } else {
                    $this->cmcourse->archived = 1;
                    $DB->update_record('local_taps_course', $this->cmcourse);
                }
            }
            if ($this->action == 'deleteclass') {
                if (!$this->check_permission('local/coursemanager:deleteclass')) {
                    return false;
                }
                $enrolments = $DB->count_records_select(
                        'local_taps_enrolment',
                        'classid = :classid AND (archived is NULL OR archived = 0)',
                        ['classid' => $this->cmclass->classid]
                        );
                if ($enrolments > 0) {
                    $this->pendingdeleteclass = $this->cmclass->id;
                } else {
                    $this->cmclass->archived = 1;
                    $DB->update_record('local_taps_class', $this->cmclass);
                }
            }
            if ($this->action == 'forcedeleteclass') {
                if (!$this->check_permission('local/coursemanager:deleteclass')) {
                    return false;
                }
                $enrolments = $DB->get_records('local_taps_enrolment', array('classid' => $this->cmclass->classid));
                foreach ($enrolments as $enrolment) {
                    $enrolment->archived = 1;
                    $DB->update_record('local_taps_enrolment', $enrolment);
                }
                $this->cmclass->archived = 1;
                $DB->update_record('local_taps_class', $this->cmclass);
            }
        }

    }
    /**
     * Generate the main content for the page
     *
     * @return string html
     */
    public function main_content() {
        if ($this->page && array_key_exists($this->page, $this->pages)) {

            $page = $this->get_current_pageobject();
            $contentmethod = 'content_'.$page->type;
            $content = $this->$contentmethod();
    
            return $content;
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
        global $USER;
        if ($this->form) {
            $content = $this->hook;

            if ($this->editing) {
                // Don't show the form just yet
                if (($this->page == 'class' || $this->page == 'class_scheduled') && ($this->cmclass->id == 0 || $this->cmclass->id == -1) ) {
                   return $content;
                }
                $content .= $this->form->render();
            } else {
                if ($this->page == 'course') {
                    $table = new \local_coursemanager\output\dashboard\coursetable($this);
                    $content .= $this->renderer->render($table);
                }
                if (strpos($this->page, 'class') === 0) {
                    $table = new \local_coursemanager\output\dashboard\classtable($this);
                    $content .= $this->renderer->render($table);
                }
            }
            return $content;
        }
    }

    /**
     * Get the content for the add feedback page.
     *
     * @return string html
     */
    private function content_dashboard() {
        $this->get_all_courses();
        $content = '';
        $class = "\\local_coursemanager\\output\\dashboard\\{$this->page}";
        $page = new $class($this);
        $content .= $this->renderer->render($page);
        return $content;
    }


    private function get_course($cmcourseid) {
        global $DB;
        $params = array('id' => $cmcourseid);
        if ($cmcourseid > 0 && $cmcourse = $DB->get_record('local_taps_course', $params)) {
            return $cmcourse;
        } else {
            $cmcourse = new stdClass();
            $cmcourse->id = -1;
            return $cmcourse;
        }
    }

    private function get_class($cmclassid) {
        global $DB;
        $params = array('id' => $cmclassid);
        if ($cmclassid > 0 && $cmclass = $DB->get_record('local_taps_class', $params)) {
            return $cmclass;
        } else {
            $cmclass = new stdClass();
            $cmclass->id = -1;
            return $cmclass;
        }
    }

    public function get_setfilters() {
        $fields = array('coursecode', 'coursename', 'startdate', 'duration', 'courseregion', 'keywords');
        $searchoptions = count($fields);

        $this->setfilters = array();
        $this->filterparams =  array();
        $params = array('page' => 'overview');
        $active = optional_param('active', 0, PARAM_INT);
        $remove = optional_param('removesearch', 0, PARAM_INT);
        $this->searchlevel = 1;
        for ($i = 1; $i <= $searchoptions; $i++) {
            $search = optional_param('search' . $i, '', PARAM_TEXT);
            $value = optional_param('searchvalue' . $i, '', PARAM_TEXT);
            if ($i == 1 && empty($search)) {
                $search = 'coursecode';
            }
            if ($i == $remove) {
                continue;
            }
            if ($active == $i) {
                continue;
            }
            if ($search) {
                if (empty($value)) {
                    $this->currentsearch = $search;
                    continue;
                }
                $this->searchparams['search' . $i] = $search;
                $this->searchparams['searchvalue' . $i] = $value;
                $this->add_filter($search, $value, $i);
                $this->searchlevel = $i + 1;
            }
        }
        foreach ($this->setfilters as $filter) {
            $params = $this->searchparams;
            $params['removesearch'] = $filter->searchnumber;
            $filter->url = new moodle_url($this->baseurl, $params);
        }
    }

    private function add_filter($search, $value, $number) {
        $this->hassearch = 1;
        $filter = new stdClass();
        $filter->field = $search;
        $filter->value = $value;
        if (in_array($search, $this->datefields)) {
            $filter->displayvalue = userdate($value, get_string('strftimedate'), 'UTC');
        } else {
            $filter->displayvalue = $value;
        }
        $filter->searchnumber = $number;
        $this->setfilters[] = $filter;
    }

    public function filters() {
        $this->filters = array();
        $fields = array('coursecode', 'coursename', 'startdate', 'duration', 'courseregion', 'keywords');
        $params = $this->searchparams;
        $params['page'] = 'overview';
        $params['start'] = $this->start;
        
        // Get array of used fields
        $used = array();
        foreach ($this->setfilters as $filter) {
            $used[] = $filter->field;
        }

        // Search id for new search option
        $searchid = count($this->setfilters);
        $searchid++;

        $this->filteroptions = array();
        foreach ($fields as $field) {
            // Skip fields already in search params.
            if (in_array($field, $used)) {
                continue;
            }
            $filter = new stdClass();
            $filter->name = $field;
            $params['search' . $searchid] = $field;
            $filter->url = new moodle_url($this->baseurl, $params);
            $this->filteroptions[] = $filter;
            if (empty($this->currentsearch)) {
                $this->currentsearch = $field;
            }
        }
    }

    public function filter_date_search_form() {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        require_once($CFG->dirroot . '/local/coursemanager/forms/datesearch.php');
        $setfilters = $this->setfilters;

        $hiddenfields = array();

        foreach ($setfilters as $filter) {
            $hiddenfields[] = $this->add_hidden_field('search' . $filter->searchnumber, $filter->field);
            $hiddenfields[] = $this->add_hidden_field('searchvalue' . $filter->searchnumber, $filter->value);
        }
        $hiddenfields[] = $this->add_hidden_field('start', $this->start);
        $hiddenfields[] = $this->add_hidden_field('page', 'overview');
        $hiddenfields[] = $this->add_hidden_field('search' . $this->searchlevel, $this->currentsearch);

        $filterinfo = array();
        $filterinfo['hiddenfields'] = $hiddenfields;

        $this->datesearchform = new \cmform_datesearch(null, $filterinfo);

        if ($this->datesearchform->is_submitted() && ($data = $this->datesearchform->get_data())) {
            $this->add_filter($this->currentsearch, $data->datesearch, $this->searchlevel);
        }
    }

    public function get_date_search_form() {
        $o = '';
        ob_start();
        $this->datesearchform->display();
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

    private function add_hidden_field($name, $value) {
        $hidden = new stdClass();
        $hidden->name = $name;
        $hidden->value = $value;
        return $hidden;
    }

    /**
     * Get a paginate view of the available courses
     */
    private function get_all_courses() {
        global $DB;

        if (empty($this->sort)) {
            $this->sort = 'coursename';
            $this->direction = 'ASC';
        }

        $wherestring = '';

        $params = array();

        foreach ($this->setfilters as $filter) {
            if (!empty($wherestring)) {
                $wherestring .= ' AND ';
            }
            if (in_array($filter->field, $this->datefields)) {
                $minoneday = $filter->value - (60 * 60 * 24);
                $plusoneday = $filter->value + (60 * 60 * 24);
                $wherestring = " $filter->field > $minoneday AND $filter->field < $plusoneday ";
            } else {
                if ($filter->field == 'duration') {
                    $value = intval($filter->value);
                    $wherestring .= " $filter->field = $value ";
                } else {
                    $wherestring .= $DB->sql_like($filter->field, ':' . $filter->field, false);
                    $params[$filter->field] = '%' . $filter->value . '%';
                }
            }
        }

        $sql = "SELECT *
                  FROM {local_taps_course}
              ORDER BY " . $this->sort . ' ' . $this->direction;

        if (!empty($wherestring)) {
            $sql = "SELECT *
                      FROM {local_taps_course}
                     WHERE ${wherestring}
                     ORDER BY " . $this->sort . ' ' . $this->direction;
        }

        $this->courselist = $DB->get_records_sql($sql, $params, $this->start * $this->limit, $this->limit);

        if (!empty($wherestring)) {
            $allresults = $DB->get_records_sql($sql, $params);
            $this->numrecords = count($allresults);
        } else {
            $this->numrecords = $DB->count_records('local_taps_course');
        }

        foreach ($this->courselist as &$course) {
            $sql = "SELECT COUNT(id) as numclasses from {local_taps_class}
                             WHERE courseid = ?
                               AND (archived IS NULL OR archived = 0)";
            if ($num = $DB->get_record_sql($sql, array($course->courseid))) {
                $course->classes = $num->numclasses;
            } else {
                $course->classes = 0 ;
            }

            $course->moodlecourse = $DB->get_record('course', array('idnumber' => $course->courseid));

        }
    }

    /**
     * Find Classes associated with this course
     */
    private function find_classes() {
        global $DB;
        if ($this->cmcourse->id > 0) {
            if (empty($this->classsort)) {
                $this->classsort = 'classstarttime';
                $this->direction = 'DESC';
            }
            $order = "ORDER BY " . $this->classsort . ' ' . $this->direction;

            $showfields = array(
                'id',
                'archived',
                'classid',
                'classname',
                'classstatus',
                'usedtimezone', // Needed to format start/end time correctly.
                'classstarttime',
                'classendtime',
                'maximumattendees');

            $taps = new \local_taps\taps();
            $statuses = array_merge($taps->get_statuses('placed'), $taps->get_statuses('attended'));
            list($insql, $params) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');

            $sql = "SELECT c.". implode(', c.', $showfields) .",  count(e.classid) as attending
                      FROM {local_taps_class} c
                      LEFT OUTER JOIN {local_taps_enrolment} e
                      ON c.classid = e.classid
                        AND (e.archived = 0 OR e.archived IS NULL)
                        AND {$DB->sql_compare_text('e.bookingstatus')} {$insql}
                      WHERE c.courseid = :courseid
                      GROUP BY c.". implode(', c.', $showfields) . " " .
                      $order;

            $params['courseid'] = $this->cmcourse->courseid;
            $this->classlist = $DB->get_records_sql($sql, $params);
        }
    }

    /**
     * Define the configured pages.
     */
    public function coursemanager_pages() {
        $this->pages = array();
        $this->add_page('dashboard', 'overview');
        $this->add_page('form', 'course', 'cmcourse');
        $this->add_page('dashboard', 'classoverview');
        $this->add_page('form', 'class', 'cmclass');
        $this->add_page('form', 'class_scheduled', 'cmclass');
        $this->add_page('form', 'class_scheduled_normal', 'cmclass');
        $this->add_page('form', 'class_scheduled_planned', 'cmclass');
        $this->add_page('form', 'class_self_paced', 'cmclass');

        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_coursemanager');
        }
    }
    /**
     * Prepare the course manager page and support a form if required.
     */
    public function prepare_page() {
        // Prepare form if required.
        $page = $this->pages[$this->page];

        if ($page->type == 'form') {
            $aform = new \local_coursemanager\forms($this);
            $aform->get_form();
            $this->form = $aform->form;
        }
    }

    /**
     * Create form objects for later use in the coursemanager
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
    private function add_page($type, $name, $hook = null) {
        $page = new stdClass();

        $viewpermission = 'local/coursemanager' . $name . ':view';
        $addpermission = 'local/coursemanager' . $name . ':add';

        if ($type == 'form') {
            // These permissions _must_ be set for forms.
            $page->view = $this->check_permission($viewpermission);
            $page->add = $this->check_permission($addpermission);
        } else {
            $page->view = $this->check_permission('local/coursemanager:view');
            $page->add = $this->check_permission('local/coursemanager:view');
        }

        if (!$page->view && !$page->add) {
            // Don't add it as no view/add access.
            return;
        }

        $page->name = $name;
        $page->realname = $name;
        $page->type = $type;

        $myparams = array('page' => $name, 'start' => $this->start);
        $params = array_merge($this->searchparams, $myparams);

        $page->url = new moodle_url($this->baseurl, $params);
        if ($hook && $this->page == $name) {
            $class = "\\local_coursemanager\\$hook";
            $classinstance = new $class($this);
            $this->hook = $classinstance->hook();
        }

        $page->active = '';
        $page->visible = false;
        $page->addcourse = false;
        $page->addclass = false;

        if ($this->page == $name) {
            $page->active = 'active';
        }

        if ($name == 'overview') {
            $page->visible = true;
            if ($page->add) {
                $page->addcourse = true;
            }
        }

        if ($this->cmcourse->id >= 1 ) {
            $myparams['cmcourse'] = $this->cmcourse->id;
            $params = array_merge($this->searchparams, $myparams);
            $currentcourse = $this->get_current_courseobject($this->cmcourse->id);
            
            if ($name == 'classoverview') {
                $page->url = new moodle_url($this->baseurl, $params);
                if (empty($currentcourse->classes)) {
                   $page->visible = false; 
                } else {
                    $page->records = $currentcourse->classes;
                    $page->visible = true;
                }
            }

            if ($page->add) {
                $page->addclass = true;
            }

            if ($name == 'course') {
                $page->url = new moodle_url($this->baseurl, $params);
                $page->visible = true;
                $page->name = $currentcourse->coursecode;
            }

            if ($name == 'class') {
                // Lots of rules for when to show the add class tab.
                $classpage = (strpos($this->page, 'class') === 0);
                $newclass = ($this->cmclass->id == -1);
                $noclasses = (empty($currentcourse->classes) && $this->page == 'course');
                $classaction = ($this->action == 'deleteclass' || $this->action == 'forcedeleteclass');
                if (($newclass && $classpage && $page->add) || $this->action || $noclasses) {
                    $params['edit'] = 1;
                    $page->visible = true;                
                    $page->name = get_string('newclass', 'local_coursemanager');
                } else if ($this->cmclass->id > 0 ) {
                    $page->active = 'active';
                    $page->visible = true;
                }
                $page->url = new moodle_url($this->baseurl, $params);
            }
        }

        if ($this->cmcourse->id == -1) {
            if ($name == 'course') {
                $page->visible = true;
                $params['edit'] = 1;
                $params['tab'] = 1;
                $page->url = new moodle_url($this->baseurl, $params);
                $page->name = get_string('newcourse', 'local_coursemanager');
            }
        }
        $this->pages[$name] = $page;
    }

    public function check_permission($permission) {
        // All coursemanagerclass_* forms should just use the coursemanagerclass: type permissions.
        $permission = preg_replace('/coursemanagerclass_.*:/', 'coursemanagerclass:', $permission);
        if (has_capability($permission, $this->context)) {
            return true;
        }
        return false;
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
        // Add each of the form pages
        $classpages = array('class_scheduled', 'class_scheduled_planned', 'class_scheduled_normal');
        if (in_array($this->page, $classpages)) {
            $this->pages['class']->active = 'active';
        }

        foreach ($this->pages as $page) {
            $navitem = clone($page);
            $navitem->cmcourse = $this->cmcourse->id;
            $navigation->items[] = $navitem;
        }

        return $navigation;
    }

    /**
     * Get applicable context.
     *
     * @global \moodle_database $DB
     * @return \context
     */
    private function get_context() {
        global $DB;

        $mdlcourseid = false;

        if ($this->cmcourse && $this->cmcourse->id !== -1) {
            $mdlcourseid = $DB->get_field('course', 'id', ['idnumber' => $this->cmcourse->courseid]);
        }

        return $mdlcourseid === false ? \context_system::instance() : \context_course::instance($mdlcourseid);
    }
}