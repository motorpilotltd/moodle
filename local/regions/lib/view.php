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

class local_regions_view {
    protected $regions;
    protected $layout = true;
    protected $data = array();
    protected $output;

    public function __construct(local_regions $base) {
        global $OUTPUT;

        $this->regions = $base;
        $this->data['version'] = $this->regions->get_version();
        $this->output = $OUTPUT;
    }

    /**
     * Disabled layout
     *
     * @return local_regions_view
     */
    public function disable_layout() {
        $this->layout = false;
        return $this;
    }

    /**
     * Render view
     *
     * @param string $view
     * @return void
     */
    public function render($view) {
        if ($this->layout) {
            echo $this->output->header();
        }

        if ($this->pageheading != '') {
            echo $this->output->heading($this->pageheading, 2);
        }

        require_once($this->regions->get_basedir('views/' . $view . '.php'));

        if ($this->layout) {
            echo $this->output->footer();
        }
    }

    /**
     * Render partial view and turn it's content
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render_partial($view, array $data = array()) {
        $this->merge_view_data($data);
        ob_start();
        require($this->regions->get_basedir('views/' . $view . '.php'));
        return ob_get_clean();
    }

    /**
     * Merge new data with the existing one
     *
     * @param array $data
     * @return void
     */
    protected function merge_view_data($data) {
        if (!empty($data) && is_array($data)) {
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string($name, $a= null) {
        return $this->regions->get_string($name, $a);
    }

    /**
     * Get language string from moodle core language
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string_fromcore($name, $a= null) {
        return $this->regions->get_string_fromcore($name, $a);
    }

    /**
     * Returns the name of the current theme
     *
     * @return string
     */
    protected function get_themename() {
        global $PAGE;

        return $PAGE->theme->name;
    }

    /**
     * Get base url of the plugin
     *
     * @param string $path
     * @return string
     */
    public function base_url($path = '', $relative = false) {
        if ($relative) {
            return '/local/regions/' . $path;
        }
        return $this->get_config()->wwwroot . '/local/regions/' . $path;
    }

    /**
     * Get Moodle base url
     *
     * @param String $path
     * @return string
     */
    public function core_url($path = '') {
        return $this->get_config()->wwwroot . '/' . $path;
    }

    /**
     * Print table
     *
     * @param array|object $table
     */
    public function print_table($table) {
        return html_writer::table($table);
    }

    /**
     * Print pagination bar
     *
     * @param int $count
     * @param int $page
     * @param int $perpage
     * @param string $url
     */
    public function print_paging_bar($count, $page = 1, $perpage = 20, $url = '') {
        $url .= "?perpage=" . $perpage . "&amp;";
        return $this->output->paging_bar($count, $page, $perpage, $url);
    }

    /**
     * Format text
     *
     * @param string $text
     * @param int    $type
     * @return string
     */
    public function textformat($text, $type = FORMAT_HTML) {
        return format_text($text, $type);
    }

    /**
     * Format date
     *
     * @param string $date
     * @return string
     */
    public function dateformat($date, $format = '') {
        return userdate($date, $format);
    }

    /**
     * Format status from integer to string
     *
     * @param int $status
     * @return string
     */
    public function statusformat($status) {
        if ($status == 1) {
            return $this->get_string('enabled');
        }
        return $this->get_string('disabled');
    }

    /**
     * Return select menu
     *
     * @param string $name
     * @param array $options
     * @param array $attribs
     * @return string
     */
    protected function form_select($name, array $options, array $attribs = array()) {
        $defaultoptions = array(
            'nothing' => '',
            'script' => '',
            'nothingvalue' => '',
            'disabled' => false,
            'tabindex' => 0,
            'listbox' => false,
            'multi' => false,
            'class' => '',
            'selected' => ''
        );
        $attribs = array_merge($defaultoptions, $attribs);

        $attributes = array();
        $attributes['disabled'] = $attribs['disabled'];
        $attributes['tabindex'] = $attribs['tabindex'];
        $attributes['multi'] = $attribs['multi'];
        $attributes['class'] = $attribs['class'];
        $attributes['id'] = $name;

        return html_writer::select($options, $name, $attribs['selected'], array($attribs['nothingvalue'] => $attribs['nothing']), $attributes);
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

    public function __get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __isset($key) {
        if (isset($this->$key)) {
            return true;
        }
        if (isset($this->data[$key])) {
            return true;
        }
        return false;
    }
}
