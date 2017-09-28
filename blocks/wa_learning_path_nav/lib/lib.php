<?php

/**
 * Library
 * 
 * @package     block_wa_learning_path_nav
 * @author		Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

namespace wa_learning_path_nav\lib;

/**
 * Include model class.
 */
function load_model($name = null) {
    global $CFG;

    if (!isset($name)) {
        $name = 'main';
    }

    // Autoloading model class.
    $model = $CFG->dirroot . '/blocks/wa_learning_path_nav/' . 'model' . DIRECTORY_SEPARATOR . $name . '.class.php';

    if (file_exists($model)) {
        require_once($model);
        return true;
    }

    throw new \Exception('Model ' . $name . ' does not exists');
}

/**
 * Include form class.
 */
function load_form($name = null) {
    global $CFG;
    if (!isset($name)) {
        $name = 'main';
    }

    // Autoloading model class.
    $form = $CFG->dirroot . '/blocks/wa_learning_path_nav/' . 'form' . DIRECTORY_SEPARATOR . $name . '.class.php';
    if (file_exists($form)) {
        require_once($form);
        $form = "\\wa_learning_path_nav\\form\\" . $name . "_form";
        return new $form;
    }

    throw new \Exception('Form ' . $form . ' for ' . $name . " does not found");
}

/**
 * Return true if request is AJAX.
 * @return boolen
 */
function is_ajax() {
    return strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
}

