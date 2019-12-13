<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage local_reportbuilder
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
* Output renderer for local_reportbuilder module
*/
class local_reportbuilder_renderer extends plugin_renderer_base {

    /**
     * Renders a table containing user-generated reports and options
     *
     * @param array $reports array of report objects
     * @return string HTML table
     */
    public function user_generated_reports_table($reports=array()) {
        global $CFG;

        if (empty($reports)) {
            return get_string('noreports', 'local_reportbuilder');
        }

        $tableheader = array(get_string('name', 'local_reportbuilder'),
                             get_string('source', 'local_reportbuilder'));

        $tableheader[] = get_string('options', 'local_reportbuilder');

        $data = array();

        $strsettings = get_string('settings', 'local_reportbuilder');
        $strclone = get_string('clonereport', 'local_reportbuilder');
        $strdelete = get_string('delete', 'local_reportbuilder');
        $stryes = get_string('yes');
        $strno = get_string('no');

        foreach ($reports as $report) {
            try {
                $row = array();
                $viewurl = new moodle_url(reportbuilder_get_report_url($report));
                $editurl = new moodle_url('/local/reportbuilder/general.php', array('id' => $report->id));
                $deleteurl = new moodle_url('/local/reportbuilder/index.php', array('id' => $report->id, 'd' => 1));
                $cloneurl = new moodle_url('/local/reportbuilder/clone.php', array('id' => $report->id));

                $row[] = html_writer::link($editurl, format_string($report->fullname)) . ' (' .
                    html_writer::link($viewurl, get_string('view')) . ')';

                $row[] = $report->sourcetitle;

                $settings = $this->output->action_icon($editurl, new pix_icon('/t/edit', $strsettings, 'moodle'), null,
                    array('title' => $strsettings));
                $delete = $this->output->action_icon($deleteurl, new pix_icon('/t/delete', $strdelete, 'moodle'), null,
                    array('title' => $strdelete));
                $cache = '';
                if (!empty($CFG->enablereportcaching) && !empty($report->cache)) {
                    $reportbuilder = new reportbuilder($report->id);
                    if (empty($reportbuilder->get_caching_problems())) {
                        $cache = $this->cachenow_button($report->id, true);
                    }
                }
                $clone = $this->output->action_icon($cloneurl, new pix_icon('/t/copy', $strclone, 'moodle'), null,
                    array('title' => $strclone));
                $row[] = "{$settings}{$cache}{$clone}{$delete}";

                $data[] = $row;
            } catch (Exception $e) {
                $row = array();
                $deleteurl = new moodle_url('/local/reportbuilder/index.php', array('id' => $report->id, 'd' => 1));
                $delete = $this->output->action_icon($deleteurl, new pix_icon('/t/delete', $strdelete, 'moodle'));
                $spacer = $this->output->spacer(array('width' => '11', 'height' => '11'));

                $row[] = format_string($report->fullname);
                $row[] = $e->getMessage();
                $row[] = "{$spacer}{$delete}";

                $data[] = $row;
            }
        }

        $reportstable = new html_table();
        $reportstable->summary = '';
        $reportstable->head = $tableheader;
        $reportstable->data = $data;

        return html_writer::table($reportstable);
    }


