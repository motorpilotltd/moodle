<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die();

abstract class local_regions_controller {
    protected $regions;
    protected $action = 'index';
    protected $request = null;
    protected $view;
    protected $pagename = '';
    protected $page;

    public function __construct($action, $request, local_regions $base) {
        global $PAGE;

        $this->page = $PAGE;
        $this->action = $action;
        $this->request = $request;
        $this->regions = $base;
        $this->regions->load_file('lib/view.php');
        $this->view = new local_regions_view($this->regions);
    }

    /**
     * Run controller action and render its view
     *
     * @return void
     */
    public final function run() {
        // Check if action exists.
        $actionname = $this->action . '_action';
        if (!method_exists($this, $actionname)) {
            print_error('invalidpage');
        }

        // Setup admin page and check page permission.
        admin_externalpage_setup($this->pagename);
        $sitecontext = context_system::instance();
        // You do not have the required permission to access this page.
        if (!has_capability('moodle/site:config', $sitecontext)) {
            print_error('pagepermission');
        }

        // Call the action.
        $this->{$actionname}();
        // Render request view.
        $this->view->render($this->request);
    }

    /**
     * Load a file
     *
     * @param string $file
     * @return mix
     */
    protected function load_file($file) {
        return $this->regions->load_file($file);
    }

    /**
     * Load a model class
     *
     * @param string $name
     * @return local_regions_model
     */
    protected function model($name) {
        return $this->regions->model($name);
    }

    /**
     * Load a form class
     *
     * @param string $name
     * @param array $options
     * @return local_regions_form
     */
    protected function form($name, $options = array()) {
        $CFG = $this->get_config();
        require_once($CFG->libdir . '/formslib.php');
        $this->load_file('lib/form.php');
        $this->load_file('forms/' . $name . '.php');
        $defaultoptions = array(
            'action' => '',
            'method' => 'post',
            'target' => '',
            'attribs' => null,
            'editable' => true
        );
        $options = array_merge($defaultoptions, $options);

        $class = 'local_regions_form_' . $name;
        return new $class(
                $options['action'],
                array('regions' => $this->regions, 'view' => $this->view),
                $options['method'],
                $options['target'],
                $options['attribs'],
                $options['editable']
                );
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string($name, $a = null) {
        return $this->regions->get_string($name, $a);
    }

    /**
     * JSON Encode for array or object
     *
     * @param array|object $data
     * @param boolean $error
     * @param boolean $exist
     * @return string
     */
    public function ajax_return($data, $error = false, $exist = true) {
        if (!is_object($data)) {
            $data = (object) $data;
        }
        $data->error = $error ? 1 : 0;
        if ($exist) {
            echo json_encode((array) $data);
            die;
        }
        return json_encode((array) $data);
    }

    /**
     * Check if the current request is ajax
     *
     * @return boolean
     */
    public function is_ajax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    /**
     * Add Javascript file to <head>
     *
     * @param array $scripts
     * @return mix
     */
    protected function head_script(array $scripts) {
        foreach ($scripts as $script) {
            $this->page->requires->js($script);
        }
    }

    /**
     * Add CSS file to the <head>
     *
     * @param array $links
     * @return void
     */
    protected function head_link(array $links) {
        foreach ($links as $link) {
            $this->page->requires->css($link);
        }
    }

    protected function get_user() {
        return $this->regions->get_user();
    }

    protected function get_course() {
        return $this->regions->get_course();
    }

    protected function get_config($name = null) {
        return $this->regions->get_config($name);
    }
}
