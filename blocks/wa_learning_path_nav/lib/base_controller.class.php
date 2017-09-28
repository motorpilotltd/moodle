<?php

/**
 * wa_learning_path_nav module
 * 
 * @package     block_wa_learning_path_nav
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk) 
 */

namespace wa_learning_path_nav\lib;

defined('MOODLE_INTERNAL') || die;

/**
 * Base controller
 * 
 * @package     block_wa_learning_path_nav
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
class base_controller {

    /**
     * Controller class name
     * @var String
     */
    public $controller;

    /**
     * Controller name, getting from routing.
     * @var String
     */
    public $a;

    /**
     * Action name, getting from routing.
     * @var String
     */
    public $c;

    /**
     * Base Url to plugin.
     * @var String
     */
    public $url;

    /**
     * Base controller constructor
     */
    public function __construct() {
        global $PAGE;
        // Set class name without namespaces.
        $this->controllername = (new \ReflectionClass($this))->getShortName();
        $this->url = new \moodle_url('/blocks/wa_learning_path_nav/', array('c' => $this->controllername));
        $this->isajax = \wa_learning_path_nav\lib\is_ajax();

        $this->c = optional_param('c', 'main', PARAM_FILE); // Controller name.
        $this->a = optional_param('a', 'index', PARAM_FILE); // Action name.

        $PAGE->set_pagelayout('standard');
        $PAGE->set_url(new \moodle_url($this->url, array('c' => $this->c, 'a' => $this->a)));
        $PAGE->set_title($this->get_string('title'));

        $strplural = $this->get_string('pluginname');

        $PAGE->set_title($strplural);
        $PAGE->set_heading($strplural);
        $PAGE->set_pagelayout('admin');
    }

    /**
     * Return translated text from this module
     * @param string $text
     * @return string
     */
    public function get_string($text) {
        return get_string($text, 'block_wa_learning_path_nav');
    }

    /**
     * Render view: Header + Conent + Footer. If request is AJAX render only Content, without Header and Footer.
     * @param string $template
     * @param string $controller
     */
    public function view($template, $controller = null) {
        global $OUTPUT;
        if (!isset($controller)) {
            $controller = $this->controllername;
        }

        // Show Moodle header if it is not ajax.
        if (!\wa_learning_path_nav\lib\is_ajax()) {
            echo $OUTPUT->header();
        }

        require("view" . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $template . ".php");

        // Finish the page.
        if (!\wa_learning_path_nav\lib\is_ajax()) {
            echo $OUTPUT->footer();
        }
    }

    /**
     * Returns a flash messages from type
     * @param String Type of message (message, error)
     * @return Array|String
     */
    public function get_flash_massage($type) {
        global $SESSION;

        if (!isset($SESSION->flash_messages[$type])) {
            return null;
        }
        $msg = $SESSION->flash_messages[$type];

        $this->clear_flash_massage($type);
        return $msg;
    }

    /**
     * Set a flash message by type
     * @param String Type of message (message, error, ...)
     * @param String Content of Message
     */
    public function set_flash_massage($type, $message) {
        global $SESSION;
        $SESSION->flash_messages[$type][] = $message;
    }

    /**
     * Clear all flash message by type
     * @param String Type of message (message, error, ...). If null clear all of types messages.
     */
    public function clear_flash_massage($type = null) {
        global $SESSION;
        if (is_null($type)) {
            unset($SESSION->flash_messages);
        } else {
            unset($SESSION->flash_messages[$type]);
        }
    }

    /**
     * Show system error in
     * @param String|Array Error to show
     */
    public function display_error($error, $type) {
        global $OUTPUT;
        if (empty($error)) {
            return false;
        }

        $content = '';
        switch ($type) {
            case 'error':
                if (is_array($error)) {
                    foreach ($error as $e) {
                        echo \html_writer::div($OUTPUT->error_text($e), 'alert alert-' . $type);
                    }
                } else {
                    echo \html_writer::div($OUTPUT->error_text($error), 'alert alert-' . $type);
                }
                break;
            case 'success':
                $content = is_array($error) ? implode('<br />', $error) : $error;
                if (is_array($error)) {
                    foreach ($error as $e) {
                        echo \html_writer::div($e, 'alert alert-' . $type);
                    }
                } else {
                    echo \html_writer::div($error, 'alert alert-' . $type);
                }
                break;

            default:
                $content = is_array($error) ? implode('<br />', $error) : $error;
                echo \html_writer::div($content, 'alert alert-' . $type);
                break;
        }
    }

    /**
     * Get table header using for sortable.
     * 
     * @global type $OUTPUT
     * @param type $field
     * @param type $fieldname
     * @param type $sort
     * @param type $dir
     * @param type $controller
     * @param type $extraurlparams
     * @return string
     */
    public function get_table_header($field, $fieldname, &$sort, &$dir, $controller = 'user', $extraurlparams = 'index') {
        global $OUTPUT;

        if ($sort != $field) {
            $fieldicon = "";
            if ($field == "lastaccess") {
                $fielddir = "DESC";
            } else {
                $fielddir = "ASC";
            }
        } else {
            $fielddir = $dir == "ASC" ? "DESC" : "ASC";
            if ($field == "lastaccess") {
                $fieldicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
            } else {
                $fieldicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
            }
            $fieldicon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . $fieldicon) . "\" alt=\"\" />";
        }
        $return = "<a class='sort-link' "
                . "href=\"?c=" . $controller . "&amp;a=" . $extraurlparams . "&amp;sort=$field&amp;dir=$fielddir\">"
                . $fieldname . "</a>$fieldicon";

        return $return;
    }

    /**
     * Call a method dynamically
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        } else {
            throw new \Exception(sprintf('The action "%s" does not exist for %s', str_replace('_action', '', $method),
                    get_class($this)));
        }
    }

}
