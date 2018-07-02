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
 * @copyright  2017 onwards Andrew Hancox (andrewdchancox@googlemail.com) on behalf of Ove Arup & Partners International Limited
 *         <https://www.arup.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/formattimestamp/filter.php');

class filter_formattimestamp_testcase extends advanced_testcase {

    function test_filter_formattimestamp_link() {
        $this->resetAfterTest(true);

        $filterplugin = new filter_formattimestamp(null, array());

        // Timezone and format.
        $filtered = $filterplugin->filter('<span class="formattimestamp_format_strftimerecentfull">1510656103 America/Port-au-Prince</span>');
        $this->assertEquals('Tue, 14 Nov 2017, 5:41 AM', $filtered);

        // Format but no timezone.
        $filtered = $filterplugin->filter('<span class="formattimestamp_format_strftimerecentfull">1510656103</span>');
        $this->assertEquals('Tue, 14 Nov 2017, 6:41 PM', $filtered);

        // Timezone with no spaces.
        $filtered = $filterplugin->filter('<span class="formattimestamp">1510656103America/Port-au-Prince</span>');
        $this->assertEquals('Tuesday, 14 November 2017, 5:41 AM', $filtered);

        // Just the timestamp.
        $filtered = $filterplugin->filter('<span class="formattimestamp">1510656103</span>');
        $this->assertEquals('Tuesday, 14 November 2017, 6:41 PM', $filtered);
    }
}
