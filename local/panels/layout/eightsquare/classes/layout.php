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

namespace panellayout_eightsquare;

class layout extends \local_panels\layout {
    public function getzonecount() {
        return 8;
    }

    public function zonecantakearray($zonenumber) {
        return false;
    }

    public function getzonesize($zonenumber) {
        return self::ZONESIZE_SMALL;
    }

    public function render($data) {
        global $OUTPUT;

        return $OUTPUT->render_from_template("panellayout_eightsquare/layout", $data);
    }
}