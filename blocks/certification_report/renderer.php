<?php


/**
 * certification block renderer
 *
 * @package    block_certification
 */

defined('MOODLE_INTERNAL') || die;
use block_certification_report\certification_report;
class block_certification_report_renderer extends plugin_renderer_base {

    private $canreset = false;

    /**
     * Show report table
     *
     * @param $certifications
     * @param $data
     * @param string $view (region | costcentre | user)
     * @param string $urlbase
     * @return string
     */
    public function show_table($certifications, $data, $view = 'regions', $urlbase = ''){
        if (count($data) === 1) {
            // Only 'viewtotal' set so no actual data.
            return html_writer::tag('div', get_string('nodata', 'block_certification_report'), array('class' => 'alert alert-warning', 'style' => 'margin-top: 10px;'));
        }

        // Which region view are we looking at?
        $regionview = optional_param('regionview', 'actual', PARAM_ALPHA) == 'geo' ? 'geo' : 'actual';

        if ($view == 'users') {
            $this->canreset = has_capability('block/certification_report:reset_certification', context_system::instance());
        }

        $header = [];
        if ($view == 'regions') {
            $headerstring = get_string('header'.$view.$regionview, 'block_certification_report');
            $altview = $regionview == 'actual' ? 'geo' : 'actual';
            $alturl = new moodle_url($urlbase);
            $alturl->param('regionview', $altview);
            $headerstring .= ' (' . html_writer::link($alturl, get_string('header'.$view.$altview.':view', 'block_certification_report')) . ')';
            $header[] = $headerstring;
        } else {
            $header[] = get_string('header'.$view, 'block_certification_report');
        }

        $tabledata = [];
        foreach($certifications as $certification){
            if(isset($data['viewtotal']['certifications'][$certification->id]) && $data['viewtotal']['certifications'][$certification->id]['progress'] !== null){
                $exempt = $data['viewtotal']['certifications'][$certification->id]['exempt'] == 1 ? html_writer::empty_tag('br').get_string('exempt', 'block_certification_report') : '';
                $optional = !$exempt && $data['viewtotal']['certifications'][$certification->id]['optional'] == 1 ? html_writer::empty_tag('br').get_string('optional', 'block_certification_report') : '';
                $cell = new html_table_cell($certification->shortname.$exempt.$optional);
                $cell->attributes['class'] = 'text-nowrap text-center';
                $header[] = $cell;
            }
        }
        $row = new html_table_row($header);
        $row->attributes['class'] = 'header-'.$view;
        $tabledata[] = $row;
        $rowurl = new moodle_url($urlbase);
        // Remove regionview switcher.
        $rowurl->remove_params('regionview');
        switch ($view) {
            case 'regions':
                $param = $regionview.'regions[]';
                break;
            case 'costcentre':
                $param = 'costcentres[]';
                $rowurl = certification_report::remove_url_params($rowurl, ['costcentres']);
                break;
            default:
                // Will be 'users'.
                $param = $view;
        }
        foreach($data as $itemid => $item){
            if($view == 'users' && $itemid != 'viewtotal'){
                $tabledata[] = $this->prepare_user_row($item, $data);
            }else{
                $line = [];
                if($itemid != 'viewtotal'){
                    // Set the main filter param for the next level.
                    $rowurl->param($param, $itemid);
                    $line[] = html_writer::link($rowurl, $item['fullname']);
                    /* Temporarily hide
                    if (count($header) > 2) {
                        $line[0] .= ' ';
                        $extraclass = $item['users'] == 0 ? 'optional' : 'status-text-'.certification_report::get_rag_status($item['progress']);
                        // $line[0] .= html_writer::span($item['progress'].'%', "user-progress {$extraclass}");
                        $userscomplete = round(($item['progress']/100) * $item['users']);
                        $line[0] .= html_writer::span($userscomplete.' / '.$item['users'], "user-progress {$extraclass}");
                    }
                    */
                }else{
                    $line[] = $item['name'];
                }
                foreach ($item['certifications'] as $certificationid => $certification) {
                    if ($data['viewtotal']['certifications'][$certificationid]['progress'] !== null) {
                        if ($certification['progress'] === null) {
                            $cell = new html_table_cell(get_string('na', 'block_certification_report'));
                        } else {
                            // $cell = new html_table_cell($certification['progress'].'%');
                            $userscomplete = round(($certification['progress']/100) * $certification['userscounter']);
                            $cell = new html_table_cell($userscomplete.' / '.$certification['userscounter']);
                        }
                        if ($certification['progress'] === null) {
                            $cell->attributes['class'] = 'status-na';
                        } else if ($certification['optional'] == 1 || $certification['exempt'] == 1){
                            $cell->attributes['class'] = 'optional';
                        } else {
                            $cell->attributes['class'] = 'status-'.certification_report::get_rag_status($certification['progress']);
                        }
                        $line[] = $cell;
                    }
                }
                $row = new html_table_row($line);
                if ($itemid == 'viewtotal') {
                    $row->attributes['class'] = 'viewtotal';
                } else {
                    $row->attributes['class'] = 'row-view'.$view;
                }
                $tabledata[] = $row;
            }
        }
        $table = new html_table();

        $table->id = 'cohorts';
        $table->attributes['class'] = 'certification-report-table';
        $table->data  = $tabledata;

        $output = '';
        $output .= html_writer::start_div('block_certification_report_data');
        $output .= html_writer::start_div('table-wrapper');
        $output .= html_writer::table($table);
        $output .= html_writer::end_div();

        $output .= html_writer::start_div();
        $output .= html_writer::empty_tag('br');
        $url = new moodle_url(str_replace('report.php', 'export.php', $urlbase));
        if ($view != 'users') {
            $output .= html_writer::tag('button', get_string('exportdatacsv:summary', 'block_certification_report'), ['onclick' => 'window.open("'.$url->out(false).'");']);
        }
        if ($view != 'regions') {
            $url->param('exportview', 'users');
            $output .= html_writer::tag('button', get_string('exportdatacsv', 'block_certification_report'), ['onclick' => 'window.open("'.$url->out(false).'");']);
        }

        $filterurl = new moodle_url($urlbase);
        $templatevars = new stdClass();
        $templatevars->url = $filterurl->out();
        $attributes = [
            'id' => 'copy-to-clipboard-popover',
            'data-content' => $this->render_from_template('block_certification_report/url_popover', $templatevars),
            'data-placement' => 'top',
            'data-html' => true,
        ];
        $output .= html_writer::tag('button', get_string('url', 'block_certification_report'), $attributes);

        $output .= html_writer::end_div();
        $output .= html_writer::end_div();

        return $output;

    }

