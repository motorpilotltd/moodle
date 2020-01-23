<?php

namespace local_reportbuilder\form;

require_once($CFG->libdir.'/formslib.php');

use moodleform;

class formaccesshack extends moodleform {

    public function definition() {
        // TODO: Implement definition() method.
    }

    /**
     * @param moodleform $form
     */
    public static function getform($form) {
        return $form->_form;
    }
}