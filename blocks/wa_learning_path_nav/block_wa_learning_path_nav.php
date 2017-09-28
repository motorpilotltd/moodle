<?php

/**
 * WA Dashboard block.
 *
 * @package		block_wa_learning_path_nav
 * @author		Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright	2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class block_wa_learning_path_nav extends block_base {

    public function init() {
        $this->pluginname = 'block_wa_learning_path_nav';
        $this->title = get_string('blocktitle', $this->pluginname);
        $this->cron = 1;
    }

    public function instance_allow_multiple() {
        // Can be more than one instance of block.
        return true;
    }

    public function has_config() {
        return false;
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function instance_allow_config() {
        return false;
    }

    public function specialization() {
        global $PAGE;
        // Load userdefined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('blocktitle', $this->pluginname);
        } else {
            $this->title = $this->config->title;
        }
    }

    public function hide_header() {
        return false;
    }

    public function get_content() {

        global $CFG, $PAGE, $USER, $OUTPUT;

        if (!isset($this->config)) {
            $this->config = new stdClass();
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        if (!isloggedin()) {
            $this->content = '';
            return $this->content;
        }

        if ($PAGE->context->contextlevel != CONTEXT_SYSTEM) {
            return '';
        }

        $this->content = new stdClass;

        // Load jQuery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');

        // Adding a javascripts and css script.
        $PAGE->requires->css('/blocks/wa_learning_path_nav/css/frontend.css');
        $PAGE->requires->css('/blocks/wa_learning_path_nav/css/chosen.css');
        $PAGE->requires->js('/blocks/wa_learning_path_nav/js/wa_lib.js');
        $PAGE->requires->js('/blocks/wa_learning_path_nav/js/chosen.jquery.min.js');

        $learningpath = null;
        require_once($CFG->dirroot . '/local/wa_learning_path/lib/lib.php');
        $id = (int) $PAGE->url->get_param('id');
        $action = $PAGE->url->get_param('a');
        $controler = $PAGE->url->get_param('c');
        $preview = optional_param('preview', 0, PARAM_INT);

        if ($controler != 'learning_path') {
            return '';
        }
        
        if (empty($id)) {
            return '';
        }

        if (!empty($id)) {
            $levels = optional_param('levels', null, PARAM_RAW_TRIMMED);
            $regions = optional_param('regions', null, PARAM_RAW_TRIMMED);
            $key = optional_param('key', 0, PARAM_RAW_TRIMMED);
            $position = optional_param('position', '', PARAM_RAW_TRIMMED);

            \wa_learning_path\lib\load_model('learningpath');
            $userregion = \wa_learning_path\lib\get_user_region();
            
            if (!empty($preview)) {
                $previewmode = true;
                $learningpath = \wa_learning_path\model\learningpath::get($id);
            } else {
                $learningpath = \wa_learning_path\model\learningpath::check_and_get($id);
                $previewmode = false;
            }
        
            
            $urlinstraction = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => 'view', 'id' => (int) $id));
            $urlmatrix = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => 'matrix', 'id' => (int) $id));
            $urlclear = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => 'matrix', 'id' => (int) $id, 'levels' => '', 'regions' => ''));
            $urlcell = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => 'matrix', 'id' => (int) $id, 'key' => $key));
            $urlposition = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => 'matrix', 'id' => (int) $id, 'key' => $key, 'position' => $position));
            
            if(!empty($userregion)) {
                $urlmatrix->param('regions', $userregion->id);
                $urlcell->param('regions', $userregion->id);
                $urlposition->param('regions', $userregion->id);
            }
            
            $subscribeurl = new \moodle_url('/local/wa_learning_path/index.php',
                    array('c' => 'learning_path', 'a' => ($learningpath->subscribed) ? 'unsubscribe' : 'subscribe', 'id' => $id));
            if($previewmode) {
                $urlinstraction->param('preview', (int) $preview);
                $urlmatrix->param('preview', (int) $preview);
                $urlclear->param('preview', (int) $preview);
                $urlcell->param('preview', (int) $preview);
                $urlposition->param('preview', (int) $preview);
                $subscribeurl->param('preview', (int) $preview);
            }
            if ($action == 'matrix') {
                $allregions = \wa_learning_path\lib\get_regions();
                unset($allregions[0]);
                $matrixlevels = array();
                $r_selected = array();
                
                // Selected region.
                $regions = !is_null($regions) ? explode(',', $regions) : null;
                if (is_null($regions) && !empty($userregion)) {
                    $r_selected[] = (int) $userregion->id;
                } else if (!is_null($regions)) {
                    $r_selected = $regions;
                } else {
                    $r_selected = array();
                }
                
                // Selected levels.
                $l_selected = !is_null($levels) ? explode(',', $levels) : array();

                if (isset($learningpath->matrix)) {
                    $matrix = json_decode($learningpath->matrix);
                    if ($matrix) {
                        $matrixlevels = $matrix->rows;
                        
                        list($r_label, $c_label) = \wa_learning_path\model\learningpath::get_cell_labels($key, $matrix);
                    }
                }
            }
        }

//        // Gets a content of block.
        ob_start();
        require_once ("$CFG->dirroot/blocks/wa_learning_path_nav/content.php");
        $this->content->text = ob_get_contents();
        ob_end_clean();

        $this->content->footer = '';

        return $this->content;
    }

}