    /**
     * Renders a table containing embedded reports and options
     *
     * @param array $reports array of report objects
     * @return string HTML table
     */
    public function embedded_reports_table($reports=array()) {
        global $CFG;

        if (empty($reports)) {
            return get_string('noembeddedreports', 'local_reportbuilder');
        }

        $tableheader = array(get_string('name', 'local_reportbuilder'),
                             get_string('source', 'local_reportbuilder'));

        $tableheader[] = get_string('options', 'local_reportbuilder');

        $strsettings = get_string('settings', 'local_reportbuilder');
        $strreload = get_string('restoredefaults', 'local_reportbuilder');
        $strclone = get_string('clonereport', 'local_reportbuilder');

        $embeddedreportstable = new html_table();
        $embeddedreportstable->summary = '';
        $embeddedreportstable->head = $tableheader;
        $embeddedreportstable->data = array();

        $stryes = get_string('yes');
        $strno = get_string('no');
        $data = array();
        foreach ($reports as $report) {
            $fullname = format_string($report->fullname);
            $viewurl = new moodle_url($report->url);
            $editurl = new moodle_url('/local/reportbuilder/general.php', array('id' => $report->id));
            $reloadurl = new moodle_url('/local/reportbuilder/index.php', array('id' => $report->id, 'em' => 1, 'd' => 1));
            $cloneurl = new moodle_url('/local/reportbuilder/clone.php', array('id' => $report->id));

            $row = array();
            $row[] = html_writer::link($editurl, $fullname) . ' (' .
                html_writer::link($viewurl, get_string('view')) . ')';

            $row[] = $report->sourcetitle;

            $settings = $this->output->action_icon($editurl, new pix_icon('/t/edit', $strsettings, 'moodle'), null,
                    array('title' => $strsettings));
            $reload = $this->output->action_icon($reloadurl, new pix_icon('/t/reload', $strreload, 'moodle'), null,
                    array('title' => $strreload));
            $cache = '';
            if (!empty($CFG->enablereportcaching) && !empty($report->cache)) {
                $reportbuilder = new reportbuilder($report->id);
                if (empty($reportbuilder->get_caching_problems())) {
                    $cache = $this->cachenow_button($report->id, true);
                }
            }
            $clone = $this->output->action_icon($cloneurl, new pix_icon('/t/copy', $strclone, 'moodle'), null,
                    array('title' => $strclone));
            $row[] = "{$settings}{$reload}{$cache}{$clone}";

            $data[] = $row;
        }
        $embeddedreportstable->data = $data;

        return html_writer::table($embeddedreportstable);
    }

    /**
     * Format records to view or restricted users.
     *
     * @param stdClass $records Records with group properties (cohorts, pos, org, users)
     * @return string $output Formatted records
     */
    public function format_records_to_view($records) {
        $output = '';

        foreach ($records as $group => $entries) {
            $str = new stdClass();
            $str->group = $group;
            $str->entries = implode(', ', $entries);
            if (strlen($str->entries) > 128) {
                $str->entries = strtok(wordwrap($str->entries, 128, "...\n"), "\n");
            }
            $output .= get_string('groupassignlist', 'local_reportbuilder', $str);
            $output .= html_writer::empty_tag('br');
        }

        return $output;
    }

    /**
     * Output report delete confirmation message
     * @param reportbuilder $report Original report instance
     * @return string
     */
    public function confirm_delete(reportbuilder $report) {
        $type = empty($report->embedded) ? 'delete' : 'reload';

        $out = html_writer::tag('p', get_string('reportconfirm' . $type, 'local_reportbuilder', $report->fullname));
        return $out;
    }

    /**
     * Output report clone confirmation message
     * @param reportbuilder $report Original report instance
     * @return string
     */
    public function confirm_clone(reportbuilder $report) {
        // Prepare list of supported clonable properties.
        $supportedproperties = array('clonereportfilters', 'clonereportcolumns', 'clonereportsearchcolumns',
            'clonereportsettings', 'clonereportgraph');
        if ($report->embedded) {
            $supportedproperties[] = 'clonereportaccessreset';
        }
        $strproperties = array();
        foreach ($supportedproperties as $propertyname) {
            $strproperties[] = get_string($propertyname, 'local_reportbuilder');
        }
        $strpropertylist = html_writer::alist($strproperties);

        $out = '';
        if ($report->embedded){
            $out .= $this->output->notification(get_string('clonereportaccesswarning', 'local_reportbuilder'), 'notifynotice');
        }

        $info = new stdClass();
        $info->origname = $report->fullname;
        $info->clonename = get_string('clonenamepattern', 'local_reportbuilder', $report->fullname);
        $info->properties = $strpropertylist;

        $out .= html_writer::tag('p', get_string('clonedescrhtml', 'local_reportbuilder', $info));

        return $out;
    }


