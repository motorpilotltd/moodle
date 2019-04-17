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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

namespace mod_dsa\task;

use core\task\adhoc_task;
use mod_dsa\apiclient;

defined('MOODLE_INTERNAL') || die();

class resynccourse extends adhoc_task {
    public $apiclient;

    public function __construct() {
        $this->apiclient = apiclient::getapiclient();
    }

    public function execute() {
        $data = $this->get_custom_data();
        $this->apiclient->sync_course($data->courseid);
    }
}
