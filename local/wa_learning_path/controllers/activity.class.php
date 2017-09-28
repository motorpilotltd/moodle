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
class activity extends \wa_learning_path\lib\base_controller {

    const DATE_FORMAT = 'Y-m-d H:i:s';

    public $id = 0;
    public $list = array();
    public $count = 0;
    public $activity = null;

    function __construct() {
        global $USER;

        parent::__construct();
    }

    public function no_access($capability = null) {
        if (empty($capability)) {
            throw new \Exception($this->get_string('no_access'));
        } else {
            throw new \Exception($this->get_string('no_access') . ': local/wa_learning_path:' . $capability);
        }
    }

    /**
     * View activities list
     */
    public function index_action() {
        global $CFG, $PAGE;

        if (!\wa_learning_path\lib\is_activity_editor()) {
            $this->no_access();
        }

        $this->sort = optional_param('sort', 'title', PARAM_ALPHANUMEXT);
        $this->dir = optional_param('dir', 'ASC', PARAM_ALPHA);
        $this->page = optional_param('page', 0, PARAM_INT);
        $this->perpage = optional_param('perpage', 30, PARAM_INT);

        $this->baseurl = new \moodle_url('?c=activity',
                array('sort' => $this->sort, 'dir' => $this->dir, 'perpage' => $this->perpage));

        \wa_learning_path\lib\load_model('activity');

        // Create filters.
        require_once('lib/filtering.php');
        $this->filtering = new \wa_learning_path\lib\wa_filtering(array('title' => 0, 'region' => 0, 'type' => 0),
                '?c=activity', null, 'activity-index');

        list($extrasql, $params) = $this->filtering->get_sql_filter();

        // Count activities.
        $this->count = \wa_learning_path\model\activity::count($extrasql, $params);

        // Get activities list.
        $this->list = \wa_learning_path\model\activity::get_list($this->sort, $this->dir, $this->page * $this->perpage,
                        $this->perpage, $extrasql, $params);


        $PAGE->set_url(new \moodle_url($this->url, array('c' => $this->c, 'a' => $this->a)));

        $PAGE->set_title($this->get_string('header_learning_activity_management'));

        $this->view('index');
    }

    /**
     * Create or edit activity.
     */
    public function edit_action($data = array()) {
        global $CFG, $PAGE, $DB;

        $id = optional_param('id', null, PARAM_INT);

        // If set means that the activitiy is created from matrix editing page.
        $idlearningpath = optional_param('lpid', 0, PARAM_INT);

        \wa_learning_path\lib\load_model('activity');
        \wa_learning_path\lib\load_form('addactivity');

        $action = new \moodle_url($this->url, array('a' => 'edit'));
        $this->form = new \wa_learning_path\form\addactivity_form($action->out(false), array(), 'post', '',
                array('class' => 'wa_activity_mform'));

        $this->form->add_hidden('c', 'activity');
        $this->form->add_hidden('a', 'edit');

        if ($this->form->is_cancelled()) {
            redirect(new moodle_url('?c=activity'));
        }

        // Check access.
        if ($id && !\wa_learning_path\lib\has_capability('editactivity')) {
            return $this->no_access('editactivity');
        }

        // Check access.
        if (!$idlearningpath && empty($id) && !\wa_learning_path\lib\has_capability('addactivity')) {
            return $this->no_access('addactivity');
        }

        $editoroptions = $this->form->get_editor_params();
        $systemcontext = \context_system::instance();

        if ($data) {
            // Create a activity.
            $this->form->set_data($data);
        } else if ($id) {
            // Edit a activity.
            $activity = \wa_learning_path\model\activity::get($id);

            if ($activity) {
                $activity->descriptionformat = 1;
                $activity = file_prepare_standard_editor(
                        $activity, 'description', $editoroptions, $systemcontext, 'local_wa_learning_path',
                        'activity_description', (int) $id
                );
                $activity->learningdescription = ['text' => $activity->learningdescription, 'format' => FORMAT_HTML];

                $this->id = $id;
                $this->title = $this->get_string('edit_activity');
                $this->form->set_data($activity);
            }
        } else {
            $this->title = $this->get_string('create_new_activity');
        }

        if ($activity = $this->form->get_data()) {
            $activity->learningdescription = $activity->learningdescription['text'];
            // Deal with wysiwyg editor.
            if (!empty($idlearningpath)) {
                $activity->idlearningpath = $idlearningpath;
            }
            $id = \wa_learning_path\model\activity::create($activity);

            $activity = file_postupdate_standard_editor($activity, 'description', $editoroptions, $systemcontext,
                    'local_wa_learning_path', 'activity_description', $id);
            $activity->id = (int) $id;
            // Update description.
            $DB->update_record('wa_learning_path_activity', $activity);

            $message = empty($activity->id) ? $this->get_string('activity_add_success') : $this->get_string('activity_update_success');

            if (\wa_learning_path\lib\is_ajax()) {
                $activity->id = (int) $id;
                $return = array('status' => 'OK', 'activity' => (array) $activity);
                echo json_encode($return);
                die();
            }
            $this->set_flash_massage('success', $message);
            redirect(new moodle_url('?c=activity'));
        } else {
			if (\wa_learning_path\lib\is_ajax()) {
				$return['status'] = 'ERROR';
				$return['errors'] = $this->form->get_all_errors();
				echo json_encode($return);
				die('');
			}
        }

        if (\wa_learning_path\lib\is_ajax()) {
			die();
//			$this->handled_ajax_form($return);
        }
        $PAGE->navbar->add($this->title);

        $PAGE->set_title($this->get_string('header_learning_activity_management') . ' ' . $this->title);

        $this->view('edit');
    }

