<?php
// This file is part of the Arup Course Management system
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
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;

class actions extends base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->addcourse = true;
        $page = $this->coursemanager->get_current_pageobject();
        if ($page->add && $this->coursemanager->cmcourse->id > 0) {
            $data->addcourseurl = new moodle_url('/local/taps/addcourse.php',
                array('courseid' => $this->coursemanager->cmcourse->courseid));
        } else {
            $data->addcourse = false;
        }
        return $data;
    }

}