<?php

/**
 * Main plugin file.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

namespace wa_learning_path;

require_once("../../config.php");


chdir(dirname(__FILE__));
require_once("lib/base_controller.class.php");

$controllername = optional_param('c', 'admin', PARAM_FILE); // Controller name.
$actionname = optional_param('a', 'index', PARAM_TEXT); // Action name.

require_login();

$context = \context_system::instance();

$PAGE->set_context($context);

// Load lib.
require_once("lib/lib.php");

if(!\wa_learning_path\lib\is_ajax()) {
    // Include jQuery script and css UI.
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');
    $PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/local/wa_learning_path/css/style.css'));
}

if (!file_exists("controllers/" . $controllername . ".class.php")) {
    throw new \Exception('Unknown controller');
}

require_once("controllers/" . $controllername . ".class.php");
$controllername = "\\wa_learning_path\\controller\\" . $controllername;
$controller = new $controllername;

// It will throw an exceptiopn if there isn't the action method.
$controller->{$actionname . '_action'}();
