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

abstract class local_regions_form extends moodleform {
    protected $regions;
    protected $view;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        if (is_array($customdata)) {
            $this->set_customdata($customdata);
            $customdata = '';
        }
        return parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     * Set custom data
     *
     * @param array $data
     * @return region_form
     */
    private function set_customdata(array $data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string($name, $a = null) {
        return $this->regions->get_string($name, $a);
    }

    /**
     * Get language string from moodle core language
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string_fromcore($name, $a = null) {
        return $this->regions->get_string_fromcore($name, $a);
    }

    /**
     * Return an array of all error messages
     *
     * @return array
     */
    public function get_errors() {
        $errors = array();
        $elements = $this->_form->_elements;
        foreach ($elements as $element) {
            $error = $this->_form->getElementError($element->getName());
            if ($error != '') {
                $errors[$element->getLabel()] = $error;
            }
        }
        return $errors;
    }
}