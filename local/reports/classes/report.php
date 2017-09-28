<?php
// This file is part of the Arup Reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_reports;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use Exception;
use moodle_exception;

class report {

    public $pages;
    public $hook;
    public $filters;
    public $searchparams;
    public $baseurl;
    public $exportxlsurl;
    public $maxexportrows;

    private $page;
    private $user;
    private $context;
    private $renderer;

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param object $user the full user object
     * @param int $reportsid the id of the requested reports
     * @param string $viewingas the type of user viewing as
     * @param string page the name of the page
     * @param int $formid the id of the form
     */
    public function __construct($page) {
        global $PAGE, $USER;

        // Get / Set the start / limit for amount of courses retreived from DB.
        $this->baseurl = '/local/reports/index.php';
        $this->maxexportrows = get_config('local_reports', 'csv_export_limit');
        $this->searchparams = array();
        $this->user = $USER;
        $this->page = $page;
        $this->context = $this->get_context();
        $this->report_pages();
        $this->renderer = $PAGE->get_renderer('local_reports', 'report');
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
     * Get the content for the report.
     *
     * @return string html
     */
    private function content_report() {
        $content = '';
        $reporttype = "\\local_reports\\reports\\{$this->page}";
        $class = "\\local_reports\\output\\report\\{$this->page}";
        $report = new $reporttype($this);
        $page = new $class($this);
        $page->set_report($report);
        $content .= $this->renderer->render($page);
        return $content;
    }


    public function filters($fields) {
        $this->filters = array();
        foreach ($fields as $field) {
            $filter = new stdClass();
            $filter->name = $field;
            $this->filters[] = $filter;
        }
    }

    private function searchparams($searchparams) {
        return $searchparams;
    }

    /**
     * Define the configured pages.
     */
    public function report_pages() {
        $this->pages = array();
        $this->add_page('report', 'learninghistory');
        $this->add_page('report', 'elearningstatus');
        if (!array_key_exists($this->page, $this->pages)) {
            print_error('error:pagedoesnotexist', 'local_reports', print_r($this->pages, true));
        }
    }


    /**
     * Create form objects for later use in the reports
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

        $viewpermission = 'local/reports:view';
        $page->view = $this->check_permission($viewpermission);
        if (!$page->view) {
            // Don't add it as no view/add access.
            return;
        }

        $page->name = $name;
        $page->type = $type;

        $myparams = array('page' => $name);

        $params = $this->searchparams($myparams);

        $page->url = new moodle_url($this->baseurl, $params);
        if ($hook && $this->page == $name) {
            $class = "\\local_reports\\$hook";
            $classinstance = new $class($this);
            $this->hook = $classinstance->hook();
        }

        if ($this->page == $name) {
            $page->active = 'active';
        }

        $this->pages[$name] = $page;
    }

    public function get_current_pageobject() {
        return $this->pages[$this->page];
    }

    public function check_permission($permission) {
        // All reportsclass_* forms should just use the reportsclass: type permissions.
        if (has_capability($permission, $this->context)) {
            return true;
        }
        return false;
    }


    public function export_report() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');
        $reporttype = "\\local_reports\\reports\\{$this->page}";
        $report = new $reporttype($this);
        return $report->get_csv_data();
    }

    public function download_csv($filename) {
        global $CFG;
        $tempfile = $CFG->dataroot . '/temp/' . $filename;
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for internet explorer
        header("Content-Type: text/csv");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:".filesize($tempfile));
        header("Content-Disposition: attachment; filename=$filename");
        readfile($tempfile);
        // delete the file.
        unlink($tempfile);
        die();
    }

    /**
     * Get applicable context.
     *
     * @global \moodle_database $DB
     * @return \context
     */
    private function get_context() {
        return \context_system::instance();
    }

    public function get_form_html($form) {
        $o = '';
        ob_start();
        $form->display();
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }
}