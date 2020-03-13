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
 * panellayout_mymodules layout.
 *
 * @package    panellayout_mymodules
 * @copyright  Arup
 * @author     Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace panellayout_mymodules;
use coursemetadatafield_arup\arupmetadata;

class layout extends \local_panels\layout {
    public function getzonecount() {
        return 0;
    }

    public function zonecantakearray($zonenumber) {
        return false;
    }

    public function getzonesize($zonenumber) {
        return self::ZONESIZE_SMALL;
    }

    public function render($data) {
        global $OUTPUT;

        // Reset the data object
        $data = new \stdClass();
        $data->courses = [];

        // Fetch all enrolled courses
        $enrolledcourses = enrol_get_my_courses(null, 'visible DESC, fullname ASC');

        // Get the course metadata
        foreach ($enrolledcourses as $course) {
            $arupmetadata = arupmetadata::fetch(['course' => $course->id]);

            if (empty($arupmetadata)) {
                continue;
            }

            $courseinfo = $arupmetadata->export_for_template($OUTPUT);
            $data->courses[] = $OUTPUT->render_from_template("datasource_course/small", $courseinfo);
        }

        return $OUTPUT->render_from_template("panellayout_mymodules/layout", $data);
    }
}