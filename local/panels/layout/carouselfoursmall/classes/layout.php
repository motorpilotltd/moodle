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

namespace panellayout_carouselfoursmall;

class layout extends \local_panels\layout {
    public function getzonecount() {
        return 5;
    }

    public function zonecantakearray($zonenumber) {
        return $zonenumber == 0;
    }

    public function getzonesize($zonenumber) {
        if ($zonenumber == 0) {
            return self::ZONESIZE_LARGE;
        } else {
            return self::ZONESIZE_SMALL;
        }
    }

    public function render($data) {
        global $OUTPUT;

        $data->multislides = is_array($data->zone0content) && count($data->zone0content) > 1;
        $data->slides = [];
        $i = 0;

        if (!is_array($data->zone0content)) {
            $data->zone0content = [$data->zone0content];
        }

        foreach ($data->zone0content as $dataitem) {
            $data->slides[] = [
                    'content'  => $dataitem,
                    'slidenum' => $i,
                    'active'   => $i == 0 ? 'active' : ''
            ];
            $i++;
        }

        return $OUTPUT->render_from_template("panellayout_carouselfoursmall/layout", $data);
    }
}