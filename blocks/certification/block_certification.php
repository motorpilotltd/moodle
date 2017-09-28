<?php

use block_certification\certification;

/**
 * Course overview block
 *
 * @package    block_certification
 */

/**
 * certification block
 */
class block_certification extends block_base {

    /**
     * Block initialization
     */
    public function init() {
        $this->title = get_string('header', 'block_certification');
    }

    /**
     * Return contents of block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $PAGE;
        $PAGE->requires->css(new moodle_url('/blocks/certification/styles/certification.css'));

        if($this->content !== NULL) {
            return $this->content;
        }

        $view = $this->get_view();

        $renderer = $PAGE->get_renderer('block_certification');
        $certifications = certification::get_data($view->type);

        $this->content = new stdClass();
        $this->content->text = $renderer->show_certifications($certifications, $view);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Sets/Gets which view is wanted.
     *
     * @global stdClass $SESSION
     * @return stClass
     */
    private function get_view() {
        global $SESSION;
        if (!isset($SESSION->block_certification) || !is_array($SESSION->block_certification)) {
            $SESSION->block_certification = [];
        }
        if (empty($SESSION->block_certification[$this->instance->id])) {
            $SESSION->block_certification[$this->instance->id] = new stdClass();
        }
        $type = optional_param('blockcertificationview', null, PARAM_ALPHA);
        if (empty($SESSION->block_certification[$this->instance->id]->view) || !is_null($type)) {
            $view = new stdClass;
            $view->type = $type;
            $view->url = $this->page->url;
            if ($view->type !== 'cohort') {
                $view->url->params(['blockcertificationview' => 'cohort']);
            } else {
                $view->url->params(['blockcertificationview' => 'category']);
            }
            $SESSION->block_certification[$this->instance->id]->view = $view;
        }
        return $SESSION->block_certification[$this->instance->id]->view;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

}