    private function handled_ajax_form($return) {
        global $PAGE, $OUTPUT, $CFG;

        ob_start();
        $this->form->display();
        $formhtml = ob_get_clean();

        // First we get the script generated by the Form API
        if (strpos($formhtml, '</script>') !== false) {
            $outputparts = explode('</script>', $formhtml);
            $html = $outputparts[1];
//            $script = str_replace('<script type="text/javascript">', '', $outputparts[0]);
            $script = $outputparts[0] . "</script>";
        } else {
            $html = $formhtml;
        }

        // Next we get the M.yui.loader call which includes the Javascript libraries
        $headcode = $PAGE->requires->get_head_code($PAGE, $OUTPUT);

        $loadpos = strpos($headcode, 'M.yui.loader');
        $cfgpos = strpos($headcode, 'M.cfg');
        $script .= substr($headcode, $loadpos, $cfgpos - $loadpos);
        // And finally the initalisation calls for those libraries
        $endcode = $PAGE->requires->get_end_code();
//        echo preg_replace('/<\/?(script|link)[^>]*>/', '', $endcode); "<br />";
//        $script .= preg_replace('/<\/?(script|link)[^>]*>/', '', $endcode);
        $script .= $endcode;

        $return['html'] = $html;
        $return['script'] = $script;
        
        echo json_encode($return);
        die('');
    }

    /**
     * Delete activity.
     */
    public function delete_action() {
        if (!\wa_learning_path\lib\has_capability('deleteactivity')) {
            return $this->no_access('deleteactivity');
        }

        $id = optional_param('id', null, PARAM_INT);
        \wa_learning_path\lib\load_model('activity');

        try {
            \wa_learning_path\model\activity::delete($id);
        } catch (\Exception $e) {
            echo $this->display_error($e->getMessage(), 'error');
            die;
        }

        $this->set_flash_massage('success', $this->get_string('activity_delete_success'));
        redirect(new moodle_url('?c=activity'));
    }

    /**
     * View Activity.
     */
    public function view_action() {
        global $CFG, $PAGE;

        $this->id = optional_param('id', null, PARAM_INT);
        \wa_learning_path\lib\load_model('activity');

        $this->activity = \wa_learning_path\model\activity::get($this->id);
//        var_dump($PAGE->navigation);
//        if ($home = $PAGE->navigation->find('wa_lp_learning_path_management', \global_navigation::TYPE_CUSTOM)) {
//            var_dump($home);
//            $home->remove();
//        }
//        die;
//        $PAGE->set_url(new \moodle_url($this->url, array('c' => $this->c, 'a' => 'index', 'id' => (int) $this->id)));
//        $PAGE->navbar->add($this->get_string('menu_plugin_navigation'));
//        $PAGE->navbar->add($this->get_string('header_learning_activity_management'),
//                new moodle_url($this->url, array('c' => 'activity')));
        $PAGE->navbar->add($this->activity->title);

        $PAGE->set_title($this->get_string('header_landing_page') . ': ' . $this->activity->title);
        $systemcontext = \context_system::instance();

        require_once("$CFG->libdir/filelib.php");
        $this->activity->description = \file_rewrite_pluginfile_urls($this->activity->description, 'pluginfile.php',
                $systemcontext->id, 'local_wa_learning_path', 'activity_description', (int) $this->id);

        $event = \local_wa_learning_path\event\activity_viewed::create(array(
                    'objectid' => $this->id,
                    'context' => \context_system::instance(),
        ));
        $event->trigger();

        $this->view('view');
    }

    /**
     * Set a completion data.
     */
    public function set_completion_action() {
        $this->id = optional_param('activityid', null, PARAM_INT);
        $completion = optional_param('completion', null, PARAM_BOOL);

        \wa_learning_path\lib\load_model('activity');

        $s = \wa_learning_path\model\activity::set_completion($this->id, (int) $completion);

        if ($s == 0) {
            $this->set_flash_massage('success', $this->get_string('activity_completion_unset_success'));
        } else {
            $this->set_flash_massage('success', $this->get_string('activity_completion_set_success'));
        }
        die('OK: ' . $s);
    }

}
