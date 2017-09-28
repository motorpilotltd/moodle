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

require_once($CFG->dirroot.'/blocks/arup_mylearning/tabs.php');
require_once($CFG->dirroot.'/blocks/arup_mylearning/content.php');

class block_arup_mylearning extends block_base {

    protected $_currenttab;
    protected $_defaulttab = 'overview';
    protected $_allowedtabs = array(
        'overview', 'myteaching', 'myhistory', 'halogen'
    );
    protected $_maskedtabs = array(
        'halogen' => 'myhistory'
    );
    protected $_tabs;
    protected $_content;

    protected $_renderer;

    public function applicable_formats() {
        return array('my' => true);
    }

    public function init() {
        $this->title   = get_string('pluginname', 'block_arup_mylearning');
        $this->_currenttab = optional_param('tab', $this->_defaulttab, PARAM_ALPHA);
    }

    public function specialization() {
        $this->title = '';

        $this->_content = new block_arup_mylearning_content($this->instance->id);

        foreach ($this->_allowedtabs as $index => $allowedtab) {
            if (!$this->_content->has_content($allowedtab)) {
                unset($this->_allowedtabs[$index]);
            }
        }

        if (!in_array($this->_currenttab, $this->_allowedtabs)) {
            $this->_currenttab = $this->_defaulttab;
        }

        $this->_tabs = new block_arup_mylearning_tabs($this->_allowedtabs, $this->_maskedtabs);
    }

    public function instance_can_be_docked() {
        return false;
    }

    public function get_content() {
        global $CFG, $PAGE, $SESSION;

        if ($this->content !== null) {
            return $this->content;
        }

        $PAGE->requires->js_call_amd('block_arup_mylearning/enhance', 'initialise');

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $this->content->text .= html_writer::start_tag('div', array('id' => 'block_arup_mylearning_tabs', 'class' => 'block_arup_mylearning_tabs'));
        foreach ($this->_allowedtabs as $tab) {
            $hide = ($tab != $this->_currenttab);
            $this->content->text .= $this->_tabs->get_tab_html($tab, $hide);
        }
        $this->content->text .= html_writer::end_tag('div');

        if (isset($SESSION->block_arup_mylearning->alert)) {
            $this->content->text .= $this->_content->renderer->alert($SESSION->block_arup_mylearning->alert->message, $SESSION->block_arup_mylearning->alert->type);
            unset($SESSION->block_arup_mylearning->alert);
        }

        $this->content->text .= html_writer::start_tag('div', array('id' => 'block_arup_mylearning_content', 'data-instance' => $this->instance->id));
        foreach ($this->_allowedtabs as $tab) {
            if ($tab == $this->_currenttab) {
                $this->content->text .= html_writer::tag(
                        'div',
                        $this->_content->get_content($tab),
                        array('id' => 'block_arup_mylearning_content_'.$tab));
            } else {
                $image = html_writer::empty_tag('img', array('src' => $CFG->wwwroot.'/blocks/arup_mylearning/pix/loader.gif'));
                $this->content->text .= html_writer::tag('div', $image, array('id' => 'block_arup_mylearning_content_'.$tab, 'class' => 'hidden'));
            }
        }
        $this->content->text .= html_writer::end_tag('div');

        return $this->content;
    }

    public function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);
        parent::instance_config_save($config, $nolongerused);
    }

    public function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_arup_mylearning');
        return true;
    }
}
