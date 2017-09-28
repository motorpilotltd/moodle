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

namespace local_reports\reports;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;
use html_writer;
use dml_read_exception;

class base {

    private $report;

    public $displayfields;
    public $sortfields;
    public $sort;
    public $start;
    public $search;
    public $hassearch;
    public $setfilters;
    public $showfilters;
    public $direction;
    public $limit;
    public $textsearch;
    public $table;
    public $numrecords;
    public $exportxlsurl;
    public $searchform;
    public $showall;
    public $reportname;
    public $errors;
    public $baseurl;

    private $strings;
    public $taps;

    public function __construct(\local_reports\report $report) {
        $this->taps = new \local_taps\taps();
        
        $this->reportname = 'base';
        $this->report = $report;
        $this->baseurl = '/local/reports/index.php';
        // $this->reportfields();

        $this->start = optional_param('start', 0, PARAM_INT);
        $this->limit = optional_param('limit', 100, PARAM_INT);
        $this->sort = optional_param('sort', '', PARAM_TEXT);
        $this->direction = optional_param('dir', 'ASC', PARAM_TEXT);
        $this->search = optional_param('search', '', PARAM_TEXT);
        $this->action = optional_param('action', '', PARAM_TEXT);
    }

    public function removefilter() {
        global $SESSION;
        $sessionvar = $this->reportname;
        $filter = optional_param('filter', '', PARAM_TEXT);
        if (!empty($filter)) {
            if (array_key_exists($filter, $this->textfilterfields)) {

                $sessionfilters = unserialize($SESSION->$sessionvar);
                if (is_array($sessionfilters)) {
                    if (array_key_exists($filter, $sessionfilters)) {
                        unset($sessionfilters[$filter]);
                    }
                }
                $SESSION->$sessionvar = serialize($sessionfilters);
            }
        }
    }

    /**
     * Get the filters in use and set the available filter options.
     */
    public function get_filters() {
        global $SESSION;
        $sessionvar = $this->reportname;
        $used = array();
        $this->setfilters = array();
        // Get the set filters from session
        if (isset($SESSION->$sessionvar)) {
            $hassessiondata = true;
            $filters = unserialize($SESSION->$sessionvar);
            if (is_array($filters)) {
                foreach ($filters as $key => $value) {
                    $used[] = $key;
                    $this->make_filter($key, $value);
                    $this->hassearch = true;
                }
            }
        }
        if (isset($SESSION->showall)) {
            $this->showall = true;
        }

        // Get the available filter options for usage in search form.
        $this->filteroptions = array();
        foreach ($this->textfilterfields as $key => $type) {
            $this->filteroptions[] = $this->make_filter($key, 'none');
        }

        $this->searchform = new \local_reports\searchform(null, $this);
        // Are we doing a text search?
        $this->textsearch = true;
        
    }

    /**
     * Add a new filter for this report. This function is used when getting and setting
     * filters stored in our session. 
     *
     * @param string $key the field to filter on
     * @param string $value the contents of the filter
     * @return object $filter if value = none, else void and sets $this->setfilters
     * and $this->showfilters (for mustache)
     */
    public function make_filter($key, $value) {
        $filter = new stdClass();
        $filter->field = $key;
        $filter->name = $this->mystr($key);
        $filter->type = $this->textfilterfields[$key];
        // $value will be array when make_filter is called from
        // function get_filters
        if (is_array($value)) {
            $filter->displayvalue = implode(", ", $value);
            $filter->value = $value;
        } else {
            $filter->value = array($value);
        }
        $params = array('action' => 'removefilter', 'filter' => $key, 'page' => $this->reportname );
        $filter->urlremove = new moodle_url($this->baseurl, $params);
        $params= array('report' => 'elearningstatus', 'search' => $key);
        $filter->url = new moodle_url($this->baseurl, $params);
        // We can fill setfilters when make_filter is called from
        // function get_filters, these filters were stored in session
        if (is_array($value)) {
            $this->setfilters[$key] = $filter;
        } elseif ($value == 'none') {
            return $filter;
        } else {
            // If filters are added from form we need to combine
            // the filter->values into an array.
            // If the filter was not set yet it we can just add the filter to setfilters
            if (!array_key_exists($key, $this->setfilters)) {
                $filter->displayvalue = $value;
                $this->setfilters[$key] = $filter;
            } else {
                $oldfilter = $this->setfilters[$key];
                if (!in_array($value, $oldfilter->value)) {
                    array_push($oldfilter->value, $value);
                    $filter->value = $oldfilter->value;
                    $filter->displayvalue = implode(", ", $filter->value);
                    $this->setfilters[$key] = $filter;
                }
            }
        }
    }

    /**
     * Set a filter
     */
    public function set_filter() {
        global $SESSION;
        $sessionvar = $this->reportname;
        // Get new search fields from form.
        $data = false;
        if ($this->searchform->is_submitted() && ($data = $this->searchform->get_data())) {
            // Learver flags will not be shown in the filter list
            if ($data->leaver_flag == 0) {
                $SESSION->showall = true;
                $this->showall = true;
            } else if ($data->leaver_flag == 1) {
                $this->showall = false;
                unset($SESSION->showall);
            }
            unset($data->leaver_flag);

            foreach ($data as $search => $value) {
                if (array_key_exists($search, $this->textfilterfields) && !empty($value)) {
                    $this->make_filter($search, $value);
                } 
            }
        }

        // Check if search for validated okay, if not it needs to be displayed without collapse
        $this->searchformokay = true;
        if ($this->searchform->is_submitted() && !$this->searchform->is_validated()) {
            $this->searchformokay = false;
        } 
        $this->searchformhtml = $this->get_form_html($this->searchform);

        // Add all setfilters to sessions
        $sfilters = array();
        foreach ($this->setfilters as $filter) {
            $sfilters[$filter->field] = $filter->value;
        }
        $this->hassearch = true;
        $SESSION->$sessionvar = serialize($sfilters);
        
    }

    /**
     * get the language string from language file
     * @param $string simple string without elearningstatus: prefix
     * @param $vars string variables
     * @return language string
     */
    public function mystr($string, $vars =  null) {
        if (empty($string)) {
            return 'nostring';
        }
        return get_string('learninghistory:' . $string, 'local_reports', $vars);
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