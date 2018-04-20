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
 * coursemetadatafield_arup field.
 *
 * @package    coursemetadatafield_arup
 * @copyright  Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class coursemetadatafield_arup_renderer extends plugin_renderer_base {
    /**
     * Renderers info to course page.
     *
     * @param \coursemetadatafield_arup\arupmetadata $arupadvert
     * @param stdClass $info
     * @return string
     */
    public function info_view($arupadvert) {
        if (empty($arupadvert->display)) {
            return '';
        }

        return $this->render_from_template('coursemetadatafield_arup/infoview', $arupadvert->export_for_template($this));
    }
}