    /**
     * Generate alertbox
     * @param string $message
     * @param string $type
     * @param bool $exitbutton
     * @return string
     */
    public function alert($message = '', $type = 'alert-warning', $exitbutton = true) {
        $class = "alert fade in {$type}";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    /**
     * Prepare html table row for  user
     * @param $user
     * @param $costcentre
     * @param $region
     * @return html_table_row
     */
    public function prepare_user_row($user, $data){
        $line = [];
        $modalurl = new moodle_url(
                '/blocks/certification_report/ajax/user_info.php',
                ['userid' => $user['userdata']->userid, 'sesskey' => sesskey()]);
        $modallink = html_writer::link(
                '#',
                $user['userdata']->firstname.' '.$user['userdata']->lastname,
                ['data-toggle' => 'modal',
                    'data-target' => '#info-modal',
                    'data-label' => $user['userdata']->firstname.' '.$user['userdata']->lastname,
                    'data-url' => $modalurl]
                );
        $line[] = $modallink;
        foreach($user['certifications'] as $certificationid => $certification){
            if(isset($data['viewtotal']['certifications'][$certificationid]) && $data['viewtotal']['certifications'][$certificationid]['progress'] !== null) {
                if ($certification['exemptionid'] > 0) {
                    $line[] = html_writer::link('#', get_string('notrequired', 'block_certification_report'), ['class' => 'setexemption', 'data-userid' => $user['userdata']->userid, 'data-certifid' => $certificationid]);
                } else if ($certification['progress'] === null) {
                    $line[] = get_string('na', 'block_certification_report');
                } else {
                    $cell = $certification['progress'] . '%';
                    if ($certification['completiondate'] > 0) {
                        $cell .= ' (' . strtoupper(userdate($certification['completiondate'], get_string('strftimedatefull', 'block_certification_report'))) . ')';
                    }
                    if ($this->canreset && $certification['currentcompletiondate'] > 0) {
                        $reset = html_writer::tag(
                                'i',
                                '',
                                ['class' => 'fa fa-undo reset-certification',
                                    'title' => get_string('resetcertification', 'block_certification_report'),
                                    'data-userid' => $user['userdata']->userid,
                                    'data-certifid' => $certificationid]
                                );
                    } else {
                        $reset = '';
                    }
                    $cell = $reset . html_writer::span($cell);
                    $cell .= html_writer::span('', 'report-shape-' . $certification['ragstatus'] . ' setexemption', ['data-userid' => $user['userdata']->userid, 'data-certifid' => $certificationid]);
                    $cell = new html_table_cell($cell);
                    $cell->attributes['class'] = 'text-nowrap td-circle';
                    $cell->id = 'certif_data_' . $user['userdata']->userid . '_' . $certificationid;
                    $line[] = $cell;
                }
            }
        }
        /**
         * Show user progress if there is more than 1 certification displayed
         */
        if (count($line) > 2) {
            $line[0] .= ' ';
            $extraclass = $user['count'] == 0 ? 'optional' : 'status-text-'.certification_report::get_rag_status($user['progress']);
            $line[0] .= html_writer::span($user['progress'].'%', "user-progress {$extraclass}");
        }
        $row = new html_table_row($line);
        $row->attributes['class'] = 'row-viewusers';
        return $row;
    }

    /**
     * Get HTML for modal
     * @param $modaldata
     * @param $header
     * @return string
     */
    public function get_modal($modaldata, $header)
    {
        $output = html_writer::start_div('exemption-modal-wrapper');
        $output .= html_writer::start_div('exemption-modal');
        $output .= html_writer::start_div('exemption-modal-header');
        $output .= html_writer::tag('p', $header);
        $output .= html_writer::end_div();

        $output .= $modaldata;

        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }

    /**
     * Show active filters
     *
     * @param array $filters
     * @return string
     */
    public function show_active_filters($filters = [], $filteroptions = [], $html = true){
        $output = html_writer::start_div();
        $plaintext = [];
        if(!empty($filters->actualregions)){
            $selectedactualregions = [];
            foreach ($filters->actualregions as $actualregion) {
                if ($actualregion == '-1') {
                    $selectedactualregions[] = 'NOT SET';
                } else {
                    $selectedactualregions[] = $actualregion;
                }
            }
            $plaintext[] = get_string('actualregions', 'block_certification_report').': '.implode(', ', $selectedactualregions);
            $output .= html_writer::div(html_writer::span(get_string('actualregions', 'block_certification_report'), 'bold').' : '.implode(', ', $selectedactualregions));
        }
        if(!empty($filters->georegions)){
            $selectedgeoregions = [];
            foreach ($filters->georegions as $georegion) {
                if ($georegion == '-1') {
                    $selectedgeoregions[] = 'NOT SET';
                } else {
                    $selectedgeoregions[] = $georegion;
                }
            }
            $plaintext[] = get_string('georegions', 'block_certification_report').': '.implode(', ', $selectedgeoregions);
            $output .= html_writer::div(html_writer::span(get_string('georegions', 'block_certification_report'), 'bold').' : '.implode(', ', $selectedgeoregions));
        }
        if(!empty($filters->costcentres)){
            $ccs = \block_certification_report\certification_report::get_costcentre_names();
            $ccs[-1] = 'NOT SET';
            $selectedccs = [];
            foreach ($filters->costcentres as $costcentre) {
                $selectedccs[] = isset($ccs[$costcentre]) ? $ccs[$costcentre] : $costcentre;
            }
            $plaintext[] = get_string('costcentres', 'block_certification_report').': '.implode(', ', $selectedccs);
            $output .= html_writer::div(html_writer::span(get_string('costcentres', 'block_certification_report'), 'bold').' : '.implode(', ', $selectedccs));
        }
        if(!empty($filters->fullname)){
            $plaintext[] = get_string('fullname', 'block_certification_report').': '.$filters->fullname;
            $output .= html_writer::div(html_writer::span(get_string('fullname', 'block_certification_report'), 'bold').' : '.$filters->fullname);
        }
        $otherfilters = ['cohorts', 'certifications', 'categories', 'groupnames', 'locationnames', 'employmentcategories', 'grades'];
        foreach ($otherfilters as $otherfilter) {
            $this->get_generic_filter_info($otherfilter, $filters, $filteroptions, $plaintext, $output);
        }
        $output .= html_writer::end_div();
        return $html ? $output : implode("\n", $plaintext);
    }

    private function get_generic_filter_info($filter, $filters, $filteroptions, &$plaintext, &$output) {
        if(!empty($filters->{$filter})){
            $selected = [];
            foreach ($filters->{$filter} as $id) {
                if (!empty($filteroptions[$filter][$id])) {
                    $selected[] = $filteroptions[$filter][$id];
                }
            }
            $plaintext[] = get_string($filter, 'block_certification_report').': '.implode(', ', $selected);
            $output .= html_writer::div(html_writer::span(get_string($filter, 'block_certification_report'), 'bold').' : '.implode(', ', $selected));
        }
    }

    public function print_reportlink_table($data) {
        return $this->render_from_template('block_certification_report/reportlinks_table', $data);
    }

    public function print_reportlink_lists($data) {
        return $this->render_from_template('block_certification_report/reportlink_lists', $data);
    }

    public function print_reportlink_manage() {
        $icon = new pix_icon('icon_blueplus', '', 'theme');
        $addlink = '';

        $manage = new moodle_url('/blocks/certification_report/manage_links.php');
        $addlink = html_writer::span(
            html_writer::link($manage,
                $this->render($icon)
                . html_writer::span(get_string('addreportlinks','block_certification_report'))), 'pull-right m-b-10 m-t-10');
        return $addlink;
    }
}