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
 * @package   block_arup_css
 */
class block_arup_css extends block_base {

    protected $_options = array(
        'h2colour' => '.course-content ul li.section h2 { color: [[colour]] !important; }',
        'h3colour' => '.course-content ul li.section h3 { color: [[colour]] !important; }',
        'h4colour' => '.course-content ul li.section h4:not(.avheading) { color: [[colour]] !important; }',
        'h5colour' => '.course-content ul li.section h5 { color: [[colour]] !important; }',
        'sectionlinecolour' => '.course-content ul li.section.main { border-bottom: 1px solid [[colour]]; }',
        'toplinecolour' => '#maincontent { border-top: 1px solid [[colour]]; }'
    );

    public $canselect;
    public $canedit;

    public function init() {
        $this->title = get_string('pluginname', 'block_arup_css');
    }

    public function specialization() {
        $this->canselect = has_capability('block/arup_css:canselectcss', $this->context);
        $this->canedit = has_capability('block/arup_css:caneditcss', $this->context);
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_can_be_docked() {
        return false;
    }

    public function get_content() {
        // Empty if can display CSS.
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (isset($this->config->css)) {
            if ($this->page->state <= moodle_page::STATE_PRINTING_HEADER) {
                $this->page->requires->css($this->_get_cached_css_url());
            } else if (has_capability('moodle/block:edit', $this->context)) {
                $this->content->text = html_writer::tag('p', get_string('cannotusecss', 'block_arup_css'));
            }
        }

        return $this->content;
    }

    public function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);

        foreach ($this->_options as $key => $value) {
            if (!$this->canselect) {
                unset($config->{$key});
            }
            if (!isset($config->{$key}) && !empty($this->config->{$key})) { // Only retain previous if not in form (as may be resetting to default).
                $config->{$key} = $this->config->{$key};
            }
        }
        if (!$this->canedit) {
            unset($config->css);
        }
        if (!isset($config->css) && !empty($this->config->css)) {
            $config->css = $this->config->css;
        }

        $this->_cache_css($this->_generate_css($config));
        parent::instance_config_save($config, $nolongerused);
    }

    public function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_arup_css');
        return true;
    }

    protected function _get_cached_css_url() {
        $fs = get_file_storage();
        $file = $fs->get_file($this->context->id, 'block_arup_css', 'css', 0, '/', 'arup_css.css');
        if (!$file) {
            $file = $this->_cache_css($this->_generate_css($this->config));
        }

        return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
                );
    }

    protected function _cache_css($css) {
        $cssfile = array(
            'contextid' => $this->context->id,
            'component' => 'block_arup_css',
            'filearea' => 'css',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'arup_css.css',
            'timecreated' => time(),
            'timemodified' => time()
        );

        $fs = get_file_storage();
        $file = $fs->get_file(
            $cssfile['contextid'], $cssfile['component'], $cssfile['filearea'], $cssfile['itemid'], $cssfile['filepath'], $cssfile['filename']
        );
        if ($file) {
            $file->delete();
        }

        return $fs->create_file_from_string($cssfile, $css);
    }

    protected function _generate_css($config) {
        $css = '';

        foreach ($this->_options as $key => $value) {
            if (!empty($config->{$key})) {
                $css .= str_replace('[[colour]]', $config->{$key}, $value);
                $css .= "\n";
            }
        }
        if (!empty($config->css)) {
            $css .= $config->css;
        }

        return $css;
    }
}
