<?php

/**
 * wa_learning_path controller
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

namespace wa_learning_path\controller;

use wa_learning_path\lib;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

/**
 * Learning Path controller
 *
 * @package     local_wa_learning_path
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class learning_path extends \wa_learning_path\lib\base_controller {

    public $id;
    public $learning_path;
    public $preview = 0;

    function __construct() {
        global $USER, $PAGE;

        parent::__construct();
        $PAGE->set_pagelayout('standard');
    }

    public function no_access($capability = null) {
        if (empty($capability)) {
            throw new \Exception($this->get_string('no_access'));
        } else {
            throw new \Exception($this->get_string('no_access') . ': local/wa_learning_path:' . $capability);
        }
    }

    public function check_capability() {
        return  !\wa_learning_path\lib\has_capability('viewlearningpath') &&
                !\wa_learning_path\lib\has_capability('printlearningmatrix') &&
                !\wa_learning_path\lib\has_capability('exportlearningmatrix');
    }

    public function check_and_get_learning_path($params = array()) {
        global $CFG, $PAGE;

        \wa_learning_path\lib\load_model('learningpath');
        $preview = optional_param('preview', 0, PARAM_INT);
        $this->id = required_param('id', PARAM_INT);

        $this->learning_path = \wa_learning_path\model\learningpath::check_and_get($this->id);
        $this->preview = false;

        if (empty($this->learning_path)) {
            $this->set_flash_massage('error', $this->get_string('learning_path_not_exists_or_access_issue'));
            redirect(new moodle_url($this->url, array('c' => 'learning_path')));
        }

        if( $this->check_capability()) {
            $this->no_access();
        }
    }

    /**
     * View learing path list
     */
    public function index_action() {
        global $CFG, $PAGE, $USER;

        if( $this->check_capability()) {
            $this->no_access();
        }

        $PAGE->set_title($this->get_string('header_learning_path_list'));
        $PAGE->set_heading($this->get_string('header_learning_path_list'));
        $PAGE->navbar->add($this->get_string('header_learning_path_list'));

        $this->mode = optional_param('mode', null, PARAM_ALPHANUM);
        $this->region = optional_param('region', null, PARAM_INT);

        \wa_learning_path\lib\load_model('learningpath');

        $this->regions = \wa_learning_path\lib\get_regions();
        $this->regions[-1] = $this->get_string('all_regions');

        // Get user region.
        $this->userregion = \wa_learning_path\lib\get_user_region();

        if(is_null($this->region) && empty($this->userregion)) {
            $this->region = -1;
        } else if(is_null($this->region) && !empty($this->userregion)) {
            $this->region = $this->userregion->id;
        }

        // Get list.
        $this->list = \wa_learning_path\model\learningpath::get_published_list(null, $this->region);

        if (!is_null($this->mode)) {
            \wa_learning_path\model\learningpath::setup_default_view($this->mode);
        }

        if (\wa_learning_path\model\learningpath::get_default_view() == \wa_learning_path\model\learningpath::VIEW_LIST) {
            $this->modetext = $this->get_string('standard_view');
            $this->modeurl = new \moodle_url($this->url,
                    array('mode' => \wa_learning_path\model\learningpath::VIEW_TILES));
            $this->template = 'list';
        } else {
            $this->modetext = $this->get_string('list_view');
            $this->modeurl = new \moodle_url($this->url,
                    array('mode' => \wa_learning_path\model\learningpath::VIEW_LIST));
            $this->template = 'tiles';
        }

        $this->modeurl->param('region', (int) $this->region);

        $this->view('index');
    }

    public function view_action($params = array()) {
        global $CFG, $PAGE;
        $this->check_and_get_learning_path($params);

        $this->base_url = new \moodle_url($this->url, array('c' => $this->c, 'a' => $this->a, 'id' => (int) $this->id));
        $PAGE->set_url($this->base_url);
        $PAGE->navbar->add($this->get_string('header_learning_path_list'),
                new moodle_url($this->url, array('c' => 'learning_path')));
        $PAGE->navbar->add($this->learning_path->title, $this->base_url);
        $PAGE->navbar->add($this->get_string('summary'));

        $PAGE->set_title($this->get_string('header_landing_page') . ': ' . $this->learning_path->title . ' ' . $this->get_string('summary'));
        $this->subscribeurl = new \moodle_url($this->url,
                array('a' => ($this->learning_path->subscribed) ? 'unsubscribe' : 'subscribe', 'id' => $this->id));
        $this->matrixurl = new \moodle_url($this->url, array('a' => 'matrix', 'id' => $this->id));

        $systemcontext = \context_system::instance();

        require_once("$CFG->libdir/filelib.php");
        $this->learning_path->introduction = \file_rewrite_pluginfile_urls($this->learning_path->introduction,
                'pluginfile.php', $systemcontext->id, 'local_wa_learning_path', 'introduction', $this->id);

        $event = \local_wa_learning_path\event\learning_path_viewed::create(array(
                    'objectid' => $this->id,
                    'context' => \context_system::instance(),
        ));
        $event->trigger();

        $this->view('view');
    }

    public function matrix_action($params = array()) {
        global $CFG, $PAGE;

        $this->check_and_get_learning_path($params);

        \wa_learning_path\lib\load_model('activity');

        // Selected rows of matrix.
        $levels = optional_param('levels', null, PARAM_RAW_TRIMMED);

        // Selected region. default value -1 user will see content for all regions.
        $regions = optional_param('regions', null, PARAM_RAW_TRIMMED);

        // Selected cell of matrix (cell-ID).
        $key = optional_param('key', 0, PARAM_RAW_TRIMMED);

        // Moodle Page setup.
        $this->base_url = new \moodle_url($this->url, array('c' => $this->c, 'a' => $this->a, 'id' => (int) $this->id));
        $pagetitle = $this->get_string('header_learning_path_list') . ': ' . $this->learning_path->title . ' ' . $this->get_string('matrix');
        $PAGE->set_url(new \moodle_url($this->url, array('c' => $this->c, 'a' => $this->a, 'id' => (int) $this->id)));
        $PAGE->navbar->add($this->get_string('header_learning_path_list'),
                new moodle_url($this->url, array('c' => 'learning_path')));
        $PAGE->navbar->add($this->learning_path->title, $this->base_url);
        $PAGE->navbar->add($this->get_string('matrix'));

        // Get region to display
        $this->regions = array();
        // Get user region.
        $this->userregion = \wa_learning_path\lib\get_user_region();
        // Get selected regions.
        $this->selectedregions = (!is_null($regions) && $regions != '') ? explode(',', $regions) : null;
        if (is_null($regions) && !empty($this->userregion)) {
            // Set user region ID
            $this->regions[] = (int) $this->userregion->id;
            $regions = (int) $this->userregion->id;
        } else if (!is_null($regions)) {
            // If region ID is provider by GET - is set up.
            $this->regions = is_null($this->selectedregions) ? array() : $this->selectedregions;
//            $this->regions = $this->selectedregions;
        } else {
            // Empty.
            $this->regions = array();
        }

        $allregionnames = \wa_learning_path\lib\get_regions();
        $this->regionnames = array_intersect_key($allregionnames, array_fill_keys($this->regions, true));

        $this->levels = !empty($levels) ? explode(',', $levels) : array();

        $this->cell_url = new \moodle_url($this->url, array('a' => 'matrix', 'id' => (int) $this->id, 'levels' => (string) $levels, 'regions' => (string) $regions));

        if($this->preview) {
            $this->cell_url->param('preview', (int) $this->preview);
        }

        $this->cell = null;

        if (isset($this->learning_path->matrix)) {
            $this->position = 'all'; // Always 'all'...
            $this->key = $key;
            $this->matrix = json_decode($this->learning_path->matrix);

            if ($this->matrix) {
                $this->matrix->visible_cols = \wa_learning_path\model\learningpath::count_visible_rows($this->matrix, $this->regions);
                if (empty($this->matrix->visible_cols)) {
                    $this->regionhascontent = [];
                    foreach ($allregionnames as $regionid => $regionname) {
                        if (\wa_learning_path\model\learningpath::count_visible_rows($this->matrix, [$regionid])) {
                            $this->regionhascontent[$regionid] = $regionname;
                        }
                    }
                }
                $this->activities = \wa_learning_path\model\learningpath::fill_activities(@$this->matrix->activities,
                                false, false, false, false, (bool)$this->learning_path->subscribed);
            }

            if (!empty($key) && isset($this->activities->{$key})) {
                // Get labels.
                list($this->r_label, $this->c_label) = \wa_learning_path\model\learningpath::get_cell_labels($key,
                                $this->matrix);
                // Set a breadcrumb info.
                $this->base_url_cell = new \moodle_url(
                        $this->url,
                        array(
                            'c' => $this->c,
                            'a' => $this->a,
                            'id' => (int) $this->id,
                            'regions' => empty($this->userregion->id) ? '' : $this->userregion->id,
                            'key' => $this->key));

                $pagetitle .= ' / ' . $this->r_label .', '. $this->c_label;

                \wa_learning_path\lib\load_form('addactivity');
                $this->form = new \wa_learning_path\form\addactivity_form();
                $this->methodologylist = array_merge($this->form->get_activity_type(false), \wa_learning_path\lib\get_methodologies());
                
                // Load system context.
                $this->systemcontext = \context_system::instance();

                // Load cell data.
                $this->cell = $this->activities->{$key};
                $this->cell->content = \file_rewrite_pluginfile_urls($this->cell->content, 'pluginfile.php',
                        $this->systemcontext->id, 'local_wa_learning_path', 'content', 0);

                $this->position_url = new \moodle_url($this->url,
                        array(
                    'a' => 'matrix',
                    'id' => (int) $this->id,
                    'levels' => (string) $levels,
                    'regions' => (string) $regions,
                    'key' => $key,
                ));

                if($this->preview) {
                    $this->position_url->param('preview', (int) $this->preview);
                }

                // Sort positions.
                foreach (['essential', 'recommended', 'elective'] as $position) {
                    usort($this->cell->positions->{$position},
                            array("wa_learning_path\controller\learning_path", "sort_activity"));
                }

                // Merge (sorted) individual positions.
                    $this->cell->positions->all = array_merge(
                            $this->cell->positions->essential, $this->cell->positions->recommended,
                            $this->cell->positions->elective);

                // Count items for all conditions: region.
                $this->count = \wa_learning_path\model\learningpath::count_activities_by_positions($this->cell->positions, $this->regions);
                
                    // Set a breadcrumb info.
                    $this->base_position_cell = new \moodle_url(
                            $this->url,
                            array(
                            'c' => $this->c,
                            'a' => $this->a,
                            'id' => (int) $this->id,
                            'regions' => empty($this->userregion->id) ? '' : $this->userregion->id,
                            'key' => $this->key));

                    $pagetitle .= $this->get_string($this->position);
                }
            }

        $PAGE->set_title($pagetitle);

        $this->view('matrix');
    }

    private function sort_activity($a, $b) {
        $c1 = ($a->type == 'activity') ? $a->title : $a->fullname;
        $c2 = ($b->type == 'activity') ? $b->title : $b->fullname;

        return strcmp(strtolower($c1), strtolower($c2));
    }

    /**
     * Subscribe user onto Learning path
     * @global \wa_learning_path\controller\type $CFG
     * @global \wa_learning_path\controller\type $PAGE
     */
    public function subscribe_action() {
        global $CFG, $PAGE;

        $this->check_and_get_learning_path();

        $subscribeid = \wa_learning_path\model\learningpath::subscribe($this->id);

        if ($subscribeid) {
            $this->set_flash_massage('success', $this->get_string('subscribe_success'));
            redirect(new \moodle_url($this->url, array('a' => 'matrix', 'id' => (int) $this->id)));
        } else {
            $this->set_flash_massage('success', $this->get_string('subscribe_failure'));
            redirect(new \moodle_url($this->url, array('a' => 'view', 'id' => (int) $this->id)));
        }
    }

    /**
     * Unsubscribe user from Learning path
     * @global type $CFG
     * @global type $PAGE
     */
    public function unsubscribe_action() {
        global $CFG, $PAGE;

        $this->check_and_get_learning_path();

        $subscribeid = \wa_learning_path\model\learningpath::unsubscribe($this->id);

        if ($subscribeid) {
            $this->set_flash_massage('success', $this->get_string('unsubscribe_success'));
            redirect(new \moodle_url($this->url, array('a' => 'view', 'id' => (int) $this->id)));
        } else {
            $this->set_flash_massage('success', $this->get_string('unsubscribe_failure'));
            redirect(new \moodle_url($this->url, array('a' => 'view', 'id' => (int) $this->id)));
        }
    }

    /**
     * Export learning path to excel file.
     */
    public function excel_action() {
        if (!\wa_learning_path\lib\has_capability('exportlearningmatrix')) {
            return $this->no_access('exportlearningmatrix');
        }

        $this->check_and_get_learning_path();

        global $CFG;
        require_once($CFG->dirroot . '/lib/excellib.class.php');

        \wa_learning_path\lib\load_model('learningpath');
        \wa_learning_path\lib\load_model('activity');

        // Get the matrix
        $matrix = json_decode($this->learning_path->matrix);
        $matrix->activities = \wa_learning_path\model\learningpath::fill_activities(@$matrix->activities, false, false, false, false, true);
        $regions = \wa_learning_path\lib\get_regions();

        // Calculate file name.
        $downloadfilename = clean_filename($this->learning_path->title . '.xls');

        // Creating a workbook.
        $workbook = new \MoodleExcelWorkbook("-");

        // Sending HTTP headers.
        $workbook->send($downloadfilename);

        // Adding the worksheet.
        $myxls = $workbook->add_worksheet($this->get_string('excel_tab1'));

        $myxls->write_string(0, 0, $this->get_string('learning_path'));
        $myxls->write_string(0, 1, $this->learning_path->title);

        $line = 3;
        for ($r = 0; $r < count($matrix->rows); $r++) {
            if ($matrix->rows[$r]->show) {
                $myxls->write_string($line, 0, $matrix->rows[$r]->name); $line++;
                $myxls->write_string($line, 0, $this->get_string('essential')); $line++;
                $myxls->write_string($line, 0, $this->get_string('recommended')); $line++;
                $myxls->write_string($line, 0, $this->get_string('elective')); $line++;
            }
        }

        for ($c = 0; $c < count($matrix->cols); $c++) {
            if ($matrix->cols[$c]->show) {
                $line = 2;
                $myxls->write_string($line, $c + 1, $matrix->cols[$c]->name);

                $line++;
                for ($r = 0; $r < count($matrix->rows); $r++) {
                    if ($matrix->rows[$r]->show) {
                        $id = '#' . $matrix->cols[$c]->id . '_' . $matrix->rows[$r]->id;
                        if (isset($matrix->activities->{$id}) && $matrix->activities->{$id}->content) {
                            $myxls->write($line, $c + 1, \wa_learning_path\lib\html_to_excel($matrix->activities->{$id}->content));
                        }
                        $line++;

                        $myxls->write($line, $c + 1, (int) @$matrix->activities->{$id}->positions->essential > 0 ? count(@$matrix->activities->{$id}->positions->essential) : '0');
                        $line++;
                        $myxls->write($line, $c + 1, (int) @$matrix->activities->{$id}->positions->recommended > 0 ? count(@$matrix->activities->{$id}->positions->recommended) : '0');
                        $line++;
                        $myxls->write($line, $c + 1, (int) @$matrix->activities->{$id}->positions->elective > 0 ? count(@$matrix->activities->{$id}->positions->elective) : '0');
                        $line++;
                    }
                }
            }
        }

        $myxls2 = $workbook->add_worksheet($this->get_string('excel_tab2'));

        $line = 0;
        $myxls2->write($line, 0, $this->get_string('level'));
        $myxls2->write($line, 1, $this->get_string('category'));
        $myxls2->write($line, 2, $this->get_string('module_activity'));
        $myxls2->write($line, 3, $this->get_string('ere'));
        $myxls2->write($line, 4, $this->get_string('method'));
        $myxls2->write($line, 5, $this->get_string('percent'));
        $myxls2->write($line, 6, $this->get_string('region'));
        $myxls2->write($line, 7, $this->get_string('completion_status'));
        $line++;

        for ($r = 0; $r < count($matrix->rows); $r++) {
            if ($matrix->rows[$r]->show) {
                $row = $matrix->rows[$r]->name;

                for ($c = 0; $c < count($matrix->cols); $c++) {
                    if ($matrix->cols[$c]->show) {
                        $col = $matrix->cols[$c]->name;
                        $id = '#' . $matrix->cols[$c]->id . '_' . $matrix->rows[$r]->id;
                        if (isset($matrix->activities->{$id})) {
                            foreach ($matrix->activities->{$id}->positions as $type => $acts) {
                                foreach ($acts as $act) {
                                    $myxls2->write($line, 0, $row);
                                    $myxls2->write($line, 1, $col);
                                    $myxls2->write($line, 2, isset($act->fullname) ? $act->fullname : $act->title);
                                    $myxls2->write($line, 3, $this->get_string($type));
                                    if ($act->type == 'module') {
                                        $myxls2->write($line, 4, $act->methodology);
                                    } else {
                                        $myxls2->write($line, 4, $act->methodology ? $this->get_string('type_'.$act->methodology) : '');
                                    }
                                    $myxls2->write($line, 5, @$act->percent);

                                    $regionnames = '';
                                    foreach(@$act->region as $rr) {
                                        if ($regionnames) {
                                            $regionnames .= ', ';
                                        }

                                        $regionnames .= $regions[$rr];
                                    }

                                    $myxls2->write($line, 6, $regionnames);

                                    if (!isset($act->completed)) {
                                        $act->completed = false;
                                    }

                                    if ($act->type == 'module') {
                                        $myxls2->write($line, 7, $act->completed ? $this->get_string('completed') : $this->get_string('not_completed'));
                                    } else {
                                        $myxls2->write($line, 7, $act->completed ? $this->get_string('marked_completed') : $this->get_string('not_marked_completed'));
                                    }

                                    $line++;
                                }
                            }
                        }
                    }
                }
            }
        }
        // Close the workbook.
        $workbook->close();

        exit;
    }

    /**
     * Export learning path to excel file.
     */
    public function print_action() {
        if (!\wa_learning_path\lib\has_capability('printlearningmatrix')) {
            return $this->no_access('printlearningmatrix');
        }

        $this->check_and_get_learning_path();

        global $CFG;
        require_once($CFG->dirroot . '/lib/excellib.class.php');

        \wa_learning_path\lib\load_model('learningpath');
        \wa_learning_path\lib\load_model('activity');

        // Get the matrix
        $this->matrix = json_decode($this->learning_path->matrix);
        echo "
        <style>

        table th, table td{
            border: 1px solid #dadada;
            border-collapse: collapse;
            padding:10px;
        }

        table{
            border-collapse: collapse;
        }

        </style>

        ";
        $this->view('print');
    }

    /**
     * Export learning path to excel file.
     */
    public function print_section_action($params = array()) {
        if (!\wa_learning_path\lib\has_capability('printlearningmatrix')) {
            return $this->no_access('printlearningmatrix');
        }

        global $CFG; require_once($CFG->dirroot . '/lib/formslib.php');
        $this->check_and_get_learning_path($params);

        \wa_learning_path\lib\load_model('activity');

        // Selected rows of matrix.
        $levels = optional_param('levels', null, PARAM_RAW_TRIMMED);

        // Selected region. default value -1 user will see content for all regions.
        $regions = optional_param('regions', null, PARAM_RAW_TRIMMED);

        // Selected cell of matrix (cell-ID).
        $this->key = $key = '#'.optional_param('key', 0, PARAM_RAW_TRIMMED);

        // Load system context.
        $this->systemcontext = \context_system::instance();

        // Get region to display
        $this->regions = array();
        // Get user region.
        $this->userregion = \wa_learning_path\lib\get_user_region();
        // Get selected regions.
        $this->selectedregions = (!is_null($regions) && $regions != '') ? explode(',', $regions) : null;
        if (is_null($regions) && !empty($this->userregion)) {
            // Set user region ID
            $this->regions[] = (int) $this->userregion->id;
        } else if (!is_null($regions)) {
            // If region ID is provider by GET - is set up.
            $this->regions = is_null($this->selectedregions) ? array() : $this->selectedregions;
        } else {
            // Empty.
            $this->regions = array();
        }

        $this->levels = !empty($levels) ? explode(',', $levels) : array();

        global $CFG;
        require_once($CFG->dirroot . '/lib/excellib.class.php');

        \wa_learning_path\lib\load_model('learningpath');
        \wa_learning_path\lib\load_model('activity');

        if (isset($this->learning_path->matrix)) {

            // Get the matrix
            $this->matrix = json_decode($this->learning_path->matrix);

            if ($this->matrix) {
                $this->activities = \wa_learning_path\model\learningpath::fill_activities(@$this->matrix->activities,
                    false, false, false, false, (bool)$this->learning_path->subscribed);
            }

            // Load cell data.
            $this->cell = $this->activities->{$key};

            $this->cell->content = \file_rewrite_pluginfile_urls($this->cell->content, 'pluginfile.php',
                $this->systemcontext->id, 'local_wa_learning_path', 'content', 0);

            $this->position = 'all';

            // Create position: 'All'.
            if ($this->position == 'all') {
                $this->cell->positions->all = array_merge(
                    $this->cell->positions->essential, $this->cell->positions->recommended,
                    $this->cell->positions->elective);
            }
        }

        echo "
        <style>
        table {
            width: 100%;
        }

        table tr {
            page-break-inside: avoid;
        }

        table th, table td{
            border: 1px solid #dadada;
            border-collapse: collapse;
            padding:10px;
            width: auto;
        }

        td.c3, td.c2 {
            width: 10%;
            text-align: center;
        }

        table{
            border-collapse: collapse;
        }

        #matrix td {
            text-align: center;
        }

        #matrix td img {
            max-height: 25px;
        }

        td.c3 img {
            max-height: 25px;
        }

        .highlight {
            border: 3px solid black;
        }

        </style>

        ";
        $this->view('print_section');
    }

}