    /** Prints select box and Export button to export current report.
     *
     * A select is shown if the global settings allow exporting in
     * multiple formats. If only one format specified, prints a button.
     * If no formats are set in global settings, no export options are shown
     *
     * for this to work page must contain:
     * if ($format != '') { $report->export_data($format);die;}
     * before header is printed
     *
     * @param integer|reportbuilder $report ID or instance of the report to exported
     * @param integer $sid Saved search ID if a saved search is active (optional)
     * @return No return value but prints export select form
     */
    public function export_select($report, $sid = 0) {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . '/local/reportbuilder/export_form.php');

        if ($report instanceof reportbuilder) {
            $id = $report->_id;
            $url = $report->get_current_url();
        } else {
            $id = $report;
            $report = new reportbuilder($id);
            if ($PAGE->has_set_url()) {
                $url = $PAGE->url;
            } else {
                $url = new moodle_url(qualified_me());
                foreach ($url->params() as $name => $value) {
                    if (in_array($name, array('spage', 'ssort', 'sid', 'clearfilters'))) {
                        $url->remove_params($name);
                    }
                }
            }
        }

        $extparams = array();
        foreach ($report->get_current_params() as $param) {
            $extparams[$param->name] = $param->value;
        }

        $export = new report_builder_export_form($url, compact('id', 'sid', 'extparams'), 'post', '', array('id' => 'rb_export_form'));
        $export->display();
    }

    /**
     * Returns a link that takes the user to a page which displays the report
     *
     * @param string $reporturl the url to redirect to
     * @return string HTML to display the link
     */
    public function view_report_link($reporturl) {

        $url = new moodle_url($reporturl);
        return html_writer::link($url, get_string('viewreport', 'local_reportbuilder'));
    }

    /**
     * Returns message that there are changes pending cache regeneration or cache is being
     * regenerated since some time
     *
     * @param int|reportbuilder $reportid Report id or reportbuilder instance
     * @return string Rendered HTML
     */
    public function cache_pending_notification($report = 0) {
        global $CFG;
        if (empty($CFG->enablereportcaching)) {
            return '';
        }
        if (is_numeric($report)) {
            $report = new reportbuilder($report);
        }
        $notice = '';
        if ($report instanceof reportbuilder) {
            //Check that regeneration is started
            $status = $report->get_cache_status();
            if ($status == RB_CACHE_FLAG_FAIL) {
                $notice = $this->container(get_string('cachegenfail','local_reportbuilder'), 'notifyproblem clearfix');
            } else if ($status == RB_CACHE_FLAG_GEN) {
                $time = userdate($report->cacheschedule->genstart);
                $notice = $this->container(get_string('cachegenstarted','local_reportbuilder', $time), 'notifynotice clearfix');
            } else if ($status == RB_CACHE_FLAG_CHANGED) {
                $context = context_system::instance();
                $capability = $report->embedded ? 'local/reportbuilder:manageembeddedreports' : 'local/reportbuilder:managereports';
                if ($report->_id > 0 && has_capability($capability, $context)) {
                    $button = html_writer::start_tag('div', array('class' => 'boxalignright rb-genbutton'));
                    $button .= $this->cachenow_button($report->_id);
                    $button .= html_writer::end_tag('div');
                } else {
                    $button = '';
                }
                $notice = $this->container(get_string('cachepending','local_reportbuilder', $button),
                        'notifynotice clearfix', 'cachenotice_'.$report->_id);
            }
        }
        return $notice;
    }

    /**
     * Display cache now button
     *
     * @param int $reportid Report id
     * @param bool $icon Show icon instead of button
     */
    public function cachenow_button($reportid, $icon = false) {
        global $PAGE, $CFG;
        static $cachenowinit = false;
        static $strcache = '';

        if (!$cachenowinit) {
            $cachenowinit = true;
            $PAGE->requires->strings_for_js(array('cachenow_title'), 'local_reportbuilder');
            $PAGE->requires->string_for_js('ok', 'moodle');
            $strcache = get_string('cachenow', 'local_reportbuilder');
            $PAGE->requires->js_call_amd('local_reportbuilder/cachenow', 'init', array());
        }

        if ($icon) {
            $html = $this->output->action_icon('', new pix_icon('i/new', $strcache, 'core'), null,
                    array('data-id' => $reportid, 'data-action' => "cachenow", 'title' => $strcache ));
        } else {
            $html = html_writer::empty_tag('input', array('type' => 'button',
                'name' => 'rb_cachenow',
                'data-id' => $reportid,
                'class' => 'show-cachenow-dialog rb-hidden',
                'id' => 'show-cachenow-dialog-' . $reportid,
                'value' => $strcache
                ));
        }
        return $html;
    }

    /**
     * Returns a link back to the manage reports page called 'View all reports'
     *
     * Used when editing a single report
     *
     * @param boolean $embedded True to link to embedded reports, false to link to user reports.
     *
     * @return string The HTML for the link
     */
    public function view_all_reports_link($embedded = false) {
        $string = $embedded ? 'allembeddedreports' : 'alluserreports';
        $url = $embedded ? new moodle_url('/local/reportbuilder/manageembeddedreports.php') : new moodle_url('/local/reportbuilder/');
        return '&laquo; ' . html_writer::link($url, get_string($string, 'local_reportbuilder'));
    }

    /**
     * Returns a button that when clicked, takes the user to a page where they can
     * save the results of a search for the current report
     *
     * @param reportbuilder $report
     * @return string HTML to display the button
     */
    public function save_button($report) {
        global $SESSION;

        $buttonsarray = optional_param_array('submitgroup', null, PARAM_TEXT);
        $search = (isset($SESSION->reportbuilder[$report->get_uniqueid()]) &&
                !empty($SESSION->reportbuilder[$report->get_uniqueid()])) ? true : false;
        // If a report has required url params then scheduled reports require a saved search.
        // This is because the user needs to be able to save the search with no filters defined.
        $hasrequiredurlparams = isset($report->src->redirecturl);
        if ($search || $hasrequiredurlparams) {
            $params = $report->get_current_url_params();
            $params['id'] = $report->_id;
            return $this->output->single_button(new moodle_url('/local/reportbuilder/save.php', $params),
                    get_string('savesearch', 'local_reportbuilder'), 'get');
        } else {
            return '';
        }
    }

    /**
     * Returns HTML for a button that lets users show and hide report columns
     * interactively within the report
     *
     * JQuery, dialog code and showhide.js.php should be included in page
     * when this is used (see code in report.php)
     *
     * @param int $reportid
     * @param string $reportshortname the report short name
     * @return string HTML to display the button
     */
    public function expand_container($content) {
        $html = '';

        // We put the data in a container so that jquery can search inside it.
        $html .= html_writer::start_div('rb-expand-container');

        // We need to construct a table with one row and one column so that the row can be inserted into the existing table.
        $cell = new html_table_cell(html_writer::span($content));
        $cell->attributes['class'] = 'rb-expand-cell';

        $row = new html_table_row(array($cell));
        $row->attributes['class'] = 'rb-expand-row';

        $table = new html_table();
        $table->data = array($row);
        $html .= html_writer::table($table);

        // Close the container.
        $html .= html_writer::end_div();

        return $html;
    }

    /**
     * Print the description of a report
     *
     * @param string $description
     * @param integer $reportid ID of the report the description belongs to
     * @return string HTML
     */
    public function print_description($description, $reportid) {
        $sitecontext = context_system::instance();
        $description = file_rewrite_pluginfile_urls($description, 'pluginfile.php', $sitecontext->id, 'local_reportbuilder', 'report_builder', $reportid);

        $out = '';
        if (isset($description) &&
            trim(strip_tags($description)) != '') {
            $out .= $this->output->box_start('generalbox reportbuilder-description');
            // format_text is HTML and multi language support for general and embedded reports.
            $out .= format_text($description);
            $out .= $this->output->box_end();
        }
        return $out;
    }


    /**
     * Return the appropriate string describing the search matches
     *
     * @deprecated since Totara 9.9, 10 Please call $this->result_count_info() instead.
     * @param integer $countfiltered Number of records that matched the search query
     * @param integer $countall Number of records in total (with no search)
     * @return string Text describing the number of results
     */
    public function print_result_count_string($countfiltered, $countall) {

        debugging(__METHOD__ . ' has been deprecated please call local_reportbuilder_renderer::result_count_info instead', DEBUG_DEVELOPER);

        $displaycountall = get_config('local_reportbuilder', 'allowtotalcount');

        // Countall is 0.
        if (empty($displaycountall) || ($countall == 0 && $countfiltered > 0)) {
            // If we're here then countall is obviously wrong, so don't display it.
            $resultstr = ((int)$countfiltered === 1) ? 'xrecord' : 'xrecords';
            $a = $countfiltered;
        } else {
            $resultstr = ((int)$countall === 1) ? 'xofyrecord' : 'xofyrecords';
            $a = new stdClass();
            $a->filtered = $countfiltered;
            $a->unfiltered = $countall;

        }
        return html_writer::span(get_string($resultstr, 'local_reportbuilder', $a), 'rb-record-count');
    }

    /**
     * Returns HTML containing a string detailing the result count for the given report.
     *
     * @param reportbuilder $report
     * @return string
     */
    public function result_count_info(reportbuilder $report) {

        $filteredcount = $report->get_filtered_count();
        if ($report->can_display_total_count()) {
            $unfilteredcount = $report->get_full_count();
            $resultstr = ((int)$unfilteredcount === 1) ? 'record' : 'records';
            $a = new stdClass();
            $a->filtered = $filteredcount;
            $a->unfiltered = $unfilteredcount;
            $string = get_string('xofy' . $resultstr, 'local_reportbuilder', $a);
        } else{
            $resultstr = ((int)$filteredcount === 1) ? 'record' : 'records';
            $string = get_string('x' . $resultstr, 'local_reportbuilder', $filteredcount);
        }

        return html_writer::span($string, 'rb-record-count');
    }

    /**
     * Generates the report HTML and debug HTML if required.
     *
     * This method should always be called after the header has been output, before
     * the report has been used for anything, and before any other renderer methods have been called.
     * By doing this the report counts will be cached and you will avoid needing to run the count queries
     * which are nearly as expensive as the reports.
     *
     * @since Totara 9.9, 10
     * @param reportbuilder $report
     * @param int $debug
     * @return array The report html and the debughtml
     */
    public function report_html(reportbuilder $report, $debug = 0) {
        // Generate and output the debug HTML before we do anything else with the report.
        // This way if there is an error it we already have debug.
        $debughtml = ($debug > 0) ? $report->debug((int)$debug, true) : '';
        // Now generate the report HTML before anything else, this is optimised to cache counts.
        $reporthtml = $report->display_table(true);
        return array($reporthtml, $debughtml);
    }

    /**
     * Renders a table containing report saved searches
     *
     * @param array $searches array of saved searches
     * @param object $report report that these saved searches belong to
     * @return string HTML table
     */
    public function saved_searches_table($searches, $report) {
        $tableheader = array(get_string('name', 'local_reportbuilder'),
                             get_string('publicsearch', 'local_reportbuilder'),
                             get_string('options', 'local_reportbuilder'));
        $data = array();
        $stredit = get_string('edit');
        $strdelete = get_string('delete', 'local_reportbuilder');
        $rowclasses = [];

        foreach ($searches as $search) {
            $editurl = new moodle_url('/local/reportbuilder/savedsearches.php',
                array('id' => $search->reportid, 'action' => 'edit', 'sid' => $search->id));
            $deleteurl = new moodle_url('/local/reportbuilder/savedsearches.php',
                array('id' => $search->reportid, 'action' => 'delete', 'sid' => $search->id));

            $actions = $this->output->action_icon($editurl, new pix_icon('/t/edit', $stredit, 'moodle'), null, ['class' => 'edit-search', 'data-searchid' => $search->id]) . ' ';
            $actions .= $this->output->action_icon($deleteurl, new pix_icon('/t/delete', $strdelete, 'moodle'), null, ['class' => 'delete-search', 'data-searchid' => $search->id]);

            $row = array();
            $row[] = $search->name;
            $row[] = ($search->ispublic) ? get_string('yes') : get_string('no');
            $row[] = $actions;
            $data[] = $row;
            $rowclasses[] = 'savedsearchid_' . $search->id;
        }

        $table = new html_table();
        $table->summary = '';
        $table->head = $tableheader;
        $table->attributes['class'] = 'fullwidth generaltable';
        $table->data = $data;
        $table->rowclasses = $rowclasses;

        return html_writer::table($table);
    }

    /**
     * Renders a list of items for the email setting in schedule reports.
     *
     * @param object $item An item object which should contain id and name properties
     * @param string $filtername The filter name where the item belongs
     * @return string $out HTML output
     */
    public function schedule_email_setting($item, $filtername) {
        $name = (isset($item->name)) ? $item->name : $item->fullname;
        $strdelete = get_string('delete');
        $out = html_writer::start_tag('div', array('data-filtername' => $filtername,
            'id' => "{$filtername}_{$item->id}",
            'data-id' => $item->id,
            'class' => 'multiselect-selected-item audience_setting'));
        $out .= format_string($name);
        $out .= $this->output->action_icon('#', new pix_icon('/t/delete', $strdelete, 'moodle'), null,
            array('class' => 'action-icon delete'));

        $out .= html_writer::end_tag('div');

        return $out;
    }

    /**
     * Returns a table showing the currently assigned groups of users
     *
     * @param array $assignments group assignment info
     * @param int $itemid the id of the restriction object users are assigned to
     * @param string $suffix type of restriction (record or user)
     * @return string HTML
     */
    public function display_assigned_groups($assignments, $itemid, $suffix) {
        $tableheader = array(get_string('assigngrouptype', 'local_reportbuilder'),
                             get_string('assignsourcename', 'local_reportbuilder'),
                             get_string('assignincludechildrengroups', 'local_reportbuilder'),
                             get_string('assignnumusers', 'local_reportbuilder'),
                             get_string('actions'));
        if ($suffix === 'record') {
            $deleteurl = new moodle_url('/local/reportbuilder/restrictions/edit_recordstoview.php',
                    array('id' => $itemid, 'sesskey' => sesskey()));
        } else if ($suffix === 'user') {
            $deleteurl = new moodle_url('/local/reportbuilder/restrictions/edit_restrictedusers.php',
                    array('id' => $itemid, 'sesskey' => sesskey()));
        } else {
            $deleteurl = null;
        }

        $table = new html_table();
        $table->attributes['class'] = 'fullwidth generaltable';
        $table->summary = '';
        $table->head = $tableheader;
        $table->data = array();
        if (empty($assignments)) {
            $table->data[] = array(get_string('nogroupassignments', 'local_reportbuilder'));
        } else {
            foreach ($assignments as $assign) {
                $includechildren = ($assign->includechildren == 1) ? get_string('yes') : get_string('no');
                $row = array();
                $row[] = new html_table_cell($assign->grouptypename);
                $row[] = new html_table_cell($assign->sourcefullname);
                $row[] = new html_table_cell($includechildren);
                $row[] = new html_table_cell($assign->groupusers);

                if ($deleteurl) {
                    $delete = $this->output->action_icon(
                        new moodle_url($deleteurl, array('deleteid' => $assign->id)),
                        new pix_icon('t/delete', get_string('delete')));
                    $row[] = new html_table_cell($delete);
                } else {
                    $row[] = '';
                }

                $table->data[] = $row;
            }
        }
        $out = $this->output->container(html_writer::table($table), 'clearfix', 'assignedgroups');
        return $out;
    }

    /**
     * Returns the base markup for a paginated user table widget
     *
     * @return string HTML
     */
    public function display_user_datatable() {
        $table = new html_table();
        $table->id = 'datatable';
        $table->attributes['class'] = 'clearfix';
        $table->head = array(get_string('learner'), get_string('assignedvia', 'local_reportbuilder'));
        $out = $this->output->container(html_writer::table($table), 'clearfix', 'assignedusers');
        return $out;
    }

    /**
     * Render a set of toolbars (either top or bottom)
     *
     * @param array $toolbar array of left and right arrays
     *              eg. $toolbar[0]['left'] = <first row left content>
     *                  $toolbar[0]['right'] = <first row right content>
     *                  $toolbar[1]['left'] = <second row left content>
     * @param string $position 'top' or 'bottom'
     * @return string the rendered html template
     */
    public function table_toolbars($toolbar, $position='top') {

        ksort($toolbar);

        $data = new stdClass();
        $data->postion = $position;
        $data->toolbars_has_items = count($toolbar) > 0 ? true : false;
        $data->toolbars = array();

        foreach ($toolbar as $index => $row) {
            // don't render empty toolbars
            // if you want to render one, add an empty content string to the toolbar
            if (empty($row['left']) && empty($row['right'])) {
                continue;
            }

            $datarow = array(
                    "left_content_has_items" => false,
                    "left_content" => array(),
                    "right_content_has_items" => false,
                    "right" => array()
            );

            if (!empty($row['left'])) {
                $datarow['left_content_has_items'] = true;
                foreach ($row['left'] as $item) {
                    $datarow['left_content'][] = $item;
                }
            }

            if (!empty($row['right'])) {
                $datarow['right_content_has_items'] = true;
                foreach (array_reverse($row['right']) as $item) {
                    $datarow['right_content'][] = $item;
                }
            }
            $data->toolbars[] = $datarow;
        }
        return $this->render_from_template('local_reportbuilder/table_toolbars', $data);
    }

    /**
     * Use a template to generate a table of visible reports.
     *
     * @param array $reports array of report objects visible to this user.
     * @param bool $canedit if this user is an admin with editing turned on.
     * @return string HTML
     */
    public function report_list($reports, $canedit) {
        // If we've generated a report list, generate the mark-up.
        if ($report_list = $this->report_list_export_for_template($reports, $canedit)) {
            $data = new stdClass();
            $data->report_list = $report_list;

            return $this->output->render_from_template('local_reportbuilder/report_list', $data);
        } else {
            return '';
        }
    }

    /**
     * Generate the data required for the report_list template.
     *
     * @param array $reports array of report objects visible to this user.
     * @param bool $canedit if this user is an admin with editing turned on.
     * @return array List of reports.
     */
    public function report_list_export_for_template($reports, $canedit) {
        $report_list = array();
        $systemcontext = context_system::instance();

        foreach ($reports as $report) {

            $reportname = format_string($report->fullname, true, ['context' => $systemcontext]);

            // Check url property is set.
            if (!isset($report->url)) {
                debugging('The url property for report ' . $reportname . ' is missing, please ask your developers to check your code', DEBUG_DEVELOPER);
                continue;
            }

            // Escaping is done in the mustache template, so no need to do it in format string
            $report_data = [
                    'name' => $reportname,
                    'href' => $report->url
            ];

            if ($canedit) {
                $report_data['edit_href'] = (string) new moodle_url('/local/reportbuilder/general.php', array('id' => $report->id));
            }

            $report_list[] = $report_data;
        }

        return $report_list;
    }


    /**
     * Returns markup for displaying saved scheduled reports.
     *
     * @deprecated since 9.0.
     * @param array $scheduledreports List of scheduled reports.
     * @param boolean $showoptions boolean Show actions to edit or delete the scheduled report.
     * @return string HTML
     */
    public function print_scheduled_reports($scheduledreports, $showoptions=true) {
        debugging("print_scheduled_reports has been deprecated. Please use scheduled_reports_list instead.");
        return $this->scheduled_reports($scheduledreports, $showoptions);
    }


    /**
     * Uses a template to generate markup for displaying saved scheduled reports.
     *
     * @param array $scheduledreports List of scheduled reports.
     * @param boolean $showoptions boolean Show actions to edit or delete the scheduled report.
     * @return string HTML containing a form plus a table of scheduled reports or text.
     */
    public function scheduled_reports($scheduledreports, $showoptions=true, $addform = '') {
        global $OUTPUT;

        $dataobject = $this->scheduled_reports_export_for_template($scheduledreports, $showoptions, $addform);
        return $OUTPUT->render_from_template('local_reportbuilder/scheduled_reports', $dataobject);
    }


    /**
     * Uses a template to generate markup for displaying saved scheduled reports.
     *
     * @param array $scheduledreports List of scheduled reports.
     * @param boolean $showoptions Show actions to edit or delete the scheduled report.
     * @param string $addform form HTML to add another scheduled report.
     * @return object Table data object for the table template.
     */
    public function scheduled_reports_export_for_template($scheduledreports, $showoptions, $addform) {

        $table = new html_table();
        $table->id = 'scheduled_reports';
        $table->attributes['class'] = 'generaltable';
        $headers = array();
        $headers[] = get_string('reportname', 'local_reportbuilder');
        $headers[] = get_string('savedsearch', 'local_reportbuilder');
        $headers[] = get_string('format', 'local_reportbuilder');
        if (get_config('reportbuilder', 'exporttofilesystem') == 1) {
            $headers[] = get_string('exportfilesystemoptions', 'local_reportbuilder');
        }
        $headers[] = get_string('schedule', 'local_reportbuilder');
        if ($showoptions) {
            $headers[] = get_string('options', 'local_reportbuilder');
        }
        $table->head = $headers;

        foreach ($scheduledreports as $sched) {
            $cells = array();
            $cells[] = new html_table_cell(format_string($sched->fullname));
            $cells[] = new html_table_cell($sched->data);
            $cells[] = new html_table_cell($sched->format);
            if (get_config('reportbuilder', 'exporttofilesystem') == 1) {
                $cells[] = new html_table_cell($sched->exporttofilesystem);
            }
            $cells[] = new html_table_cell($sched->schedule);
            if ($showoptions) {
                $text = get_string('edit');
                $icon = $this->output->pix_icon('i/settings', $text, 'moodle', ['classes' => 'ft-size-100']);
                $url = new moodle_url('/local/reportbuilder/scheduled.php', array('id' => $sched->id));
                $attributes = array('href' => $url);
                $cellcontent = html_writer::tag('a', $icon, $attributes);
                $cellcontent .= ' ';
                $text = get_string('delete');
                $icon = $this->output->pix_icon('t/delete', $text, 'moodle', ['classes' => 'ft-size-100']);
                $url = new moodle_url('/local/reportbuilder/deletescheduled.php', array('id' => $sched->id));
                $attributes = array('href' => $url);
                $cellcontent .= html_writer::tag('a', $icon, $attributes);
                $cell = new html_table_cell($cellcontent);
                $cell->attributes['class'] = 'options';
                $cells[] = $cell;
            }
            $row = new html_table_row($cells);
            $table->data[] = $row;
        }

        $dataobject = new stdClass();
        $dataobject->scheduled_reports_table = html_writer::table($table);
        $dataobject->scheduled_reports_count = count($scheduledreports);
        $dataobject->scheduled_report_form = $addform;

        return $dataobject;
    }


    /**
     * The renderer for the My Reports page.
     */
    public function my_reports_page() {
        global $CFG;

        // This is required for scheduled_reports_add_form.
        require_once($CFG->dirroot . '/local/reportbuilder/scheduled_forms.php');

        // Prepare the data for the list of reports.
        $reports = get_my_reports_list();
        $context = context_system::instance();
        $canedit = has_capability('local/reportbuilder:managereports',$context);

        // Prepare the data for the list of scheduled reports.
        $scheduledreports = get_my_scheduled_reports_list();

        // Get the form that allow you to select a report to schedule.
        $mform = new scheduled_reports_add_form($CFG->wwwroot . '/local/reportbuilder/scheduled.php', array());
        $addform = $mform->render();

        // Build the template data.
        $template_data = $this->scheduled_reports_export_for_template($scheduledreports, true, $addform);
        $template_data->report_list = $this->report_list_export_for_template($reports, $canedit);

        return $this->render_from_template('local_reportbuilder/myreports', $template_data);
    }
}
