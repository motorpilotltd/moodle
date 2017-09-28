<?php

/**
 * Main plugin file.
 *
 * @package     block_wa_learning_path_nav
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk) 
 */

namespace wa_learning_path_nav;

require_once("../../config.php");


chdir(dirname(__FILE__));
require_once("lib/base_controller.class.php");

$controllername = optional_param('c', 'admin', PARAM_FILE); // Controller name.
$actionname = optional_param('a', 'index', PARAM_TEXT); // Action name.

require_login();

$context = \context_system::instance();

require_capability('block/wa_learning_path_nav:manage', \context_system::instance());

$PAGE->set_context($context);

// Load lib.
require_once("lib/lib.php");

if(!\wa_learning_path_nav\lib\is_ajax()) {
    // Include jQuery script and css UI.
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');
    // Include a main JS library: wa_lib.
    $PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/blocks/wa_learning_path_nav/css/backend.css'));
}

if (!file_exists("controllers/" . $controllername . ".class.php")) {
    throw new \Exception('Unknown controller');
}

require_once("controllers/" . $controllername . ".class.php");
$controllername = "\\wa_learning_path_nav\\controller\\" . $controllername;
$controller = new $controllername;

// It will throw an exceptiopn if there isn't the action method.
$controller->{$actionname . '_action'}();

