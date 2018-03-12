<?php

use block_my_cohort_cert\my_cohort_cert;

/**
 *
 * @package    block_my_cohort_cert
 */

/**
 * my_cohort_cert block
 */
class block_my_cohort_cert extends block_base {

    /**
     * Block initialization
     */
    public function init() {
        $this->title = get_string('header', 'block_my_cohort_cert');
    }

    function get_required_javascript() {
        parent::get_required_javascript();
        $arguments = array(
            'instanceid' => $this->instance->id,
            'adminnodeid' => null
        );
        $this->page->requires->js_call_amd('block_my_cohort_cert/mycohortcertblock', 'init', $arguments);
    }


    /**
     * Return contents of block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $PAGE;

        if($this->content !== NULL) {
            return $this->content;
        }

        $renderer = $PAGE->get_renderer('block_my_cohort_cert');

        $this->content = new stdClass();

        $data = my_cohort_cert::get_data();

        $this->content->text = $renderer->show_tree($data);
        $this->content->footer = '';

        return $this->content;
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
