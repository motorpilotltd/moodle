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
 * @package    filter_formattimestamp
 * @copyright  2017 onwards Andrew Hancox (andrewdchancox@googlemail.com) on behalf of Ove Arup & Partners International Limited <https://www.arup.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_formattimestamp extends moodle_text_filter {
    function filter($text, array $options = array()) {

        if (empty($text) or is_numeric($text)) {
            return $text;
        }

        if (strpos($text, 'formattimestamp') === false) { // The regex is pretty gnarly so lets try to skip it if possible.
            return $text;
        }

        $hideforrolesearch = '/<span\s+class="formattimestamp(_([0-9a-zA-Z\/]+)){0,1}"\s*>([0-9]*)<\/span>/ims';
        $result = preg_replace_callback($hideforrolesearch, [$this, 'dofiltering'], $text);

        if (is_null($result)) {
            return $text; // Something went wrong when doing regex.
        } else {
            return $result;
        }
    }

    function dofiltering($block) {
        $tz = empty($block[2]) ? 99 : $block[2];
        $timestamp = $block[3];

        if (empty($timestamp)) {
            return '';
        }

        return userdate($timestamp, '', $tz);
    }
}


