<?php

/**
 * wa_learning_path admin controller
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
 * Main controller
 *
 * @package     local_wa_learning_path
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class admin extends \wa_learning_path\lib\base_controller {

    const DATE_FORMAT = 'Y-m-d H:i:s';

    function __construct() {
        global $USER;

        if (!\wa_learning_path\lib\is_contenteditor($USER->id)) {
            $this->no_access();
        }

        parent::__construct();
    }

    public function no_access() {
        throw new \Exception($this->get_string('no_access'));
    }

    /**
     * Create or edit learing path.
     */
    public function edit_action($data = array()) {
        global $USER, $PAGE;

        $id = optional_param('id', null, PARAM_INT);
        if (!\wa_learning_path\lib\has_capability('addlearningpath')) {
            if ($id) {
                $this->edit_matrix_action();
            } else {
                $this->no_access();
            }
        }


        $submitdisplay = optional_param('submitbutton3', null, PARAM_RAW);
        $submitsaveandclose = optional_param('submitbutton2', null, PARAM_RAW);

        \wa_learning_path\lib\load_model('learningpath');
        \wa_learning_path\lib\load_form('introduction');

        $this->form = new \wa_learning_path\form\introduction_form();
        $this->form->add_hidden('c', 'admin');
        $this->form->add_hidden('a', 'edit');

        if ($this->form->is_cancelled()) {
            redirect(new moodle_url('?c=admin'));
        }

        $editoroptions = $this->form->get_editor_params();
        $systemcontext = \context_system::instance();

        if ($data) {
            // Create a learning path.
            $this->form->set_data($data);
        } else if ($id) {
            // Edit a learning_path.
            $learning_path = \wa_learning_path\model\learningpath::get($id);
            if (!$learning_path) {
                $this->forward('admin', 'index', array());
                return;
            }

            $PAGE->navbar->add($learning_path->title);
            $PAGE->navbar->add($this->get_string('edit_summary'));

            if ($learning_path) {
                $this->status = $learning_path->status;
                $learning_path->introductionformat = $learning_path->format;

                $learning_path = file_prepare_standard_editor(
                    $learning_path,
                    'introduction',
                    $editoroptions,
                    $systemcontext,
                    'local_wa_learning_path', 'introduction', (int)$learning_path->id
                );

                // Deal with filemanger.
                file_prepare_standard_filemanager(
                        $learning_path,
                        'image',
                        $this->form->get_filemanager_params(),
                        $systemcontext,
                        \wa_learning_path\model\learningpath::FILE_COMPONENT,
                        \wa_learning_path\model\learningpath::FILE_AREA,
                        $id);

                $this->id = $id;
                $learning_path->learningpath_id = $learning_path->id;

                $this->form->add_header($this->get_string('edit_learning_path'));
                $this->form->add_id_field();

                $this->form->set_data($learning_path);
            }
        } else {
            $this->form->add_header($this->get_string('create_learning_path'));
            $PAGE->navbar->add($this->get_string('create_learning_path'));
        }

        if ($learningpath = $this->form->get_data()) {
            if (isset($learning_path) && $learningpath->status == WA_LEARNING_PATH_PUBLISH && $learning_path->status != $learningpath->status && !\wa_learning_path\lib\has_capability('publishlearningpath')) {
                $this->no_access();
            }

            $learningpath->format = $learningpath->introduction_editor['format'];
            $learningpath->itemid = $learningpath->introduction_editor['itemid'];

            $learningpath->id = $id = \wa_learning_path\model\learningpath::create($learningpath);

            // Deal with wysiwyg editor.
            $learningpath = file_postupdate_standard_editor($learningpath, 'introduction', $editoroptions, $systemcontext, 'local_wa_learning_path', 'introduction', $id);
            $id = \wa_learning_path\model\learningpath::create($learningpath);

            // Deal with filemanger.
            file_postupdate_standard_filemanager(
                    $learningpath,
                    'image',
                    $this->form->get_filemanager_params(),
                    $systemcontext,
                    \wa_learning_path\model\learningpath::FILE_COMPONENT,
                    \wa_learning_path\model\learningpath::FILE_AREA,
                    $id);

            if (is_null($submitdisplay)) {
                $message = empty($learning_path->id) ? $this->get_string('learning_path_add_success') : $this->get_string('learning_path_update_success');
                $this->set_flash_massage('success', $message);
            }

            if (!is_null($submitsaveandclose)) {
                redirect(new moodle_url('?c=admin'));
            } elseif (!is_null($submitdisplay)) {
                redirect(new moodle_url('?c=learning_path&a=view&id='.$id));
            } else {
                redirect(new moodle_url('?c=admin&a=edit&id='.$id));
            }
        }

        $this->view('edit');
    }

    /**
     * Create or edit learing path.
     */
    public function edit_matrix_action() {
        global $PAGE, $CFG, $USER;

        if (!\wa_learning_path\lib\has_capability('amendlearningcontent') && !\wa_learning_path\lib\has_capability('editmatrixgrid')) {
            $this->no_access();
        }

        $id = optional_param('id', null, PARAM_INT);
        $this->returnhash = optional_param('returnhash', null, PARAM_RAW);
        $submitdisplay = optional_param('submitbutton3', null, PARAM_RAW);
        $submitsaveandclose = optional_param('submitbutton2', null, PARAM_RAW);

        \wa_learning_path\lib\load_form('matrix');
        \wa_learning_path\lib\load_form('activity');
        \wa_learning_path\lib\load_model('learningpath');

        $this->form = new \wa_learning_path\form\matrix_form();
        $this->form->add_hidden('c', 'admin');
        $this->form->add_hidden('a', 'edit_matrix');

        $this->activityform = new \wa_learning_path\form\activity_form();
        $this->activityform->add_hidden('c', 'admin');
        $this->activityform->add_hidden('a', 'edit_matrix');

        $editoroptions = $this->activityform->get_editor_params();
        $systemcontext = \context_system::instance();

        if ($this->form->is_cancelled()) {
            redirect(new moodle_url('?c=admin'));
        }

        \wa_learning_path\lib\load_form('addactivity');

        $action = new \moodle_url($this->url, array('c' => 'activity', 'a' => 'edit'));

        $this->activity_form = new \wa_learning_path\form\addactivity_form($action->out(false), array('submit_button' => false), 'post', '', array('class' => 'wa_activity_mform'));

        $this->activity_form->add_hidden('c', 'activity');
        $this->activity_form->add_hidden('a', 'edit');

        \wa_learning_path\lib\load_model('activity');
        $this->modules = \wa_learning_path\lib\get_modules();
        $this->activities_list = \wa_learning_path\model\activity::get_list('title', 'ASC', 0, 99999,  $extrasql = ' (idlearningpath = 0 or idlearningpath = '.(int)$id.')');

        if ($matrix = $this->form->get_data()) {
            // Edit a learning_path.
            $learning_path = \wa_learning_path\model\learningpath::get($id);

            $matrix_data = json_decode($matrix->matrix);
            if ($matrix_data) {
                foreach ($matrix_data->activities as &$act) {
                    $tmp = new \stdClass();
                    $itemid = optional_param('activitydraftid', 0, PARAM_INT);
                    if (isset($act->content)) {
                        $tmp->content_editor['text'] = $act->content;
                        $tmp->content_editor['format'] = $learning_path->format;

                        $tmp->contentformat = $learning_path->format;
                        $matrix_data->itemid = $tmp->content_editor['itemid'] = $itemid;

                        $tmp = file_postupdate_standard_editor(
                            $tmp,
                            'content',
                            $editoroptions,
                            $systemcontext,
                            'local_wa_learning_path', 'content', 0);

                        $act->content = $tmp->content;
                    }
                }
            }

            if (!\wa_learning_path\lib\has_capability('editlearningmatrix')) {
                $prevmatrix = json_decode($learning_path->matrix);
                $matrix_data->rows = $prevmatrix->rows;
                $matrix_data->max_id = $prevmatrix->max_id;

                if (\wa_learning_path\lib\has_capability('amendlearningcontent')) {
                    foreach($prevmatrix->cols as &$col) {
                        foreach($matrix_data->cols as &$col2) {
                            if ($col2->id == $col->id) {
                                $col->region = $col2->region;
                            }
                        }
                    }
                }
                $matrix_data->cols = $prevmatrix->cols;
            }

            if ($id) {
                $ids = array();
                foreach($matrix_data->activities as $k => $md) {
                    foreach($md->positions as $section => $positions) {
                        foreach($positions as $pos) {
                            if ($pos->type == 'activity' && $pos->id) {
                                $ids[] = $pos->id;
                            }
                        }
                    }
                }

                if ($ids) {
                    \wa_learning_path\model\learningpath::link_activities_to_learning_path($id, $ids);
                }
            }

            \wa_learning_path\model\learningpath::set_matrix($matrix->id, json_encode($matrix_data));
            $message = $this->get_string('learning_matrix_saved_success');
            $this->set_flash_massage('success', $message);

            if (!is_null($submitsaveandclose)) {
                redirect(new moodle_url('?c=admin'));
            } elseif (!is_null($submitdisplay)) {
                redirect(new moodle_url('?c=learning_path&a=matrix&id='.$id));
            } else {
                //redirect(new moodle_url('?c=admin&a=edit_matrix&id='.$id));
            }
        }

        if ($id) {
            // Edit a learning_path.
            $learning_path = \wa_learning_path\model\learningpath::get($id);
            if (!$learning_path) {
                $this->forward('admin', 'index', array());
                return;
            }

            $PAGE->navbar->add($learning_path->title);
            $PAGE->navbar->add($this->get_string('edit_matrix'));

            if ($learning_path) {
                $this->id = $id;
                $this->status = $learning_path->status;
                $learning_path->learningpath_id = $learning_path->id;
            }

            //$this->form->add_header($this->get_string('edit_path_matrix'));
            $this->form->add_id_field();
            $this->form->set_data($learning_path);


            if (isset($learning_path->matrix)) {
                $matrix = json_decode($learning_path->matrix);
                unset($itemid);

                if ($matrix) {
                    $this->max_id = @$matrix->max_id;
                    $this->columns = $matrix->cols;
                    $this->rows = $matrix->rows;
                    $this->activities = \wa_learning_path\model\learningpath::fill_activities(@$matrix->activities, $this->modules, $this->activities_list);

                    $text = '';
                    if ((array)$this->activities) {
                        $USER->ignoresesskey = true;
                        foreach ($this->activities as $key => &$activity) {

                            $activity->contentformat = @$learning_path->format;
                            $tmp = file_prepare_standard_editor(
                                $activity,
                                'content',
                                $editoroptions,
                                $systemcontext,
                                'local_wa_learning_path', 'content', 0
                            );

                            $_REQUEST['content']['itemid'] = $_GET['content']['itemid'] = $activity->content_editor['itemid'];
                            $activity->content = $activity->content_editor['text'];

                            if ($key == $this->returnhash) {
                                $text = $activity->content_editor['text'];
                            }
                        }

                        $USER->ignoresesskey = false;

                        $learning_path->content_editor = $activity->content_editor;
                        $learning_path->content_editor['text'] = $text;
                        $this->activityform->set_data($learning_path);

                        $itemid = $activity->content_editor['itemid'];
                    }
                }

                if (!isset($itemid)) {
                    $activity = new \stdClass();
                    $activity->content = '';
                    $activity->contentformat = $learning_path->format;

                    $tmp = file_prepare_standard_editor(
                        $activity,
                        'content',
                        $editoroptions,
                        $systemcontext,
                        'local_wa_learning_path', 'content', 0
                    );

                    $learning_path->content_editor = $activity->content_editor;
                    $this->activityform->set_data($learning_path);

                    $itemid = $activity->content_editor['itemid'];

                }

                $this->itemid = $itemid;
                $this->form->add_hidden('activitydraftid', $itemid);
            }
        } else {
        }

        if (!isset($this->columns)) {
            $this->columns = array(array('name' => $this->get_string('default_column'), 'show' => 1));
        }

        if (!isset($this->rows)) {
            $this->rows = array(array('name' => $this->get_string('default_row'), 'show' => 1));
        }

        if (!isset($this->activities)) {
            $this->activities = array();
        }

        $this->view('edit_matrix');
    }

    /**
     * View learing path list
     */
    public function index_action() {
        global $CFG;

        $this->sort = optional_param('sort', 'title', PARAM_ALPHANUM);
        $this->dir = optional_param('dir', 'ASC', PARAM_ALPHA);
        $this->page = optional_param('page', 0, PARAM_INT);
        $this->perpage = optional_param('perpage', 30, PARAM_INT);

        $this->baseurl = new \moodle_url('?c=admin',
                array('sort' => $this->sort, 'dir' => $this->dir, 'perpage' => $this->perpage));

        \wa_learning_path\lib\load_model('learningpath');

        // Create filters.
        require_once('lib/filtering.php');
        $this->filtering = new \wa_learning_path\lib\wa_filtering(array('lp_title' => 0, 'lp_region' => 0, 'lp_status' => 0),
                '?c=admin', null, 'learningpath-index');

        list($extrasql, $params) = $this->filtering->get_sql_filter();

        // Count audiencies.
        $this->learningpathscount = \wa_learning_path\model\learningpath::count($extrasql, $params);

        // Get audiencies list.
        $this->learningpaths = \wa_learning_path\model\learningpath::get_list($this->sort, $this->dir,
                        $this->page * $this->perpage, $this->perpage, $extrasql, $params);

        $this->view('index');
    }

    /**
     * Delete learning path.
     */
    public function delete_action() {
        if (!\wa_learning_path\lib\has_capability('deletelearningpath')) {
            return $this->no_access();
        }

        $id = optional_param('id', null, PARAM_INT);
        \wa_learning_path\lib\load_model('learningpath');

        try {
            \wa_learning_path\model\learningpath::delete($id);
        } catch (\Exception $e) {
            echo $this->display_error($e->getMessage(), 'error');
            die;
        }

        $this->set_flash_massage('success', $this->get_string('delete_success'));
        redirect(new moodle_url('?c=admin'));
    }

    /**
     * Set learning path status
     */
    public function status_action() {
        $id = required_param('id', PARAM_INT);
        $status = required_param('status', PARAM_INT);
        \wa_learning_path\lib\load_model('learningpath');

        if ($status == WA_LEARNING_PATH_PUBLISH && !\wa_learning_path\lib\has_capability('publishlearningpath')) {
            return $this->no_access();
        }

        \wa_learning_path\model\learningpath::set_status($id, $status);
        echo "OK"; die;
    }

    /**
     * Subscribe edit
     */
    public function edit_subscriptions_action() {
        $this->id = required_param('id', PARAM_INT);

        $this->view('edit_subscriptions');
    }

}
